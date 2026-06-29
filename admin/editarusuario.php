<?php
/**
 * admin/editarusuario.php — Edicion de un usuario del panel (admin-only).
 *
 * URL: admin/editarusuario/cod/N/  (rewrite de .htaccess).
 *
 * Permite editar nombre, userName, email, rol, activo y (opcionalmente) clave.
 *
 * Safety:
 *   - Si el usuario editado es el mismo que esta logueado:
 *       * Mostramos un warning banner.
 *       * Bloqueamos el cambio de rol (se hace desde otro admin).
 *       * Bloqueamos auto-desactivacion.
 *   - Si el usuario editado es admin y se intenta desactivar / cambiar rol /
 *     borrar: verificamos que quede al menos un admin activo.
 *   - Clave opcional: si el campo viene vacio NO se reescribe el hash.
 */
require_once('funciones/db.php');
require_once('conexion.php');

if (!isset($_SESSION['ADM_Username'])) {
	echo "<script>window.location.href=\"" . $URL . "admin/index/\"</script>";
	exit;
}
if (!samap_rol_es('admin')) {
	samap_flash_set('error', 'Solo los administradores pueden editar usuarios.');
	header('Location: ' . $URL . 'admin/home/');
	exit;
}

$roles_validos = ['admin', 'editor', 'comercial'];
$rol_etiqueta = [
	'admin'     => 'Administrador',
	'editor'    => 'Editor de contenidos',
	'comercial' => 'Comercial',
];

// ---- ID del usuario logueado ----
$current_user_id = 0;
$cu = $conexion->prepare('SELECT id FROM tbl_user WHERE userName = ?');
if ($cu) {
	$cu->bind_param('s', $_SESSION['ADM_Username']);
	$cu->execute();
	$cuRes = $cu->get_result();
	if ($cuRow = $cuRes->fetch_assoc()) {
		$current_user_id = (int) $cuRow['id'];
	}
	$cu->close();
}

$cod = (int) ($_GET['cod'] ?? 0);
if ($cod <= 0) {
	samap_flash_set('error', 'ID de usuario invalido.');
	header('Location: ' . $URL . 'admin/usuarios/');
	exit;
}

$errores = [];
$mensaje_ok = '';

// ---- POST: actualizar ----
if ($_SERVER['REQUEST_METHOD'] === 'POST'
	&& samap_csrf_validar()
	&& (($_POST['MM_action'] ?? '') === 'editar_usuario')) {

	$es_self = ($cod === $current_user_id);

	$nombre   = trim((string)($_POST['nombre']   ?? ''));
	$userName = trim((string)($_POST['userName'] ?? ''));
	$email    = trim((string)($_POST['email']    ?? ''));
	$rol_in   = (string)($_POST['rol']           ?? '');
	$activo_in = isset($_POST['activo']) ? 1 : 0;
	$clave    = (string)($_POST['clave']         ?? '');
	$clave2   = (string)($_POST['clave2']        ?? '');

	// ---- Validaciones ----
	if ($nombre === '') {
		$errores[] = 'El nombre es obligatorio.';
	}
	if ($userName === '') {
		$errores[] = 'El usuario es obligatorio.';
	} elseif (!preg_match('/^[A-Za-z0-9._-]{3,60}$/', $userName)) {
		$errores[] = 'El usuario solo puede tener letras, numeros, guion, punto o guion bajo (3 a 60 caracteres).';
	}
	if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$errores[] = 'El email no tiene un formato valido.';
	}
	if (!in_array($rol_in, $roles_validos, true)) {
		$errores[] = 'Rol invalido.';
	}
	if ($clave !== '' || $clave2 !== '') {
		if (strlen($clave) < 8) {
			$errores[] = 'La contrasena debe tener al menos 8 caracteres.';
		}
		if ($clave !== $clave2) {
			$errores[] = 'La confirmacion de contrasena no coincide.';
		}
	}

	// ---- Auto-edicion: no se puede cambiar el rol propio ----
	if ($es_self && $rol_in !== '') {
		// Leemos el rol actual para comparar
		$rol_actual = '';
		$rq = $conexion->prepare('SELECT rol FROM tbl_user WHERE id = ?');
		$rq->bind_param('i', $cod);
		$rq->execute();
		$rqRes = $rq->get_result();
		if ($rqRes && $rqRow = $rqRes->fetch_assoc()) {
			$rol_actual = (string)($rqRow['rol'] ?? 'admin');
		}
		$rq->close();
		// Forzamos el rol al actual y avisamos si intentaba cambiarlo.
		if ($rol_in !== $rol_actual) {
			$errores[] = 'No podés cambiar tu propio rol. Pedile a otro administrador que lo haga.';
			$rol_in = $rol_actual;
		}
	}

	// ---- Auto-desactivacion ----
	if ($es_self && $activo_in === 0) {
		$errores[] = 'No podés desactivarte a vos mismo.';
		$activo_in = 1;
	}

	// ---- Unicidad de userName (excluyendo la fila actual) ----
	if (empty($errores)) {
		$ex = $conexion->prepare('SELECT id FROM tbl_user WHERE userName = ? AND id <> ? AND deleted_at IS NULL');
		$ex->bind_param('si', $userName, $cod);
		$ex->execute();
		$exRes = $ex->get_result();
		if ($exRes && $exRes->num_rows > 0) {
			$errores[] = 'Ya existe otro usuario con ese nombre de usuario.';
		}
		$ex->close();
	}

	// ---- Lockout: no dejar al sistema sin admins ----
	if (empty($errores)) {
		$rol_actual = '';
		$activo_actual = 1;
		$del_actual = null;
		$rq = $conexion->prepare('SELECT rol, activo, deleted_at FROM tbl_user WHERE id = ?');
		$rq->bind_param('i', $cod);
		$rq->execute();
		$rqRes = $rq->get_result();
		if ($rqRes && $rqRow = $rqRes->fetch_assoc()) {
			$rol_actual   = (string)($rqRow['rol'] ?? 'admin');
			$activo_actual = (int)($rqRow['activo'] ?? 1);
			$del_actual   = $rqRow['deleted_at'] ?? null;
		}
		$rq->close();

		$era_admin = ($rol_actual === 'admin') && ($activo_actual === 1) && empty($del_actual);
		$deja_de_ser_admin = ($rol_in !== 'admin') || ($activo_in === 0);

		if ($era_admin && $deja_de_ser_admin) {
			$cnt = 0;
			$cq = $conexion->prepare("SELECT COUNT(*) AS c FROM tbl_user WHERE rol = 'admin' AND activo = 1 AND deleted_at IS NULL AND id <> ?");
			$cq->bind_param('i', $cod);
			$cq->execute();
			$cRes = $cq->get_result();
			if ($cRes && $cRow = $cRes->fetch_assoc()) {
				$cnt = (int) $cRow['c'];
			}
			$cq->close();
			if ($cnt === 0) {
				$errores[] = 'No podés quitar el rol o desactivar al unico administrador activo.';
			}
		}
	}

	if (empty($errores)) {
		// Construimos el UPDATE dinamicamente (clave opcional).
		$sets = 'nombre = ?, userName = ?, email = ?, rol = ?, activo = ?';
		$types = 'ssssi';
		$params = [&$nombre, &$userName, &$email, &$rol_in, &$activo_in];

		if ($clave !== '') {
			$hash = password_hash($clave, PASSWORD_BCRYPT);
			$sets .= ', userPass = ?';
			$types .= 's';
			$params[] = &$hash;
		}

		$sql = "UPDATE tbl_user SET $sets WHERE id = ?";
		$types .= 'i';
		$params[] = &$cod;

		$up = $conexion->prepare($sql);
		if (!$up) {
			$errores[] = 'No se pudo preparar la actualizacion.';
		} else {
			// bind_param requiere referencias reales; usamos call_user_func_array
			// para no pelearnos con los refs.
			$bind_names[] = $types;
			for ($i = 0; $i < count($params); $i++) {
				$bind_name = 'bind' . $i;
				$$bind_name = $params[$i];
				$bind_names[] = &$$bind_name;
			}
			call_user_func_array([$up, 'bind_param'], $bind_names);
			$up->execute();
			$up->close();

			// Si el admin logueado se cambia a si mismo, refrescamos la sesion.
			if ($es_self) {
				session_regenerate_id(true);
				$_SESSION['ADM_Username'] = $userName;
				$_SESSION['ADM_Nombre']   = $nombre;
				$_SESSION['ADM_Rol']      = $rol_in;
			}
			$mensaje_ok = 'Usuario actualizado correctamente.';
		}
	}
}

// ---- Cargar datos frescos del usuario ----
$row = null;
$sq = $conexion->prepare('SELECT id, nombre, userName, userPass, email, rol, activo, ultimo_acceso, deleted_at FROM tbl_user WHERE id = ?');
$sq->bind_param('i', $cod);
$sq->execute();
$sqRes = $sq->get_result();
if ($sqRes) {
	$row = $sqRes->fetch_assoc();
}
$sq->close();

if (!$row) {
	samap_flash_set('error', 'El usuario no existe.');
	header('Location: ' . $URL . 'admin/usuarios/');
	exit;
}

$es_self = ((int) $row['id'] === $current_user_id);

// Valores para el form (post-fail repoblamos con lo enviado; si no, con la DB).
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($errores)) {
	$form_nombre   = (string)($_POST['nombre']   ?? $row['nombre']);
	$form_userName = (string)($_POST['userName'] ?? $row['userName']);
	$form_email    = (string)($_POST['email']    ?? ($row['email'] ?? ''));
	$form_rol      = (string)($_POST['rol']      ?? $row['rol']);
	$form_activo   = isset($_POST['activo']) ? 1 : (int)$row['activo'];
} else {
	$form_nombre   = (string)$row['nombre'];
	$form_userName = (string)$row['userName'];
	$form_email    = (string)($row['email'] ?? '');
	$form_rol      = (string)$row['rol'];
	$form_activo   = (int)$row['activo'];
}

$ultimo_acceso_fmt = '';
if (!empty($row['ultimo_acceso']) && $row['ultimo_acceso'] !== '0000-00-00 00:00:00') {
	$ts = strtotime((string)$row['ultimo_acceso']);
	if ($ts !== false) {
		$ultimo_acceso_fmt = date('d/m/Y H:i', $ts);
	}
}
?>
<!DOCTYPE html>
<html lang="es">
<head>

	<meta charset="utf-8">
	<meta http-equiv="x-ua-compatible" content="ie=edge">
<meta http-equiv="Content-Language" content="es"/>
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
	<meta name="description" content="">
	<meta name="keywords" content="">
	<meta name="author" content="">
	<title>EDITAR USUARIO -  Administrador</title>

	</script><link rel="stylesheet" href="<?php echo $URL;?>admin/app/css/bootstrap.css">

	<link rel="stylesheet" href="<?php echo $URL;?>admin/plugins/fontawesome/css/font-awesome.min.css">
	<link rel="stylesheet" href="<?php echo $URL;?>admin/plugins/animo/animate+animo.css">
	<link rel="stylesheet" href="<?php echo $URL;?>admin/plugins/csspinner/csspinner.min.css">

	<link rel="stylesheet" href="<?php echo $URL;?>admin/app/css/app.css?v=202606291705">

	<script src="<?php echo $URL;?>admin/plugins/modernizr/modernizr.js" type="application/javascript"></script>

	<script src="<?php echo $URL;?>admin/plugins/fastclick/fastclick.js" type="application/javascript"></script>
</head>
<body>

	<section class="wrapper">

		<?php include 'header.php'; ?>
		<?php include 'aside.php'; ?>

		<section>

			<section class="main-content">

				<h3>Editar Usuario #<?php echo (int)$row['id']; ?></h3>

				<?php if ($es_self): ?>
					<div class="alert alert-warning" style="background:#ffc61d;color:#333;border:none;padding:10px 15px;border-radius:4px;margin-bottom:15px;">
						<em class="fa fa-exclamation-triangle"></em>
						<strong>Estas editando tu propio usuario.</strong> Cuidado con los cambios.
						No podes cambiar tu propio rol ni desactivarte a vos mismo.
					</div>
				<?php endif; ?>

				<?php if (!empty($errores)): ?>
					<div class="alert alert-danger" style="padding:10px 15px;border-radius:4px;margin-bottom:15px;">
						<strong><em class="fa fa-exclamation-triangle"></em> No se pudo guardar:</strong>
						<ul style="margin:6px 0 0 18px;">
							<?php foreach ($errores as $e): ?>
								<li><?php echo htmlspecialchars($e, ENT_QUOTES, 'UTF-8'); ?></li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>

				<?php if ($mensaje_ok !== ''): ?>
					<div class="alert alert-success" style="padding:8px 12px;margin-bottom:15px;">
						<em class="fa fa-check"></em> <?php echo htmlspecialchars($mensaje_ok, ENT_QUOTES, 'UTF-8'); ?>
					</div>
				<?php endif; ?>

				<div class="panel panel-default">
					<div class="panel-heading">Datos del usuario</div>
					<div class="panel-body">
						<form class="form-horizontal" action="" method="post" name="form_usuario" id="form_usuario" autocomplete="off">
							<?php echo samap_csrf_field(); ?>
							<input type="hidden" name="MM_action" value="editar_usuario">

							<fieldset>
								<div class="form-group">
									<label class="col-lg-2 control-label">ID</label>
									<div class="col-lg-2">
										<p class="form-control-static">#<?php echo (int)$row['id']; ?></p>
									</div>
									<?php if ($ultimo_acceso_fmt !== ''): ?>
										<label class="col-lg-2 control-label">Ultimo acceso</label>
										<div class="col-lg-3">
											<p class="form-control-static"><?php echo htmlspecialchars($ultimo_acceso_fmt, ENT_QUOTES, 'UTF-8'); ?></p>
										</div>
									<?php else: ?>
										<label class="col-lg-2 control-label">Ultimo acceso</label>
										<div class="col-lg-3">
											<p class="form-control-static text-muted">Nunca</p>
										</div>
									<?php endif; ?>
								</div>
							</fieldset>

							<fieldset>
								<div class="form-group">
									<label class="col-lg-2 control-label">Nombre</label>
									<div class="col-lg-6">
										<input type="text" name="nombre" value="<?php echo htmlspecialchars($form_nombre, ENT_QUOTES, 'UTF-8'); ?>" class="form-control" required maxlength="200">
									</div>
								</div>
							</fieldset>

							<fieldset>
								<div class="form-group">
									<label class="col-lg-2 control-label">Usuario</label>
									<div class="col-lg-6">
										<input type="text" name="userName" value="<?php echo htmlspecialchars($form_userName, ENT_QUOTES, 'UTF-8'); ?>" class="form-control" required maxlength="60" pattern="[A-Za-z0-9._-]{3,60}">
									</div>
								</div>
							</fieldset>

							<fieldset>
								<div class="form-group">
									<label class="col-lg-2 control-label">Email</label>
									<div class="col-lg-6">
										<input type="email" name="email" value="<?php echo htmlspecialchars($form_email, ENT_QUOTES, 'UTF-8'); ?>" class="form-control" maxlength="200">
										<span class="help-block">Opcional. Para futuras notificaciones o recuperacion de contrasena.</span>
									</div>
								</div>
							</fieldset>

							<fieldset>
								<div class="form-group">
									<label class="col-lg-2 control-label">Rol</label>
									<div class="col-lg-6">
										<select name="rol" class="form-control" <?php echo $es_self ? 'disabled' : ''; ?>>
											<?php foreach ($roles_validos as $r): ?>
												<option value="<?php echo htmlspecialchars($r, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $form_rol === $r ? 'selected' : ''; ?>>
													<?php echo htmlspecialchars($rol_etiqueta[$r], ENT_QUOTES, 'UTF-8'); ?>
												</option>
											<?php endforeach; ?>
										</select>
										<?php if ($es_self): ?>
											<input type="hidden" name="rol" value="<?php echo htmlspecialchars($form_rol, ENT_QUOTES, 'UTF-8'); ?>">
											<span class="help-block text-muted">Tu rol esta bloqueado. Pedile a otro administrador que lo cambie.</span>
										<?php else: ?>
											<span class="help-block">Ultimo cambio: el sistema no permitira dejar al panel sin administradores.</span>
										<?php endif; ?>
									</div>
								</div>
							</fieldset>

							<fieldset>
								<div class="form-group">
									<div class="col-lg-6 col-lg-offset-2">
										<label class="checkbox-inline">
											<input type="checkbox" name="activo" value="1" <?php echo $form_activo === 1 ? 'checked' : ''; ?> <?php echo $es_self ? 'disabled' : ''; ?>>
											Usuario activo (puede iniciar sesion)
										</label>
										<?php if ($es_self): ?>
											<input type="hidden" name="activo" value="1">
											<span class="help-block text-muted" style="display:block;clear:both;">No podes desactivarte a vos mismo.</span>
										<?php endif; ?>
									</div>
								</div>
							</fieldset>

							<hr style="border-color:#eee;">

							<fieldset>
								<div class="form-group">
									<label class="col-lg-2 control-label">Nueva contrasena</label>
									<div class="col-lg-4">
										<input type="password" name="clave" id="clave" class="form-control" minlength="8" autocomplete="new-password">
										<span class="help-block">Dejala vacia si no queres cambiarla. Minimo 8 caracteres.</span>
									</div>
									<label class="col-lg-2 control-label">Repetir</label>
									<div class="col-lg-4">
										<input type="password" name="clave2" id="clave2" class="form-control" minlength="8" autocomplete="new-password">
									</div>
								</div>
							</fieldset>

							<fieldset>
								<div class="form-group">
									<div class="col-lg-6 col-lg-offset-2">
										<a href="<?php echo htmlspecialchars($URL, ENT_QUOTES, 'UTF-8'); ?>admin/usuarios/" class="btn btn-default">Cancelar</a>
										<button type="submit" class="btn btn-primary">Guardar cambios</button>
									</div>
								</div>
							</fieldset>

						</form>
					</div>
				</div>

			</section>

		</section>

	</section>


	<script src="<?php echo $URL;?>admin/plugins/jquery/jquery.min.js"></script>
	<script src="<?php echo $URL;?>admin/plugins/bootstrap/js/bootstrap.min.js"></script>

	<script src="<?php echo $URL;?>admin/app/js/app.js?v=202606291718"></script>

</body>
</html>
