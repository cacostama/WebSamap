<?php
require_once('funciones/db.php');
require_once('conexion.php');

if (isset($_SESSION['ADM_Username'])) {

	$conexion->set_charset('utf8mb4');

	// Campos editables. El orden define el orden del UPDATE.
	$campos = [
		'eyebrow', 'titulo', 'subtitulo',
		'btn1_texto', 'btn1_url', 'btn2_texto', 'btn2_url',
		'stat1_num', 'stat1_label',
		'stat2_num', 'stat2_label',
		'stat3_num', 'stat3_label',
		'whatsapp',
	];

	// Valores por defecto: los mismos que usa samap_portada() en el sitio
	// publico, para que el formulario no aparezca vacio si falta la fila.
	$defaults = [
		'eyebrow'     => 'Medicina Prepaga · Sanatorio Adventista',
		'titulo'      => 'Cuidándote siempre',
		'subtitulo'   => 'Cobertura médica para vos y tu familia con el respaldo del Sanatorio Adventista.',
		'btn1_texto'  => 'Solicitar información',
		'btn1_url'    => '',
		'btn2_texto'  => 'Conocer planes',
		'btn2_url'    => 'planes/',
		'imagen'      => '',
		'stat1_num'   => '+40',
		'stat1_label' => 'años de experiencia',
		'stat2_num'   => '+8.000',
		'stat2_label' => 'familias adheridas',
		'stat3_num'   => 'Respaldo',
		'stat3_label' => 'del Sanatorio Adventista',
		'whatsapp'    => '5950982304977',
	];

	$tabla_ok = true;

	if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form2") && samap_puede_escribir() && samap_csrf_validar()) {

		try {
			$imagen_real = samap_guardar_imagen_upload('imagen', $rutaPortada);
		} catch (RuntimeException $e) {
			samap_flash_set('error', $e->getMessage());
			header('Location: ' . $URL . 'admin/portada/');
			exit;
		}

		// $POST_RAW: sin el escape global de db.php. La query es preparada y
		// parametriza sola; leer de $_POST escapado meteria backslashes.
		$valores = [];
		foreach ($campos as $c) {
			$valores[$c] = trim((string)($POST_RAW[$c] ?? ''));
		}
		$quitar_imagen = !empty($POST_RAW['quitar_imagen']);

		// Aseguramos que exista la fila 1 antes de actualizar.
		@$conexion->query('INSERT IGNORE INTO tbl_portada (id) VALUES (1)');

		$sets = [];
		foreach ($campos as $c) {
			$sets[] = "`$c` = ?";
		}
		$tipos = str_repeat('s', count($campos));
		$bind  = array_values($valores);

		if ($imagen_real !== '') {
			$sets[]  = '`imagen` = ?';
			$tipos  .= 's';
			$bind[]  = $imagen_real;
		} elseif ($quitar_imagen) {
			$sets[]  = '`imagen` = ?';
			$tipos  .= 's';
			$bind[]  = '';
		}
		$sets[] = '`updated_at` = NOW()';

		$sql  = 'UPDATE tbl_portada SET ' . implode(', ', $sets) . ' WHERE id = 1';
		$stmt = $conexion->prepare($sql);
		if (!$stmt) {
			samap_flash_set('error', 'No se pudo guardar la portada: ' . $conexion->error);
			header('Location: ' . $URL . 'admin/portada/');
			exit;
		}
		$stmt->bind_param($tipos, ...$bind);
		if (!$stmt->execute()) {
			samap_flash_set('error', 'No se pudo guardar la portada: ' . $conexion->error);
			header('Location: ' . $URL . 'admin/portada/');
			exit;
		}
		$stmt->close();

		@samap_audit_log('update', 'tbl_portada', 1, 'Actualizó la portada del sitio', null, $valores);
		samap_flash_set('success', 'Portada actualizada correctamente.');
		header('Location: ' . $URL . 'admin/portada/');
		exit;
	}

	// ---- Cargar la fila actual ----
	$row = [];
	$res = @$conexion->query('SELECT * FROM tbl_portada WHERE id = 1 LIMIT 1');
	if ($res === false) {
		// La tabla todavia no existe: falta correr migracion-portada.sql
		$tabla_ok = false;
	} elseif ($res->num_rows > 0) {
		$row = $res->fetch_assoc();
	}

	$P = $defaults;
	foreach ($defaults as $k => $_) {
		if (isset($row[$k]) && trim((string)$row[$k]) !== '') {
			$P[$k] = $row[$k];
		}
	}
	$imagen_actual = trim((string)$P['imagen']);

} else {

	echo"<script>window.location.href=\"".$URL."admin/home/\"</script>";
	exit;

}

/** Helper corto para imprimir valores escapados en el form. */
function ph($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="es">
<head>

	<meta charset="utf-8">
	<meta http-equiv="x-ua-compatible" content="ie=edge">
	<meta http-equiv="Content-Language" content="es"/>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>PORTADA -  Administrador</title>

	<link rel="stylesheet" href="<?php echo $URL;?>admin/app/css/bootstrap.css">
	<link rel="stylesheet" href="<?php echo $URL;?>admin/plugins/fontawesome/css/font-awesome.min.css">
	<link rel="stylesheet" href="<?php echo $URL;?>admin/plugins/animo/animate+animo.css">
	<link rel="stylesheet" href="<?php echo $URL;?>admin/plugins/csspinner/csspinner.min.css">
	<link rel="stylesheet" href="<?php echo $URL;?>admin/app/css/app.css?v=202606291705">

	<script src="<?php echo $URL;?>admin/plugins/modernizr/modernizr.js" type="application/javascript"></script>
	<script src="<?php echo $URL;?>admin/plugins/fastclick/fastclick.js" type="application/javascript"></script>

	<style type="text/css">
	/* Mismo lenguaje visual que el editor de blog. Paleta institucional. */
	.samap-card { background:#fff; border:1px solid #e2e8ee; border-radius:6px; margin-bottom:20px; box-shadow:0 1px 3px rgba(39,71,103,.06); }
	.samap-card__head { padding:14px 20px; border-bottom:1px solid #eef2f6; display:flex; align-items:center; gap:10px; }
	.samap-card__head em { color:#6CA3AB; }
	.samap-card__title { font-size:14px; font-weight:600; color:#274767; text-transform:uppercase; letter-spacing:.5px; margin:0; }
	.samap-card__hint { font-size:12px; color:#8a97a3; margin-left:auto; font-weight:400; text-transform:none; letter-spacing:0; }
	.samap-card__body { padding:20px; }

	.samap-field { margin-bottom:18px; }
	.samap-field:last-child { margin-bottom:0; }
	.samap-field > label { display:block; font-size:13px; font-weight:600; color:#2F2E2D; margin-bottom:6px; }
	.samap-help { font-size:12px; color:#8a97a3; margin-top:5px; display:block; }
	.samap-field .form-control:focus { border-color:#6CA3AB; box-shadow:0 0 0 2px rgba(108,163,171,.18); }

	.samap-grid2 { display:flex; gap:14px; flex-wrap:wrap; }
	.samap-grid2 > * { flex:1 1 220px; }
	.samap-stat-row { display:flex; gap:14px; align-items:flex-end; margin-bottom:12px; flex-wrap:wrap; }
	.samap-stat-row .num { flex:0 0 130px; }
	.samap-stat-row .lbl { flex:1 1 220px; }

	.samap-form-col { padding-bottom:78px; }
	.samap-actionbar { position:sticky; bottom:12px; margin-top:4px; background:#fff; border:1px solid #e2e8ee; border-radius:6px; padding:12px 20px; display:flex; align-items:center; gap:10px; box-shadow:0 -2px 8px rgba(39,71,103,.07); z-index:20; }
	.samap-actionbar .btn-primary { background:#274767; border-color:#274767; }
	.samap-actionbar .btn-primary:hover, .samap-actionbar .btn-primary:focus { background:#1d3750; border-color:#1d3750; }
	.samap-actionbar__status { margin-left:auto; font-size:12px; color:#8a97a3; }

	.samap-img-actual { display:flex; gap:16px; align-items:flex-start; padding:14px; border:1px solid #e6ebf0; border-radius:5px; background:#f8fafb; margin-bottom:16px; }
	.samap-img-actual img { width:200px; height:auto; border-radius:4px; border:1px solid #dde4ea; background:#fff; display:block; }
	.samap-img-actual__meta { flex:1; min-width:0; }
	.samap-img-actual__meta strong { display:block; font-size:13px; color:#274767; margin-bottom:4px; }
	.samap-img-actual__meta code { font-size:11px; background:#eef2f6; color:#55606b; padding:2px 6px; border-radius:3px; word-break:break-all; }
	.samap-img-actual__acciones { margin-top:10px; display:flex; gap:8px; flex-wrap:wrap; }

	/* ---- Vista previa del hero ---- */
	.rdp { position:sticky; top:20px; border:1px solid #e2e8ee; border-radius:6px; background:#fff; overflow:hidden; box-shadow:0 1px 3px rgba(39,71,103,.06); }
	.rdp__bar { background:#274767; color:#fff; font-size:11px; text-transform:uppercase; letter-spacing:1px; padding:9px 16px; display:flex; align-items:center; gap:8px; }
	.rdp__bar em { color:#6CA3AB; }
	.rdp__body { padding:22px; background:linear-gradient(180deg,#f7fafb 0%,#fff 100%); max-height:76vh; overflow:auto; }
	.rdp__eyebrow { display:inline-block; background:#e8eff1; color:#4d7c86; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.8px; padding:5px 11px; border-radius:20px; margin-bottom:12px; }
	.rdp__titulo { font-family:'Poppins',Helvetica,Arial,sans-serif; font-size:30px; line-height:1.1; font-weight:800; color:#274767; margin:0 0 10px; }
	.rdp__sub { font-size:14px; color:#55606b; line-height:1.5; margin-bottom:16px; }
	.rdp__cta { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:18px; }
	.rdp__btn { font-size:12.5px; font-weight:600; padding:9px 16px; border-radius:24px; color:#fff; }
	.rdp__btn--wa { background:#25b04d; }
	.rdp__btn--azul { background:#274767; }
	.rdp__img { width:100%; aspect-ratio:4/3; object-fit:cover; border-radius:10px; box-shadow:0 12px 30px rgba(39,71,103,.18); display:block; margin-bottom:18px; }
	.rdp__stats { display:flex; gap:10px; border:1px solid #e8eef2; border-radius:10px; padding:14px 10px; background:#fff; text-align:center; }
	.rdp__stats > div { flex:1; min-width:0; }
	.rdp__num { display:block; font-family:'Poppins',Helvetica,Arial,sans-serif; font-size:19px; font-weight:800; color:#274767; }
	.rdp__lbl { display:block; font-size:10.5px; color:#8a97a3; margin-top:2px; line-height:1.3; }

	.samap-alerta-tabla { background:#fdecea; border:1px solid #f5c6c2; color:#8d2f26; padding:14px 18px; border-radius:6px; margin-bottom:20px; font-size:13px; line-height:1.5; }
	.samap-alerta-tabla code { background:#fff; padding:2px 6px; border-radius:3px; color:#8d2f26; }

	@media (max-width:991px){ .rdp { position:static; margin-top:20px; } .rdp__body { max-height:none; } }
	</style>
</head>
<body>

	<section class="wrapper">

		<?php include 'header.php'; ?>
		<?php include 'aside.php'; ?>

		<section>
			<section class="main-content">

				<h3>Portada</h3>

				<?php if (!$tabla_ok): ?>
					<div class="samap-alerta-tabla">
						<strong>Falta crear la tabla de la portada.</strong><br>
						Todavía no se aplicó la migración, así que los cambios que guardes acá no se van a
						registrar. Ejecutá <code>migracion-portada.sql</code> en la base de datos y volvé a entrar.
						Mientras tanto, el sitio sigue mostrando los textos por defecto sin ningún problema.
					</div>
				<?php endif; ?>

				<form class="form" action="" method="post" enctype="multipart/form-data" name="form2" id="form2">
					<?php echo samap_csrf_field(); ?>
					<input type="hidden" name="MM_insert" value="form2" />
					<input type="hidden" name="quitar_imagen" id="quitar-imagen" value="" />

					<div class="row">
						<div class="col-md-7 samap-form-col">

							<!-- 1 - Imagen -->
							<div class="samap-card">
								<div class="samap-card__head">
									<em class="fa fa-picture-o"></em>
									<h4 class="samap-card__title">1 &middot; Imagen principal</h4>
									<span class="samap-card__hint">La foto grande de la portada</span>
								</div>
								<div class="samap-card__body">

									<div class="samap-img-actual" id="img-actual-box">
										<img id="img-actual"
										     src="<?php echo $imagen_actual !== '' ? $URL.'documentos/portada/'.ph($imagen_actual) : $URL.'documentos/slider/03.jpg'; ?>"
										     alt="Imagen actual de la portada"
										     onerror="this.src='<?php echo $URL; ?>assets/images/blog_articles.png';" />
										<div class="samap-img-actual__meta">
											<strong>Imagen actual</strong>
											<code><?php echo $imagen_actual !== '' ? ph($imagen_actual) : 'documentos/slider/03.jpg (por defecto)'; ?></code>
											<?php if ($imagen_actual !== ''): ?>
											<div class="samap-img-actual__acciones">
												<button type="button" class="btn btn-default btn-sm" id="btn-ajustar-actual">
													<em class="fa fa-crop"></em> Ajustar esta imagen
												</button>
												<button type="button" class="btn btn-default btn-sm" id="btn-quitar-imagen">
													<em class="fa fa-undo"></em> Volver a la de por defecto
												</button>
											</div>
											<?php endif; ?>
											<span class="samap-help" id="ajustar-estado"></span>
										</div>
									</div>

									<?php
									$upload_campo      = 'imagen';
									$upload_label      = 'Cambiar imagen';
									$upload_subcarpeta = 'portada';
									$upload_ruta       = $rutaPortada;
									$upload_actual     = '';
									$upload_medida     = 'Medida recomendada: 1200 × 900 px';
									$upload_label_col  = 'col-sm-12';
									$upload_input_col  = 'col-sm-12';
									include 'partials/upload-imagen.php';
									?>

								</div>
							</div>

							<!-- 2 - Textos -->
							<div class="samap-card">
								<div class="samap-card__head">
									<em class="fa fa-font"></em>
									<h4 class="samap-card__title">2 &middot; Textos</h4>
								</div>
								<div class="samap-card__body">

									<div class="samap-field">
										<label for="f-eyebrow">Texto chico de arriba</label>
										<input type="text" class="form-control" id="f-eyebrow" name="eyebrow" maxlength="150" value="<?php echo ph($P['eyebrow']); ?>">
										<span class="samap-help">La etiqueta gris arriba del título.</span>
									</div>

									<div class="samap-field">
										<label for="f-titulo">Título principal</label>
										<input type="text" class="form-control" id="f-titulo" name="titulo" maxlength="200" value="<?php echo ph($P['titulo']); ?>">
										<span class="samap-help">El texto grande. Conviene que sea corto.</span>
									</div>

									<div class="samap-field">
										<label for="f-subtitulo">Subtítulo</label>
										<textarea class="form-control" id="f-subtitulo" name="subtitulo" rows="3" maxlength="400"><?php echo ph($P['subtitulo']); ?></textarea>
									</div>

								</div>
							</div>

							<!-- 3 - Botones -->
							<div class="samap-card">
								<div class="samap-card__head">
									<em class="fa fa-hand-pointer-o"></em>
									<h4 class="samap-card__title">3 &middot; Botones</h4>
								</div>
								<div class="samap-card__body">

									<div class="samap-field">
										<label>Botón verde (WhatsApp)</label>
										<div class="samap-grid2">
											<input type="text" class="form-control" id="f-btn1-texto" name="btn1_texto" maxlength="80" placeholder="Texto del botón" value="<?php echo ph($P['btn1_texto']); ?>">
											<input type="text" class="form-control" name="btn1_url" maxlength="300" placeholder="Dejalo vacío para que use WhatsApp" value="<?php echo ph($P['btn1_url']); ?>">
										</div>
										<span class="samap-help">Si dejás la dirección vacía, el botón abre el WhatsApp configurado abajo.</span>
									</div>

									<div class="samap-field">
										<label>Botón azul</label>
										<div class="samap-grid2">
											<input type="text" class="form-control" id="f-btn2-texto" name="btn2_texto" maxlength="80" placeholder="Texto del botón" value="<?php echo ph($P['btn2_texto']); ?>">
											<input type="text" class="form-control" name="btn2_url" maxlength="300" placeholder="planes/" value="<?php echo ph($P['btn2_url']); ?>">
										</div>
										<span class="samap-help">Dirección interna, por ejemplo <code>planes/</code> o <code>contacto/</code>.</span>
									</div>

									<div class="samap-field">
										<label for="f-whatsapp">Número de WhatsApp</label>
										<input type="text" class="form-control" id="f-whatsapp" name="whatsapp" maxlength="30" value="<?php echo ph($P['whatsapp']); ?>">
										<span class="samap-help">
											Con código de país y sin espacios ni signos. Ejemplo: <code>595981123456</code>.
											Se usa en la portada, en el menú y en el pie de todas las páginas.
										</span>
									</div>

								</div>
							</div>

							<!-- 4 - Estadisticas -->
							<div class="samap-card">
								<div class="samap-card__head">
									<em class="fa fa-bar-chart"></em>
									<h4 class="samap-card__title">4 &middot; Los tres datos destacados</h4>
									<span class="samap-card__hint">La franja blanca debajo de la foto</span>
								</div>
								<div class="samap-card__body">

									<?php for ($i = 1; $i <= 3; $i++): ?>
									<div class="samap-stat-row">
										<div class="num">
											<label style="font-size:12px;color:#8a97a3;">Dato <?php echo $i; ?></label>
											<input type="text" class="form-control" id="f-stat<?php echo $i; ?>-num" name="stat<?php echo $i; ?>_num" maxlength="50" value="<?php echo ph($P['stat'.$i.'_num']); ?>">
										</div>
										<div class="lbl">
											<label style="font-size:12px;color:#8a97a3;">Descripción</label>
											<input type="text" class="form-control" id="f-stat<?php echo $i; ?>-lbl" name="stat<?php echo $i; ?>_label" maxlength="120" value="<?php echo ph($P['stat'.$i.'_label']); ?>">
										</div>
									</div>
									<?php endfor; ?>

								</div>
							</div>

							<div class="samap-actionbar">
								<button type="submit" class="btn btn-primary"><em class="fa fa-check"></em> Guardar cambios</button>
								<button type="button" class="btn btn-default" onclick="window.location.href='<?php echo $URL; ?>admin/home/';">Cancelar</button>
								<span class="samap-actionbar__status" id="estado-form">Sin cambios sin guardar</span>
							</div>

						</div>

						<!-- Vista previa -->
						<div class="col-md-5">
							<div class="rdp">
								<div class="rdp__bar"><em class="fa fa-eye"></em> Vista previa &mdash; así se publica</div>
								<div class="rdp__body">
									<span class="rdp__eyebrow" id="p-eyebrow"><?php echo ph($P['eyebrow']); ?></span>
									<h1 class="rdp__titulo" id="p-titulo"><?php echo ph($P['titulo']); ?></h1>
									<p class="rdp__sub" id="p-sub"><?php echo ph($P['subtitulo']); ?></p>
									<div class="rdp__cta">
										<span class="rdp__btn rdp__btn--wa" id="p-btn1"><?php echo ph($P['btn1_texto']); ?></span>
										<span class="rdp__btn rdp__btn--azul" id="p-btn2"><?php echo ph($P['btn2_texto']); ?></span>
									</div>
									<img class="rdp__img" id="p-img"
									     src="<?php echo $imagen_actual !== '' ? $URL.'documentos/portada/'.ph($imagen_actual) : $URL.'documentos/slider/03.jpg'; ?>"
									     alt="Portada"
									     onerror="this.src='<?php echo $URL; ?>assets/images/blog_articles.png';">
									<div class="rdp__stats">
										<?php for ($i = 1; $i <= 3; $i++): ?>
										<div>
											<span class="rdp__num" id="p-stat<?php echo $i; ?>-num"><?php echo ph($P['stat'.$i.'_num']); ?></span>
											<span class="rdp__lbl" id="p-stat<?php echo $i; ?>-lbl"><?php echo ph($P['stat'.$i.'_label']); ?></span>
										</div>
										<?php endfor; ?>
									</div>
								</div>
							</div>
						</div>

					</div>
				</form>

			</section>
		</section>
	</section>

	<?php include 'partials/scripts-comunes.php'; ?>

	<script type="text/javascript">
	(function () {
		'use strict';

		var BASE_URL = <?php echo json_encode($URL, JSON_UNESCAPED_UNICODE); ?>;
		var IMG_ACTUAL = <?php echo json_encode($imagen_actual, JSON_UNESCAPED_UNICODE); ?>;
		var IMG_DEFECTO = BASE_URL + 'documentos/slider/03.jpg';

		function $id(id) { return document.getElementById(id); }

		// --- Aviso de cambios sin guardar ---
		// "listo" evita que el render inicial lo marque como sucio.
		var haycambios = false, listo = false;
		function marcarCambios() {
			if (!listo || haycambios) return;
			haycambios = true;
			var e = $id('estado-form');
			if (e) { e.textContent = 'Tenés cambios sin guardar'; e.style.color = '#c1832b'; }
		}

		// --- Vista previa en vivo ---
		function espejo(origen, destino, vacio) {
			var src = $id(origen), dst = $id(destino);
			if (!src || !dst) return;
			function upd() {
				dst.textContent = src.value.trim() || (vacio || '');
				marcarCambios();
			}
			src.addEventListener('input', upd);
			upd();
		}
		espejo('f-eyebrow',    'p-eyebrow');
		espejo('f-titulo',     'p-titulo');
		espejo('f-subtitulo',  'p-sub');
		espejo('f-btn1-texto', 'p-btn1');
		espejo('f-btn2-texto', 'p-btn2');
		for (var i = 1; i <= 3; i++) {
			espejo('f-stat' + i + '-num', 'p-stat' + i + '-num');
			espejo('f-stat' + i + '-lbl', 'p-stat' + i + '-lbl');
		}
		$(function () { setTimeout(function () { listo = true; }, 300); });

		window.addEventListener('beforeunload', function (ev) {
			if (!haycambios) return;
			ev.preventDefault();
			ev.returnValue = '';
		});
		var form = $id('form2');
		if (form) form.addEventListener('submit', function () { haycambios = false; });

		// --- Imagen ---
		var inputImagen = $id('upload-imagen');
		var previewImg  = $id('p-img');
		var actualImg   = $id('img-actual');

		if (inputImagen) {
			inputImagen.addEventListener('change', function () {
				var f = inputImagen.files && inputImagen.files[0];
				if (!f) return;
				var r = new FileReader();
				r.onload = function (e) {
					if (previewImg) previewImg.src = e.target.result;
					if (actualImg) actualImg.src = e.target.result;
				};
				r.readAsDataURL(f);
				var q = $id('quitar-imagen');
				if (q) q.value = '';
				marcarCambios();
			});
		}

		// "Ajustar esta imagen": mete la foto ya guardada en el mismo
		// recortador del componente, reusando su flujo (validacion + Cropper).
		var btnAjustar = $id('btn-ajustar-actual');
		if (btnAjustar && inputImagen && IMG_ACTUAL) {
			btnAjustar.addEventListener('click', function () {
				var estado = $id('ajustar-estado');
				if (estado) estado.textContent = 'Abriendo el recortador…';
				btnAjustar.disabled = true;

				fetch(BASE_URL + 'documentos/portada/' + encodeURIComponent(IMG_ACTUAL), { credentials: 'same-origin' })
					.then(function (r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.blob(); })
					.then(function (blob) {
						var tipo = blob.type && blob.type.indexOf('image/') === 0 ? blob.type : 'image/jpeg';
						var file = new File([blob], IMG_ACTUAL, { type: tipo, lastModified: Date.now() });
						var dt = new DataTransfer();
						dt.items.add(file);
						inputImagen.files = dt.files;
						inputImagen.dispatchEvent(new Event('change', { bubbles: true }));
						if (estado) estado.textContent = 'Recortá y confirmá abajo, después guardá.';
					})
					.catch(function () {
						if (estado) {
							estado.textContent = 'No se pudo abrir la imagen actual. Subí una nueva con "Cambiar imagen".';
							estado.style.color = '#c1832b';
						}
					})
					.then(function () { btnAjustar.disabled = false; });
			});
		}

		var btnQuitar = $id('btn-quitar-imagen');
		if (btnQuitar) {
			btnQuitar.addEventListener('click', function () {
				if (!confirm('¿Volver a la imagen por defecto de la portada?')) return;
				var q = $id('quitar-imagen');
				if (q) q.value = '1';
				if (inputImagen) inputImagen.value = '';
				if (previewImg) previewImg.src = IMG_DEFECTO;
				if (actualImg) actualImg.src = IMG_DEFECTO;
				marcarCambios();
			});
		}
	})();
	</script>

</body>
</html>
