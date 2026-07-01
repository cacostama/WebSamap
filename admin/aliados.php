<?php
require_once('funciones/db.php');

if (isset($_SESSION['ADM_Username'])){


	mysqli_select_db($connect, $database);
	$papelera = isset($_GET['papelera']) && $_GET['papelera'] === '1';
	$query_convenios= "SELECT a.*, c.nombre AS categoria_nombre
	                   FROM tbl_aliados a
	                   LEFT JOIN tbl_categorias_aliado c ON a.categoria_id = c.id
	                   WHERE " . ($papelera ? "a.deleted_at IS NOT NULL" : "a.deleted_at IS NULL") . "
	                   ORDER BY c.orden ASC, a.orden ASC, a.id ASC";
	$convenios = mysqli_query($connect, $query_convenios) or die(mysqli_error($connect));

    function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "")
{
    // Escapar el valor dependiendo del tipo
    switch ($theType) {
        case "text":
            $theValue = ($theValue != "") ? "'" . addslashes($theValue) . "'" : "NULL";
            break;
        case "long":
        case "int":
            $theValue = ($theValue != "") ? intval($theValue) : "NULL";
            break;
        case "double":
            $theValue = ($theValue != "") ? "'" . doubleval($theValue) . "'" : "NULL";
            break;
        case "date":
            $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
            break;
        case "defined":
            $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
            break;
    }
    return $theValue;
}


    if ((isset($_GET['borrar'])) && ($_GET['id'] != "")) {
	  if (!samap_puede_escribir() || !samap_csrf_validar()) {
	    samap_flash_set('error', 'No se pudo eliminar el aliado. Volvé a intentarlo.');
	    header('Location: ' . $URL . 'admin/aliados/');
	    exit;
	  }

	  $id_borrar = (int) $_GET['id'];
	  $audit_row_aliado = null;
	  $audQ = mysqli_query($connect, "SELECT id, titulo, imagen, descuento FROM tbl_aliados WHERE id = " . $id_borrar);
	  if ($audQ) { $audit_row_aliado = mysqli_fetch_assoc($audQ); }

	  $deleteSQL = sprintf("UPDATE tbl_aliados SET deleted_at=NOW() WHERE id=%s",
	                       GetSQLValueString($_GET['id'], "int"));

	  mysqli_select_db($connect, $database);
	  $Result1 = mysqli_query($connect, $deleteSQL) or die(mysqli_error($connect));

	  @samap_audit_log('delete', 'tbl_aliados', $id_borrar, "Borró (soft) el aliado #$id_borrar: " . substr((string)($audit_row_aliado['titulo'] ?? ''), 0, 100), $audit_row_aliado, null);

	  samap_flash_set('success', 'Listo, el aliado se eliminó. Ya no se muestra en el sitio web.');
	  header('Location: ' . $URL . 'admin/aliados/');
	  exit;
	}

	// ---- Papelera: restaurar un registro soft-deleted ----
	if (isset($_GET['restaurar']) && $_GET['restaurar'] === 'si' && ($_GET['id'] ?? '') != '' && samap_puede_escribir() && samap_csrf_validar()) {
		$id = (int)$_GET['id'];
		$restSQL = sprintf("UPDATE tbl_aliados SET deleted_at = NULL WHERE id = %d", $id);
		mysqli_select_db($connect, $database);
		@mysqli_query($connect, $restSQL);
		@samap_audit_log('restore', 'tbl_aliados', $id, "Restauró el aliado #$id");
		samap_flash_set('success', 'El aliado fue restaurado. Ya se muestra nuevamente en el sitio web.');
		header('Location: ' . $URL . 'admin/aliados/?papelera=1');
		exit;
	}

	// ---- Papelera: borrar definitivamente (DELETE fisico) ----
	if (isset($_GET['borrar_def']) && $_GET['borrar_def'] === 'si' && ($_GET['id'] ?? '') != '' && samap_puede_escribir() && samap_csrf_validar()) {
		$id = (int)$_GET['id'];
		$defSQL = sprintf("DELETE FROM tbl_aliados WHERE id = %d", $id);
		mysqli_select_db($connect, $database);
		@mysqli_query($connect, $defSQL);
		@samap_audit_log('hard_delete', 'tbl_aliados', $id, "Eliminó definitivamente el aliado #$id");
		samap_flash_set('warning', 'El aliado fue eliminado definitivamente. Ya no se puede recuperar.');
		header('Location: ' . $URL . 'admin/aliados/?papelera=1');
		exit;
	}

	// ---- Acciones masivas (Feature 12) ----
	if (isset($_GET['borrar_masivo']) && $_GET['borrar_masivo'] === 'si' && samap_puede_escribir() && samap_csrf_validar()) {
		$ids = isset($_GET['ids']) && is_array($_GET['ids']) ? $_GET['ids'] : [];
		$count = 0;
		foreach ($ids as $id_raw) {
			$id = (int)$id_raw;
			if ($id > 0) {
				$updSQL = sprintf('UPDATE tbl_aliados SET deleted_at = NOW() WHERE id = %d', $id);
				mysqli_select_db($connect, $database);
				if (@mysqli_query($connect, $updSQL)) { $count += (int)@mysqli_affected_rows($connect); }
			}
		}
		samap_audit_log('delete_bulk', 'tbl_aliados', 0, 'Borró masivamente ' . $count . ' registros');
		samap_flash_set('success', "$count registros eliminados.");
		header('Location: ' . $URL . 'admin/aliados/');
		exit;
	}


// ---- Reordenar (Feature 13): AJAX POST con ids[] -> UPDATE orden = i ----
if (isset($_GET['reordenar']) && $_GET['reordenar'] === 'si' && samap_puede_escribir() && samap_csrf_validar()) {
    $ids_post = $_POST['ids'] ?? $_GET['ids'] ?? [];
    $ids_post = is_array($ids_post) ? $ids_post : [];
    header('Content-Type: application/json; charset=utf-8');
    if (!$ids_post) { echo json_encode(['ok' => false, 'error' => 'no_ids']); exit; }
    $i = 1;
    $ok = true;
    foreach ($ids_post as $id_raw) {
        $id = (int)$id_raw;
        if ($id <= 0) continue;
        $updSQL = sprintf("UPDATE tbl_aliados SET orden = %d WHERE id = %d", $i, $id);
        mysqli_select_db($connect, $database);
        if (!@mysqli_query($connect, $updSQL)) { $ok = false; break; }
        $i++;
    }
    if ($ok) {
        @samap_audit_log('reorder', 'tbl_aliados', 0, 'Reordenó ' . ($i - 1) . ' registros');
    }
    echo json_encode(['ok' => $ok]);
    exit;
}	// ---- Inputs para partials/tabla-searchable.php ----

	$tabla_titulo        = 'Aliados';
	$btn_agregar_label   = 'Agregar Aliado';
	$btn_agregar_url     = 'admin/agregar-aliado.php';
	$edit_url_pattern    = 'admin/editaraliado/cod/{id}/';
	$delete_url_pattern  = 'admin/aliados.php?id={id}&borrar=si&csrf_token={csrf}';
	$delete_confirm      = '¿Querés eliminar este aliado? Dejará de mostrarse en el sitio web.';
	$empty_message       = 'Todavía no hay aliados cargados.';
	$slug                = 'aliados';
	$papelera_activa     = $papelera;
	$trash_count         = 0;
	$enable_bulk         = !$papelera;
	$ordenable           = !$papelera;
	if (!$papelera) {
		@mysqli_select_db($connect, $database);
		$rcRes = @mysqli_query($connect, "SELECT COUNT(*) AS c FROM tbl_aliados WHERE deleted_at IS NOT NULL");
		if ($rcRes) { $rcRow = mysqli_fetch_assoc($rcRes); $trash_count = (int)($rcRow['c'] ?? 0); }
	}

	$URL_BASE = $URL;
	$columns = [
		['th' => 'ID',         'td_html' => function($r) { return '<td>' . (int)$r['id'] . '</td>'; }],
		['th' => 'Aliado',     'td_html' => function($r) { return '<td>' . htmlspecialchars((string)$r['titulo'], ENT_QUOTES, 'UTF-8') . '</td>'; }],
		['th' => 'Categoría',  'td_html' => function($r) {
			$cat = (string)($r['categoria_nombre'] ?? '');
			if ($cat === '') {
				return '<td><span style="color:#bbb;">Sin categoría</span></td>';
			}
			return '<td>' . htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') . '</td>';
		}],
		['th' => 'Descuento',  'td_html' => function($r) { return '<td>' . htmlspecialchars((string)($r['descuento'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>'; }],
		['th' => 'Imagen',     'td_html' => function($r) use ($URL_BASE) {
			return samap_thumb_td($URL_BASE, 'aliados', (string)($r['imagen'] ?? ''));
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
	<title>ALIADOS -  Administrador</title>

	</script><link rel="stylesheet" href="<?php echo $URL;?>admin/app/css/bootstrap.css">

	<link rel="stylesheet" href="<?php echo $URL;?>admin/plugins/fontawesome/css/font-awesome.min.css">
	<link rel="stylesheet" href="<?php echo $URL;?>admin/plugins/animo/animate+animo.css">
	<link rel="stylesheet" href="<?php echo $URL;?>admin/plugins/csspinner/csspinner.min.css">

	<link rel="stylesheet" href="<?php echo $URL;?>admin/app/css/app.css?v=202606291705">

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
				<?php include 'partials/papelera-toggle.php'; ?>
				<?php $papelera_activa = $papelera; if (isset($convenios)) { $rows = $convenios; include 'partials/tabla-searchable.php'; } ?>
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
	<script src="<?php echo $URL;?>admin/plugins/datatable/media/js/jquery.dataTables.min.js"></script>
	<script src="<?php echo $URL;?>admin/plugins/datatable/extensions/datatable-bootstrap/js/dataTables.bootstrap.js"></script>
	<script src="<?php echo $URL;?>admin/plugins/datatable/extensions/datatable-bootstrap/js/dataTables.bootstrapPagination.js"></script>
	<script src="<?php echo $URL;?>admin/plugins/datatable/extensions/ColVis/js/dataTables.colVis.min.js"></script>

	<script src="<?php echo $URL;?>admin/app/js/app.js?v=202606301500"></script>

</body>
</html>
