<?php
require_once('funciones/db.php');

if (isset($_SESSION['ADM_Username'])){
	
	$COD = $_GET['cod'];
	settype($COD, 'integer');

	mysqli_select_db($connect, $database);
	$query_plan = sprintf("SELECT * FROM tbl_aliados WHERE id = '%d'",$COD);
	$plan = mysqli_query($connect, $query_plan) or die(mysqli_error($link));
	$row_plan = mysqli_fetch_assoc($plan);
	$totalRows_plan = mysqli_num_rows($plan);

	if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form2")) {

		$especiales = array("á", "Á", "é", "É", "í", "Í", "ó", "Ó", "ú", "Ú", "ñ", "Ñ", " ");
		$correctos   = array("a", "A", "e", "E", "i", "I", "o", "O", "u", "U", "n", "N", "-");
	    //--------INICIO IMAGEN1---------//

	    $detalle= htmlentities( $_POST['detalle']);

		$imagen_real=$_FILES['imagen']['name'];

		if (!empty($_FILES['imagen']['name']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {

    $rutaAliados = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') . '/documentos/aliados/';

    // Sanitizar nombre
    $imagen_real = str_replace($especiales, $correctos, $_FILES['imagen']['name']);
    $imagen_real = basename($imagen_real);

    $destino = $rutaAliados . $imagen_real;

    // Mover archivo
    if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $destino)) {
        die("Error al mover archivo a: " . $destino);
    }

    // Validar que sea imagen (opcional pero recomendado)
    if (@getimagesize($destino) === false) {
        // unlink($destino); // si querés borrar lo que no sea imagen
        die("El archivo subido no es una imagen válida.");
    }
}
  
		
	    //--------FIN IMAGEN1---------//

			$categoria = $_POST['categoria'] ?? '';
			$descuento = $_POST['descuento'] ?? '';
			$orden     = (int) ($_POST['orden'] ?? 0);

			$sql_update = "UPDATE tbl_aliados SET titulo='".$_POST['titulo']."', categoria='".$categoria."', descuento='".$descuento."', orden=".$orden.", detalle='".$_POST['detalle']."'";

			if ($imagen_real != "") {
				$sql_update .= ", imagen='".$imagen_real."'";
			}

			$sql_update .= " WHERE id='".$_POST['id']."'";
			mysqli_select_db($connect, $database);
			$Result1 = mysqli_query($connect, $sql_update) or die(mysqli_error($connect));
			echo"<script>alert('Listo, el aliado se actualizó correctamente.'); window.location.href=\"".$URL."admin/aliados/\"</script>";

	}

	// Categorias disponibles para agrupar aliados en "Descuentos Exclusivos".
	$categorias_aliado = ['Farmacias', 'Ópticas', 'Laboratorios', 'Gimnasios', 'Cooperativas', 'Ortopedia', 'Otros'];

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
	<title>EDITAR ALIADO -  Administrador</title>

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
				<h3>Editar Convenio
				</h3>

				<div class="panel panel-default">
					<div class="panel-heading">Formulario de Edición</div>
					<div class="panel-body">
						<form class="form-horizontal" action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data" name="form2" id="form2">


							

								<fieldset>
									<div class="form-group">
										<label class="col-lg-2 control-label">Titulo</label>
										<div class="col-lg-10">
											<input type="text" name="titulo" placeholder="" value="<?php echo $row_plan['titulo']; ?>"  class="form-control">

										</div>

									</div>
								</fieldset>

								<fieldset>
									<div class="form-group">
										<label class="col-lg-2 control-label">Categoría</label>
										<div class="col-lg-4">
											<select name="categoria" class="form-control">
												<option value="">— Elegí una categoría —</option>
												<?php foreach ($categorias_aliado as $cat) {
													$sel = ($row_plan['categoria'] === $cat) ? ' selected' : '';
												?>
													<option value="<?php echo $cat; ?>"<?php echo $sel; ?>><?php echo $cat; ?></option>
												<?php } ?>
											</select>
											<span class="help-block">Agrupa el comercio en la sección "Descuentos Exclusivos" del sitio.</span>
										</div>
										<label class="col-lg-2 control-label">Descuento</label>
										<div class="col-lg-4">
											<input type="text" name="descuento" placeholder="Ej: Hasta 25%" value="<?php echo htmlspecialchars((string) $row_plan['descuento'], ENT_QUOTES, 'UTF-8'); ?>" class="form-control">
											<span class="help-block">Texto que se muestra al socio. Dejalo vacío si no aplica.</span>
										</div>
									</div>
								</fieldset>

								<fieldset>
									<div class="form-group">
										<label class="col-sm-2 control-label">Descripcion

										</label>
										<div class="col-sm-10">
											<textarea class="form-control" id="code_preview1" name="detalle" style="height: 300px;"><?php echo $row_plan['detalle']?></textarea>
										</div>
									</div>
								</fieldset>

								<fieldset>
									<div class="form-group">
										<label name="imagen" class="col-sm-2 control-label">Imagen</label>
										<div class="col-sm-4">
											<input name="imagen" type="file" data-classbutton="btn btn-default" data-classinput="form-control inline" class="filestyle form-control">
											<span><br>Medida recomendada: 850 × 500 px</span>
										</div>

										<div class="col-sm-4">
											<?php if ($row_plan['imagen'] != "") {?>
												<img width="100px" src="<?php echo $URL?>documentos/aliados/<?php echo $row_plan['imagen']; ?>" alt=""/>
											<?php } else {?>
												<img width="60px" src="<?php echo $URL?>img/sin-imagen.jpg" alt=""/>
											<?php }?>
										</div>

									</div>
								</fieldset>	

								<input type="hidden" name="id" value="<?php echo $row_plan['id']; ?>" />

								<input type="hidden" name="MM_insert" value="form2" />
								<fieldset>
									<div class="form-group">
										<div class="col-sm-4 col-sm-offset-2">
											<button type="submit" class="btn btn-default">Cancelar</button>
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