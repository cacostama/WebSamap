<?php
require_once('funciones/db.php');
require_once('conexion.php');

if (isset($_SESSION['ADM_Username'])){

	$COD = $_GET['cod'];
	settype($COD, 'integer');


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




			$id_post = (int) ($_POST['id'] ?? 0);
			$fecha_post = (int) ($_POST['fecha'] ?? 0);
			$titulo_post = (string) ($_POST['titulo'] ?? '');
			$horario_post = (string) ($_POST['horario'] ?? '');
			$lugar_post = (string) ($_POST['lugar'] ?? '');
			$texto_post = (string) ($_POST['texto'] ?? '');
			$idSpeaker1_post = (int) ($_POST['idSpeaker1'] ?? 0);
			$idSpeaker2_post = (int) ($_POST['idSpeaker2'] ?? 0);
			$idSpeaker3_post = (int) ($_POST['idSpeaker3'] ?? 0);
			$idSpeaker4_post = (int) ($_POST['idSpeaker4'] ?? 0);
			$idSpeaker5_post = (int) ($_POST['idSpeaker5'] ?? 0);
			$idSpeaker6_post = (int) ($_POST['idSpeaker6'] ?? 0);
			$idSpeaker7_post = (int) ($_POST['idSpeaker7'] ?? 0);
			$idSpeaker8_post = (int) ($_POST['idSpeaker8'] ?? 0);
			$idSpeaker9_post = (int) ($_POST['idSpeaker9'] ?? 0);
			$idSpeaker10_post = (int) ($_POST['idSpeaker10'] ?? 0);

			if ($imagen_real != "") {
				$stmt = $conexion->prepare('UPDATE tbl_agenda_detalle SET idAgenda = ?, titulo = ?, horario = ?, lugar = ?, texto = ?, idSpeaker1 = ?, idSpeaker2 = ?, idSpeaker3 = ?, idSpeaker4 = ?, idSpeaker5 = ?, idSpeaker6 = ?, idSpeaker7 = ?, idSpeaker8 = ?, idSpeaker9 = ?, idSpeaker10 = ?, imagen = ? WHERE id = ?');
				if ($stmt) {
					$stmt->bind_param('issssiiiiiiiiiiis', $fecha_post, $titulo_post, $horario_post, $lugar_post, $texto_post, $idSpeaker1_post, $idSpeaker2_post, $idSpeaker3_post, $idSpeaker4_post, $idSpeaker5_post, $idSpeaker6_post, $idSpeaker7_post, $idSpeaker8_post, $idSpeaker9_post, $idSpeaker10_post, $imagen_real, $id_post);
					$stmt->execute();
					$stmt->close();
				}
			} else {
				$stmt = $conexion->prepare('UPDATE tbl_agenda_detalle SET idAgenda = ?, titulo = ?, horario = ?, lugar = ?, texto = ?, idSpeaker1 = ?, idSpeaker2 = ?, idSpeaker3 = ?, idSpeaker4 = ?, idSpeaker5 = ?, idSpeaker6 = ?, idSpeaker7 = ?, idSpeaker8 = ?, idSpeaker9 = ?, idSpeaker10 = ? WHERE id = ?');
				if ($stmt) {
					$stmt->bind_param('issssiiiiiiiiiii', $fecha_post, $titulo_post, $horario_post, $lugar_post, $texto_post, $idSpeaker1_post, $idSpeaker2_post, $idSpeaker3_post, $idSpeaker4_post, $idSpeaker5_post, $idSpeaker6_post, $idSpeaker7_post, $idSpeaker8_post, $idSpeaker9_post, $idSpeaker10_post, $id_post);
					$stmt->execute();
					$stmt->close();
				}
			}
			samap_flash_set('success', 'Agenda guardada correctamente.');
			header('Location: ' . $URL . 'admin/agenda/');
			exit;

	}

	mysqli_select_db($connect, $database);
	$query_agenda = sprintf("SELECT * FROM tbl_agenda_detalle WHERE id = '%d'",$COD);
	$agenda = mysqli_query($connect, $query_agenda) or die(mysqli_error($connect));
	$row_agenda = mysqli_fetch_assoc($agenda);
	$totalRows_agenda = mysqli_num_rows($agenda);

	mysqli_select_db($connect, $database);
	$query_fecha = "SELECT * FROM tbl_agenda ";
	$fecha = mysqli_query($connect, $query_fecha) or die(mysqli_error($connect));
	$row_fecha = mysqli_fetch_assoc($fecha);
	$totalRows_fecha = mysqli_num_rows($fecha);

	mysqli_select_db($connect, $database);
	$query_speaker1= "SELECT * FROM tbl_speaker";
	$speaker1 = mysqli_query($connect, $query_speaker1) or die(mysqli_error($connect));
	$row_speaker1 = mysqli_fetch_assoc($speaker1);
	$totalRows_speaker1 = mysqli_num_rows($speaker1);

	mysqli_select_db($connect, $database);
	$query_speaker2= "SELECT * FROM tbl_speaker";
	$speaker2 = mysqli_query($connect, $query_speaker2) or die(mysqli_error($connect));
	$row_speaker2 = mysqli_fetch_assoc($speaker2);
	$totalRows_speaker2 = mysqli_num_rows($speaker2);

	mysqli_select_db($connect, $database);
	$query_speaker3= "SELECT * FROM tbl_speaker";
	$speaker3 = mysqli_query($connect, $query_speaker3) or die(mysqli_error($connect));
	$row_speaker3 = mysqli_fetch_assoc($speaker3);
	$totalRows_speaker3 = mysqli_num_rows($speaker3);

	mysqli_select_db($connect, $database);
	$query_speaker4= "SELECT * FROM tbl_speaker";
	$speaker4 = mysqli_query($connect, $query_speaker4) or die(mysqli_error($connect));
	$row_speaker4 = mysqli_fetch_assoc($speaker4);
	$totalRows_speaker4 = mysqli_num_rows($speaker4);

	mysqli_select_db($connect, $database);
	$query_speaker5= "SELECT * FROM tbl_speaker";
	$speaker5= mysqli_query($connect, $query_speaker5) or die(mysqli_error($connect));
	$row_speaker5 = mysqli_fetch_assoc($speaker5);
	$totalRows_speaker5 = mysqli_num_rows($speaker5);

	mysqli_select_db($connect, $database);
	$query_speaker6= "SELECT * FROM tbl_speaker";
	$speaker6= mysqli_query($connect, $query_speaker6) or die(mysqli_error($connect));
	$row_speaker6= mysqli_fetch_assoc($speaker6);
	$totalRows_speaker6 = mysqli_num_rows($speaker6);

	mysqli_select_db($connect, $database);
	$query_speaker7= "SELECT * FROM tbl_speaker";
	$speaker7 = mysqli_query($connect, $query_speaker7) or die(mysqli_error($connect));
	$row_speaker7 = mysqli_fetch_assoc($speaker7);
	$totalRows_speaker7 = mysqli_num_rows($speaker7);

	mysqli_select_db($connect, $database);
	$query_speaker8= "SELECT * FROM tbl_speaker";
	$speaker8 = mysqli_query($connect, $query_speaker8) or die(mysqli_error($connect));
	$row_speaker8 = mysqli_fetch_assoc($speaker8);
	$totalRows_speaker8 = mysqli_num_rows($speaker8);

	mysqli_select_db($connect, $database);
	$query_speaker9= "SELECT * FROM tbl_speaker";
	$speaker9 = mysqli_query($connect, $query_speaker9) or die(mysqli_error($connect));
	$row_speaker9 = mysqli_fetch_assoc($speaker9);
	$totalRows_speaker9 = mysqli_num_rows($speaker9);

	mysqli_select_db($connect, $database);
	$query_speaker10= "SELECT * FROM tbl_speaker";
	$speaker10 = mysqli_query($connect, $query_speaker10) or die(mysqli_error($connect));
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
				<h3>Editar Agenda
				</h3>

				<div class="panel panel-default">
					<div class="panel-heading">Formulario de Edición</div>
					<div class="panel-body">
						<form class="form-horizontal" action="" method="post" enctype="multipart/form-data" name="form2" id="form2">


								<fieldset>
									<div class="form-group">
										<label class="col-sm-2 control-label">Fecha</label>
										<div class="col-sm-10">
											<div class="row">
												<div class="col-sm-4">
													<select name="fecha" id="fecha" style="width: 255px;" required>
														<option value="" >Seleccionar Fecha</option>
														<?php do {  ?>

														<option value="<?php echo $row_fecha['id'];?>" <?php if($row_fecha['id']== $row_agenda['idAgenda']){ echo "selected";} ?>   ><?php echo $row_fecha['nombre'];?></option>
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
											<input type="text" name="titulo" placeholder="" value="<?php echo $row_agenda['titulo']; ?>"  class="form-control">

										</div>

									</div>
								</fieldset>
								<fieldset>
									<div class="form-group">
										<label class="col-lg-2 control-label">Horario</label>
										<div class="col-lg-10">
											<input type="text" name="horario" placeholder="" value="<?php echo $row_agenda['horario']; ?>"  class="form-control">

											
											
										</div>

									</div>
								</fieldset>
								<fieldset>
									<div class="form-group">
										<label class="col-lg-2 control-label">Lugar</label>
										<div class="col-lg-10">
											<input type="text" name="lugar" placeholder="" value="<?php echo $row_agenda['lugar']; ?>"  class="form-control">

											
											
										</div>

									</div>
								</fieldset>
								<fieldset>
									<div class="form-group">
										<label class="col-sm-2 control-label">Texto
											
										</label>
										<div class="col-sm-10">
											<textarea class="form-control" id="code_preview1" name="texto" style="height: 300px;"><?php echo $row_agenda['texto']; ?></textarea>
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

														<option value="<?php echo $row_speaker1['id'];?>" <?php if($row_speaker1['id']== $row_agenda['idSpeaker1']){ echo "selected";} ?>  ><?php echo $row_speaker1['nombre'];?></option>
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

														<option value="<?php echo $row_speaker2['id'];?>" <?php if($row_speaker2['id']== $row_agenda['idSpeaker2']){ echo "selected";} ?>  ><?php echo $row_speaker2['nombre'];?></option>
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

														<option value="<?php echo $row_speaker3['id'];?>" <?php if($row_speaker3['id']== $row_agenda['idSpeaker3']){ echo "selected";} ?>  ><?php echo $row_speaker3['nombre'];?></option>
														<?php
			                                                $row_speaker3 = mysqli_fetch_assoc($speaker3);
			                                                } while ($row_speaker3);   //end horizontal looper 
			                                            ?>
													</select>
													<br><br>
												</div>

												<div class="col-sm-4">
													<select name="idSpeaker4" id="idSpeaker4" style="width: 255px;">
														<option value="" <?php if($row_speaker4['id']== $row_agenda['idSpeaker4']){ echo "selected";} ?>  >Seleccionar Speaker 4</option>
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

														<option value="<?php echo $row_speaker5['id'];?>" <?php if($row_speaker5['id']== $row_agenda['idSpeaker5']){ echo "selected";} ?>  ><?php echo $row_speaker5['nombre'];?></option>
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

														<option value="<?php echo $row_speaker6['id'];?>" <?php if($row_speaker6['id']== $row_agenda['idSpeaker6']){ echo "selected";} ?>  ><?php echo $row_speaker6['nombre'];?></option>
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

														<option value="<?php echo $row_speaker7['id'];?>" <?php if($row_speaker7['id']== $row_agenda['idSpeaker7']){ echo "selected";} ?>  ><?php echo $row_speaker7['nombre'];?></option>
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

														<option value="<?php echo $row_speaker8['id'];?>" <?php if($row_speaker8['id']== $row_agenda['idSpeaker8']){ echo "selected";} ?>  ><?php echo $row_speaker8['nombre'];?></option>
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

														<option value="<?php echo $row_speaker9['id'];?>" <?php if($row_speaker9['id']== $row_agenda['idSpeaker9']){ echo "selected";} ?>  ><?php echo $row_speaker9['nombre'];?></option>
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

														<option value="<?php echo $row_speaker10['id'];?>" <?php if($row_speaker10['id']== $row_agenda['idSpeaker10']){ echo "selected";} ?>  ><?php echo $row_speaker10['nombre'];?></option>
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

								<input type="hidden" name="id" value="<?php echo $row_agenda['id']; ?>" />
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
