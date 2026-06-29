<?php
require_once('funciones/db.php');

if (isset($_SESSION['ADM_Username'])){
	
	mysqli_select_db($connect, $database);
	$query_galeria = "SELECT * FROM tbl_galeria";
	$galeria = mysqli_query($connect, $query_galeria) or die(mysqli_error($connect));
	$row_galeria = mysqli_fetch_assoc($galeria);
	$totalRows_galeria = mysqli_num_rows($galeria);

	if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form2") && samap_puede_escribir() && samap_csrf_validar()) {

		try {
			$fotos = $_FILES['fotos'];
			$galeria_id = (int) ($_POST['galeria'] ?? 0);
			if ($galeria_id <= 0) {
				throw new RuntimeException('Debe seleccionar una galeria.');
			}
			if (empty($fotos) || empty($fotos['name']) || !is_array($fotos['name'])) {
				throw new RuntimeException('Debe seleccionar al menos una foto.');
			}

			$inserts = 0;
			$total = count($fotos['name']);
			for ($i = 0; $i < $total; $i++) {
				if (($fotos['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
					continue;
				}
				// Reempaquetamos el archivo i-esimo como un $_FILES['single']
				// para reusar samap_guardar_imagen_upload (que espera un solo
				// archivo, no el array indexado de un multi-upload).
				$_FILES['__foto_single'] = [
					'name'     => $fotos['name'][$i],
					'type'     => $fotos['type'][$i] ?? '',
					'tmp_name' => $fotos['tmp_name'][$i],
					'error'    => $fotos['error'][$i] ?? UPLOAD_ERR_OK,
					'size'     => $fotos['size'][$i] ?? 0,
				];
				$nombre = samap_guardar_imagen_upload('__foto_single', $rutaGaleria);
				if ($nombre === '') {
					continue;
				}
				$insertSQL = "INSERT INTO tbl_fotos (nombre, descripcion, ruta, galeria_id) VALUES ('$nombre', '', '$rutaGaleria', $galeria_id)";
				mysqli_select_db($connect, $database);
				$Result1 = mysqli_query($connect, $insertSQL) or die(mysqli_error($connect));
				$inserts++;
			}
			unset($_FILES['__foto_single']);

			if ($inserts === 0) {
				throw new RuntimeException('No se recibio ninguna foto valida.');
			}
			samap_flash_set('success', 'Fotos guardadas correctamente.');
			header('Location: ' . $URL . 'admin/fotos/');
			exit;
		} catch (RuntimeException $e) {
			samap_flash_set('error', $e->getMessage());
			header('Location: ' . $URL . 'admin/fotos/');
			exit;
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
	<meta name="description" content="">
	<meta name="keywords" content="">
	<meta name="author" content="">
	<title>AGREGAR SPEAKER -  Administrador</title>

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
				<h3>Agregar Speaker
				</h3>

				<div class="panel panel-default">
					<div class="panel-heading">Formulario de Carga</div>
					<div class="panel-body">
						<form class="form-horizontal" action="" method="post" enctype="multipart/form-data" name="form2" id="form2">
							<?php echo samap_csrf_field(); ?>

								<fieldset>
									<div class="form-group">
										<label class="col-sm-2 control-label">Galería</label>
										<div class="col-sm-10">
											<div class="row">
												<div class="col-sm-4">
													<select name="galeria" id="galeria" style="width: 255px;" required>
														<option value="" >Seleccionar Galería</option>

														<?php do { ?>
															<option value="<?php echo $row_galeria['id']; ?>"><?php echo $row_galeria['nombre']; ?></option>
														<?php
			                                                $row_galeria = mysqli_fetch_assoc($galeria);
			                                                } while ($row_galeria);   //end horizontal looper 
			                                            ?>

													</select>
													
												</div>
											</div>
										</div>
								</fieldset>
							
								
								<fieldset>
									<?php
									$upload_campo      = 'fotos';
									$upload_label      = 'Foto';
									$upload_subcarpeta = 'galeria';
									$upload_ruta       = $rutaGaleria;
									$upload_medida     = 'Mantener proporcion. JPG/PNG/WEBP, max 5 MB.';
									$upload_multiple   = true;
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



		<script src="<?php echo $URL;?>admin/plugins/jquery/jquery.min.js"></script>
		<script src="<?php echo $URL;?>admin/plugins/bootstrap/js/bootstrap.min.js"></script>

		<script src="<?php echo $URL;?>admin/plugins/chosen/chosen.jquery.min.js"></script>
		<script src="<?php echo $URL;?>admin/plugins/slider/js/bootstrap-slider.js"></script>
		<script src="<?php echo $URL;?>admin/plugins/filestyle/bootstrap-filestyle.min.js"></script>

		<script src="<?php echo $URL;?>admin/plugins/animo/animo.min.js"></script>

		<script src="<?php echo $URL;?>admin/plugins/sparklines/jquery.sparkline.min.js"></script>

		<script src="<?php echo $URL;?>admin/plugins/slimscroll/jquery.slimscroll.min.js"></script>


		<!--[if lt IE 8]><script src="js/excanvas.min.js"></script><![endif]-->
		<script src="<?php echo $URL;?>admin/plugins/moment/min/moment-with-langs.min.js"></script>
		<script src="<?php echo $URL;?>admin/plugins/datetimepicker/js/bootstrap-datetimepicker.min.js"></script>


	<script src="<?php echo $URL;?>admin/plugins/inputmask/jquery.inputmask.bundle.min.js"></script>
	<script src="<?php echo $URL;?>admin/app/js/app.js?v=202606291718"></script>

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