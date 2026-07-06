<?php
require_once('funciones/db.php');

if (isset($_SESSION['ADM_Username'])){
	
} else{

	echo"<script>window.location.href=\"".$URL."admin/index/\"</script>";

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
	<title>HOME -  Administrador</title>

	</script><link rel="stylesheet" href="<?php echo $URL;?>admin/app/css/bootstrap.css">

	<link rel="stylesheet" href="<?php echo $URL;?>admin/plugins/fontawesome/css/font-awesome.min.css">
	<link rel="stylesheet" href="<?php echo $URL;?>admin/plugins/animo/animate+animo.css">
	<link rel="stylesheet" href="<?php echo $URL;?>admin/plugins/csspinner/csspinner.min.css">

	<link rel="stylesheet" href="<?php echo $URL;?>admin/app/css/app.css?v=202606291705">
</head>
<body>

	<section class="wrapper">

		<?php include 'header.php'; ?>
		<?php include 'aside.php'; ?>

		<section>

			<section class="main-content">

				<?php
					// db.php solo abre la conexion; el resto de las paginas del panel
					// seleccionan la base justo antes de la primera query.
					mysqli_select_db($connect, $database);

					// Total de usuarios para el chip del encabezado. La tabla tbl_user
					// no tiene soft-delete, se cuentan todas las filas.
					$qUsers     = mysqli_query($connect, 'SELECT COUNT(*) AS c FROM tbl_user');
					$rowUsers   = $qUsers ? mysqli_fetch_assoc($qUsers) : null;
					$totalUsers = $rowUsers ? (int)$rowUsers['c'] : 0;
				?>

				<h3>Escritorio <small style="margin-left:8px;color:#888;">Bienvenido, <?php echo htmlspecialchars((string)($_SESSION['ADM_Nombre'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></small> <span class="label label-success" style="font-size:11px;margin-left:4px;vertical-align:middle;"><?php echo (int)$totalUsers; ?> usuario<?php echo $totalUsers === 1 ? '' : 's'; ?> activo<?php echo $totalUsers === 1 ? '' : 's'; ?></span></h3>
				<!--<div data-toggle="notify" data-onload data-message="&lt;b&gt;New Updates Available!&lt;/b&gt; Don't forget to check them!" data-options="{&quot;status&quot;:&quot;danger&quot;, &quot;pos&quot;:&quot;top-right&quot;}" class="hidden-xs"></div>-->

				<div class="row">

					<div class="col-md-12">

						<div class="row">
						</div>
						<div class="row">

							<?php
								// Definicion de las tarjetas del escritorio.
								// Campos: tabla | etiqueta visible | icono FA | color | url listado | url alta | columna titulo.
								$dash_sections = array(
									array('planes',            'Planes',                'fa-umbrella',     '#27AE60', 'admin/planes/',         'admin/agregar-plan/',      'titulo'),
									array('blog',              'Notas del blog',        'fa-book',         '#27AE60', 'admin/blogs/',          'admin/agregar-blog/',      'titulo'),
									array('medicos',           'Médicos',               'fa-user-md',      '#00afd1', 'admin/medicos/',        'admin/agregar-medico/',    'concat'),
									array('servicios',         'Servicios',             'fa-medkit',       '#27AE60', 'admin/servicios/',      'admin/agregar-servicio/',  'titulo'),
									array('slider',            'Sliders',               'fa-picture-o',    '#27AE60', 'admin/slider/',         'admin/agregar-slider/',    'nombre'),
									array('convenios',         'Convenios',             'fa-building-o',   '#27AE60', 'admin/convenios/',      'admin/agregar-convenio/',  'titulo'),
									array('aliados',           'Aliados',               'fa-handshake-o',  '#27AE60', 'admin/aliados/',        'admin/agregar-aliado/',    'titulo'),
									array('guiamedica',        'Guía Médica',           'fa-hospital-o',   '#00afd1', 'admin/guia/',           'admin/agregar-guia/',      'nombre'),
									array('sanatorio',         'Sanatorios',            'fa-h-square',     '#00afd1', 'admin/sanatorios/',     'admin/agregar-sanatorio/', 'nombre'),
									array('ciudad',            'Ciudades',              'fa-map-marker',   '#00afd1', 'admin/ciudad/',         'admin/agregar-ciudad/',    'nombre'),
									array('especialidad',      'Especialidades',        'fa-stethoscope',  '#ffc61d', 'admin/guia/',           'admin/agregar-guia/',      'nombre'),
									array('categorias_aliado', 'Categorías de aliados', 'fa-tags',         '#ffc61d', 'admin/categorias/',     'admin/agregar-categoria/', 'nombre'),
								);

								// Resolver todas las tarjetas en una sola ida a MySQL. Antes se
								// ejecutaban dos consultas por tarjeta (24 consultas secuenciales).
								$dash_stats = array();
								$stats_sql = array();
								foreach ($dash_sections as $sec) {
									$tbl = $sec[0];
									$col_mode = $sec[6];
									if ($col_mode === 'nombre') {
										$display_col = 'nombre';
									} elseif ($col_mode === 'concat') {
										$display_col = "CONCAT(IFNULL(titulo,''), ' ', IFNULL(nombre,''))";
									} else {
										$display_col = 'titulo';
									}
									$stats_sql[] = "SELECT CONVERT('$tbl' USING utf8mb4) COLLATE utf8mb4_unicode_ci AS section_key,
										COUNT(*) AS total,
										COALESCE(MAX(id), 0) AS last_id,
										CONVERT(COALESCE(SUBSTRING_INDEX(
											GROUP_CONCAT($display_col ORDER BY id DESC SEPARATOR '\\n'),
											'\\n', 1
										), '') USING utf8mb4) COLLATE utf8mb4_unicode_ci AS last_title
										FROM tbl_$tbl WHERE deleted_at IS NULL";
								}
								$qStats = mysqli_query($connect, implode(' UNION ALL ', $stats_sql));
								if ($qStats) {
									while ($stat = mysqli_fetch_assoc($qStats)) {
										$dash_stats[$stat['section_key']] = $stat;
									}
								}

								foreach ($dash_sections as $sec):
									$tbl       = $sec[0];
									$label     = $sec[1];
									$icon      = $sec[2];
									$bg        = $sec[3];
									$list_url  = $sec[4];
									$add_url   = $sec[5];
									$col_mode  = $sec[6];

									$stat       = $dash_stats[$tbl] ?? null;
									$count      = $stat ? (int)$stat['total'] : 0;
									$last_id    = $stat ? (int)$stat['last_id'] : 0;
									$last_title = $stat ? trim((string)$stat['last_title']) : '';
									if (function_exists('mb_strlen')) {
										if (mb_strlen($last_title) > 60) { $last_title = mb_substr($last_title, 0, 60) . '...'; }
									} elseif (strlen($last_title) > 60) {
										$last_title = substr($last_title, 0, 60) . '...';
									}
									$subtitle = ($count > 0) ? ($label . ' activos') : 'Aún no cargaste registros';
							?>

							<div class="col-md-4">
								<div class="panel widget bg-success" style="background: <?php echo $bg; ?>; position:relative;">
									<div class="panel-body" style="padding-bottom:38px;">
										<div class="text-right text">
											<em class="fa <?php echo $icon; ?> fa-2x"></em>
										</div>
										<h3 class="mt0" style="font-size:36px;line-height:1;margin-bottom:4px;"><?php echo number_format($count, 0, ',', '.'); ?></h3>
										<p class="text" style="margin-bottom:2px;font-size:14px;opacity:.95;"><?php echo htmlspecialchars($subtitle, ENT_QUOTES, 'UTF-8'); ?></p>
										<?php if ($count > 0 && $last_id > 0): ?>
										<p class="text" style="margin-bottom:0;font-size:11px;opacity:.85;line-height:1.35;">Última edición: #<?php echo $last_id; ?> &mdash; &laquo;<?php echo htmlspecialchars($last_title, ENT_QUOTES, 'UTF-8'); ?>&raquo;</p>
										<?php else: ?>
										<p class="text" style="margin-bottom:0;font-size:11px;opacity:.85;line-height:1.35;">&nbsp;</p>
										<?php endif; ?>
										<a href="<?php echo $URL; ?><?php echo $add_url; ?>" style="position:absolute;right:12px;bottom:10px;z-index:2;background:rgba(0,0,0,0.18);color:#fff;text-decoration:none;padding:3px 9px;border-radius:3px;font-size:12px;">
											<i class="fa fa-plus"></i> Agregar
										</a>
									</div>
									<a href="<?php echo $URL; ?><?php echo $list_url; ?>" style="position:absolute;top:0;left:0;right:0;bottom:0;display:block;text-indent:-9999px;overflow:hidden;color:transparent;z-index:1;" aria-label="Ver <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>"></a>
								</div>
							</div>

							<?php
								endforeach;
							?>

						</div>

					</div>

				</div>
			</section>

		</section>

	</section>
	<?php include 'partials/scripts-comunes.php'; ?>

</body>
</html>
