<?php
require_once('funciones/db.php');
require_once('conexion.php');

if (isset($_SESSION['ADM_Username'])){

	$COD = $_GET['cod'];
	settype($COD, 'integer');

	mysqli_select_db($connect, $database);
	$query_blog = sprintf("SELECT * FROM tbl_blog WHERE id = '%d'",$COD);
	$blog = mysqli_query($connect, $query_blog) or die(mysqli_error($connect));
	$row_blog = mysqli_fetch_assoc($blog);
	$totalRows_blog = mysqli_num_rows($blog);

	if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form2") && samap_puede_escribir() && samap_csrf_validar()) {

		try {
			$imagen_real = samap_guardar_imagen_upload('imagen', $rutaBlog);
		} catch (RuntimeException $e) {
			samap_flash_set('error', $e->getMessage());
			header('Location: ' . $URL . 'admin/blogs/');
			exit;
		}

			$id_post = (int) ($_POST['id'] ?? 0);
			$titulo_post = (string) ($_POST['titulo'] ?? '');
			$intro_post = (string) ($_POST['intro'] ?? '');
			$texto_post = (string) ($_POST['texto'] ?? '');

			if ($imagen_real != "") {
				$stmt = $conexion->prepare('UPDATE tbl_blog SET titulo = ?, intro = ?, texto = ?, imagen = ? WHERE id = ?');
				if ($stmt) {
					$stmt->bind_param('ssssi', $titulo_post, $intro_post, $texto_post, $imagen_real, $id_post);
					$stmt->execute();
					$stmt->close();
				}
			} else {
				$stmt = $conexion->prepare('UPDATE tbl_blog SET titulo = ?, intro = ?, texto = ? WHERE id = ?');
				if ($stmt) {
					$stmt->bind_param('sssi', $titulo_post, $intro_post, $texto_post, $id_post);
					$stmt->execute();
					$stmt->close();
				}
			}

			$snap = is_array($row_blog) ? $row_blog : [];
			$snap['titulo'] = $_POST['titulo']  ?? ($row_blog['titulo']  ?? '');
			$snap['intro']  = $_POST['intro']   ?? ($row_blog['intro']   ?? '');
			$snap['texto']  = $_POST['texto']   ?? ($row_blog['texto']   ?? '');
			$snap['imagen'] = $imagen_real !== '' ? $imagen_real : ($row_blog['imagen'] ?? '');
			$snap['id']     = $_POST['id']      ?? ($row_blog['id'] ?? 0);
			@samap_audit_log('update', 'tbl_blog', (int)($_POST['id'] ?? ''), "Editó el artículo #" . (int)($_POST['id'] ?? '') . ": " . substr((string)($_POST['titulo'] ?? ''), 0, 100), is_array($row_blog) ? $row_blog : null, $snap);
			samap_flash_set('success', 'Blog guardado correctamente.');
			header('Location: ' . $URL . 'admin/blogs/');
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
	<title>EDITAR BLOG -  Administrador</title>

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
				<h3>Editar Plan
				</h3>

				<div class="panel panel-default">
					<div class="panel-heading">Formulario de Edición</div>
					<div class="panel-body">
						<div class="row">
							<div class="col-md-7">
						<form class="form-horizontal" action="" method="post" enctype="multipart/form-data" name="form2" id="form2">
							<?php echo samap_csrf_field(); ?>


								<fieldset>
									<div class="form-group">
										<label class="col-lg-2 control-label">Titulo</label>
										<div class="col-lg-10">
											<input type="text" name="titulo" id="blog-titulo" placeholder="" value="<?php echo htmlspecialchars($row_blog['titulo'], ENT_QUOTES, 'UTF-8'); ?>"  class="form-control">

										</div>

									</div>
								</fieldset>

								<fieldset>
									<div class="form-group">
										<label class="col-sm-2 control-label">Intro

										</label>
										<div class="col-sm-10">
											<textarea class="form-control" id="blog-intro" name="intro" style="height: 150px;"><?php echo htmlspecialchars($row_blog['intro'], ENT_QUOTES, 'UTF-8'); ?></textarea>
										</div>
									</div>
								</fieldset>

								<fieldset>
									<div class="form-group">
										<label class="col-sm-2 control-label">Descripcion

										</label>
										<div class="col-sm-10">
											<textarea class="form-control" id="code_preview1" name="texto" style="height: 300px;"><?php echo $row_blog['texto']?></textarea>
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
											<?php if ($row_blog['imagen'] != "") {?>
												<img width="100px" src="<?php echo $URL?>documentos/blog/<?php echo $row_blog['imagen']; ?>" alt="" loading="lazy" decoding="async"/>
											<?php } else {?>
												<img width="60px" src="<?php echo $URL?>img/sin-imagen.jpg" alt="" loading="lazy" decoding="async"/>
											<?php }?>
										</div>

									</div>
								</fieldset>

								<input type="hidden" name="id" value="<?php echo $row_blog['id']; ?>" />

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
							<div class="col-md-5">
								<div class="samap-blog-preview" id="blog-preview" style="position:sticky;top:80px;border:1px solid #d8dee5;border-radius:6px;background:#fff;padding:24px 28px;font-family:Georgia,serif;max-height:80vh;overflow:auto;box-shadow:0 2px 6px rgba(0,0,0,0.05);">
									<div style="font-size:11px;color:#888;text-transform:uppercase;letter-spacing:1px;margin-bottom:8px;">Vista previa en vivo</div>
									<h1 id="blog-preview-titulo" style="font-size:28px;line-height:1.2;color:#2F2E2D;margin:0 0 12px 0;"><?php echo htmlspecialchars($row_blog['titulo'], ENT_QUOTES, 'UTF-8'); ?></h1>
									<div id="blog-preview-intro" style="font-size:15px;font-style:italic;color:#555;margin-bottom:18px;line-height:1.5;"><?php echo $row_blog['intro']; ?></div>
									<hr style="border:none;border-top:1px solid #eee;margin:0 0 18px;">
									<div id="blog-preview-texto" style="font-size:15px;line-height:1.7;color:#2F2E2D;"><?php echo $row_blog['texto']; ?></div>
								</div>
							</div>
						</div>
						<style>
						/* Split-view: en pantallas chicas el preview va debajo. */
						@media (max-width: 991px) {
							.samap-blog-preview { position: static !important; max-height: none !important; margin-top: 20px; }
						}
						.samap-blog-preview img { max-width: 100%; height: auto; }
						.samap-blog-preview h1, .samap-blog-preview h2, .samap-blog-preview h3 { font-family: 'Poppins', Helvetica, Arial, sans-serif; }
						.samap-blog-preview p { margin: 0 0 1em 0; }
						.samap-blog-preview a { color: #274767; }
						</style>
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
	<script src="<?php echo $URL;?>admin/app/js/app.js?v=202606301500"></script>

	<script type="text/javascript">
	  $(document).ready(function() {
	    $('#code_preview0').summernote({height: 300});
	  	$('#code_preview1').summernote({
	  		height: 300,
	  		onChange: function(contents) {
	  			if (window.samapFormMarkDirty) window.samapFormMarkDirty();
	  			var el = document.getElementById('blog-preview-texto');
	  			if (el) { el.innerHTML = contents; }
	  		}
	  	});
	    });

	    // Live preview para titulo e intro (Feature 16).
	    (function(){
	    	function bind(id, target) {
	    		var src = document.getElementById(id);
	    		var dst = document.getElementById(target);
	    		if (!src || !dst) return;
	    		var update = function() {
	    			dst.textContent = src.value;
	    		};
	    		src.addEventListener('input', update);
	    		update();
	    	}
	    	bind('blog-titulo', 'blog-preview-titulo');
	    	bind('blog-intro', 'blog-preview-intro');
	    })();
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
