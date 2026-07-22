<?php
require_once('funciones/db.php');
require_once('conexion.php');

if (isset($_SESSION['ADM_Username'])){



	if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form2") && samap_puede_escribir() && samap_csrf_validar()) {

		try {
			$imagen_real = samap_guardar_imagen_upload('imagen', $rutaBlog);
		} catch (RuntimeException $e) {
			samap_flash_set('error', $e->getMessage());
			header('Location: ' . $URL . 'admin/blogs/');
			exit;
		}
			$fechaActual = date("Y-m-d");
			// Usamos $POST_RAW (sin el escape global de db.php): la query es
			// preparada (bind_param) y ya parametriza sola. Leer de $_POST
			// escapado agregaria backslashes literales (\") y romperia el HTML
			// del editor (Summernote) y las imagenes base64. Sin htmlentities:
			// el cuerpo es HTML enriquecido y debe guardarse tal cual.
			$titulo = (string)($POST_RAW['titulo'] ?? '');
			$intro  = (string)($POST_RAW['intro'] ?? '');
			$texto  = (string)($POST_RAW['texto'] ?? '');
			$IMAGEN = $imagen_real;

			$conexion->set_charset('utf8mb4');
			$new_id = 0;
			$stmt = $conexion->prepare('INSERT INTO tbl_blog (fecha, titulo, intro, texto, imagen) VALUES (?, ?, ?, ?, ?)');
			if ($stmt) {
				$stmt->bind_param('sssss', $fechaActual, $titulo, $intro, $texto, $IMAGEN);
			}
			if (!$stmt || !$stmt->execute()) {
				samap_flash_set('error', 'No se pudo guardar el blog: ' . $conexion->error);
				header('Location: ' . $URL . 'admin/agregar-blog/');
				exit;
			}
			$new_id = $stmt->insert_id;
			$stmt->close();
			@samap_audit_log('insert', 'tbl_blog', $new_id, "Creó el artículo: " . substr((string)$titulo, 0, 100), null, ['id' => $new_id, 'fecha' => $fechaActual, 'titulo' => $titulo, 'imagen' => $IMAGEN]);
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
	<title>AGREGAR BLOG-  Administrador</title>

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
				<h3>Agregar Blog
				</h3>

				<div class="panel panel-default">
					<div class="panel-heading">Formulario de Carga</div>
					<div class="panel-body">
						<div class="row">
							<div class="col-md-7">
						<form class="form-horizontal" action="" method="post" enctype="multipart/form-data" name="form2" id="form2">
							<?php echo samap_csrf_field(); ?>


								<fieldset>
									<div class="form-group">
										<label class="col-lg-2 control-label">Titulo</label>
										<div class="col-lg-10">
											<input type="text" name="titulo" id="blog-titulo" placeholder="" value=""  class="form-control">

										</div>

									</div>
								</fieldset>

								<fieldset>
									<div class="form-group">
										<label class="col-sm-2 control-label">Intro

										</label>
										<div class="col-sm-10">
											<textarea class="form-control" id="blog-intro" name="intro" style="height: 150px;"></textarea>
										</div>
									</div>
								</fieldset>

								<fieldset>
									<div class="form-group">
										<label class="col-sm-2 control-label">Descripcion

										</label>
										<div class="col-sm-10">
											<textarea class="form-control" id="code_preview1" name="texto" style="height: 300px;"></textarea>
										</div>
									</div>
								</fieldset>

								<fieldset>
									<?php
									$upload_campo      = 'imagen';
									$upload_label      = 'Imagen';
									$upload_subcarpeta = 'blog';
									$upload_ruta       = $rutaBlog;
									$upload_medida     = 'Medida recomendada: 850 × 500 px';
									$upload_label_col  = 'col-sm-2';
									$upload_input_col  = 'col-sm-4';
									include 'partials/upload-imagen.php';
									?>
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
							<div class="col-md-5">
								<div class="samap-blog-preview" id="blog-preview" style="position:sticky;top:80px;border:1px solid #d8dee5;border-radius:6px;background:#fff;padding:24px 28px;font-family:Georgia,serif;max-height:80vh;overflow:auto;box-shadow:0 2px 6px rgba(0,0,0,0.05);">
									<div style="font-size:11px;color:#888;text-transform:uppercase;letter-spacing:1px;margin-bottom:8px;">Vista previa en vivo</div>
									<h1 id="blog-preview-titulo" style="font-size:28px;line-height:1.2;color:#2F2E2D;margin:0 0 12px 0;">(Título del artículo)</h1>
									<div id="blog-preview-intro" style="font-size:15px;font-style:italic;color:#555;margin-bottom:18px;line-height:1.5;">(Acá va la introducción.)</div>
									<hr style="border:none;border-top:1px solid #eee;margin:0 0 18px;">
									<div id="blog-preview-texto" style="font-size:15px;line-height:1.7;color:#2F2E2D;">(Cuerpo del artículo.)</div>
								</div>
							</div>
						</div>
						<style>
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
	<?php include 'partials/scripts-comunes.php'; ?>

	<script src='<?php echo $URL;?>admin/plugins/summernote/summernote.min.js'></script>
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

