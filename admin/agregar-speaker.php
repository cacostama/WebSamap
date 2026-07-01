<?php
require_once('funciones/db.php');
require_once('conexion.php');

if (isset($_SESSION['ADM_Username'])){

	mysqli_select_db($connect, $database);
	$query_nacionalidad = "SELECT * FROM tbl_nacionalidad";
	$nacionalidad = mysqli_query($connect, $query_nacionalidad) or die(mysqli_error($connect));
	$row_nacionalidad = mysqli_fetch_assoc($nacionalidad);
	$totalRows_nacionalidad = mysqli_num_rows($nacionalidad);

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


			$nombre = (string) ($_POST['nombre'] ?? '');
			$titulo = (string) ($_POST['titulo'] ?? '');
			$intro = (string) ($_POST['intro'] ?? '');
			$texto = (string) ($_POST['detalle'] ?? '');
			$linkedin = (string) ($_POST['linkedin'] ?? '');
			$ig = (string) ($_POST['ig'] ?? '');
			$fb = (string) ($_POST['fb'] ?? '');
			$tw = (string) ($_POST['tw'] ?? '');
			$web = (string) ($_POST['web'] ?? '');
			$nacionalidad_id = (int) ($_POST['nacionalidad'] ?? 0);

			$IMAGEN = $imagen_real;

			$stmt = $conexion->prepare('INSERT INTO tbl_speaker (nombre, titulo, intro, texto, linkedin, ig, fb, tw, web, idNacionalidad, imagen) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
			if ($stmt) {
				$stmt->bind_param('sssssssssis', $nombre, $titulo, $intro, $texto, $linkedin, $ig, $fb, $tw, $web, $nacionalidad_id, $IMAGEN);
				$stmt->execute();
				$stmt->close();
			}
			samap_flash_set('success', 'Speaker guardado correctamente.');
			header('Location: ' . $URL . 'admin/speakers/');
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
	<title>AGREGAR SPEAKER -  Administrador</title>

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
				<h3>Agregar Speaker
				</h3>

				<div class="panel panel-default">
					<div class="panel-heading">Formulario de Carga</div>
					<div class="panel-body">
						<form class="form-horizontal" action="" method="post" enctype="multipart/form-data" name="form2" id="form2">


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
										<label class="col-lg-2 control-label">Titulo</label>
										<div class="col-lg-10">
											<input type="text" name="titulo" placeholder="" value=""  class="form-control">

										</div>

									</div>
								</fieldset>
								<fieldset>
									<div class="form-group">
										<label class="col-lg-2 control-label">Intro</label>
										<div class="col-lg-10">
											<input type="text" name="intro" placeholder="" value=""  class="form-control">

											
											
										</div>

									</div>
								</fieldset>
								<fieldset>
									<div class="form-group">
										<label class="col-sm-2 control-label">Texto
											
										</label>
										<div class="col-sm-10">
											<textarea class="form-control" id="code_preview1" name="detalle" style="height: 300px;"></textarea>
										</div>
									</div>
								</fieldset>
								<fieldset>
									<div class="form-group">
										<label class="col-lg-2 control-label">Linked In</label>
										<div class="col-lg-10">
											<input type="text" name="linkedin" placeholder="" value=""  class="form-control">
										</div>

									</div>
								</fieldset>
								
								<fieldset>
									<div class="form-group">
										<label class="col-lg-2 control-label">Web</label>
										<div class="col-lg-10">
											<input type="text" name="web" placeholder="" value=""  class="form-control">
										</div>

									</div>
								</fieldset>
								
							<fieldset>
								<div class="form-group">
									<label class="col-sm-2 control-label">Nacionalidad</label>
									<div class="col-sm-10">
										<div class="row">
											<div class="col-sm-4">
												<select name="nacionalidad" id="nacionalidad" style="width: 255px;" required>
													<option value="" >Seleccionar Nacionalidad</option>

													<?php do { ?>
														<option value="<?php echo $row_nacionalidad['id']; ?>" <?php if ($row_speaker['idNacionalidad'] == $row_nacionalidad['id']) {  echo "selected=\"selected\""; } ?>><?php echo $row_nacionalidad['nacionalidad']; ?></option>
													<?php
		                                                $row_nacionalidad = mysqli_fetch_assoc($nacionalidad);
		                                                } while ($row_nacionalidad);   //end horizontal looper 
		                                            ?>

												</select>
												
											</div>
										</div>
									</div>
							</fieldset>
								
							<fieldset>
									<div class="form-group">
										<label name="imagen" class="col-sm-2 control-label">Foto</label>
										<div class="col-sm-4">
											<input name="imagen" type="file" data-classbutton="btn btn-default" data-classinput="form-control inline" class="filestyle form-control">
											
											
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
