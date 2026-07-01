<?php
require_once('funciones/db.php');
require_once('conexion.php');

if (isset($_SESSION['ADM_Username'])){



	if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form2") && samap_puede_escribir() && samap_csrf_validar()) {

		try {
			$imagen_real = samap_guardar_imagen_upload('imagen', $rutaSlider);
		} catch (RuntimeException $e) {
			samap_flash_set('error', $e->getMessage());
			header('Location: ' . $URL . 'admin/slider/');
			exit;
		}

			$nombre = ($_POST['nombre'] ?? '');
			$IMAGEN = $imagen_real;

			$new_id = 0;
			$stmt = $conexion->prepare('INSERT INTO tbl_slider (nombre, imagen) VALUES (?, ?)');
			if ($stmt) {
				$stmt->bind_param('ss', $nombre, $IMAGEN);
				$stmt->execute();
				$new_id = $stmt->insert_id;
				$stmt->close();
			}
			@samap_audit_log('insert', 'tbl_slider', $new_id, "Creó el slider: " . substr((string)$nombre, 0, 100), null, ['id' => $new_id, 'nombre' => $nombre, 'imagen' => $IMAGEN]);
			samap_flash_set('success', 'Slider guardado correctamente.');
			header('Location: ' . $URL . 'admin/slider/');
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
	<title>AGREGAR LIDER -  Administrador</title>

	</script><link rel="stylesheet" href="<?php echo $URL;?>admin/app/css/bootstrap.css">

	<link rel="stylesheet" href="<?php echo $URL;?>admin/plugins/fontawesome/css/font-awesome.min.css">
	<link rel="stylesheet" href="<?php echo $URL;?>admin/plugins/animo/animate+animo.css">
	<link rel="stylesheet" href="<?php echo $URL;?>admin/plugins/csspinner/csspinner.min.css">

	<link rel="stylesheet" href="<?php echo $URL;?>admin/app/css/app.css?v=202606291705">
	<link rel='stylesheet' href='<?php echo $URL;?>admin/plugins/summernote/summernote.min.css'>
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
				<h3>Agregar Slider
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
									<?php
									$upload_campo      = 'imagen';
									$upload_label      = 'Slider';
									$upload_subcarpeta = 'slider';
									$upload_ruta       = $rutaSlider;
									$upload_medida     = 'Medida recomendada: 838 × 960 px';
									$upload_label_col  = 'col-sm-2';
									$upload_input_col  = 'col-sm-4';
									include 'partials/upload-imagen.php';
									?>
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
	<?php include 'partials/scripts-comunes.php'; ?>

	<script src='<?php echo $URL;?>admin/plugins/summernote/summernote.min.js'></script>
	<script type="text/javascript">
	  $(document).ready(function() {
	    $('#code_preview0').summernote({height: 300});
	  	$('#code_preview1').summernote({
	  		height: 300,
	  		onChange: function() { if (window.samapFormMarkDirty) window.samapFormMarkDirty(); }
	  	});
	    });
	</script>
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

