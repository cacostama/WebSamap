<?php
require_once('funciones/db.php');

if (isset($_SESSION['ADM_Username'])){


	mysqli_select_db($connect, $database);
	$papelera = isset($_GET['papelera']) && $_GET['papelera'] === '1';
	$query_medicos= "SELECT * FROM tbl_medicos WHERE " . ($papelera ? "deleted_at IS NOT NULL" : "deleted_at IS NULL");
	$medicos = mysqli_query($connect, $query_medicos) or die(mysqli_error($connect));

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
	    samap_flash_set('error', 'No se pudo eliminar el médico. Volvé a intentarlo.');
	    header('Location: ' . $URL . 'admin/medicos/');
	    exit;
	  }

	  $id_borrar = (int) $_GET['id'];
	  $audit_row_medico = null;
	  $audQ = mysqli_query($connect, "SELECT id, titulo, nombre, especialidad, imagen FROM tbl_medicos WHERE id = " . $id_borrar);
	  if ($audQ) { $audit_row_medico = mysqli_fetch_assoc($audQ); }

	  $deleteSQL = sprintf("UPDATE tbl_medicos SET deleted_at=NOW() WHERE id=%s",
	                       GetSQLValueString($_GET['id'], "int"));

	  mysqli_select_db($connect, $database);
	  $Result1 = mysqli_query($connect, $deleteSQL) or die(mysqli_error($connect));

	  $aud_label = trim(((string)($audit_row_medico['titulo'] ?? '')) . ' ' . ((string)($audit_row_medico['nombre'] ?? '')));
	  @samap_audit_log('delete', 'tbl_medicos', $id_borrar, "Borró (soft) el médico #$id_borrar: " . substr($aud_label, 0, 100), $audit_row_medico, null);

	  samap_flash_set('success', 'Listo, el médico se eliminó. Ya no se muestra en el sitio web.');
	  header('Location: ' . $URL . 'admin/medicos/');
	  exit;
	}

	// ---- Papelera: restaurar un registro soft-deleted ----
	if (isset($_GET['restaurar']) && $_GET['restaurar'] === 'si' && ($_GET['id'] ?? '') != '' && samap_puede_escribir() && samap_csrf_validar()) {
		$id = (int)$_GET['id'];
		$restSQL = sprintf("UPDATE tbl_medicos SET deleted_at = NULL WHERE id = %d", $id);
		mysqli_select_db($connect, $database);
		@mysqli_query($connect, $restSQL);
		@samap_audit_log('restore', 'tbl_medicos', $id, "Restauró el médico #$id");
		samap_flash_set('success', 'El médico fue restaurado. Ya se muestra nuevamente en el sitio web.');
		header('Location: ' . $URL . 'admin/medicos/?papelera=1');
		exit;
	}

	// ---- Papelera: borrar definitivamente (DELETE fisico) ----
	if (isset($_GET['borrar_def']) && $_GET['borrar_def'] === 'si' && ($_GET['id'] ?? '') != '' && samap_puede_escribir() && samap_csrf_validar()) {
		$id = (int)$_GET['id'];
		$defSQL = sprintf("DELETE FROM tbl_medicos WHERE id = %d", $id);
		mysqli_select_db($connect, $database);
		@mysqli_query($connect, $defSQL);
		@samap_audit_log('hard_delete', 'tbl_medicos', $id, "Eliminó definitivamente el médico #$id");
		samap_flash_set('warning', 'El médico fue eliminado definitivamente. Ya no se puede recuperar.');
		header('Location: ' . $URL . 'admin/medicos/?papelera=1');
		exit;
	}

	// ---- Acciones masivas (Feature 12) ----
	if (isset($_GET['borrar_masivo']) && $_GET['borrar_masivo'] === 'si' && samap_puede_escribir() && samap_csrf_validar()) {
		$ids = isset($_GET['ids']) && is_array($_GET['ids']) ? $_GET['ids'] : [];
		$count = 0;
		foreach ($ids as $id_raw) {
			$id = (int)$id_raw;
			if ($id > 0) {
				$updSQL = sprintf('UPDATE tbl_medicos SET deleted_at = NOW() WHERE id = %d', $id);
				mysqli_select_db($connect, $database);
				if (@mysqli_query($connect, $updSQL)) { $count += (int)@mysqli_affected_rows($connect); }
			}
		}
		samap_audit_log('delete_bulk', 'tbl_medicos', 0, 'Borró masivamente ' . $count . ' registros');
		samap_flash_set('success', "$count registros eliminados.");
		header('Location: ' . $URL . 'admin/medicos/');
		exit;
	}

	// ---- Inputs para partials/tabla-searchable.php ----

	$tabla_titulo        = 'Médicos';
	$btn_agregar_label   = 'Agregar Médico';
	$btn_agregar_url     = 'admin/agregar-medico.php';
	$edit_url_pattern    = 'admin/editarmedico/cod/{id}/';
	$delete_url_pattern  = 'admin/medicos.php?id={id}&borrar=si&csrf_token={csrf}';
	$delete_confirm      = '¿Querés eliminar este médico? Dejará de mostrarse en el sitio web.';
	$empty_message       = 'Todavía no hay médicos cargados.';
	$slug                = 'medicos';
	$papelera_activa     = $papelera;
	$trash_count         = 0;
	$enable_bulk         = !$papelera;
	if (!$papelera) {
		@mysqli_select_db($connect, $database);
		$rcRes = @mysqli_query($connect, "SELECT COUNT(*) AS c FROM tbl_medicos WHERE deleted_at IS NOT NULL");
		if ($rcRes) { $rcRow = mysqli_fetch_assoc($rcRes); $trash_count = (int)($rcRow['c'] ?? 0); }
	}

	$URL_BASE = $URL;
	$columns = [
		['th' => 'ID',           'td_html' => function($r) { return '<td>' . (int)$r['id'] . '</td>'; }],
		['th' => 'Especialidad', 'td_html' => function($r) { return '<td>' . htmlspecialchars((string)($r['especialidad'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>'; }],
		['th' => 'Médico',       'td_html' => function($r) {
			$t = htmlspecialchars((string)($r['titulo'] ?? ''), ENT_QUOTES, 'UTF-8');
			$n = htmlspecialchars((string)($r['nombre'] ?? ''), ENT_QUOTES, 'UTF-8');
			return '<td>' . $t . ' ' . $n . '</td>';
		}],
		['th' => 'Imagen',       'td_html' => function($r) use ($URL_BASE) {
			$img = htmlspecialchars((string)($r['imagen'] ?? ''), ENT_QUOTES, 'UTF-8');
			return $img === '' ? '<td><span style="color:#bbb;">—</span></td>' : '<td><img height="30px" src="' . htmlspecialchars($URL_BASE, ENT_QUOTES, 'UTF-8') . 'documentos/medicos/' . $img . '" alt="" loading="lazy" decoding="async"></td>';
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
	<title>MEDICOS -  Administrador</title>

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
				<?php $papelera_activa = $papelera; if (isset($medicos)) { $rows = $medicos; include 'partials/tabla-searchable.php'; } ?>
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

	<script src="<?php echo $URL;?>admin/app/js/app.js?v=202606291718"></script>

</body>
</html>
