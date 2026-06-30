<?php
require_once('funciones/db.php');
require_once('conexion.php');

if (isset($_SESSION['ADM_Username'])){

	$COD = $_GET['cod'];
	settype($COD, 'integer');

	mysqli_select_db($connect, $database);
	$query_plan = sprintf("SELECT * FROM tbl_aliados WHERE id = '%d'",$COD);
	$plan = mysqli_query($connect, $query_plan) or die(mysqli_error($connect));
	$row_plan = mysqli_fetch_assoc($plan);
	$totalRows_plan = mysqli_num_rows($plan);

	if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form2") && samap_puede_escribir() && samap_csrf_validar()) {

	    $detalle= htmlentities( (string)($_POST['detalle'] ?? ''), ENT_QUOTES, 'UTF-8' );

		try {
			$imagen_real = samap_guardar_imagen_upload('imagen', $rutaAliados);
		} catch (RuntimeException $e) {
			samap_flash_set('error', $e->getMessage());
			header('Location: ' . $URL . 'admin/aliados/');
			exit;
		}

			$categoria_id  = (int) ($_POST['categoria_id'] ?? 0);
			$categoria_bind = $categoria_id > 0 ? $categoria_id : null;
			$descuento = $_POST['descuento'] ?? '';
			$orden     = (int) ($_POST['orden'] ?? 0);

			$id_post = (int) ($_POST['id'] ?? 0);
			$titulo_post = (string) ($_POST['titulo'] ?? '');
			$detalle_post = (string) ($_POST['detalle'] ?? '');

			if ($imagen_real != "") {
				$stmt = $conexion->prepare('UPDATE tbl_aliados SET titulo = ?, categoria_id = ?, descuento = ?, orden = ?, detalle = ?, imagen = ? WHERE id = ?');
				if ($stmt) {
					$stmt->bind_param('sisissi', $titulo_post, $categoria_bind, $descuento, $orden, $detalle_post, $imagen_real, $id_post);
					$stmt->execute();
					$stmt->close();
				}
			} else {
				$stmt = $conexion->prepare('UPDATE tbl_aliados SET titulo = ?, categoria_id = ?, descuento = ?, orden = ?, detalle = ? WHERE id = ?');
				if ($stmt) {
					$stmt->bind_param('sisisi', $titulo_post, $categoria_bind, $descuento, $orden, $detalle_post, $id_post);
					$stmt->execute();
					$stmt->close();
				}
			}

			$snap = is_array($row_plan) ? $row_plan : [];
			$snap['titulo']       = $_POST['titulo']   ?? ($row_plan['titulo']       ?? '');
			$snap['categoria_id'] = $categoria_id;
			$snap['descuento']    = $descuento;
			$snap['orden']        = $orden;
			$snap['detalle']      = $_POST['detalle'] ?? ($row_plan['detalle']      ?? '');
			$snap['imagen']       = $imagen_real !== '' ? $imagen_real : ($row_plan['imagen'] ?? '');
			$snap['id']           = $_POST['id']      ?? ($row_plan['id']           ?? 0);
			@samap_audit_log('update', 'tbl_aliados', (int)($_POST['id'] ?? ''), "Editó el aliado #" . (int)($_POST['id'] ?? '') . ": " . substr((string)($_POST['titulo'] ?? ''), 0, 100), is_array($row_plan) ? $row_plan : null, $snap);
			samap_flash_set('success', 'Listo, el aliado se actualizó correctamente.');
			header('Location: ' . $URL . 'admin/aliados/');
			exit;

	}

	// Categorias disponibles (las administra Marketing en tbl_categorias_aliado).
	mysqli_select_db($connect, $database);
	$cat_rs = mysqli_query($connect, "SELECT id, nombre FROM tbl_categorias_aliado WHERE deleted_at IS NULL AND activo = 1 ORDER BY orden ASC, nombre ASC");
	$categorias_aliado = [];
	while ($cat_rs && $crow = mysqli_fetch_assoc($cat_rs)) { $categorias_aliado[] = $crow; }

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
				<h3>Editar Convenio
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
											<input type="text" name="titulo" placeholder="" value="<?php echo $row_plan['titulo']; ?>"  class="form-control">

										</div>

									</div>
								</fieldset>

								<fieldset>
									<div class="form-group">
										<label class="col-lg-2 control-label">Categoría</label>
										<div class="col-lg-4">
											<select name="categoria_id" class="form-control">
												<option value="">— Elegí una categoría —</option>
												<?php foreach ($categorias_aliado as $cat) {
													$sel = ((int) $row_plan['categoria_id'] === (int) $cat['id']) ? ' selected' : '';
												?>
													<option value="<?php echo (int) $cat['id']; ?>"<?php echo $sel; ?>><?php echo htmlspecialchars($cat['nombre'], ENT_QUOTES, 'UTF-8'); ?></option>
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
												<img width="100px" src="<?php echo $URL?>documentos/aliados/<?php echo $row_plan['imagen']; ?>" alt="" loading="lazy" decoding="async"/>
											<?php } else {?>
												<img width="60px" src="<?php echo $URL?>img/sin-imagen.jpg" alt="" loading="lazy" decoding="async"/>
											<?php }?>
										</div>

									</div>
								</fieldset>	

								<input type="hidden" name="id" value="<?php echo $row_plan['id']; ?>" />

								<input type="hidden" name="MM_insert" value="form2" />
								<fieldset>
									<div class="form-group">
										<div class="col-sm-4 col-sm-offset-2">
											<a href="<?php echo $URL;?>admin/aliados/" class="btn btn-default">Cancelar</a>
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
