<?php
require_once('funciones/db.php');

if (isset($_SESSION['ADM_Username'])){

	mysqli_select_db($connect, $database);

	// La guia tiene 300+ filas. NO hacemos SELECT * aca -- el listado se
	// pide via AJAX al endpoint admin/api/datatable.php?tabla=guia, que
	// pagina del lado del servidor. HTML inicial: ~3 KB vs 446 KB que
	// antes viajaban con TODAS las filas embebidas.
	$ajax_url = $URL . 'admin/api/datatable.php?tabla=guia';


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
	    header('Location: ' . $URL . 'admin/guia/');
	    exit;
	  }

	  $deleteSQL = sprintf("UPDATE tbl_guiamedica SET deleted_at=NOW() WHERE id=%s",
	                       GetSQLValueString($_GET['id'], "int"));

	  mysqli_select_db($connect, $database);
	  $Result1 = mysqli_query($connect, $deleteSQL) or die(mysqli_error($connect));

	  samap_flash_set('success', 'Listo, el médico se eliminó. Ya no se muestra en el sitio web.');
	  header('Location: ' . $URL . 'admin/guia/');
	  exit;
	}

	// ---- Inputs para partials/tabla-searchable.php ----
	$tabla_titulo        = 'Guía Médica';
	$btn_agregar_label   = 'Agregar Médico';
	$btn_agregar_url     = 'admin/agregar-guia.php';
	$edit_url_pattern    = 'admin/editarguia/cod/{id}/';
	$delete_url_pattern  = 'admin/guia.php?id={id}&borrar=si&csrf_token={csrf}';
	$delete_confirm      = '¿Querés eliminar este registro de la guía? Dejará de mostrarse en el sitio web.';
	$empty_message       = 'Todavía no hay registros cargados.';

	// Las columnas son simbolicas: el partial las usa para renderizar los <th>.
	// El contenido real de las celdas lo trae el endpoint AJAX como array,
	// asi que td_html puede ser un no-op (DataTables ignora el tbody vacio).
	$columns = [
		['th' => 'ID',           'td_html' => function($r) { return '<td></td>'; }],
		['th' => 'Médico',       'td_html' => function($r) { return '<td></td>'; }],
		['th' => 'Nombre',       'td_html' => function($r) { return '<td></td>'; }],
		['th' => 'Especialidad', 'td_html' => function($r) { return '<td></td>'; }],
		['th' => 'Sanatorio',    'td_html' => function($r) { return '<td></td>'; }],
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
	<title>SPEAKERS -  Administrador</title>

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
				<?php $rows = null; include 'partials/tabla-searchable.php'; ?>
			</section>

		</section>

	</section>
	<?php include 'partials/scripts-comunes.php'; ?>

</body>
</html>
