<?php
require_once('funciones/db.php');

if (!isset($_SESSION['ADM_Username'])) {
	echo "<script>window.location.href=\"" . $URL . "admin/home/\"</script>";
	exit;
}

// ============================================================================
//  Biblioteca de medios
//  Escanea los directorios de imagenes del sitio y los muestra como una
//  galeria filtrable. Para que marketing pueda ver y reutilizar las imagenes
//  existentes en lugar de subir duplicados cada vez que edita contenido.
// ============================================================================

$carpetas = [
	'slider'    => 'documentos/slider/',
	'planes'    => 'documentos/',
	'servicios' => 'documentos/servicios/',
	'medicos'   => 'documentos/medicos/',
	'blog'      => 'documentos/blog/',
	'aliados'   => 'documentos/aliados/',
	'galeria'   => 'documentos/galeria/',
	'bandera'   => 'documentos/bandera/',
	'sponsor'   => 'documentos/sponsor/',
];

$ext_permitidas = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'JPG', 'JPEG', 'PNG', 'WEBP', 'GIF'];

// ---------------------------------------------------------------------------
//  Borrado seguro (con CSRF + verificacion de path real).
// ---------------------------------------------------------------------------
if (isset($_GET['borrar'], $_GET['path']) && samap_puede_escribir() && samap_csrf_validar()) {
	$path_rel = (string)$_GET['path'];

	// Quita el prefijo de URL: http://host/, https://host/, //host/ o / raiz.
	$path_rel = preg_replace('#^https?://[^/]+/#i', '', $path_rel);
	$path_rel = preg_replace('#^//[^/]+/#', '', $path_rel);
	$path_rel = ltrim($path_rel, '/');

	$abs       = __DIR__ . '/../' . $path_rel;
	$abs_real  = realpath($abs);

	// Verificacion: el archivo resuelto (realpath) tiene que caer dentro de
	// alguno de los directorios de imagenes conocidos. Usamos un separador al
	// final para que /documentos no confunda con /documentos_otro.
	$permitido = false;
	if ($abs_real !== false && is_file($abs_real)) {
		$abs_norm = rtrim($abs_real, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
		foreach ($carpetas as $cp) {
			$dir_real = realpath(__DIR__ . '/../' . $cp);
			if ($dir_real === false) continue;
			$dir_norm = rtrim($dir_real, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
			if ($abs_norm === $dir_norm || strpos($abs_norm, $dir_norm) === 0) {
				$permitido = true;
				break;
			}
		}
	}

	if ($permitido && @unlink($abs_real)) {
		samap_flash_set('success', 'Imagen eliminada.');
		header('Location: ' . $URL . 'admin/medios/');
		exit;
	}
	samap_flash_set('error', 'No se pudo eliminar la imagen. Verifica los permisos del archivo o que la URL corresponda a una imagen valida.');
	header('Location: ' . $URL . 'admin/medios/');
	exit;
}

// ---------------------------------------------------------------------------
//  Escaneo de las carpetas configuradas.
// ---------------------------------------------------------------------------
$imagenes = [];
foreach ($carpetas as $carpeta_nombre => $carpeta_path) {
	$abs = __DIR__ . '/../' . $carpeta_path;
	if (!is_dir($abs)) continue;
	$files = @scandir($abs);
	if (!$files) continue;
	foreach ($files as $f) {
		if ($f === '.' || $f === '..') continue;
		if (!is_file($abs . $f)) continue;
		$ext = pathinfo($f, PATHINFO_EXTENSION);
		if (!in_array($ext, $ext_permitidas, true)) continue;

		$size = @getimagesize($abs . $f);
		$imagenes[] = [
			'nombre'   => $f,
			'carpeta'  => $carpeta_nombre,
			'path_web' => $URL . $carpeta_path . rawurlencode($f),
			'path_abs' => $abs . $f,
			'size_kb'  => (int) (@filesize($abs . $f) / 1024),
			'mtime'    => (int) @filemtime($abs . $f),
			'width'    => $size ? (int) $size[0] : 0,
			'height'   => $size ? (int) $size[1] : 0,
		];
	}
}

// Mas nuevas primero.
usort($imagenes, function ($a, $b) { return $b['mtime'] - $a['mtime']; });

// ---------------------------------------------------------------------------
//  Filtros desde la URL.
// ---------------------------------------------------------------------------
$filtro_carpeta = isset($_GET['carpeta']) ? (string) $_GET['carpeta'] : '';
$filtro_q       = isset($_GET['q']) ? trim((string) $_GET['q']) : '';
$filtro_carpeta = isset($carpetas[$filtro_carpeta]) ? $filtro_carpeta : '';

if ($filtro_carpeta !== '') {
	$imagenes = array_filter($imagenes, function ($i) use ($filtro_carpeta) {
		return $i['carpeta'] === $filtro_carpeta;
	});
}
if ($filtro_q !== '') {
	$filtro_q_lower = mb_strtolower($filtro_q);
	$imagenes = array_filter($imagenes, function ($i) use ($filtro_q_lower) {
		return str_contains(mb_strtolower($i['nombre']), $filtro_q_lower);
	});
}

$total_imagenes = count($imagenes);
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
	<title>Biblioteca de medios - Administrador</title>

	</script><link rel="stylesheet" href="<?php echo $URL; ?>admin/app/css/bootstrap.css">

	<link rel="stylesheet" href="<?php echo $URL; ?>admin/plugins/fontawesome/css/font-awesome.min.css">
	<link rel="stylesheet" href="<?php echo $URL; ?>admin/plugins/animo/animate+animo.css">
	<link rel="stylesheet" href="<?php echo $URL; ?>admin/plugins/csspinner/csspinner.min.css">

	<link rel="stylesheet" href="<?php echo $URL; ?>admin/app/css/app.css?v=202606291705">

	<script src="<?php echo $URL; ?>admin/plugins/modernizr/modernizr.js" type="application/javascript"></script>
	<script src="<?php echo $URL; ?>admin/plugins/fastclick/fastclick.js" type="application/javascript"></script>
</head>
<body>

	<section class="wrapper">

		<?php include 'header.php'; ?>
		<?php include 'aside.php'; ?>

		<section>

			<section class="main-content">

				<h3>Biblioteca de medios
					<small style="margin-left:8px;color:#888;font-weight:normal;">
						<?php echo (int) $total_imagenes; ?> im&aacute;genes
						<?php if ($filtro_carpeta !== ''): ?>
							en <?php echo htmlspecialchars($filtro_carpeta, ENT_QUOTES, 'UTF-8'); ?>
						<?php endif; ?>
						<?php if ($filtro_q !== ''): ?>
							que coinciden con &quot;<?php echo htmlspecialchars($filtro_q, ENT_QUOTES, 'UTF-8'); ?>&quot;
						<?php endif; ?>
					</small>
				</h3>

				<form class="form-inline" method="get" action="<?php echo $URL; ?>admin/medios/" style="margin-bottom:18px;">
					<select name="carpeta" class="form-control input-sm">
						<option value="">Todas las carpetas</option>
						<?php foreach ($carpetas as $cn => $cp): ?>
							<option value="<?php echo htmlspecialchars($cn, ENT_QUOTES, 'UTF-8'); ?>"<?php echo $filtro_carpeta === $cn ? ' selected' : ''; ?>><?php echo htmlspecialchars($cn, ENT_QUOTES, 'UTF-8'); ?></option>
						<?php endforeach; ?>
					</select>
					<input type="text" name="q" value="<?php echo htmlspecialchars($filtro_q, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Buscar por nombre..." class="form-control input-sm" style="margin-left:8px;width:280px;">
					<button type="submit" class="btn btn-primary btn-sm" style="margin-left:8px;">Filtrar</button>
					<a href="<?php echo $URL; ?>admin/medios/" class="btn btn-default btn-sm" style="margin-left:4px;">Limpiar</a>
				</form>

				<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:14px;">
					<?php if (empty($imagenes)): ?>
						<div style="grid-column:1/-1;padding:30px;text-align:center;color:#888;background:#fff;border:1px solid #eee;border-radius:4px;">
							No se encontraron im&aacute;genes con los filtros aplicados.
						</div>
					<?php else: ?>
						<?php foreach ($imagenes as $img):
							$e_nombre    = htmlspecialchars($img['nombre'], ENT_QUOTES, 'UTF-8');
							$e_path_web  = htmlspecialchars($img['path_web'], ENT_QUOTES, 'UTF-8');
							$e_carpeta   = htmlspecialchars($img['carpeta'], ENT_QUOTES, 'UTF-8');
							$js_path     = addslashes($img['path_web']);
							$js_nombre   = addslashes($img['nombre']);
							$e_url_path  = urlencode($img['path_web']);
							$e_csrf      = urlencode(samap_csrf_valor());
						?>
							<div style="background:#fff;border:1px solid #eee;border-radius:4px;overflow:hidden;position:relative;">
								<a href="<?php echo $e_path_web; ?>" target="_blank" style="display:block;background:#f5f5f5;text-align:center;padding:8px;min-height:140px;line-height:140px;">
									<img src="<?php echo $e_path_web; ?>" style="max-width:100%;max-height:140px;vertical-align:middle;" alt="<?php echo $e_nombre; ?>">
								</a>
								<div style="padding:8px;font-size:11px;color:#656565;">
									<div style="font-weight:700;margin-bottom:4px;word-break:break-all;"><?php echo $e_nombre; ?></div>
									<div>
										<span class="label label-default" style="font-size:10px;"><?php echo $e_carpeta; ?></span>
										<?php if ($img['width']): ?>
											<span style="margin-left:4px;"><?php echo (int) $img['width']; ?>x<?php echo (int) $img['height']; ?>px</span>
										<?php endif; ?>
										<span style="margin-left:4px;"><?php echo (int) $img['size_kb']; ?> KB</span>
									</div>
									<div style="margin-top:4px;color:#aaa;"><?php echo date('Y-m-d H:i', (int) $img['mtime']); ?></div>
								</div>
								<div style="padding:6px 8px;border-top:1px solid #eee;background:#fafafa;display:flex;gap:4px;">
									<button type="button" class="btn btn-xs btn-default" onclick="navigator.clipboard.writeText('<?php echo $js_path; ?>'); var b=this; var html=b.innerHTML; b.innerHTML='<i class=\'fa fa-check\'></i> Copiado'; setTimeout(function(){ b.innerHTML=html; }, 2000);" style="flex:1;">
										<i class="fa fa-clipboard"></i> Copiar URL
									</button>
									<?php if (samap_puede_escribir()): ?>
										<a href="?borrar=1&amp;path=<?php echo $e_url_path; ?>&amp;csrf_token=<?php echo $e_csrf; ?>" onclick="return confirm('&iquest;Borrar <?php echo htmlspecialchars(addslashes($img['nombre']), ENT_QUOTES, 'UTF-8'); ?>? Esta acci&oacute;n no se puede deshacer.');" class="btn btn-xs btn-danger" title="Borrar">
											<i class="fa fa-trash"></i>
										</a>
									<?php endif; ?>
								</div>
							</div>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>

			</section>

		</section>

	</section>

	<script src="<?php echo $URL; ?>admin/plugins/jquery/jquery.min.js"></script>
	<script src="<?php echo $URL; ?>admin/plugins/bootstrap/js/bootstrap.min.js"></script>

	<script src="<?php echo $URL; ?>admin/plugins/chosen/chosen.jquery.min.js"></script>
	<script src="<?php echo $URL; ?>admin/plugins/slider/js/bootstrap-slider.js"></script>
	<script src="<?php echo $URL; ?>admin/plugins/filestyle/bootstrap-filestyle.min.js"></script>

	<script src="<?php echo $URL; ?>admin/plugins/animo/animo.min.js"></script>

	<script src="<?php echo $URL; ?>admin/plugins/sparklines/jquery.sparkline.min.js"></script>

	<script src="<?php echo $URL; ?>admin/plugins/slimscroll/jquery.slimscroll.min.js"></script>

	<!--[if lt IE 8]><script src="js/excanvas.min.js"></script><![endif]-->
	<script src="<?php echo $URL; ?>admin/plugins/datatable/media/js/jquery.dataTables.min.js"></script>
	<script src="<?php echo $URL; ?>admin/plugins/datatable/extensions/datatable-bootstrap/js/dataTables.bootstrap.js"></script>
	<script src="<?php echo $URL; ?>admin/plugins/datatable/extensions/datatable-bootstrap/js/dataTables.bootstrapPagination.js"></script>
	<script src="<?php echo $URL; ?>admin/plugins/datatable/extensions/ColVis/js/dataTables.colVis.min.js"></script>

	<script src="<?php echo $URL; ?>admin/app/js/app.js?v=202606291718"></script>

</body>
</html>
