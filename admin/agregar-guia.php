<?php
require_once('funciones/db.php');

if (isset($_SESSION['ADM_Username'])){
	
	mysqli_select_db($connect, $database);
	$query_especialidad = "SELECT * FROM tbl_especialidad ORDER BY nombre  ASC";
	$especialidad = mysqli_query($connect, $query_especialidad) or die(mysqli_error($connect));
	$row_especialidad = mysqli_fetch_assoc($especialidad);
	$totalRows_especialidad= mysqli_num_rows($especialidad);

	mysqli_select_db($connect, $database);
	$query_sanatorio = "SELECT * FROM tbl_sanatorio";
	$sanatorio = mysqli_query($connect, $query_sanatorio) or die(mysqli_error($connect));
	$row_sanatorio = mysqli_fetch_assoc($sanatorio);
	$totalRows_sanatorio= mysqli_num_rows($sanatorio);

	if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form2")) {

		$especiales = array("á", "Á", "é", "É", "í", "Í", "ó", "Ó", "ú", "Ú", "ñ", "Ñ", " ");
		$correctos   = array("a", "A", "e", "E", "i", "I", "o", "O", "u", "U", "n", "N", "-");
	    

			$titulo = $_POST['titulo'];
			$nombre = $_POST['nombre'];
			$especialidad2 = intval($_POST['especialidad']);
			$sanatorio = $_POST['sanatorio'];

			$insertSQL = "INSERT INTO tbl_guiamedica (idEspecialidad, idSanatorios, titulo, nombre, cv) VALUES ('$especialidad2','$sanatorio','$titulo','$nombre','texto')";
			mysqli_select_db($connect, $database);
			$Result1 = mysqli_query($connect, $insertSQL) or die(mysqli_error($connect));
			samap_flash_set('success', 'Médico guardado correctamente.');
			header('Location: ' . $URL . 'admin/guia/');
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
	<title>AGREGAR MÉDICO -  Sanatorio</title>

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
				<h3>Agregar Médico</h3>

				<div class="panel panel-default">
					<div class="panel-heading">Formulario de Carga</div>
					<div class="panel-body">
						<form class="form-horizontal" action="" method="post" enctype="multipart/form-data" name="form2" id="form2">


								

							<fieldset>
								<div class="form-group">
									<label class="col-lg-2 control-label">Titulo</label>
									<div class="col-lg-10">
										<input type="text" name="titulo" placeholder="" value=""  class="form-control">
									</div>
								</div>
							</fieldset>

							<fieldset>
								<div class="form-group">
									<label class="col-lg-2 control-label">Nombre</label>
									<div class="col-lg-10">
										<input type="text" name="nombre" placeholder="" value=""  class="form-control">
									</div>
								</div>
							</fieldset>

							<fieldset>
								<div class="form-group">
									<label class="col-sm-2 control-label">Especialidad
										
									</label>
									<div class="col-sm-10">
										<select name="especialidad" id="especialidad" style="width: 255px;" required>

											<option  value="">Seleccione una Especialidad </option>

                                            <?php do{ ?> 
                                            	<option  value="<?php echo $row_especialidad['id']; ?>"><?php echo $row_especialidad['nombre']; ?> </option>
                                            <?php
												$row_especialidad = mysqli_fetch_assoc($especialidad);
												} while ($row_especialidad);
											?> 
                                        </select>
									</div>
								</div>
							</fieldset>			
																						
							<fieldset>
								<div class="form-group">
									<label class="col-sm-2 control-label">Sanatorio
										
									</label>
									<div class="col-sm-10">
										<select name="sanatorio" id="sanatorio" style="width: 255px;" required>

											<option  value="">Seleccione un Sanatorio </option>

                                            <?php do{ ?> 
                                            	<option value="<?php echo $row_sanatorio['id']; ?>"><?php echo $row_sanatorio['nombre']; ?> </option>
                                            <?php
												$row_sanatorio = mysqli_fetch_assoc($sanatorio);
												} while ($row_sanatorio);
											?> 
                                        </select>
									</div>
								</div>
							</fieldset>	

								
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