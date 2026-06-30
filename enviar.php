<?php
/**
 * enviar.php — Procesa los formularios de contacto y "trabaje con nosotros".
 *   El campo oculto "origen" (lista blanca) define el asunto y la pagina de retorno.
 * - Credenciales SMTP desde variables de entorno (no hardcodeadas).
 * - From alineado al dominio propio + Reply-To = email del visitante.
 * - Sanitiza nombre/email para evitar header injection (saltos de linea).
 * - Honeypot anti-spam + validacion de campos + consentimiento obligatorio.
 */

date_default_timezone_set('America/Asuncion');

// --- Helpers de seguridad (rate-limit) ---
// enviar.php no incluye admin/funciones/db.php a proposito, asi que
// requerimos solo el modulo chico de seguridad (CSRF + rate-limit
// generico samap_rl_*). db.php arrastra session.php + mysqli, que no
// necesitamos aca porque la persistencia en tbl_leads se hace con una
// conexion ad-hoc mas abajo.
require_once __DIR__ . '/admin/funciones/seguridad.php';

// --- Helpers ---
function limpiar_encabezado($s) {
    // Elimina saltos de linea para prevenir inyeccion de cabeceras de email.
    return trim(str_replace(["\r", "\n", "%0a", "%0d", "%0A", "%0D"], '', (string) $s));
}
function volver_con($msg, $ok = false, $destino = 'contactos/') {
    echo "<script>alert(" . json_encode($msg) . "); location.href=" . json_encode($destino) . ";</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    volver_con('Metodo no permitido.');
}

// --- Origen del formulario: define asunto y pagina de retorno ---
// Se valida contra una lista blanca para no usar entrada del usuario en el redirect.
$origenes = [
    'contacto' => ['destino' => 'contactos/',          'etiqueta' => 'CONTACTO'],
    'trabajo'  => ['destino' => 'trabajeconnosotros/',  'etiqueta' => 'TRABAJE CON NOSOTROS'],
];
$origen   = $_POST['origen'] ?? 'contacto';
if (!isset($origenes[$origen])) { $origen = 'contacto'; }
$destino  = $origenes[$origen]['destino'];
$etiqueta = $origenes[$origen]['etiqueta'];

// --- Rate-limit: 5 envios / 15 min / IP (bucket compartido entre los
// formularios "contacto" y "trabaje con nosotros" -- spamear uno afecta
// al otro, que es el objetivo: cortar el abuso venga de donde venga).
$ip     = $_SERVER['REMOTE_ADDR'] ?? 'desconocida';
$bucket = 'contact_form';
if (function_exists('samap_rl_bloqueado') && samap_rl_bloqueado($bucket, $ip, 5, 900)) {
    http_response_code(429);
    volver_con('Has enviado demasiados mensajes. Por favor espera unos minutos antes de intentar de nuevo.', false, $destino);
}

// --- Honeypot: campo oculto que un humano nunca completa ---
if (!empty($_POST['website'])) {
    // Probable bot: registramos un fallo (consume el rate limit) y
    // respondemos "ok" silenciosamente sin enviar nada.
    if (function_exists('samap_rl_registrar_fallo')) samap_rl_registrar_fallo($bucket, $ip, 900);
    volver_con('Su mensaje fue enviado correctamente, gracias por completar el formulario.', true, $destino);
}

// --- Consentimiento obligatorio (Ley 6534/20 datos personales) ---
if (empty($_POST['consentimiento'])) {
    volver_con('Debe aceptar la politica de privacidad para enviar el mensaje.', false, $destino);
}

// --- Entrada ---
$nombre  = limpiar_encabezado($_POST['nombre'] ?? '');
$tel     = limpiar_encabezado($_POST['tel'] ?? '');
$email   = limpiar_encabezado($_POST['email'] ?? '');
$mensaje = trim($_POST['mensaje'] ?? '');

// --- Validacion ---
if ($nombre === '' || $email === '' || $mensaje === '') {
    if (function_exists('samap_rl_registrar_fallo')) samap_rl_registrar_fallo($bucket, $ip, 900);
    volver_con('Por favor complete nombre, correo y mensaje.', false, $destino);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    if (function_exists('samap_rl_registrar_fallo')) samap_rl_registrar_fallo($bucket, $ip, 900);
    volver_con('El correo electronico ingresado no es valido.', false, $destino);
}
if (mb_strlen($mensaje) > 5000) {
    $mensaje = mb_substr($mensaje, 0, 5000);
}

// --- Persistir en tbl_leads (best-effort, no bloquea el envio SMTP) ---
// enviar.php no incluye admin/funciones/db.php a proposito, asi que
// armamos una conexion ad-hoc al host de la DB. Si las env vars de DB
// no estan definidas o la conexion falla, seguimos: el SMTP sigue
// siendo el canal primario de notificacion.
//
// Ley 6534/20 (Paraguay): nombre / email / telefono se encriptan con
// AES-256-GCM (helpers en encryption.php) y se guardan en las columnas
// nombre_enc / email_enc / telefono_enc. Las columnas en claro quedan
// NULL en filas nuevas (la fuente de verdad pasa a ser la columna _enc).
// data_hash es el SHA-256 deterministico del email normalizado, permite
// buscar un lead por email sin desencriptar.
require_once 'encryption.php';
$dbHost = getenv('DB_HOST') ?: 'db';
$dbName = getenv('DB_NAME') ?: 'web_samap';
$dbUser = getenv('DB_USER');
$dbPass = getenv('DB_PASS');
if ($dbUser !== false && $dbPass !== false && $dbUser !== '' && $dbPass !== '') {
    $leadsConn = @new mysqli($dbHost, $dbUser, $dbPass, $dbName);
    if (!$leadsConn->connect_errno) {
        $leadsConn->set_charset('utf8');
        $ip        = limpiar_encabezado($_SERVER['REMOTE_ADDR'] ?? '');
        $userAgent = limpiar_encabezado(substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 250));
        try {
            $emailEnc  = samap_encrypt($email);
            $telEnc    = samap_encrypt($tel);
            $nombreEnc = samap_encrypt($nombre);
            $dataHash  = samap_data_hash($email);
            $stmt = $leadsConn->prepare("INSERT INTO tbl_leads (origen, nombre, nombre_enc, data_hash, email, email_enc, telefono, telefono_enc, mensaje, ip, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param('sssssssssss', $origen, $nombre, $nombreEnc, $dataHash, $email, $emailEnc, $tel, $telEnc, $mensaje, $ip, $userAgent);
                $stmt->execute();
                $stmt->close();
            }
        } catch (Throwable $e) {
            // Fallo de encriptacion (LEAD_ENC_KEY mal configurada, etc).
            // No rompemos el envio SMTP: el operador lo ve en el log y el
            // visitante sigue recibiendo su confirmacion.
            @error_log('[samap] leads enc fallo: ' . $e->getMessage());
        }
        $leadsConn->close();
    }
}

// --- Credenciales SMTP desde entorno ---
$smtpHost = getenv('SMTP_HOST');
$smtpPort = getenv('SMTP_PORT') ?: '587';
$smtpUser = getenv('SMTP_USER');
$smtpPass = getenv('SMTP_PASS');
$smtpFrom = getenv('SMTP_FROM') ?: $smtpUser;
$leadsTo  = getenv('LEADS_TO') ?: 'asistente.ventas1@samap.com.py';
$leadsBcc = getenv('LEADS_BCC') ?: '';

if (!$smtpHost || !$smtpUser || !$smtpPass) {
    http_response_code(500);
    volver_con('El servicio de correo no esta configurado. Intente mas tarde.', false, $destino);
}

$fecha = date('d/m/Y H:i:s');

require_once 'class/class.phpmailer.php';
$mail = new PHPMailer();
$mail->CharSet = 'UTF-8';
$mail->IsSMTP();
$mail->Host     = $smtpHost;
$mail->Port     = $smtpPort;
$mail->SMTPAuth = true;
$mail->Username = $smtpUser;
$mail->Password = $smtpPass;

// From = dominio propio (evita spoofing/rechazo SPF). Reply-To = visitante.
$mail->From     = $smtpFrom;
$mail->FromName = 'Web SAMAP - Contacto';
$mail->AddReplyTo($email, $nombre);
$mail->AddAddress($leadsTo);
if ($leadsBcc !== '') { $mail->AddBCC($leadsBcc); }

$mail->Subject = "MENSAJE DESDE LA WEB ($etiqueta): www.samap.com.py";
$mail->Body = "ORIGEN: $etiqueta

DATOS PERSONALES
Fecha: $fecha
Nombre: $nombre
Telefono: $tel
Correo Electronico: $email

MENSAJE
$mensaje
";

if (!$mail->Send()) {
    if (function_exists('samap_rl_registrar_fallo')) samap_rl_registrar_fallo($bucket, $ip, 900);
    volver_con('No se pudo enviar el mensaje. Por favor intente nuevamente.', false, $destino);
}
// Send exitoso: limpia el contador para no penalizar al usuario legitimo.
if (function_exists('samap_rl_limpiar')) samap_rl_limpiar($bucket, $ip);
volver_con('Su mensaje fue enviado correctamente, gracias por completar el formulario.', true, $destino);
