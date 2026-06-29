<?php
require_once('funciones/db.php');

if (isset($_SESSION['ADM_Username'])){
	
	$COD = $_GET['cod'];
	settype($COD, 'integer');

	mysqli_select_db($connect, $database);
	$query_sponsor = sprintf("SELECT * FROM tbl_slider WHERE id = '%d'",$COD);
	$sponsor = mysqli_query($connect, $query_sponsor) or die(mysqli_error($connect));
	$row_sponsor = mysqli_fetch_assoc($sponsor);
	$totalRows_sponsor = mysqli_num_rows($sponsor);

	if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form2") && samap_puede_escribir() && samap_csrf_validar()) {

		try {
			$imagen_real = samap_guardar_imagen_upload('imagen', $rutaSlider);
		} catch (RuntimeException $e) {
			samap_flash_set('error', $e->getMessage());
			header('Location: ' . $URL . 'admin/slider/');
			exit;
		}

			$sql_update = "UPDATE tbl_slider SET nombre='".$_POST['titulo']."'";

			if ($imagen_real != "") {
				$sql_update .= ", imagen='".$imagen_real."'";
			}

			$sql_update .= " WHERE id='".$_POST['id']."'";
			mysqli_select_db($connect, $database);
			$Result1 = mysqli_query($connect, $sql_update) or die(mysqli_error($connect));
			$snap = is_array($row_sponsor) ? $row_sponsor : [];
			$snap['nombre'] = $_POST['titulo']  ?? ($row_sponsor['nombre'] ?? '');
			$snap['imagen'] = $imagen_real !== '' ? $imagen_real : ($row_sponsor['imagen'] ?? '');
			$snap['id']     = $_POST['id']      ?? ($row_sponsor['id'] ?? 0);
			@samap_audit_log('update', 'tbl_slider', (int)$_POST['id'], "Editó el slider #" . (int)$_POST['id'] . ": " . substr((string)$_POST['titulo'], 0, 100), is_array($row_sponsor) ? $row_sponsor : null, $snap);
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
	<title>EDITAR SLIDER -  Administrador</title>

	</script><link rel="stylesheet" href="<?php echo $URL;?>admin/app/css/bootstrap.css">

	<link rel="stylesheet" href="<?php echo $URL;?>admin/plugins/fontawesome/css/font-awesome.min.css">
	<link rel="stylesheet" href="<?php echo $URL;?>admin/plugins/animo/animate+animo.css">
	<link rel="stylesheet" href="<?php echo $URL;?>admin/plugins/csspinner/csspinner.min.css">

	<link rel="stylesheet" href="<?php echo $URL;?>admin/app/css/app.css">

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
				<h3>Editar Slider
				</h3>

				<div class="panel panel-default">
					<div class="panel-heading">Formulario de Edición</div>
					<div class="panel-body">
						<form class="form-horizontal" action="" method="post" enctype="multipart/form-data" name="form2" id="form2">
							<?php echo samap_csrf_field(); ?>


								<fieldset>
									<div class="form-group">
										<label class="col-lg-2 control-label">Titulo</label>
										<div class="col-lg-10">
											<input type="text" name="titulo" placeholder="" value="<?php echo $row_sponsor['nombre']; ?>"  class="form-control">

										</div>

									</div>
								</fieldset>							
								
								<fieldset>
									<div class="form-group">
										<label name="imagen" class="col-sm-2 control-label">Slider</label>
										<div class="col-sm-4">
											<input name="imagen" type="file" data-classbutton="btn btn-default" data-classinput="form-control inline" class="filestyle form-control">
											<span><br>Tamaño del Slider: 4168 x 2345 px</span>
										</div>

										<div class="col-sm-4">
											<?php if ($row_sponsor['imagen'] != "") {?>
												<img width="100px" src="<?php echo $URL?>documentos/slider/<?php echo $row_sponsor['imagen']; ?>" alt=""/>
											<?php } else {?>
												<img width="60px" src="<?php echo $URL?>img/sin-imagen.jpg" alt=""/>
											<?php }?>
										</div>

									</div>
								</fieldset>	

								<input type="hidden" name="id" value="<?php echo $row_sponsor['id']; ?>" />

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
