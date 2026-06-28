<?php
require_once('funciones/db.php');

if (isset($_SESSION['ADM_Username'])){
	
	$COD = $_GET['cod'];
	settype($COD, 'integer');

	mysqli_select_db($connect, $database);
	$query_sanatorio = sprintf("SELECT * FROM tbl_sanatorio WHERE id = '%d'",$COD);
	$sanatorio = mysqli_query($connect, $query_sanatorio) or die(mysqli_error($link));
	$row_sanatorio = mysqli_fetch_assoc($sanatorio);
	$totalRows_sanatorio = mysqli_num_rows($sanatorio);

	mysqli_select_db($connect, $database);
	$query_ciudad = "SELECT * FROM tbl_ciudad";
	$ciudad = mysqli_query($connect, $query_ciudad) or die(mysqli_error($link));
	$row_ciudad = mysqli_fetch_assoc($ciudad);
	$totalRows_ciudad = mysqli_num_rows($ciudad);

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

			$sql_update = "UPDATE tbl_sanatorio SET nombre='".$_POST['nombre']."', direccion='".$_POST['direccion']."', telefono='".$_POST['telefono']."', idCiudad='".$_POST['ciudad']."', estado='".$_POST['estado']."'"; 

			if ($imagen_real != "") {
				$sql_update .= ", imagen='".$imagen_real."'"; 
			}

			$sql_update .= " WHERE id='".$_POST['id']."'";
			mysqli_select_db($connect, $database);
			$Result1 = mysqli_query($connect, $sql_update) or die(mysqli_error($connect));
			echo"<script>alert('SANATORIO MODIFICADO CORRECTAMENTE!'); window.location.href=\"".$URL."admin/sanatorios/\"</script>";

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
	<title>EDITAR SANATORIO -  Administrador</title>

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
				<h3>Editar Sanatorio
				</h3>

				<div class="panel panel-default">
					<div class="panel-heading">Formulario de Edición</div>
					<div class="panel-body">
						<form class="form-horizontal" action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data" name="form2" id="form2">


							<fieldset>
								<div class="form-group">
									<label class="col-lg-2 control-label">Nombre</label>
									<div class="col-lg-10">
										<input type="text" name="nombre" placeholder="" value="<?php echo $row_sanatorio['nombre']; ?>"  class="form-control">
									</div>
								</div>
							</fieldset>

							<fieldset>
								<div class="form-group">
									<label class="col-lg-2 control-label">Dirección</label>
									<div class="col-lg-10">
										<input type="text" name="direccion" placeholder="" value="<?php echo $row_sanatorio['direccion']; ?>"  class="form-control">
									</div>
								</div>
							</fieldset>

							<fieldset>
								<div class="form-group">
									<label class="col-lg-2 control-label">Teléfono</label>
									<div class="col-lg-10">
										<input type="text" name="telefono" placeholder="" value="<?php echo $row_sanatorio['telefono']; ?>"  class="form-control">
									</div>
								</div>
							</fieldset>

							
							<fieldset>
								<div class="form-group">
									<label class="col-sm-2 control-label">Ciudad
										
									</label>
									<div class="col-sm-10">
										<select name="ciudad" id="ciudad" style="width: 255px;">
                                            <?php do{ ?> 

                                            	<option <?php if($row_ciudad['id']== $row_sanatorio['idCiudad']){ echo "selected";} ?> value="<?php echo $row_ciudad['id']; ?>"><?php echo $row_ciudad['nombre']; ?> </option>

                                            <?php
												$row_ciudad = mysqli_fetch_assoc($ciudad);
												} while ($row_ciudad);
											?> 
                                        </select>
									</div>
								</div>
							</fieldset>			
																						
							<fieldset>
								<div class="form-group">
									<label class="col-sm-2 control-label">Estado
										
									</label>
									<div class="col-sm-10">
										<select name="estado" id="estado" style="width: 255px;">
                                            <option value="1" <?php if ($row_sanatorio['estado'] == '1') {
                                                echo "selected=\"selected\"";
                                            } ?>>Activo</option>
                                            <option value="0" <?php if ($row_sanatorio['estado'] == '0') {
                                                echo "selected=\"selected\"";
                                           
                                            } ?>>Inactivo</option>
                                        </select>
									</div>
								</div>
							</fieldset>			

							<input type="hidden" name="id" value="<?php echo $row_sanatorio['id']; ?>" />

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