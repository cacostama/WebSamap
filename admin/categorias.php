<?php
require_once('funciones/db.php');

if (isset($_SESSION['ADM_Username'])){

	mysqli_select_db($connect, $database);
	$papelera = isset($_GET['papelera']) && $_GET['papelera'] === '1';
	$query_categorias = "SELECT c.*, (SELECT COUNT(*) FROM tbl_aliados a WHERE a.categoria_id = c.id AND a.deleted_at IS NULL) AS aliados
	                     FROM tbl_categorias_aliado c
	                     WHERE " . ($papelera ? "c.deleted_at IS NOT NULL" : "c.deleted_at IS NULL") . "
	                     ORDER BY c.orden ASC, c.nombre ASC";
	$categorias = mysqli_query($connect, $query_categorias) or die(mysqli_error($connect));

	if ((isset($_GET['borrar'])) && ($_GET['id'] != "")) {
	  if (!samap_puede_escribir() || !samap_csrf_validar()) {
	    echo"<script>alert('No se pudo eliminar la categoría. Volvé a intentarlo.'); window.location.href=\"".$URL."admin/categorias/\"</script>";
	    exit;
	  }
	  $id = (int) $_GET['id'];
	  $deleteSQL = "UPDATE tbl_categorias_aliado SET deleted_at=NOW() WHERE id=".$id;
	  mysqli_select_db($connect, $database);
	  $Result1 = mysqli_query($connect, $deleteSQL) or die(mysqli_error($connect));
	  echo"<script>alert('Listo, la categoría se eliminó. Ya no se muestra en el sitio web.'); window.location.href=\"".$URL."admin/categorias/\"</script>";
	}

	// ---- Papelera: restaurar un registro soft-deleted ----
	if (isset($_GET['restaurar']) && $_GET['restaurar'] === 'si' && ($_GET['id'] ?? '') != '' && samap_puede_escribir() && samap_csrf_validar()) {
		$id = (int)$_GET['id'];
		$restSQL = sprintf("UPDATE tbl_categorias_aliado SET deleted_at = NULL WHERE id = %d", $id);
		mysqli_select_db($connect, $database);
		@mysqli_query($connect, $restSQL);
		echo "<script>alert('La categoría fue restaurada. Ya se muestra nuevamente en el sitio web.'); window.location.href=\"".$URL."admin/categorias/?papelera=1\"</script>";
		exit;
	}

	// ---- Papelera: borrar definitivamente (DELETE fisico) ----
	if (isset($_GET['borrar_def']) && $_GET['borrar_def'] === 'si' && ($_GET['id'] ?? '') != '' && samap_puede_escribir() && samap_csrf_validar()) {
		$id = (int)$_GET['id'];
		$defSQL = sprintf("DELETE FROM tbl_categorias_aliado WHERE id = %d", $id);
		mysqli_select_db($connect, $database);
		@mysqli_query($connect, $defSQL);
		echo "<script>alert('La categoría fue eliminada definitivamente. Ya no se puede recuperar.'); window.location.href=\"".$URL."admin/categorias/?papelera=1\"</script>";
		exit;
	}

	// ---- Inputs para partials/tabla-searchable.php ----
	$tabla_titulo        = 'Categorías de Aliados';
	$btn_agregar_label   = 'Agregar Categoría';
	$btn_agregar_url     = 'admin/agregar-categoria.php';
	$edit_url_pattern    = 'admin/editarcategoria/cod/{id}/';
	$delete_url_pattern  = 'admin/categorias.php?id={id}&borrar=si&csrf_token={csrf}';
	$delete_confirm      = '¿Querés eliminar esta categoría? Los comercios que la usaban quedarán sin categoría.';
	$empty_message       = 'Todavía no hay categorías cargadas.';
	$slug                = 'categorias';
	$papelera_activa     = $papelera;
	$trash_count         = 0;
	if (!$papelera) {
		@mysqli_select_db($connect, $database);
		$rcRes = @mysqli_query($connect, "SELECT COUNT(*) AS c FROM tbl_categorias_aliado WHERE deleted_at IS NOT NULL");
		if ($rcRes) { $rcRow = mysqli_fetch_assoc($rcRes); $trash_count = (int)($rcRow['c'] ?? 0); }
	}

	$columns = [
		['th' => 'Orden',    'td_html' => function($r) { return '<td>' . (int)$r['orden'] . '</td>'; }],
		['th' => 'Nombre',   'td_html' => function($r) { return '<td>' . htmlspecialchars((string)$r['nombre'], ENT_QUOTES, 'UTF-8') . '</td>'; }],
		['th' => 'Ícono',    'td_html' => function($r) {
			$icon = htmlspecialchars((string)($r['icono'] ?? ''), ENT_QUOTES, 'UTF-8');
			return '<td><i class="fa ' . $icon . '"></i> <small style="color:#999;">' . $icon . '</small></td>';
		}],
		['th' => 'Color',    'td_html' => function($r) {
			$col = (string)($r['color'] ?? '');
			if ($col === '') {
				return '<td><span style="color:#bbb;">Color por defecto</span></td>';
			}
			$col_e = htmlspecialchars($col, ENT_QUOTES, 'UTF-8');
			return '<td><span style="display:inline-block;width:16px;height:16px;border-radius:3px;vertical-align:middle;background:' . $col_e . '"></span> ' . $col_e . '</td>';
		}],
		['th' => 'Comercios','td_html' => function($r) { return '<td>' . (int)$r['aliados'] . '</td>'; }],
		['th' => 'Estado',   'td_html' => function($r) {
			$activo = ((int)($r['activo'] ?? 0)) === 1;
			return '<td><div class="btn ' . ($activo ? 'btn-success' : 'btn-danger') . ' btn-xs">' . ($activo ? 'ACTIVA' : 'OCULTA') . '</div></td>';
		}],
	];

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
	<title>CATEGORÍAS DE ALIADOS -  Administrador</title>

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

				<h3><?php echo htmlspecialchars($tabla_titulo, ENT_QUOTES, 'UTF-8'); ?></h3>
				<p style="color:#888;margin-top:-.5rem;">Definí las categorías que agrupan a los comercios adheridos en la sección "Descuentos Exclusivos" del sitio.</p>
				<?php include 'partials/papelera-toggle.php'; ?>
				<?php $papelera_activa = $papelera; if (isset($categorias)) { $rows = $categorias; include 'partials/tabla-searchable.php'; } ?>
			</section>

		</section>

	</section>

	<script src="<?php echo $URL;?>admin/plugins/jquery/jquery.min.js"></script>
	<script src="<?php echo $URL;?>admin/plugins/bootstrap/js/bootstrap.min.js"></script>
	<script src="<?php echo $URL;?>admin/plugins/chosen/chosen.jquery.min.js"></script>
	<script src="<?php echo $URL;?>admin/plugins/slider/js/bootstrap-slider.js"></script>
	<script src="<?php echo $URL;?>admin/plugins/filestyle/bootstrap-filestyle.min.js"></script>
	<script src="<?php echo $URL;?>admin/plugins/animo/animo.min.js"></script>
	<script src="<?php echo $URL;?>admin/plugins/slimscroll/jquery.slimscroll.min.js"></script>
	<script src="<?php echo $URL;?>admin/app/js/app.js"></script>

</body>
</html>
