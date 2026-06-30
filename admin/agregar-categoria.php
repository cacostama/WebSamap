<?php
require_once('funciones/db.php');
require_once('conexion.php');

if (isset($_SESSION['ADM_Username'])){

	if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form2") && samap_puede_escribir()) {

		$nombre = trim($_POST['nombre'] ?? '');
		$icono  = trim($_POST['icono'] ?? '');
		$color  = trim($_POST['color'] ?? '');
		$orden  = (int) ($_POST['orden'] ?? 0);
		$activo = isset($_POST['activo']) ? 1 : 0;

		if ($icono === '') { $icono = 'fa-tags'; }
		$color_bind = $color !== '' ? $color : null;

		if ($nombre !== '') {
			$new_id = 0;
			$stmt = $conexion->prepare('INSERT INTO tbl_categorias_aliado (nombre, icono, color, orden, activo) VALUES (?, ?, ?, ?, ?)');
			if ($stmt) {
				$stmt->bind_param('sssii', $nombre, $icono, $color_bind, $orden, $activo);
				$stmt->execute();
				$new_id = $stmt->insert_id;
				$stmt->close();
			}
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
										<div style="display:flex;gap:10px;align-items:center;">
											<em id="icono-preview" class="fa fa-tags" style="font-size:24px;color:#6CA3AB;width:32px;text-align:center;"></em>
											<select name="icono" id="icono-select" class="form-control" style="flex:1;" onchange="document.getElementById('icono-preview').className = 'fa ' + this.value;">
												<option value="fa-prescription-bottle-alt">Farmacias (botella)</option>
												<option value="fa-glasses">Ópticas (anteojos)</option>
												<option value="fa-vials">Laboratorios (tubos)</option>
												<option value="fa-dumbbell">Gimnasios (mancuerna)</option>
												<option value="fa-handshake">Cooperativas (apretón)</option>
												<option value="fa-wheelchair">Ortopedia (silla)</option>
												<option value="fa-tags" selected>Otros (etiqueta)</option>
												<option value="fa-tooth">Odontología (diente)</option>
												<option value="fa-stethoscope">Médico (estetoscopio)</option>
												<option value="fa-heart">Salud (corazón)</option>
												<option value="fa-utensils">Gastronomía (cubiertos)</option>
												<option value="fa-graduation-cap">Educación (birrete)</option>
												<option value="fa-shopping-bag">Comercio (bolsa)</option>
												<option value="fa-car">Automotor (auto)</option>
												<option value="fa-home">Hogar (casa)</option>
											</select>
										</div>
										<span class="help-block">Elegí el ícono que mejor representa a la categoría. La vista previa de la izquierda muestra cómo se verá en el sitio.</span>
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
	<?php include 'partials/scripts-comunes.php'; ?>

</body>
</html>
