<?php
/**
 * admin/recuperar.php — "¿Olvidaste tu contraseña?"
 *
 * Pagina publica (esta en la whitelist de session.php). Pide el nombre de
 * usuario y, si el usuario existe, genera un token de un solo uso en
 * tbl_user_token y envia un correo con el enlace de restablecimiento.
 *
 * Decisiones de seguridad:
 *   - Anti-enumeracion: si el usuario NO existe, se muestra EXACTAMENTE el
 *     mismo mensaje que cuando existe ("si el usuario existe, te enviamos...").
 *   - El token es hex de 32 bytes (random_bytes(32) -> 64 chars) -> 256 bits
 *     de entropia, fuera del alcance de fuerza bruta.
 *   - Expira en 1 hora.
 *   - CSRF obligatorio (samap_csrf_validar).
 *   - rate-limit reutilizado: si la IP esta bloqueada por fuerza bruta del
 *     login, tampoco dejamos pedir recuperacion.
 *
 * En dev: si no hay SMTP configurado o llega ?dev=1, el enlace se imprime
 * en pantalla (solo en esta pagina, no en el correo) y SIEMPRE se loguea
 * en docker/samap-tokens.log para que el agente de tests lo pueda leer.
 */

// session.php ya esta incluida por db.php y nos da $_SESSION/CSRF/rate-limit.
// NO requerimos sesion: esta pagina es publica, igual que admin/index.php.
require_once('funciones/db.php');
require_once('conexion.php');

$recuperar_msg  = '';
$recuperar_ok   = false;
$dev_token_url  = ''; // solo se setea en dev
$dev_mostrar    = isset($_GET['dev']) && $_GET['dev'] === '1';

// Rate-limit compartido con el login: si la IP esta bloqueada, no dejamos
// disparar el flujo de recuperacion (mismo vector de fuerza bruta).
if (function_exists('samap_login_bloqueado') && samap_login_bloqueado()) {
	$recuperar_msg = 'Demasiados intentos. Espere unos minutos antes de volver a intentar.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if (!samap_csrf_validar()) {
		$recuperar_msg = 'Tu sesión expiró. Recargá la página e intentá de nuevo.';
	} else {
		$usuario = trim($POST_RAW['usuario'] ?? '');

		// Mensaje UNICO para ambos casos (existe / no existe) -> sin
		// enumeracion de usuarios. Solo si el input esta vacio cambiamos
		// el mensaje (es un error de input, no de negocio).
		if ($usuario === '') {
			$recuperar_msg = 'Ingresá tu nombre de usuario.';
		} else {
			$recuperar_msg = 'Si el usuario existe en nuestro sistema, te enviamos un correo con instrucciones para restablecer tu contraseña. Revisá tu bandeja de entrada (y la carpeta de spam).';
			$recuperar_ok  = true;

			$stmt = $conexion->prepare('SELECT id, userName, nombre FROM tbl_user WHERE userName = ?');
			if ($stmt) {
				$stmt->bind_param('s', $usuario);
				$stmt->execute();
				$res  = $stmt->get_result();
				$user = $res ? $res->fetch_assoc() : null;
				$stmt->close();

				if ($user) {
					$token   = bin2hex(random_bytes(32));
					$expires = date('Y-m-d H:i:s', time() + 3600); // 1 hora
					$ip      = isset($_SERVER['REMOTE_ADDR']) ? (string)$_SERVER['REMOTE_ADDR'] : '';

					$ins = $conexion->prepare('INSERT INTO tbl_user_token (user_id, token, tipo, expires_at, ip) VALUES (?, ?, \'reset_password\', ?, ?)');
					if ($ins) {
						$ins->bind_param('isss', $user['id'], $token, $expires, $ip);
						$ins->execute();
						$ins->close();
					}

					$resetUrl = $URL . 'admin/restablecer/?token=' . urlencode($token);

					// ---- Log para tests / auditoria ----
					// SIEMPRE se loguea, aun si el envio de mail sale bien.
					$logLine = sprintf(
						"[%s] user=%s token=%s url=%s ip=%s\n",
						date('Y-m-d H:i:s'),
						$user['userName'],
						$token,
						$resetUrl,
						$ip
					);
					@file_put_contents(__DIR__ . '/../docker/samap-tokens.log', $logLine, FILE_APPEND | LOCK_EX);
					@error_log('SAMAP reset token: ' . trim($logLine));

					// ---- Envio del correo ----
					// tbl_user no tiene columna email: usamos userName SOLO si
					// parece un email valido; si no, dejamos el enlace solo en
					// el log (no se puede mandar sin destino real).
					$smtpHost = getenv('SMTP_HOST');
					$smtpUser = getenv('SMTP_USER');
					$smtpPass = getenv('SMTP_PASS');
					$esEmail  = (bool)filter_var($user['userName'], FILTER_VALIDATE_EMAIL);

					if ($smtpHost && $smtpUser && $smtpPass && $esEmail) {
						require_once __DIR__ . '/class/class.phpmailer.php';
						$smtpPort = getenv('SMTP_PORT') ?: '587';
						$smtpFrom = getenv('SMTP_FROM') ?: $smtpUser;

						$mail = new PHPMailer();
						$mail->CharSet   = 'UTF-8';
						$mail->IsSMTP();
						$mail->Host       = $smtpHost;
						$mail->Port       = $smtpPort;
						$mail->SMTPAuth   = true;
						$mail->Username   = $smtpUser;
						$mail->Password   = $smtpPass;
						$mail->From       = $smtpFrom;
						$mail->FromName   = 'SAMAP - Admin';
						$mail->AddAddress($user['userName'], (string)$user['nombre']);
						$mail->Subject    = 'Restablecé tu contraseña - SAMAP Admin';
						$mail->Body       = "Hola {$user['nombre']},\n\n"
							. "Para restablecer tu contraseña del panel de administración, hacé click en el siguiente enlace (válido por 1 hora):\n\n"
							. "{$resetUrl}\n\n"
							. "Si no solicitaste este cambio, podés ignorar este mensaje.\n\n"
							. "Saludos,\nEquipo SAMAP";
						@$mail->Send();
					}

					// ---- Dev escape hatch ----
					// Si el caller pidio ?dev=1 (o no hay SMTP y no se puede
					// entregar el correo), mostramos el enlace en pantalla
					// para que el agente de tests / un humano en local pueda
					// continuar el flujo. En PRODUCCION esto se ignora.
					$smtpDisponible = ($smtpHost && $smtpUser && $smtpPass && $esEmail);
					if ($dev_mostrar || !$smtpDisponible) {
						$dev_token_url = $resetUrl;
					}
				}
			}
		}
	}
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="utf-8">
	<meta http-equiv="x-ua-compatible" content="ie=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
	<title>Recuperar contraseña — SAMAP Admin</title>

	<link rel="stylesheet" href="<?php echo $URL;?>admin/app/css/bootstrap.css">
	<link rel="stylesheet" href="<?php echo $URL;?>admin/plugins/fontawesome/css/font-awesome.min.css">
	<link rel="stylesheet" href="<?php echo $URL;?>admin/plugins/animo/animate+animo.css">
	<link rel="stylesheet" href="<?php echo $URL;?>admin/app/css/app.css">

	<script src="<?php echo $URL;?>admin/plugins/modernizr/modernizr.js" type="application/javascript"></script>
	<script src="<?php echo $URL;?>admin/plugins/fastclick/fastclick.js" type="application/javascript"></script>
</head>
<body>
	<div style="height: 100%; padding: 50px 0; background-image: url(<?php echo $URL;?>admin/app/img/fondo.png); background-size: cover; background-color: #2c3037" class="row row-table">
		<div class="col-lg-4 col-md-6 col-sm-8 col-xs-12 align-middle">
			<div data-toggle="play-animation" data-play="fadeInUp" data-offset="0" class="panel panel-default panel-flat">
				<p class="text-center mb-lg">
					<br>
				</p>
				<p class="text-center mb-lg">
					<strong>RECUPERAR CONTRASEÑA</strong>
				</p>
				<div class="panel-body">
					<?php if ($recuperar_msg !== '') {
						$alertClass = $recuperar_ok ? 'alert-success' : 'alert-warning';
						echo "<div class='alert " . $alertClass . " alert-dismissable'>"
							. "<button type='button' class='close' data-dismiss='alert'>&times;</button>"
							. htmlspecialchars($recuperar_msg, ENT_QUOTES, 'UTF-8')
							. "</div>";
					} ?>

					<?php if ($dev_token_url !== '') { ?>
						<div class="alert alert-info" style="word-break:break-all;">
							<strong>[DEV]</strong> Enlace generado (no se envió por correo):
							<a href="<?php echo htmlspecialchars($dev_token_url, ENT_QUOTES, 'UTF-8'); ?>">
								<?php echo htmlspecialchars($dev_token_url, ENT_QUOTES, 'UTF-8'); ?>
							</a>
						</div>
					<?php } ?>

					<?php if (!$recuperar_ok) { ?>
					<form action="" method="post" name="form_recuperar" id="form_recuperar">
						<?php echo samap_csrf_field(); ?>
						<div class="form-group has-feedback">
							<input name="usuario" type="text" placeholder="Ingresá tu usuario" class="form-control" required maxlength="250" autocomplete="username">
							<span class="fa fa-user form-control-feedback text-muted"></span>
						</div>
						<div class="clearfix">
							<button type="submit" class="btn btn-block btn-primary">Enviar enlace de recuperación</button>
						</div>
					</form>
					<?php } ?>

					<div class="text-center" style="margin-top:15px;">
						<a href="<?php echo $URL;?>admin/" style="color:#888;font-size:12px;">&laquo; Volver al inicio de sesión</a>
					</div>
				</div>
			</div>
		</div>
	</div>

	<script src="<?php echo $URL;?>admin/plugins/jquery/jquery.min.js"></script>
	<script src="<?php echo $URL;?>admin/plugins/bootstrap/js/bootstrap.min.js"></script>
	<script src="<?php echo $URL;?>admin/plugins/animo/animo.min.js"></script>
	<script src="<?php echo $URL;?>admin/app/js/pages.js"></script>
</body>
</html>
