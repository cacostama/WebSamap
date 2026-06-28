<?php
/**
 * admin/agregar-usuario.php — Alta de usuarios del panel (admin-only).
 *
 * Valida server-side: nombre, userName unico, clave >= 8 chars, rol valido.
 * Inserta con password_hash(..., PASSWORD_BCRYPT) y activo=1.
 */
require_once('funciones/db.php');
require_once('conexion.php');

if (!isset($_SESSION['ADM_Username'])) {
	echo "<script>window.location.href=\"" . $URL . "admin/index/\"</script>";
	exit;
}
if (!samap_rol_es('admin')) {
	samap_flash_set('error', 'Solo los administradores pueden agregar usuarios.');
	header('Location: ' . $URL . 'admin/home/');
	exit;
}

$errores = [];
$form_nombre   = '';
$form_userName = '';
$form_rol      = 'editor';

$roles_validos = ['admin', 'editor', 'comercial'];

if ($_SERVER['REQUEST_METHOD'] === 'POST'
	&& samap_csrf_validar()
	&& (($_POST['MM_action'] ?? '') === 'crear_usuario')) {

	$form_nombre   = trim((string)($_POST['nombre']   ?? ''));
	$form_userName = trim((string)($_POST['userName'] ?? ''));
	$clave         = (string)($_POST['clave']        ?? '');
	$clave2        = (string)($_POST['clave2']       ?? '');
	$form_rol      = (string)($_POST['rol']          ?? 'editor');

	// ---- Validaciones ----
	if ($form_nombre === '') {
		$errores[] = 'El nombre es obligatorio.';
	}
	if ($form_userName === '') {
		$errores[] = 'El usuario es obligatorio.';
	} elseif (!preg_match('/^[A-Za-z0-9._-]{3,60}$/', $form_userName)) {
		$errores[] = 'El usuario solo puede tener letras, numeros, guion, punto o guion bajo (3 a 60 caracteres).';
	}
	if (strlen($clave) < 8) {
		$errores[] = 'La contrasena debe tener al menos 8 caracteres.';
	}
	if ($clave !== $clave2) {
		$errores[] = 'La confirmacion de contrasena no coincide.';
	}
	if (!in_array($form_rol, $roles_validos, true)) {
		$errores[] = 'Rol invalido.';
	}

	// ---- Unicidad de userName (ademas del UNIQUE del esquema) ----
	if (empty($errores)) {
		$ex = $conexion->prepare('SELECT id FROM tbl_user WHERE userName = ? AND deleted_at IS NULL');
		$ex->bind_param('s', $form_userName);
		$ex->execute();
		$exRes = $ex->get_result();
		if ($exRes && $exRes->num_rows > 0) {
			$errores[] = 'Ya existe un usuario con ese nombre de usuario.';
		}
		$ex->close();
	}

	if (empty($errores)) {
		$hash = password_hash($clave, PASSWORD_BCRYPT);
		$ins = $conexion->prepare('INSERT INTO tbl_user (nombre, userName, userPass, rol, activo, deleted_at) VALUES (?, ?, ?, ?, 1, NULL)');
		if (!$ins) {
			$errores[] = 'No se pudo preparar la insercion.';
		} else {
			$ins->bind_param('ssss', $form_nombre, $form_userName, $hash, $form_rol);
			$ins->execute();
			$ins->close();
			samap_flash_set('success', 'Usuario creado correctamente.');
			header('Location: ' . $URL . 'admin/usuarios/');
			exit;
		}
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
	<title>AGREGAR USUARIO -  Administrador</title>

	</script><link rel="stylesheet" href="<?php echo $URL;?>admin/app/css/bootstrap.css">

	<link rel="stylesheet" href="<?php echo $URL;?>admin/plugins/fontawesome/css/font-awesome.min.css">
	<link rel="stylesheet" href="<?php echo $URL;?>admin/plugins/animo/animate+animo.css">
	<link rel="stylesheet" href="<?php echo $URL;?>admin/plugins/csspinner/csspinner.min.css">

	<link rel="stylesheet" href="<?php echo $URL;?>admin/app/css/app.css">

	<script src="<?php echo $URL;?>admin/plugins/modernizr/modernizr.js" type="application/javascript"></script>

	<script src="<?php echo $URL;?>admin/plugins/fastclick/fastclick.js" type="application/javascript"></script>
</head>
<body>

	<section class="wrapper">

		<?php include 'header.php'; ?>
		<?php include 'aside.php'; ?>

		<section>

			<section class="main-content">

				<h3>Agregar Usuario</h3>

				<?php if (!empty($errores)): ?>
					<div class="alert alert-danger" style="padding:10px 15px;border-radius:4px;margin-bottom:15px;">
						<strong><em class="fa fa-exclamation-triangle"></em> No se pudo crear el usuario:</strong>
						<ul style="margin:6px 0 0 18px;">
							<?php foreach ($errores as $e): ?>
								<li><?php echo htmlspecialchars($e, ENT_QUOTES, 'UTF-8'); ?></li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>

				<div class="panel panel-default">
					<div class="panel-heading">Datos del nuevo usuario</div>
					<div class="panel-body">
						<form class="form-horizontal" action="" method="post" name="form_usuario" id="form_usuario" autocomplete="off">
							<?php echo samap_csrf_field(); ?>
							<input type="hidden" name="MM_action" value="crear_usuario">

							<fieldset>
								<div class="form-group">
									<label class="col-lg-2 control-label">Nombre</label>
									<div class="col-lg-6">
										<input type="text" name="nombre" value="<?php echo htmlspecialchars($form_nombre, ENT_QUOTES, 'UTF-8'); ?>" class="form-control" required maxlength="200">
										<span class="help-block">Nombre completo para mostrar en el panel.</span>
									</div>
								</div>
							</fieldset>

							<fieldset>
								<div class="form-group">
									<label class="col-lg-2 control-label">Usuario</label>
									<div class="col-lg-6">
										<input type="text" name="userName" value="<?php echo htmlspecialchars($form_userName, ENT_QUOTES, 'UTF-8'); ?>" class="form-control" required maxlength="60" pattern="[A-Za-z0-9._-]{3,60}">
										<span class="help-block">Identificador de inicio de sesion. Solo letras, numeros, guion, punto o guion bajo.</span>
									</div>
								</div>
							</fieldset>

							<fieldset>
								<div class="form-group">
									<label class="col-lg-2 control-label">Contrasena</label>
									<div class="col-lg-4">
										<input type="password" name="clave" id="clave" class="form-control" required minlength="8" autocomplete="new-password">
										<span class="help-block">Minimo 8 caracteres.</span>
									</div>
									<label class="col-lg-2 control-label">Repetir</label>
									<div class="col-lg-4">
										<input type="password" name="clave2" id="clave2" class="form-control" required minlength="8" autocomplete="new-password">
									</div>
								</div>
							</fieldset>

							<fieldset>
								<div class="form-group">
									<label class="col-lg-2 control-label">Rol</label>
									<div class="col-lg-6">
										<select name="rol" class="form-control">
											<option value="admin"     <?php echo $form_rol === 'admin'     ? 'selected' : ''; ?>>Administrador (acceso total)</option>
											<option value="editor"    <?php echo $form_rol === 'editor'    ? 'selected' : ''; ?>>Editor de contenidos (sin gestion de usuarios)</option>
											<option value="comercial" <?php echo $form_rol === 'comercial' ? 'selected' : ''; ?>>Comercial (solo lectura de leads)</option>
										</select>
										<span class="help-block">
											<strong>Administrador:</strong> gestiona usuarios, contenido y configuracion.<br>
											<strong>Editor:</strong> gestiona contenido (planes, slider, blog, etc.) pero no usuarios.<br>
											<strong>Comercial:</strong> solo ve el listado de leads, no puede crear/editar contenido.
										</span>
									</div>
								</div>
							</fieldset>

							<fieldset>
								<div class="form-group">
									<div class="col-lg-6 col-lg-offset-2">
										<a href="<?php echo htmlspecialchars($URL, ENT_QUOTES, 'UTF-8'); ?>admin/usuarios/" class="btn btn-default">Cancelar</a>
										<button type="submit" class="btn btn-primary">Crear usuario</button>
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

	<script src="<?php echo $URL;?>admin/app/js/app.js"></script>

</body>
</html>
