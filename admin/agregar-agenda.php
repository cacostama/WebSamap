<?php
require_once('funciones/db.php');

if (isset($_SESSION['ADM_Username'])){
	


	if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form2")) {

		$especiales = array("á", "Á", "é", "É", "í", "Í", "ó", "Ó", "ú", "Ú", "ñ", "Ñ", " ");
		$correctos   = array("a", "A", "e", "E", "i", "I", "o", "O", "u", "U", "n", "N", "-");
	    //--------INICIO IMAGEN1---------//
		$imagen_real=$_FILES['imagen']['name'];

		if ($_FILES['imagen']['name'] != "") { 

			$imagen_real=str_replace($especiales, $correctos, $_FILES['imagen']['name']);
			move_uploaded_file($_FILES['imagen']['tmp_name'],$rutaSpeaker.$imagen_real);
			$img_original = "$rutaSpeaker/".$imagen_real;
			$type = @getimagesize($img_original);

		}   
		$imagen_real2=$_FILES['imagen2']['name'];

		if ($_FILES['imagen2']['name'] != "") { 

			$imagen_real2=str_replace($especiales, $correctos, $_FILES['imagen2']['name']);
			move_uploaded_file($_FILES['imagen2']['tmp_name'],$rutaSpeaker.$imagen_real2);
			$img_original = "$rutaSpeaker/".$imagen_real2;
			$type = @getimagesize($img_original);

		}   
	    //--------FIN IMAGEN1---------//




			$fecha = $_POST['fecha'];
			$titulo = $_POST['titulo'];
			$horario = $_POST['horario'];
			$lugar = $_POST['lugar'];
			$texto = $_POST['texto'];
			$idSpeaker1 = $_POST['idSpeaker1'];
			$idSpeaker2 = $_POST['idSpeaker2'];
			$idSpeaker3 = $_POST['idSpeaker3'];
			$idSpeaker4 = $_POST['idSpeaker4'];
			$idSpeaker5 = $_POST['idSpeaker5'];
			$idSpeaker6 = $_POST['idSpeaker6'];
			$idSpeaker7 = $_POST['idSpeaker7'];
			$idSpeaker8 = $_POST['idSpeaker8'];
			$idSpeaker9 = $_POST['idSpeaker9'];
			$idSpeaker10 = $_POST['idSpeaker10'];
			
			$IMAGEN = $imagen_real;
			

			$insertSQL = "INSERT INTO tbl_agenda_detalle (idAgenda, titulo, horario, lugar, texto, idSpeaker1, idSpeaker2, idSpeaker3, idSpeaker4, idSpeaker5, idSpeaker6, idSpeaker7, idSpeaker8, idSpeaker9, idSpeaker10) VALUES ('$fecha','$titulo','$horario','$lugar','$texto','$idSpeaker1','$idSpeaker2','$idSpeaker3','$idSpeaker4','$idSpeaker5','$idSpeaker6','$idSpeaker7','$idSpeaker8','$idSpeaker9','$idSpeaker10')";
			mysqli_select_db($connect, $database);
			$Result1 = mysqli_query($connect, $insertSQL) or die(mysqli_error($link));
			samap_flash_set('success', 'Agenda guardada correctamente.');
			header('Location: ' . $URL . 'admin/agenda/');

	}

	mysqli_select_db($connect, $database);
	$query_fecha = "SELECT * FROM tbl_agenda ";
	$fecha = mysqli_query($connect, $query_fecha) or die(mysqli_error($link));
	$row_fecha = mysqli_fetch_assoc($fecha);
	$totalRows_fecha = mysqli_num_rows($fecha);

	mysqli_select_db($connect, $database);
	$query_speaker1= "SELECT * FROM tbl_speaker";
	$speaker1 = mysqli_query($connect, $query_speaker1) or die(mysqli_error($link));
	$row_speaker1 = mysqli_fetch_assoc($speaker1);
	$totalRows_speaker1 = mysqli_num_rows($speaker1);

	mysqli_select_db($connect, $database);
	$query_speaker2= "SELECT * FROM tbl_speaker";
	$speaker2 = mysqli_query($connect, $query_speaker2) or die(mysqli_error($link));
	$row_speaker2 = mysqli_fetch_assoc($speaker2);
	$totalRows_speaker2 = mysqli_num_rows($speaker2);

	mysqli_select_db($connect, $database);
	$query_speaker3= "SELECT * FROM tbl_speaker";
	$speaker3 = mysqli_query($connect, $query_speaker3) or die(mysqli_error($link));
	$row_speaker3 = mysqli_fetch_assoc($speaker3);
	$totalRows_speaker3 = mysqli_num_rows($speaker3);

	mysqli_select_db($connect, $database);
	$query_speaker4= "SELECT * FROM tbl_speaker";
	$speaker4 = mysqli_query($connect, $query_speaker4) or die(mysqli_error($link));
	$row_speaker4 = mysqli_fetch_assoc($speaker4);
	$totalRows_speaker4 = mysqli_num_rows($speaker4);

	mysqli_select_db($connect, $database);
	$query_speaker5= "SELECT * FROM tbl_speaker";
	$speaker5= mysqli_query($connect, $query_speaker5) or die(mysqli_error($link));
	$row_speaker5 = mysqli_fetch_assoc($speaker5);
	$totalRows_speaker5 = mysqli_num_rows($speaker5);

	mysqli_select_db($connect, $database);
	$query_speaker6= "SELECT * FROM tbl_speaker";
	$speaker6= mysqli_query($connect, $query_speaker6) or die(mysqli_error($link));
	$row_speaker6= mysqli_fetch_assoc($speaker6);
	$totalRows_speaker6 = mysqli_num_rows($speaker6);

	mysqli_select_db($connect, $database);
	$query_speaker7= "SELECT * FROM tbl_speaker";
	$speaker7 = mysqli_query($connect, $query_speaker7) or die(mysqli_error($link));
	$row_speaker7 = mysqli_fetch_assoc($speaker7);
	$totalRows_speaker7 = mysqli_num_rows($speaker7);

	mysqli_select_db($connect, $database);
	$query_speaker8= "SELECT * FROM tbl_speaker";
	$speaker8 = mysqli_query($connect, $query_speaker8) or die(mysqli_error($link));
	$row_speaker8 = mysqli_fetch_assoc($speaker8);
	$totalRows_speaker8 = mysqli_num_rows($speaker8);

	mysqli_select_db($connect, $database);
	$query_speaker9= "SELECT * FROM tbl_speaker";
	$speaker9 = mysqli_query($connect, $query_speaker9) or die(mysqli_error($link));
	$row_speaker9 = mysqli_fetch_assoc($speaker9);
	$totalRows_speaker9 = mysqli_num_rows($speaker9);

	mysqli_select_db($connect, $database);
	$query_speaker10= "SELECT * FROM tbl_speaker";
	$speaker10 = mysqli_query($connect, $query_speaker10) or die(mysqli_error($link));
	$row_speaker10 = mysqli_fetch_assoc($speaker10);
	$totalRows_speaker10 = mysqli_num_rows($speaker10);

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
	<title>AGREGAR AGENDA -  Administrador</title>

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
				<h3>Agregar Agenda
				</h3>

				<div class="panel panel-default">
					<div class="panel-heading">Formulario de Carga</div>
					<div class="panel-body">
						<form class="form-horizontal" action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data" name="form2" id="form2">


								<fieldset>
									<div class="form-group">
										<label class="col-sm-2 control-label">Fecha</label>
										<div class="col-sm-10">
											<div class="row">
												<div class="col-sm-4">
													<select name="fecha" id="fecha" style="width: 255px;" required>
														<option value="" >Seleccionar Fecha</option>
														<?php do {  ?>

														<option value="<?php echo $row_fecha['id'];?>" ><?php echo $row_fecha['nombre'];?></option>
														<?php
			                                                $row_fecha = mysqli_fetch_assoc($fecha);
			                                                } while ($row_fecha);   //end horizontal looper 
			                                            ?>
													</select>
													
												</div>
											</div>
										</div>
									</div>
								</fieldset>

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
										<label class="col-lg-2 control-label">Horario</label>
										<div class="col-lg-10">
											<input type="text" name="horario" placeholder="" value=""  class="form-control">

											
											
										</div>

									</div>
								</fieldset>
								<fieldset>
									<div class="form-group">
										<label class="col-lg-2 control-label">Lugar</label>
										<div class="col-lg-10">
											<input type="text" name="lugar" placeholder="" value=""  class="form-control">

											
											
										</div>

									</div>
								</fieldset>
								<fieldset>
									<div class="form-group">
										<label class="col-sm-2 control-label">Texto
											
										</label>
										<div class="col-sm-10">
											<textarea class="form-control" id="code_preview1" name="texto" style="height: 300px;"></textarea>
										</div>
									</div>
								</fieldset>
								
								<fieldset>
									<div class="form-group">
										<label class="col-sm-2 control-label">Speakers</label>
										<div class="col-sm-10">
											<div class="row">
												<div class="col-sm-4">
													<select name="idSpeaker1" id="idSpeaker1" style="width: 255px;">
														<option value="" >Seleccionar Speaker 1</option>
														<?php do {  ?>

														<option value="<?php echo $row_speaker1['id'];?>" ><?php echo $row_speaker1['nombre'];?></option>
														<?php
			                                                $row_speaker1 = mysqli_fetch_assoc($speaker1);
			                                                } while ($row_speaker1);   //end horizontal looper 
			                                            ?>
													</select>
													<br><br>
												</div>

												<div class="col-sm-4">
													<select name="idSpeaker2" id="idSpeaker2" style="width: 255px;">
														<option value="" >Seleccionar Speaker 2</option>
														<?php do {  ?>

														<option value="<?php echo $row_speaker2['id'];?>" ><?php echo $row_speaker2['nombre'];?></option>
														<?php
			                                                $row_speaker2 = mysqli_fetch_assoc($speaker2);
			                                                } while ($row_speaker2);   //end horizontal looper 
			                                            ?>
													</select>
													<br><br>
												</div>

												<div class="col-sm-4">
													<select name="idSpeaker3" id="idSpeaker3" style="width: 255px;">
														<option value="" >Seleccionar Speaker 3</option>
														<?php do {  ?>

														<option value="<?php echo $row_speaker3['id'];?>" ><?php echo $row_speaker3['nombre'];?></option>
														<?php
			                                                $row_speaker3 = mysqli_fetch_assoc($speaker3);
			                                                } while ($row_speaker3);   //end horizontal looper 
			                                            ?>
													</select>
													<br><br>
												</div>

												<div class="col-sm-4">
													<select name="idSpeaker4" id="idSpeaker4" style="width: 255px;">
														<option value="" >Seleccionar Speaker 4</option>
														<?php do {  ?>

														<option value="<?php echo $row_speaker4['id'];?>" ><?php echo $row_speaker4['nombre'];?></option>
														<?php
			                                                $row_speaker4 = mysqli_fetch_assoc($speaker4);
			                                                } while ($row_speaker4);   //end horizontal looper 
			                                            ?>
													</select>
													<br><br>
												</div>

												<div class="col-sm-4">
													<select name="idSpeaker5" id="idSpeaker5" style="width: 255px;">
														<option value="" >Seleccionar Speaker 5</option>
														<?php do {  ?>

														<option value="<?php echo $row_speaker5['id'];?>" ><?php echo $row_speaker5['nombre'];?></option>
														<?php
			                                                $row_speaker5 = mysqli_fetch_assoc($speaker5);
			                                                } while ($row_speaker5);   //end horizontal looper 
			                                            ?>
													</select>
													<br><br>
												</div>

												<div class="col-sm-4">
													<select name="idSpeaker6" id="idSpeaker6" style="width: 255px;">
														<option value="" >Seleccionar Speaker 6</option>
														<?php do {  ?>

														<option value="<?php echo $row_speaker6['id'];?>" ><?php echo $row_speaker6['nombre'];?></option>
														<?php
			                                                $row_speaker6 = mysqli_fetch_assoc($speaker6);
			                                                } while ($row_speaker6);   //end horizontal looper 
			                                            ?>
													</select>
													<br><br>
												</div>

												<div class="col-sm-4">
													<select name="idSpeaker7" id="idSpeaker7" style="width: 255px;">
														<option value="" >Seleccionar Speaker 7</option>
														<?php do {  ?>

														<option value="<?php echo $row_speaker7['id'];?>" ><?php echo $row_speaker7['nombre'];?></option>
														<?php
			                                                $row_speaker7 = mysqli_fetch_assoc($speaker7);
			                                                } while ($row_speaker7);   //end horizontal looper 
			                                            ?>
													</select>
													<br><br>
												</div>

												<div class="col-sm-4">
													<select name="idSpeaker8" id="idSpeaker8" style="width: 255px;">
														<option value="" >Seleccionar Speaker 8</option>
														<?php do {  ?>

														<option value="<?php echo $row_speaker8['id'];?>" ><?php echo $row_speaker8['nombre'];?></option>
														<?php
			                                                $row_speaker8 = mysqli_fetch_assoc($speaker8);
			                                                } while ($row_speaker8);   //end horizontal looper 
			                                            ?>
													</select>
													<br><br>
												</div>

												<div class="col-sm-4">
													<select name="idSpeaker9" id="idSpeaker9" style="width: 255px;">
														<option value="" >Seleccionar Speaker 9</option>
														<?php do {  ?>

														<option value="<?php echo $row_speaker9['id'];?>" ><?php echo $row_speaker9['nombre'];?></option>
														<?php
			                                                $row_speaker9 = mysqli_fetch_assoc($speaker9);
			                                                } while ($row_speaker9);   //end horizontal looper 
			                                            ?>
													</select>
													<br><br>
												</div>

												<div class="col-sm-4">
													<select name="idSpeaker10" id="idSpeaker10" style="width: 255px;">
														<option value="" >Seleccionar Speaker 10</option>
														<?php do {  ?>

														<option value="<?php echo $row_speaker10['id'];?>" ><?php echo $row_speaker10['nombre'];?></option>
														<?php
			                                                $row_speaker10 = mysqli_fetch_assoc($speaker10);
			                                                } while ($row_speaker10);   //end horizontal looper 
			                                            ?>
													</select>
													<br><br>
												</div>
											</div>
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
	<script src="<?php echo $URL;?>admin/app/js/app.js"></script>

	<script type="text/javascript">
	  $(document).ready(function() {
	    $('#code_preview0').summernote({height: 300});
	  	$('#code_preview1').summernote({height: 300});
	    });
	</script>
	
	<script src='//static.codepen.io/assets/common/stopExecutionOnTimeout-41c52890748cd7143004e05d3c5f786c66b19939c4500ce446314d1748483e13.js'>
	</script><script src='https://cdnjs.cloudflare.com/ajax/libs/summernote/0.6.6/summernote.min.js'></script>
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