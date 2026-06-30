<?php
require_once('funciones/db.php');

if (isset($_SESSION['ADM_Username'])){


	mysqli_select_db($connect, $database);
	$papelera = isset($_GET['papelera']) && $_GET['papelera'] === '1';
	// Sanatorios tiene ~210 filas. En la vista NORMAL pedimos los datos via AJAX
	// paginado (admin/api/datatable.php) para que el HTML inicial sea liviano.
	// En la papelera, mantenemos el listado completo (suele tener pocos registros).
	if (!$papelera) {
		$ajax_url = $URL . 'admin/api/datatable.php?tabla=sanatorios';
	} else {
		$query_sanatorios = "SELECT a.id, a.nombre, b.nombre AS ciudad, a.direccion, a.estado FROM tbl_sanatorio a LEFT JOIN tbl_ciudad b ON a.idCiudad = b.id WHERE a.deleted_at IS NOT NULL";
		$sanatorios = mysqli_query($connect, $query_sanatorios) or die(mysqli_error($connect));
	}

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

    if (isset($_GET['borrar']) && $_GET['id'] != "") {
	  if (!samap_puede_escribir() || !samap_csrf_validar()) {
	    samap_flash_set('error', 'No se pudo eliminar el sanatorio. Volvé a intentarlo.');
	    header('Location: ' . $URL . 'admin/sanatorios/');
	    exit;
	  }

	  $id_borrar = (int) $_GET['id'];
	  $audit_row_sanat = null;
	  $audQ = mysqli_query($connect, "SELECT id, nombre, direccion FROM tbl_sanatorio WHERE id = " . $id_borrar);
	  if ($audQ) { $audit_row_sanat = mysqli_fetch_assoc($audQ); }

	  $deleteSQL = sprintf("UPDATE tbl_sanatorio SET deleted_at=NOW() WHERE id=%s",
	                       GetSQLValueString($_GET['id'], "int"));

	  mysqli_select_db($connect, $database);
	  $Result1 = mysqli_query($connect, $deleteSQL) or die(mysqli_error($connect));

	  @samap_audit_log('delete', 'tbl_sanatorio', $id_borrar, "Borró (soft) el sanatorio #$id_borrar: " . substr((string)($audit_row_sanat['nombre'] ?? ''), 0, 100), $audit_row_sanat, null);

	  samap_flash_set('success', 'Listo, el sanatorio se eliminó. Ya no se muestra en el sitio web.');
	  header('Location: ' . $URL . 'admin/sanatorios/');
	  exit;
	}

	// ---- Papelera: restaurar un registro soft-deleted ----
	if (isset($_GET['restaurar']) && $_GET['restaurar'] === 'si' && ($_GET['id'] ?? '') != '' && samap_puede_escribir() && samap_csrf_validar()) {
		$id = (int)$_GET['id'];
		$restSQL = sprintf("UPDATE tbl_sanatorio SET deleted_at = NULL WHERE id = %d", $id);
		mysqli_select_db($connect, $database);
		@mysqli_query($connect, $restSQL);
		@samap_audit_log('restore', 'tbl_sanatorio', $id, "Restauró el sanatorio #$id");
		samap_flash_set('success', 'El sanatorio fue restaurado. Ya se muestra nuevamente en el sitio web.');
		header('Location: ' . $URL . 'admin/sanatorios/?papelera=1');
		exit;
	}

	// ---- Papelera: borrar definitivamente (DELETE fisico) ----
	if (isset($_GET['borrar_def']) && $_GET['borrar_def'] === 'si' && ($_GET['id'] ?? '') != '' && samap_puede_escribir() && samap_csrf_validar()) {
		$id = (int)$_GET['id'];
		$defSQL = sprintf("DELETE FROM tbl_sanatorio WHERE id = %d", $id);
		mysqli_select_db($connect, $database);
		@mysqli_query($connect, $defSQL);
		@samap_audit_log('hard_delete', 'tbl_sanatorio', $id, "Eliminó definitivamente el sanatorio #$id");
		samap_flash_set('warning', 'El sanatorio fue eliminado definitivamente. Ya no se puede recuperar.');
		header('Location: ' . $URL . 'admin/sanatorios/?papelera=1');
		exit;
	}

	// ---- Acciones masivas (Feature 12) ----
	if (isset($_GET['borrar_masivo']) && $_GET['borrar_masivo'] === 'si' && samap_puede_escribir() && samap_csrf_validar()) {
		$ids = isset($_GET['ids']) && is_array($_GET['ids']) ? $_GET['ids'] : [];
		$count = 0;
		foreach ($ids as $id_raw) {
			$id = (int)$id_raw;
			if ($id > 0) {
				$updSQL = sprintf('UPDATE tbl_sanatorio SET deleted_at = NOW() WHERE id = %d', $id);
				mysqli_select_db($connect, $database);
				if (@mysqli_query($connect, $updSQL)) { $count += (int)@mysqli_affected_rows($connect); }
			}
		}
		samap_audit_log('delete_bulk', 'tbl_sanatorio', 0, 'Borró masivamente ' . $count . ' registros');
		samap_flash_set('success', "$count registros eliminados.");
		header('Location: ' . $URL . 'admin/sanatorios/');
		exit;
	}

	// ---- Inputs para partials/tabla-searchable.php ----

	$tabla_titulo        = 'Sanatorios';
	$btn_agregar_label   = 'Agregar Sanatorio';
	$btn_agregar_url     = 'admin/agregar-sanatorio.php';
	$edit_url_pattern    = 'admin/editarsanatorio/cod/{id}/';
	$delete_url_pattern  = 'admin/sanatorios.php?id={id}&borrar=si&csrf_token={csrf}';
	$delete_confirm      = '¿Querés eliminar este sanatorio? Dejará de mostrarse en el sitio web.';
	$empty_message       = 'Todavía no hay sanatorios cargados.';
	$slug                = 'sanatorios';
	$papelera_activa     = $papelera;
	$trash_count         = 0;
	$enable_bulk         = !$papelera;
	if (!$papelera) {
		@mysqli_select_db($connect, $database);
		$rcRes = @mysqli_query($connect, "SELECT COUNT(*) AS c FROM tbl_sanatorio WHERE deleted_at IS NOT NULL");
		if ($rcRes) { $rcRow = mysqli_fetch_assoc($rcRes); $trash_count = (int)($rcRow['c'] ?? 0); }
	}

	$columns = [
		['th' => 'ID',        'td_html' => function($r) { return '<td>' . (int)$r['id'] . '</td>'; }],
		['th' => 'Nombre',    'td_html' => function($r) { return '<td>' . htmlspecialchars((string)$r['nombre'], ENT_QUOTES, 'UTF-8') . '</td>'; }],
		['th' => 'Ciudad',    'td_html' => function($r) { return '<td>' . htmlspecialchars((string)($r['ciudad'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>'; }],
		['th' => 'Dirección', 'td_html' => function($r) { return '<td>' . htmlspecialchars((string)($r['direccion'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>'; }],
		['th' => 'Estado',    'td_html' => function($r) {
			$activo = ((int)($r['estado'] ?? 0)) === 1;
			$cls = $activo ? 'btn-success' : 'btn-danger';
			$lbl = $activo ? 'ACTIVO' : 'IN ACTIVO';
			return '<td><div class="btn ' . $cls . '">' . $lbl . '</div></td>';
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
	<title>SANATORIOS -  Administrador</title>

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

				<h3><?php echo htmlspecialchars($tabla_titulo, ENT_QUOTES, 'UTF-8'); ?></h3>
				<?php include 'partials/papelera-toggle.php'; ?>
				<?php
					$papelera_activa = $papelera;
					// En modo serverSide (sin $sanatorios) le pasamos $rows=null y el
					// partial renderiza solo el esqueleto -- DataTables trae las filas
					// por AJAX al endpoint en $ajax_url.
					$rows = $sanatorios ?? null;
					include 'partials/tabla-searchable.php';
				?>
			</section>

		</section>

	</section>
	<?php include 'partials/scripts-comunes.php'; ?>

</body>
</html>
