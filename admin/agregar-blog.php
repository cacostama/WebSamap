<?php
require_once('funciones/db.php');
require_once('conexion.php');

if (isset($_SESSION['ADM_Username'])){



	if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form2") && samap_puede_escribir() && samap_csrf_validar()) {

		try {
			$imagen_real = samap_guardar_imagen_upload('imagen', $rutaBlog);
		} catch (RuntimeException $e) {
			samap_flash_set('error', $e->getMessage());
			header('Location: ' . $URL . 'admin/agregar-blog/');
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
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="">
	<meta name="keywords" content="">
	<meta name="author" content="">
	<title>AGREGAR BLOG -  Administrador</title>

	<link rel="stylesheet" href="<?php echo $URL;?>admin/app/css/bootstrap.css">

	<link rel="stylesheet" href="<?php echo $URL;?>admin/plugins/fontawesome/css/font-awesome.min.css">
	<link rel="stylesheet" href="<?php echo $URL;?>admin/plugins/animo/animate+animo.css">
	<link rel="stylesheet" href="<?php echo $URL;?>admin/plugins/csspinner/csspinner.min.css">

	<link rel="stylesheet" href="<?php echo $URL;?>admin/app/css/app.css?v=202606291705">

	<script src="<?php echo $URL;?>admin/plugins/modernizr/modernizr.js" type="application/javascript"></script>
	<script src="<?php echo $URL;?>admin/plugins/fastclick/fastclick.js" type="application/javascript"></script>
	<link rel='stylesheet' href='<?php echo $URL;?>admin/plugins/summernote/summernote.min.css'>

	<style type="text/css">
	/* ---- Editor de blog SAMAP -------------------------------------------
	   Paleta institucional: azul #274767, verde mar #6CA3AB, neutro #2F2E2D.
	   Mismo diseño que "Editar Blog".
	   -------------------------------------------------------------------- */
	.samap-card {
		background: #fff;
		border: 1px solid #e2e8ee;
		border-radius: 6px;
		margin-bottom: 20px;
		box-shadow: 0 1px 3px rgba(39,71,103,0.06);
	}
	.samap-card__head {
		padding: 14px 20px;
		border-bottom: 1px solid #eef2f6;
		display: flex;
		align-items: center;
		gap: 10px;
	}
	.samap-card__head em { color: #6CA3AB; }
	.samap-card__title {
		font-size: 14px;
		font-weight: 600;
		color: #274767;
		text-transform: uppercase;
		letter-spacing: .5px;
		margin: 0;
	}
	.samap-card__hint {
		font-size: 12px;
		color: #8a97a3;
		margin-left: auto;
		font-weight: 400;
		text-transform: none;
		letter-spacing: 0;
	}
	.samap-card__body { padding: 20px; }

	.samap-field { margin-bottom: 18px; }
	.samap-field:last-child { margin-bottom: 0; }
	.samap-field > label {
		display: block;
		font-size: 13px;
		font-weight: 600;
		color: #2F2E2D;
		margin-bottom: 6px;
	}
	.samap-field .samap-help {
		font-size: 12px;
		color: #8a97a3;
		margin-top: 5px;
		display: block;
	}
	.samap-field .form-control:focus {
		border-color: #6CA3AB;
		box-shadow: 0 0 0 2px rgba(108,163,171,0.18);
	}
	.samap-counter { font-variant-numeric: tabular-nums; }
	.samap-counter.is-over { color: #f6504d; font-weight: 600; }

	/* Aviso de imagenes pegadas dentro del texto */
	.samap-aviso-img {
		display: flex;
		align-items: center;
		gap: 12px;
		background: #fff8e6;
		border: 1px solid #f0d9a0;
		border-radius: 5px;
		padding: 10px 14px;
		margin-bottom: 10px;
		font-size: 12.5px;
		color: #7a5b1c;
	}
	.samap-aviso-img > em { font-size: 17px; color: #c1832b; flex-shrink: 0; }
	.samap-aviso-img__txt { flex: 1; line-height: 1.45; }
	.samap-aviso-img__txt strong { display: block; color: #6b4e14; }
	.samap-aviso-img .btn { flex-shrink: 0; }

	/* La columna del formulario reserva lugar al pie para que la barra de
	   acciones (sticky) nunca tape el final del ultimo bloque. */
	.samap-form-col { padding-bottom: 78px; }

	.samap-actionbar {
		position: sticky;
		bottom: 12px;
		margin-top: 4px;
		background: #fff;
		border: 1px solid #e2e8ee;
		border-radius: 6px;
		padding: 12px 20px;
		display: flex;
		align-items: center;
		gap: 10px;
		box-shadow: 0 -2px 8px rgba(39,71,103,0.07);
		z-index: 20;
	}
	.samap-actionbar .btn-primary { background: #274767; border-color: #274767; }
	.samap-actionbar .btn-primary:hover,
	.samap-actionbar .btn-primary:focus { background: #1d3750; border-color: #1d3750; }
	.samap-actionbar__status { margin-left: auto; font-size: 12px; color: #8a97a3; }

	.samap-empty {
		padding: 20px;
		border: 1px dashed #cfd8e0;
		border-radius: 5px;
		background: #fbfcfd;
		color: #8a97a3;
		font-size: 13px;
		text-align: center;
		margin-bottom: 16px;
	}

	/* Vista previa */
	.samap-blog-preview {
		position: sticky;
		top: 20px;
		border: 1px solid #e2e8ee;
		border-radius: 6px;
		background: #fff;
		overflow: hidden;
		box-shadow: 0 1px 3px rgba(39,71,103,0.06);
	}
	.samap-blog-preview__bar {
		background: #274767;
		color: #fff;
		font-size: 11px;
		text-transform: uppercase;
		letter-spacing: 1px;
		padding: 9px 16px;
		display: flex;
		align-items: center;
		gap: 8px;
	}
	.samap-blog-preview__bar em { color: #6CA3AB; }
	.samap-blog-preview__body {
		padding: 22px 26px 28px;
		font-family: Georgia, 'Times New Roman', serif;
		max-height: 74vh;
		overflow: auto;
	}
	.samap-blog-preview__img {
		width: 100%;
		height: auto;
		border-radius: 4px;
		margin-bottom: 18px;
		display: block;
	}
	#blog-preview-titulo {
		font-size: 27px;
		line-height: 1.22;
		color: #2F2E2D;
		margin: 0 0 12px 0;
		font-family: 'Poppins', Helvetica, Arial, sans-serif;
		font-weight: 700;
	}
	#blog-preview-intro {
		font-size: 15px;
		font-style: italic;
		color: #555;
		margin-bottom: 16px;
		line-height: 1.55;
	}
	#blog-preview-texto { font-size: 15px; line-height: 1.75; color: #2F2E2D; }
	#blog-preview-texto img { display: none; } /* nunca se publican */
	.samap-blog-preview__body h1,
	.samap-blog-preview__body h2,
	.samap-blog-preview__body h3 { font-family: 'Poppins', Helvetica, Arial, sans-serif; }
	.samap-blog-preview__body p { margin: 0 0 1em 0; }
	.samap-blog-preview__body a { color: #274767; }
	.samap-preview-vacio { color: #a9b3bd; font-style: italic; }

	.note-editor { margin-bottom: 0 !important; }

	@media (max-width: 991px) {
		.samap-blog-preview { position: static; margin-top: 20px; }
		.samap-blog-preview__body { max-height: none; }
	}
	</style>
</head>
<body>

	<section class="wrapper">

		<?php include 'header.php'; ?>
		<?php include 'aside.php'; ?>

		<section>

			<section class="main-content">

				<h3>Agregar Blog</h3>

				<form class="form" action="" method="post" enctype="multipart/form-data" name="form2" id="form2">
					<?php echo samap_csrf_field(); ?>
					<input type="hidden" name="id" value="" />
					<input type="hidden" name="MM_insert" value="form2" />

					<div class="row">

						<!-- ============ Columna izquierda: formulario ============ -->
						<div class="col-md-7 samap-form-col">

							<!-- Paso 1: la foto. -->
							<div class="samap-card">
								<div class="samap-card__head">
									<em class="fa fa-picture-o"></em>
									<h4 class="samap-card__title">1 &middot; Imagen destacada</h4>
									<span class="samap-card__hint">Es la foto que se ve en el artículo y en el listado</span>
								</div>
								<div class="samap-card__body">

									<?php
									// Componente compartido: valida tipo/peso, y al elegir un archivo
									// abre Cropper.js (recortar, rotar, resetear) antes de subir.
									$upload_campo      = 'imagen';
									$upload_label      = 'Elegir imagen';
									$upload_subcarpeta = 'blog';
									$upload_ruta       = $rutaBlog;
									$upload_actual     = '';
									$upload_medida     = 'Medida recomendada: 850 × 500 px';
									$upload_label_col  = 'col-sm-12';
									$upload_input_col  = 'col-sm-12';
									include 'partials/upload-imagen.php';
									?>

								</div>
							</div>

							<!-- Paso 2: el texto. -->
							<div class="samap-card">
								<div class="samap-card__head">
									<em class="fa fa-pencil"></em>
									<h4 class="samap-card__title">2 &middot; Contenido</h4>
								</div>
								<div class="samap-card__body">

									<div class="samap-field">
										<label for="blog-titulo">Título</label>
										<input type="text" name="titulo" id="blog-titulo" class="form-control"
										       maxlength="250" value="">
										<span class="samap-help">
											Aparece como título del artículo y en el listado.
											<span class="samap-counter" id="cont-titulo"></span>
										</span>
									</div>

									<div class="samap-field">
										<label for="blog-intro">Introducción</label>
										<textarea name="intro" id="blog-intro" class="form-control" rows="3"
										          maxlength="250"></textarea>
										<span class="samap-help">
											Resumen corto que se muestra en el listado del blog.
											<span class="samap-counter" id="cont-intro"></span>
										</span>
									</div>

									<div class="samap-field">
										<label for="code_preview1">Descripción</label>

										<div class="samap-aviso-img" id="aviso-img" style="display:none;">
											<em class="fa fa-exclamation-triangle"></em>
											<div class="samap-aviso-img__txt">
												<strong>Hay <span id="aviso-img-cant">0</span> imagen(es) pegada(s) dentro del texto.</strong>
												No se publican &mdash; la foto del artículo es la de arriba &mdash; y hacen
												mucho más pesado el artículo.
											</div>
											<button type="button" class="btn btn-warning btn-sm" id="btn-limpiar-img">
												<em class="fa fa-eraser"></em> Quitar del texto
											</button>
										</div>

										<textarea class="form-control" id="code_preview1" name="texto" style="height:300px;"></textarea>
										<span class="samap-help">
											Solo texto. La foto del artículo se carga arriba, en <strong>Imagen destacada</strong>.
										</span>
									</div>

								</div>
							</div>

							<div class="samap-actionbar">
								<button type="submit" class="btn btn-primary">
									<em class="fa fa-check"></em> Publicar artículo
								</button>
								<button type="button" class="btn btn-default" onclick="window.location.href='<?php echo $URL; ?>admin/blogs/';">
									Cancelar
								</button>
								<span class="samap-actionbar__status" id="estado-form">Sin cambios sin guardar</span>
							</div>

						</div>

						<!-- ============ Columna derecha: vista previa ============ -->
						<div class="col-md-5">
							<div class="samap-blog-preview">
								<div class="samap-blog-preview__bar">
									<em class="fa fa-eye"></em> Vista previa &mdash; así se publica
								</div>
								<div class="samap-blog-preview__body">
									<img id="blog-preview-img"
									     class="samap-blog-preview__img"
									     src="<?php echo $URL; ?>assets/images/blog_articles.png"
									     alt="Imagen destacada" />
									<h1 id="blog-preview-titulo" class="samap-preview-vacio">(Título del artículo)</h1>
									<div id="blog-preview-intro" class="samap-preview-vacio">(Acá va la introducción.)</div>
									<hr style="border:none;border-top:1px solid #eee;margin:0 0 18px;">
									<div id="blog-preview-texto" class="samap-preview-vacio">(Cuerpo del artículo.)</div>
								</div>
							</div>
						</div>

					</div>
				</form>

			</section>

		</section>

	</section>

	<?php include 'partials/scripts-comunes.php'; ?>

	<!-- Summernote: la libreria SIEMPRE antes del script que la inicializa. -->
	<script src='<?php echo $URL;?>admin/plugins/summernote/summernote.min.js'></script>

	<script type="text/javascript">
	(function () {
		'use strict';

		var BASE_URL = <?php echo json_encode($URL, JSON_UNESCAPED_UNICODE); ?>;
		var PLACEHOLDER = BASE_URL + 'assets/images/blog_articles.png';

		function $id(id) { return document.getElementById(id); }

		// La foto del articulo es siempre la destacada. Las imagenes pegadas
		// dentro del texto no se publican, asi que tampoco se muestran aca.
		function sinImagenes(html) {
			return String(html || '')
				.replace(/<figure\b[^>]*>[\s\S]*?<\/figure>/gi, '')
				.replace(/<img\b[^>]*>/gi, '');
		}

		$(document).ready(function () {
			// Summernote 0.6.x: los callbacks van al nivel de las opciones,
			// NO dentro de un objeto "callbacks" (eso es de 0.8+).
			$('#code_preview1').summernote({
				height: 300,
				onChange: function (contents) {
					if (window.samapFormMarkDirty) window.samapFormMarkDirty();
					marcarCambios();
					var el = $id('blog-preview-texto');
					if (el) {
						var limpio = sinImagenes(contents);
						el.innerHTML = limpio;
						el.className = limpio.replace(/<[^>]*>/g, '').trim() ? '' : 'samap-preview-vacio';
					}
					revisarImagenesTexto(contents);
				}
			});
			revisarImagenesTexto(editorHtml());
		});

		// --- Imágenes pegadas dentro del texto ------------------------------
		// Summernote 0.6.x usa .code() como getter/setter del HTML.
		function editorHtml() {
			var $ed = $('#code_preview1');
			try { return $ed.code(); } catch (e) { return $ed.val() || ''; }
		}
		function contarImagenes(html) {
			var m = String(html || '').match(/<img\b[^>]*>/gi);
			return m ? m.length : 0;
		}
		function revisarImagenesTexto(html) {
			var aviso = $id('aviso-img');
			if (!aviso) return;
			var n = contarImagenes(html);
			var cant = $id('aviso-img-cant');
			if (cant) cant.textContent = n;
			aviso.style.display = n > 0 ? 'flex' : 'none';
		}

		var btnLimpiar = $id('btn-limpiar-img');
		if (btnLimpiar) {
			btnLimpiar.addEventListener('click', function () {
				if (!confirm('¿Quitar las imágenes pegadas dentro del texto? El texto se mantiene.')) return;
				var limpio = sinImagenes(editorHtml());
				var $ed = $('#code_preview1');
				try { $ed.code(limpio); } catch (e) { $ed.val(limpio); }
				var el = $id('blog-preview-texto');
				if (el) el.innerHTML = limpio;
				revisarImagenesTexto(limpio);
				marcarCambios();
				if (window.samapFlash) {
					window.samapFlash('success', 'Imágenes quitadas del texto.');
				}
			});
		}

		// --- Título e introducción en vivo ----------------------------------
		function bindTexto(srcId, dstId, contadorId, maximo, vacio) {
			var src = $id(srcId), dst = $id(dstId), cont = $id(contadorId);
			if (!src || !dst) return;
			function update() {
				var v = src.value;
				dst.textContent = v || vacio;
				dst.className = v ? '' : 'samap-preview-vacio';
				if (cont) {
					cont.textContent = v.length + ' / ' + maximo;
					cont.className = 'samap-counter' + (v.length > maximo ? ' is-over' : '');
				}
				marcarCambios();
			}
			src.addEventListener('input', update);
			update();
		}
		bindTexto('blog-titulo', 'blog-preview-titulo', 'cont-titulo', 250, '(Título del artículo)');
		bindTexto('blog-intro', 'blog-preview-intro', 'cont-intro', 250, '(Acá va la introducción.)');

		// --- Aviso de cambios sin guardar ------------------------------------
		// "listo" evita que el render inicial marque el formulario como sucio.
		var haycambios = false;
		var listo = false;
		function marcarCambios() {
			if (!listo || haycambios) return;
			haycambios = true;
			var e = $id('estado-form');
			if (e) { e.textContent = 'Tenés cambios sin guardar'; e.style.color = '#c1832b'; }
		}
		$(function () { setTimeout(function () { listo = true; }, 300); });

		window.addEventListener('beforeunload', function (ev) {
			if (!haycambios) return;
			ev.preventDefault();
			ev.returnValue = '';
		});
		var form = $id('form2');
		if (form) form.addEventListener('submit', function () { haycambios = false; });

		// --- Imagen destacada elegida ---------------------------------------
		var inputImagen = $id('upload-imagen');
		var previewImg  = $id('blog-preview-img');
		if (inputImagen && previewImg) {
			inputImagen.addEventListener('change', function () {
				var f = inputImagen.files && inputImagen.files[0];
				if (!f) { previewImg.src = PLACEHOLDER; return; }
				var r = new FileReader();
				r.onload = function (e) { previewImg.src = e.target.result; };
				r.readAsDataURL(f);
				marcarCambios();
			});
		}
	})();
	</script>

</body>
</html>
