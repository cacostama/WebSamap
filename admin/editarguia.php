<?php
require_once('funciones/db.php');
require_once('conexion.php');

if (isset($_SESSION['ADM_Username'])){

	$COD = $_GET['cod'];
	settype($COD, 'integer');

	mysqli_select_db($connect, $database);
	$query_guia = sprintf("SELECT * FROM tbl_guiamedica WHERE id = '%d'",$COD);
	$guia = mysqli_query($connect, $query_guia) or die(mysqli_error($connect));
	$row_guia = mysqli_fetch_assoc($guia);
	$totalRows_guia = mysqli_num_rows($guia);

	mysqli_select_db($connect, $database);
	$query_especialidad = "SELECT * FROM tbl_especialidad";
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
	    //--------INICIO IMAGEN1---------//

	   
		$imagen_real=$_FILES['imagen']['name'];

		if ($_FILES['imagen']['name'] != "") { 

			$imagen_real=str_replace($especiales, $correctos, $_FILES['imagen']['name']);
			move_uploaded_file($_FILES['imagen']['tmp_name'],$rutaBlog.$imagen_real);
			$img_original = "$rutaBlog/".$imagen_real;
			$type = @getimagesize($img_original);

		}   
		
	    //--------FIN IMAGEN1---------//

			$id_post = (int) ($_POST['id'] ?? 0);
			$titulo_post = (string) ($_POST['titulo'] ?? '');
			$nombre_post = (string) ($_POST['nombre'] ?? '');
			$especialidad_post = (int) ($_POST['especialidada'] ?? 0);
			$sanatorio_post = (int) ($_POST['sanatorio'] ?? 0);

			if ($imagen_real != "") {
				$stmt = $conexion->prepare('UPDATE tbl_guiamedica SET titulo = ?, nombre = ?, idEspecialidad = ?, idSanatorios = ?, imagen = ? WHERE id = ?');
				if ($stmt) {
					$stmt->bind_param('ssiiis', $titulo_post, $nombre_post, $especialidad_post, $sanatorio_post, $imagen_real, $id_post);
					$stmt->execute();
					$stmt->close();
				}
			} else {
				$stmt = $conexion->prepare('UPDATE tbl_guiamedica SET titulo = ?, nombre = ?, idEspecialidad = ?, idSanatorios = ? WHERE id = ?');
				if ($stmt) {
					$stmt->bind_param('ssiii', $titulo_post, $nombre_post, $especialidad_post, $sanatorio_post, $id_post);
					$stmt->execute();
					$stmt->close();
				}
			}
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
	<title>EDITAR MÉDICO -  Administrador</title>

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
				<h3>Editar Medico </h3>

				<div class="panel panel-default">
					<div class="panel-heading">Formulario de Edición</div>
					<div class="panel-body">
						<form class="form-horizontal" action="" method="post" enctype="multipart/form-data" name="form2" id="form2">


							<fieldset>
								<div class="form-group">
									<label class="col-lg-2 control-label">Titulo</label>
									<div class="col-lg-10">
										<input type="text" name="titulo" placeholder="" value="<?php echo $row_guia['titulo']; ?>"  class="form-control">
									</div>
								</div>
							</fieldset>

							<fieldset>
								<div class="form-group">
									<label class="col-lg-2 control-label">Nombre</label>
									<div class="col-lg-10">
										<input type="text" name="nombre" placeholder="" value="<?php echo $row_guia['nombre']; ?>"  class="form-control">
									</div>
								</div>
							</fieldset>

							<fieldset>
								<div class="form-group">
									<label class="col-sm-2 control-label">Especialidada
										
									</label>
									<div class="col-sm-10">
										<select name="especialidada" id="especialidada" style="width: 255px;">
                                            <?php do{ ?> 

                                            	<option <?php if($row_especialidad['id']== $row_guia['idEspecialidad']){ echo "selected";} ?> value="<?php echo $row_especialidad['id']; ?>"><?php echo $row_especialidad['nombre']; ?> </option>

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
										<select name="sanatorio" id="sanatorio" style="width: 255px;">
                                            <?php do{ ?> 

                                            	<option <?php if($row_sanatorio['id']== $row_guia['idSanatorios']){ echo "selected";} ?> value="<?php echo $row_sanatorio['id']; ?>"><?php echo $row_sanatorio['nombre']; ?> </option>

                                            <?php
												$row_sanatorio = mysqli_fetch_assoc($sanatorio);
												} while ($row_sanatorio);
											?> 
                                        </select>
									</div>
								</div>
							</fieldset>		

							<input type="hidden" name="id" value="<?php echo $row_guia['id']; ?>" />

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
