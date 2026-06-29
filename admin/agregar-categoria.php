<?php
require_once('funciones/db.php');

if (isset($_SESSION['ADM_Username'])){

	if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form2") && samap_puede_escribir()) {

		$nombre = trim($_POST['nombre'] ?? '');
		$icono  = trim($_POST['icono'] ?? '');
		$color  = trim($_POST['color'] ?? '');
		$orden  = (int) ($_POST['orden'] ?? 0);
		$activo = isset($_POST['activo']) ? 1 : 0;

		if ($icono === '') { $icono = 'fa-tags'; }
		$color_sql = $color !== '' ? "'".$color."'" : 'NULL';

		if ($nombre !== '') {
			$insertSQL = "INSERT INTO tbl_categorias_aliado (nombre, icono, color, orden, activo)
			              VALUES ('".$nombre."','".$icono."',".$color_sql.",".$orden.",".$activo.")";
			mysqli_select_db($connect, $database);
			mysqli_query($connect, $insertSQL) or die(mysqli_error($connect));
			$new_id = mysqli_insert_id($connect);
			@samap_audit_log('insert', 'tbl_categorias_aliado', $new_id, "Creó la categoría: " . substr((string)$nombre, 0, 100), null, ['id' => $new_id, 'nombre' => $nombre, 'icono' => $icono, 'orden' => $orden, 'activo' => $activo]);
			samap_flash_set('success', 'Listo, la categoría se agregó correctamente.');
			header('Location: ' . $URL . 'admin/categorias/');
			exit;
		} else {
			samap_flash_set('error', 'Necesitás escribir un nombre para la categoría.');
		}
	}

} else{

	echo"<script>window.location.href=\"".$URL."admin/home/\"</script>";

}

?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="utf-8">
	<meta http-equiv="x-ua-compatible" content="ie=edge">
<meta http-equiv="Content-Language" content="es"/>
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
	<title>AGREGAR CATEGORÍA -  Administrador</title>
	<link rel="stylesheet" href="<?php echo $URL;?>admin/app/css/bootstrap.css">
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
				<h3>Agregar Categoría</h3>

				<div class="panel panel-default">
					<div class="panel-heading">Formulario de Carga</div>
					<div class="panel-body">
						<form class="form-horizontal" action="" method="post" name="form2" id="form2">

							<fieldset>
								<div class="form-group">
									<label class="col-lg-2 control-label">Nombre</label>
									<div class="col-lg-6">
										<input type="text" name="nombre" placeholder="Ej: Farmacias" value="" class="form-control" required>
									</div>
								</div>
							</fieldset>

							<fieldset>
								<div class="form-group">
									<label class="col-lg-2 control-label">Ícono</label>
									<div class="col-lg-6">
										<input type="text" name="icono" list="iconos-fa" placeholder="fa-tags" value="fa-tags" class="form-control">
										<datalist id="iconos-fa">
											<option value="fa-prescription-bottle-alt">
											<option value="fa-glasses">
											<option value="fa-vials">
											<option value="fa-dumbbell">
											<option value="fa-handshake">
											<option value="fa-wheelchair">
											<option value="fa-tags">
											<option value="fa-tooth">
											<option value="fa-stethoscope">
											<option value="fa-heart">
										</datalist>
										<span class="help-block">Código de ícono Font Awesome. Si no sabés cuál, dejá <strong>fa-tags</strong>.</span>
									</div>
								</div>
							</fieldset>

							<fieldset>
								<div class="form-group">
									<label class="col-lg-2 control-label">Color</label>
									<div class="col-lg-4">
										<input type="color" name="color" value="#6CA3AB" class="form-control" style="height:38px;padding:4px;">
										<span class="help-block">Color de acento de la categoría (opcional).</span>
									</div>
									<label class="col-lg-2 control-label">Orden</label>
									<div class="col-lg-2">
										<input type="number" name="orden" value="0" class="form-control">
									</div>
								</div>
							</fieldset>

							<fieldset>
								<div class="form-group">
									<div class="col-lg-6 col-lg-offset-2">
										<label class="checkbox-inline"><input type="checkbox" name="activo" value="1" checked> Mostrar esta categoría en el sitio</label>
									</div>
								</div>
							</fieldset>

							<input type="hidden" name="MM_insert" value="form2" />
							<fieldset>
								<div class="form-group">
									<div class="col-sm-6 col-sm-offset-2">
										<a href="<?php echo $URL?>admin/categorias/" class="btn btn-default">Cancelar</a>
										<button type="submit" class="btn btn-primary">Guardar</button>
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
