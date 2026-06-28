<?php
require_once('funciones/db.php');

if (isset($_SESSION['ADM_Username'])){

	$q_raw = $_GET['q'] ?? '';
	$q = trim($q_raw);
	$q = mb_substr($q, 0, 100);
	$q = preg_replace('/[\x00-\x1F\x7F]/u', '', $q);

	$sections = array(
		array(
			'key'   => 'planes',
			'table' => 'tbl_planes',
			'col'   => 'titulo',
			'label' => 'Planes',
			'icon'  => 'umbrella',
			'edit'  => 'editarplan',
			'list'  => 'planes',
		),
		array(
			'key'   => 'blog',
			'table' => 'tbl_blog',
			'col'   => 'titulo',
			'label' => 'Blog',
			'icon'  => 'book',
			'edit'  => 'editarblog',
			'list'  => 'blogs',
		),
		array(
			'key'   => 'medicos',
			'table' => 'tbl_medicos',
			'col'   => "CONCAT(titulo, ' ', nombre)",
			'label' => 'Médicos',
			'icon'  => 'user-md',
			'edit'  => 'editarmedico',
			'list'  => 'medicos',
		),
		array(
			'key'   => 'servicios',
			'table' => 'tbl_servicios',
			'col'   => 'titulo',
			'label' => 'Servicios',
			'icon'  => 'medkit',
			'edit'  => 'editarservicio',
			'list'  => 'servicios',
		),
		array(
			'key'   => 'slider',
			'table' => 'tbl_slider',
			'col'   => 'nombre',
			'label' => 'Sliders',
			'icon'  => 'picture-o',
			'edit'  => 'editarslider',
			'list'  => 'slider',
		),
		array(
			'key'   => 'convenios',
			'table' => 'tbl_convenios',
			'col'   => 'titulo',
			'label' => 'Convenios',
			'icon'  => 'building-o',
			'edit'  => 'editarconvenio',
			'list'  => 'convenios',
		),
		array(
			'key'   => 'aliados',
			'table' => 'tbl_aliados',
			'col'   => 'titulo',
			'label' => 'Aliados',
			'icon'  => 'handshake-o',
			'edit'  => 'editaraliado',
			'list'  => 'aliados',
		),
		array(
			'key'   => 'guiamedica',
			'table' => 'tbl_guiamedica',
			'col'   => 'nombre',
			'label' => 'Guía Médica',
			'icon'  => 'hospital-o',
			'edit'  => '',
			'list'  => 'guia',
			'seeall' => 'Ver toda la Gu&iacute;a M&eacute;dica',
		),
		array(
			'key'   => 'sanatorio',
			'table' => 'tbl_sanatorio',
			'col'   => 'nombre',
			'label' => 'Sanatorios',
			'icon'  => 'h-square',
			'edit'  => '',
			'list'  => 'sanatorios',
		),
		array(
			'key'   => 'ciudad',
			'table' => 'tbl_ciudad',
			'col'   => 'nombre',
			'label' => 'Ciudad',
			'icon'  => 'map-marker',
			'edit'  => '',
			'list'  => 'ciudad',
			'seeall' => 'Ver todas las ciudades',
		),
		array(
			'key'   => 'especialidad',
			'table' => 'tbl_especialidad',
			'col'   => 'nombre',
			'label' => 'Especialidad',
			'icon'  => 'stethoscope',
			'edit'  => '',
			'list'  => 'guia',
			'seeall' => 'Ver todas las especialidades',
		),
		array(
			'key'   => 'categorias_aliado',
			'table' => 'tbl_categorias_aliado',
			'col'   => 'nombre',
			'label' => 'Categorías',
			'icon'  => 'tags',
			'edit'  => '',
			'list'  => 'categorias',
			'seeall' => 'Ver todas las categor&iacute;as',
		),
	);

	$total_results = 0;
	$total_sections = 0;
	$results = array();

	if ($q !== '') {
		mysqli_select_db($connect, $database);
		$like = '%' . mysqli_real_escape_string($connect, $q) . '%';

		foreach ($sections as $idx => $sec) {
			$sql = "SELECT id, " . $sec['col'] . " AS display
			        FROM " . $sec['table'] . "
			        WHERE deleted_at IS NULL
			          AND " . $sec['col'] . " LIKE '" . $like . "'
			        ORDER BY id DESC
			        LIMIT 5";
			$rs = mysqli_query($connect, $sql);
			$rows = array();
			if ($rs) {
				while ($r = mysqli_fetch_assoc($rs)) {
					$rows[] = $r;
				}
				mysqli_free_result($rs);
			}
			$results[$idx] = $rows;
			$total_results += count($rows);
			if (count($rows) > 0) {
				$total_sections++;
			}
		}
	}

	$q_esc = htmlspecialchars($q, ENT_QUOTES);

} else {

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
	<title>Buscar - Administrador</title>

	</script><link rel="stylesheet" href="<?php echo $URL;?>admin/app/css/bootstrap.css">

	<link rel="stylesheet" href="<?php echo $URL;?>admin/plugins/fontawesome/css/font-awesome.min.css">
	<link rel="stylesheet" href="<?php echo $URL;?>admin/plugins/animo/animate+animo.css">
	<link rel="stylesheet" href="<?php echo $URL;?>admin/plugins/csspinner/csspinner.min.css">

	<link rel="stylesheet" href="<?php echo $URL;?>admin/app/css/app.css">

	<script src="<?php echo $URL;?>admin/plugins/modernizr/modernizr.js" type="application/javascript"></script>

	<script src="<?php echo $URL;?>admin/plugins/fastclick/fastclick.js" type="application/javascript"></script>
</head>
<body>

	<section class="wrapper">

		<?php include 'header.php'; ?>
		<?php include 'aside.php'; ?>

		<section>

			<section class="main-content">

				<h3>Resultados de búsqueda</h3>

				<div class="row" style="margin-bottom:20px;">
					<div class="col-md-8 col-md-offset-2">
						<form action="<?php echo $URL;?>admin/buscar/" method="get" class="form-inline" style="display:block;">
							<div class="input-group" style="width:100%;">
								<input type="text" name="q" value="<?php echo $q_esc; ?>" class="form-control input-lg" placeholder="Buscar en todo el sitio..." autofocus>
								<span class="input-group-btn">
									<button class="btn btn-primary btn-lg" type="submit"><em class="fa fa-search"></em> Buscar</button>
								</span>
							</div>
						</form>
					</div>
				</div>

				<?php if ($q === ''): ?>

					<div class="row">
						<div class="col-md-6 col-md-offset-3 text-center" style="padding:40px 0;">
							<em class="fa fa-search fa-3x" style="color:#aaa;"></em>
							<h4 style="margin-top:20px;">Ingresá un término de búsqueda en la barra de arriba.</h4>
							<p class="text-muted">Podés buscar planes, blogs, médicos, servicios, sliders, convenios, aliados y más.</p>
						</div>
					</div>

				<?php elseif ($total_results === 0): ?>

					<div class="row">
						<div class="col-md-8 col-md-offset-2 text-center" style="padding:30px 0;">
							<div class="alert alert-warning" style="text-align:left;">
								<p>No se encontraron resultados para <strong>"<?php echo $q_esc; ?>"</strong>. Probá con otra palabra clave o revisá la sección correspondiente.</p>
							</div>
							<a href="<?php echo $URL;?>admin/home/" class="btn btn-default"><em class="fa fa-dashboard"></em> Volver al Escritorio</a>
						</div>
					</div>

				<?php else: ?>

					<p class="text-muted" style="margin-bottom:20px;">
						Resultados para: <strong>"<?php echo $q_esc; ?>"</strong> &mdash; <?php echo (int)$total_results; ?> resultado<?php echo ($total_results == 1 ? '' : 's'); ?> en <?php echo (int)$total_sections; ?> <?php echo ($total_sections == 1 ? 'secci&oacute;n' : 'secciones'); ?>
					</p>

					<?php foreach ($sections as $idx => $sec): ?>
						<?php if (empty($results[$idx])) continue; ?>
						<div class="panel panel-primary">
							<div class="panel-heading">
								<em class="fa fa-<?php echo $sec['icon']; ?>"></em>
								<?php echo htmlspecialchars($sec['label'], ENT_QUOTES); ?>
								<span class="badge" style="background:#fff;color:#337ab7;"><?php echo count($results[$idx]); ?></span>
							</div>
							<div class="panel-body" style="padding:0;">
								<table class="table table-striped" style="margin:0;">
									<tbody>
									<?php foreach ($results[$idx] as $row):
										$id = (int)$row['id'];
										$display = (string)$row['display'];
										$display_esc = htmlspecialchars($display, ENT_QUOTES);

										$pos = mb_stripos($display, $q);
										if ($pos !== false && $q !== '') {
											$pre  = mb_substr($display, 0, $pos);
											$len  = mb_strlen($q);
											$mid  = mb_substr($display, $pos, $len);
											$post = mb_substr($display, $pos + $len);
											$highlighted = htmlspecialchars($pre, ENT_QUOTES)
												. '<mark style="background:#FFEB3B;padding:0 2px;">' . htmlspecialchars($mid, ENT_QUOTES) . '</mark>'
												. htmlspecialchars($post, ENT_QUOTES);
										} else {
											$highlighted = $display_esc;
										}

										if ($sec['edit'] !== '') {
											$href = $URL . 'admin/' . $sec['edit'] . '/cod/' . $id . '/';
										} else {
											$href = $URL . 'admin/' . $sec['list'] . '/';
										}
									?>
										<tr>
											<td><a href="<?php echo $href; ?>"><?php echo $highlighted; ?></a></td>
										</tr>
									<?php endforeach; ?>
									</tbody>
								</table>
							</div>
							<div class="panel-footer" style="text-align:right;">
								<a href="<?php echo $URL;?>admin/<?php echo $sec['list']; ?>/" class="btn btn-xs btn-default"><?php echo isset($sec['seeall']) ? $sec['seeall'] : 'Ver todos los ' . strtolower($sec['label']); ?> &rarr;</a>
							</div>
						</div>
					<?php endforeach; ?>

				<?php endif; ?>

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


	<script src="<?php echo $URL;?>admin/app/js/app.js"></script>

</body>
</html>
