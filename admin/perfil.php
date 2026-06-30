<?php
require_once('funciones/db.php');
include('conexion.php');

if (isset($_SESSION['ADM_Username'])){


} else{

	echo"<script>window.location.href=\"".$URL."admin/index/\"</script>";

}

$u_id       = 0;
$u_userName = '';
$u_nombre   = '';
$u_rol      = 'admin';

$rolesEtiqueta = [
	'admin'     => 'Administrador',
	'editor'    => 'Editor de contenidos',
	'comercial' => 'Comercial',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST'
	&& samap_csrf_validar()
	&& ($_POST['MM_action'] ?? '') === 'cambiar_password') {

	$current = $POST_RAW['clave_actual'] ?? '';
	$new     = $POST_RAW['clave_nueva'] ?? '';
	$confirm = $POST_RAW['clave_confirmar'] ?? '';
	$errors  = [];

	$stmt = $conexion->prepare('SELECT id, userName, nombre, userPass, rol FROM tbl_user WHERE userName = ?');
	$stmt->bind_param('s', $_SESSION['ADM_Username']);
	$stmt->execute();
	$res = $stmt->get_result();
	$u = $res->fetch_assoc();
	$stmt->close();

	if (!$u || !password_verify($current, (string)$u['userPass'])) {
		$errors[] = 'La contraseña actual es incorrecta.';
	}
	if (strlen($new) < 8) {
		$errors[] = 'La nueva contraseña debe tener al menos 8 caracteres.';
	}
	if ($new !== $confirm) {
		$errors[] = 'La confirmación no coincide con la nueva contraseña.';
	}
	if ($new !== '' && $new === $current) {
		$errors[] = 'La nueva contraseña debe ser distinta de la actual.';
	}

	if (empty($errors)) {
		$nuevoHash = password_hash($new, PASSWORD_BCRYPT);
		$up = $conexion->prepare('UPDATE tbl_user SET userPass = ? WHERE id = ?');
		$up->bind_param('si', $nuevoHash, $u['id']);
		$up->execute();
		$up->close();
		@samap_audit_log('update', 'tbl_user', (int)$u['id'], "Cambió su propia contraseña");
		session_regenerate_id(true);
		$_SESSION['ADM_Username'] = $u['userName'];
		$_SESSION['ADM_Nombre']   = $u['nombre'];
		$_SESSION['ADM_Rol']      = $u['rol'] ?? 'admin';
		$_SESSION['ADM_last_activity'] = time();
		samap_flash_set('success', 'Contraseña actualizada correctamente.');
		header('Location: ' . $URL . 'admin/perfil/');
		exit;
	} else {
		$error_msg = implode("\n", $errors);
		samap_flash_set('error', $error_msg);
		header('Location: ' . $URL . 'admin/perfil/');
		exit;
	}
}

if (!empty($conexion) && !empty($_SESSION['ADM_Username'])) {
	$stmt = $conexion->prepare('SELECT id, userName, nombre, rol FROM tbl_user WHERE userName = ?');
	$stmt->bind_param('s', $_SESSION['ADM_Username']);
	$stmt->execute();
	$res = $stmt->get_result();
	if ($row = $res->fetch_assoc()) {
		$u_id       = (int)$row['id'];
		$u_userName = (string)$row['userName'];
		$u_nombre   = (string)$row['nombre'];
		$u_rol      = (string)($row['rol'] ?? 'admin');
	}
	$stmt->close();
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
	<title>MI PERFIL - Administrador</title>

	</script><link rel="stylesheet" href="<?php echo $URL;?>admin/app/css/bootstrap.css">

	<link rel="stylesheet" href="<?php echo $URL;?>admin/plugins/fontawesome/css/font-awesome.min.css">
	<link rel="stylesheet" href="<?php echo $URL;?>admin/plugins/animo/animate+animo.css">
	<link rel="stylesheet" href="<?php echo $URL;?>admin/plugins/csspinner/csspinner.min.css">

	<link rel="stylesheet" href="<?php echo $URL;?>admin/app/css/app.css?v=202606291705">
</head>
<body>

	<section class="wrapper">

		<?php include 'header.php'; ?>
		<?php include 'aside.php'; ?>

		<section>

			<section class="main-content">

				<h3>Mi perfil</h3>

				<div class="panel panel-default">
					<div class="panel-heading"><em class="fa fa-user"></em> Mis datos</div>
					<div class="panel-body">
						<dl class="dl-horizontal" style="margin:0;">
							<dt style="text-align:left;width:160px;">Nombre</dt>
							<dd style="margin-left:180px;"><?php echo htmlspecialchars($u_nombre, ENT_QUOTES); ?></dd>
							<dt style="text-align:left;width:160px;">Usuario</dt>
							<dd style="margin-left:180px;"><?php echo htmlspecialchars($u_userName, ENT_QUOTES); ?></dd>
							<dt style="text-align:left;width:160px;">Rol</dt>
							<dd style="margin-left:180px;"><span class="label label-primary"><?php echo htmlspecialchars($rolesEtiqueta[$u_rol] ?? 'Administrador', ENT_QUOTES); ?></span></dd>
						</dl>
					</div>
				</div>

				<div class="panel panel-default">
					<div class="panel-heading"><em class="fa fa-lock"></em> Cambiar contraseña</div>
					<div class="panel-body">
						<form class="form-horizontal" action="<?php echo $URL; ?>admin/perfil/" method="post" name="form_password" id="form_password" data-parsley-validate>
							<?php echo samap_csrf_field(); ?>
							<input type="hidden" name="MM_action" value="cambiar_password">

							<fieldset>
								<div class="form-group">
									<label class="col-sm-3 control-label">Contraseña actual</label>
									<div class="col-sm-6">
										<input type="password" name="clave_actual" id="clave_actual" required class="form-control" autocomplete="current-password">
										<span class="help-block">Ingresá tu contraseña actual para confirmar el cambio.</span>
									</div>
								</div>
							</fieldset>

							<fieldset>
								<div class="form-group">
									<label class="col-sm-3 control-label">Nueva contraseña</label>
									<div class="col-sm-6">
										<input type="password" name="clave_nueva" id="clave_nueva" required minlength="8" data-parsley-minlength="8" class="form-control" autocomplete="new-password">
										<span class="help-block">Mínimo 8 caracteres. Recomendamos incluir números y letras.</span>
									</div>
								</div>
							</fieldset>

							<fieldset>
								<div class="form-group">
									<label class="col-sm-3 control-label">Repetir nueva contraseña</label>
									<div class="col-sm-6">
										<input type="password" name="clave_confirmar" id="clave_confirmar" required minlength="8" data-parsley-equalto="#clave_nueva" class="form-control" autocomplete="new-password">
									</div>
								</div>
							</fieldset>

							<fieldset>
								<div class="form-group">
									<div class="col-sm-6 col-sm-offset-3">
										<button type="button" class="btn btn-default" onclick="window.location.href='<?php echo $URL; ?>admin/home/';">Cancelar</button>
										<button type="submit" class="btn btn-primary">Cambiar contraseña</button>
									</div>
								</div>
							</fieldset>
						</form>
					</div>
				</div>

			</section>

		</section>

	</section>
	<?php include 'partials/scripts-comunes.php'; ?>
	<script src="<?php echo $URL;?>admin/plugins/parsley/parsley.min.js"></script>

</body>
</html>
