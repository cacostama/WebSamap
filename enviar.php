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

// --- Honeypot: campo oculto que un humano nunca completa ---
if (!empty($_POST['website'])) {
    // Probable bot: respondemos "ok" silenciosamente sin enviar nada.
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
    volver_con('Por favor complete nombre, correo y mensaje.', false, $destino);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    volver_con('El correo electronico ingresado no es valido.', false, $destino);
}
if (mb_strlen($mensaje) > 5000) {
    $mensaje = mb_substr($mensaje, 0, 5000);
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
    volver_con('No se pudo enviar el mensaje. Por favor intente nuevamente.', false, $destino);
}
volver_con('Su mensaje fue enviado correctamente, gracias por completar el formulario.', true, $destino);
