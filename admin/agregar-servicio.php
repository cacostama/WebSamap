<?php
require_once('funciones/db.php');

if (isset($_SESSION['ADM_Username'])){

	mysqli_select_db($connect, $database);
	$query_categorias = "SELECT id, nombre FROM tbl_categorias_aliado WHERE deleted_at IS NULL AND activo = 1 ORDER BY orden ASC, nombre ASC";
	$categorias = mysqli_query($connect, $query_categorias) or die(mysqli_error($connect));

	if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form2") && samap_puede_escribir() && samap_csrf_validar()) {

		try {
			$imagen_real = samap_guardar_imagen_upload('imagen', $rutaServicios);
		} catch (RuntimeException $e) {
			samap_flash_set('error', $e->getMessage());
			header('Location: ' . $URL . 'admin/servicios/');
			exit;
		}

			$intro = $_POST['intro'];
			$nombre = $_POST['nombre'];
			$detalle= htmlentities( (string)($_POST['detalle'] ?? ''), ENT_QUOTES, 'UTF-8' );
			$IMAGEN = $imagen_real;
			$categoria_id = (int) ($_POST['categoria_id'] ?? 0);
			$categoria_sql = $categoria_id > 0 ? $categoria_id : 'NULL';

			$insertSQL = "INSERT INTO tbl_servicios (titulo, intro, detalle, url, imagen, categoria_id) VALUES ('$nombre', '$intro','$detalle','','$IMAGEN',$categoria_sql)";
			mysqli_select_db($connect, $database);
			$Result1 = mysqli_query($connect, $insertSQL) or die(mysqli_error($connect));
			$new_id = mysqli_insert_id($connect);
			@samap_audit_log('insert', 'tbl_servicios', $new_id, "Creó el servicio: " . substr((string)$nombre, 0, 100), null, ['id' => $new_id, 'titulo' => $nombre, 'categoria_id' => $categoria_id, 'imagen' => $IMAGEN]);
			samap_flash_set('success', 'Servicio guardado correctamente.');
			header('Location: ' . $URL . 'admin/servicios/');
			exit;

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
	<meta name="description" content="">
	<meta name="keywords" content="">
	<meta name="author" content="">
	<title>AGREGAR SERVICIO -  Administrador</title>

	</script><link rel="stylesheet" href="<?php echo $URL;?>admin/app/css/bootstrap.css">

	<link rel="stylesheet" href="<?php echo $URL;?>admin/plugins/fontawesome/css/font-awesome.min.css">
	<link rel="stylesheet" href="<?php echo $URL;?>admin/plugins/animo/animate+animo.css">
	<link rel="stylesheet" href="<?php echo $URL;?>admin/plugins/csspinner/csspinner.min.css">

	<link rel="stylesheet" href="<?php echo $URL;?>admin/app/css/app.css?v=202606291705">

	<script src="<?php echo $URL;?>admin/plugins/modernizr/modernizr.js" type="application/javascript"></script>

	<script src="<?php echo $URL;?>admin/plugins/fastclick/fastclick.js" type="application/javascript"></script>
	<link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/summernote/0.6.6/summernote.min.css'>
	<style type="text/css">
	.note-editor {
		margin-bottom: 5rem !important;
	}
	</style>
</head>
<body>

	<section class="wrapper">

		<?php include 'header.php'; ?>
		<?php include 'aside.php'; ?>

		<section>

			<section class="main-content">
				<h3>Agregar Servicio
				</h3>

				<div class="panel panel-default">
					<div class="panel-heading">Formulario de Carga</div>
					<div class="panel-body">
						<form class="form-horizontal" action="" method="post" enctype="multipart/form-data" name="form2" id="form2">
							<?php echo samap_csrf_field(); ?>


								<fieldset>
									<div class="form-group">
										<label class="col-lg-2 control-label">Titulo</label>
										<div class="col-lg-10">
											<input type="text" name="nombre" placeholder="" value=""  class="form-control">											
										</div>

									</div>
								</fieldset>	

								<fieldset>
									<div class="form-group">
										<label class="col-sm-2 control-label">Intro
											
										</label>
										<div class="col-sm-10">
											<textarea class="form-control" name="intro" style="height: 300px;"></textarea>
										</div>
									</div>
								</fieldset>

								<fieldset>
									<div class="form-group">
										<label class="col-sm-2 control-label">Descripcion
											
										</label>
										<div class="col-sm-10">
											<textarea class="form-control" id="code_preview1" name="detalle" style="height: 300px;"></textarea>
										</div>
									</div>
								</fieldset>

								<fieldset>
									<?php
									$upload_campo      = 'imagen';
									$upload_label      = 'Imagen';
									$upload_subcarpeta = 'servicios';
									$upload_ruta       = $rutaServicios;
									$upload_medida     = 'Mantener proporcion. JPG/PNG/WEBP, max 5 MB.';
									$upload_label_col  = 'col-sm-2';
									$upload_input_col  = 'col-sm-4';
									include 'partials/upload-imagen.php';
									?>
								</fieldset>

								<fieldset>
									<div class="form-group">
										<label class="col-sm-2 control-label">Comercios adheridos</label>
										<div class="col-sm-6">
											<select name="categoria_id" class="form-control">
												<option value="0">— Ninguno (usar solo el texto de arriba) —</option>
												<?php while ($cat = mysqli_fetch_assoc($categorias)) { ?>
													<option value="<?php echo (int) $cat['id']; ?>"><?php echo htmlspecialchars($cat['nombre'], ENT_QUOTES, 'UTF-8'); ?></option>
												<?php } ?>
											</select>
											<span class="help-block">Si elegís una categoría, esta página mostrará automáticamente los comercios adheridos (logo + descuento) que cargaste en <strong>Aliados</strong>. Dejala en "Ninguno" para beneficios que no son una lista de comercios.</span>
										</div>
									</div>
								</fieldset>

								<input type="hidden" name="id" value="" />
								<input type="hidden" name="MM_insert" value="form2" />
								<fieldset>
									<div class="form-group">
										<div class="col-sm-4 col-sm-offset-2">
											<button type="button" class="btn btn-default" onclick="window.history.back();">Cancelar</button>
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

		<script src="<?php echo $URL;?>admin/plugins/chosen/chosen.jquery.min.js"></script>
		<script src="<?php echo $URL;?>admin/plugins/slider/js/bootstrap-slider.js"></script>
		<script src="<?php echo $URL;?>admin/plugins/filestyle/bootstrap-filestyle.min.js"></script>

		<script src="<?php echo $URL;?>admin/plugins/animo/animo.min.js"></script>

		<script src="<?php echo $URL;?>admin/plugins/sparklines/jquery.sparkline.min.js"></script>

		<script src="<?php echo $URL;?>admin/plugins/slimscroll/jquery.slimscroll.min.js"></script>

		<script src="<?php echo $URL;?>admin/plugins/flot/jquery.flot.min.js"></script>
	<script src="<?php echo $URL;?>admin/plugins/flot/jquery.flot.tooltip.min.js"></script>
	<script src="<?php echo $URL;?>admin/plugins/flot/jquery.flot.resize.min.js"></script>
	<script src="<?php echo $URL;?>admin/plugins/flot/jquery.flot.pie.min.js"></script>
	<script src="<?php echo $URL;?>admin/plugins/flot/jquery.flot.time.min.js"></script>
	<script src="<?php echo $URL;?>admin/plugins/flot/jquery.flot.categories.min.js"></script>

		<!--[if lt IE 8]><script src="js/excanvas.min.js"></script><![endif]-->
		<script src="<?php echo $URL;?>admin/plugins/moment/min/moment-with-langs.min.js"></script>
		<script src="<?php echo $URL;?>admin/plugins/datetimepicker/js/bootstrap-datetimepicker.min.js"></script>


	<script src="<?php echo $URL;?>admin/plugins/inputmask/jquery.inputmask.bundle.min.js"></script>
	<script src="<?php echo $URL;?>admin/app/js/app.js"></script>

	<script type="text/javascript">
	  $(document).ready(function() {
	    $('#code_preview0').summernote({height: 300});
	  	$('#code_preview1').summernote({height: 300});
	    });
	</script>
		<script src='https://cdnjs.cloudflare.com/ajax/libs/summernote/0.6.6/summernote.min.js'></script>
	<script >var content_row = 1;
		function addContent() {
		  html = '<div id="content-row">';
		  html += '<div class="form-group">';
		  html += '<label class="col-sm-2">Page Content</label>';
		  html += '<div class="col-sm-10">';
		  html += '<textarea class="form-control" id="code_preview' + content_row + '" name="page_code[' + content_row + '][code]" style="height: 300px;"></textarea>';
		  html += '</div>';
		  html += '</div>';
		  html += '</div>';
		  $('#content-row').append(html);
		  $('#code_preview' + content_row).summernote({ height: 300 });

		  content_row++;
		}
		//# sourceURL=pen.js
	</script>

		
		

</body>
</html>