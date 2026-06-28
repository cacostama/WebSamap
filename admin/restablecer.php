<?php
/**
 * admin/restablecer.php — Canje del token de recuperacion.
 *
 * Pagina publica. Llega con ?token=... y le pide al usuario que defina la
 * nueva contrasena. Al confirmar:
 *   1. Busca el token (debe existir, no estar usado, no estar expirado).
 *   2. Hashea la nueva contrasena con bcrypt.
 *   3. UPDATE tbl_user.userPass.
 *   4. Marca el token como usado (used_at = NOW) para que no se pueda reusar.
 *   5. Regenera el session id por si el usuario ya estaba logueado en otra
 *      pestana.
 *
 * Decisiones de seguridad:
 *   - Mensaje de error generico: "El enlace no es valido / expiro / ya se
 *     uso" - sin filtrar el motivo exacto para no ayudar a un atacante.
 *   - Minimo 8 caracteres en la nueva contrasena (mismo piso que perfil.php).
 *   - CSRF obligatorio.
 *   - El token se valida por igualdad exacta (no hace falta hashing porque
 *     ya es 64 chars hex randomicos -> 256 bits).
 */

require_once('funciones/db.php');
require_once('conexion.php');

$error = '';
$ok    = false;
$token = trim((string)($_GET['token'] ?? $_POST['token'] ?? ''));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if (!samap_csrf_validar()) {
		$error = 'Tu sesión expiró. Recargá la página e intentá de nuevo.';
		$token = trim((string)($POST_RAW['token'] ?? ''));
	} else {
		$token = trim((string)($POST_RAW['token'] ?? ''));
		$new   = (string)($POST_RAW['clave_nueva']    ?? '');
		$conf  = (string)($POST_RAW['clave_confirmar'] ?? '');

		if (strlen($new) < 8) {
			$error = 'La nueva contraseña debe tener al menos 8 caracteres.';
		} elseif ($new !== $conf) {
			$error = 'La confirmación no coincide con la nueva contraseña.';
		} elseif ($token === '') {
			$error = 'El enlace no es válido.';
		} else {
			$stmt = $conexion->prepare("SELECT id, user_id, expires_at, used_at FROM tbl_user_token WHERE token = ? AND tipo = 'reset_password' LIMIT 1");
			if (!$stmt) {
				$error = 'El enlace no es válido.';
			} else {
				$stmt->bind_param('s', $token);
				$stmt->execute();
				$res = $stmt->get_result();
				$tok = $res ? $res->fetch_assoc() : null;
				$stmt->close();

				// Mensaje de error UNIFORME para no filtrar si el motivo
				// fue "no existe", "ya usado" o "expirado". Cumplimos
				// ademas el contrato de OWASP.
				if (!$tok || $tok['used_at'] !== null || strtotime((string)$tok['expires_at']) < time()) {
					$error = 'El enlace no es válido, ya fue utilizado o expiró.';
				} else {
					$nuevoHash = password_hash($new, PASSWORD_BCRYPT);

					$up = $conexion->prepare('UPDATE tbl_user SET userPass = ? WHERE id = ?');
					if ($up) {
						$up->bind_param('si', $nuevoHash, $tok['user_id']);
						$up->execute();
						$up->close();
					}

					@samap_audit_log('update', 'tbl_user', (int)$tok['user_id'], "Restableció su contraseña vía token");

					$used = date('Y-m-d H:i:s');
					$mk = $conexion->prepare('UPDATE tbl_user_token SET used_at = ? WHERE id = ?');
					if ($mk) {
						$mk->bind_param('si', $used, $tok['id']);
						$mk->execute();
						$mk->close();
					}

					// Si el usuario tenia una sesion abierta en otra pestana
					// la invalidamos: la nueva contraseña no debe servir
					// para "robar" la sesion anterior.
					if (session_status() === PHP_SESSION_ACTIVE) {
						session_regenerate_id(true);
					}

					$ok = true;
				}
			}
		}
	}
} else {
	// GET: si no viene token, mostramos el formulario igual pero con
	// mensaje (asi el usuario sabe que el enlace esta mal). El render de
	// abajo se encarga de los tres estados (ok / error / form).
	if ($token === '') {
		$error = 'Falta el token de recuperación. Usá el enlace que te llegó por correo.';
	}
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="utf-8">
	<meta http-equiv="x-ua-compatible" content="ie=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
	<title>Restablecer contraseña — SAMAP Admin</title>

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
					<strong>RESTABLECER CONTRASEÑA</strong>
				</p>
				<div class="panel-body">
					<?php if ($ok) { ?>
						<div class="alert alert-success alert-dismissable">
							<strong>¡Listo!</strong> Tu contraseña fue actualizada. Ya podés iniciar sesión con la nueva.
						</div>
						<div class="text-center" style="margin-top:15px;">
							<a href="<?php echo $URL;?>admin/" class="btn btn-primary">Ir al inicio de sesión</a>
						</div>
					<?php } else { ?>
						<?php if ($error !== '') { ?>
							<div class="alert alert-danger alert-dismissable">
								<button type="button" class="close" data-dismiss="alert">&times;</button>
								<?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
							</div>
						<?php } ?>

						<?php if ($error === '' || $token !== '') { ?>
						<form action="" method="post" name="form_restablecer" id="form_restablecer">
							<?php echo samap_csrf_field(); ?>
							<input type="hidden" name="token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">

							<div class="form-group has-feedback">
								<input name="clave_nueva" type="password" placeholder="Nueva contraseña (mínimo 8 caracteres)" class="form-control" required minlength="8" autocomplete="new-password">
								<span class="fa fa-lock form-control-feedback text-muted"></span>
							</div>
							<div class="form-group has-feedback">
								<input name="clave_confirmar" type="password" placeholder="Repetir nueva contraseña" class="form-control" required minlength="8" autocomplete="new-password">
								<span class="fa fa-lock form-control-feedback text-muted"></span>
							</div>
							<div class="clearfix">
								<button type="submit" class="btn btn-block btn-primary">Cambiar contraseña</button>
							</div>
						</form>
						<?php } ?>

						<div class="text-center" style="margin-top:15px;">
							<a href="<?php echo $URL;?>admin/recuperar/" style="color:#888;font-size:12px;">Solicitar un nuevo enlace</a>
						</div>
					<?php } ?>
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
