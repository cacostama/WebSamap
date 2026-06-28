<?php
require_once('funciones/db.php');

if (isset($_SESSION['ADM_Username'])){


	mysqli_select_db($connect, $database);
	$query_sanatorios= "SELECT a.id, a.nombre,b.nombre AS ciudad, a.direccion, a. estado FROM tbl_sanatorio a LEFT JOIN tbl_ciudad b ON a.idCiudad=b.id WHERE a.deleted_at IS NULL";
	$sanatorios = mysqli_query($connect, $query_sanatorios) or die(mysqli_error($link));

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
	    echo"<script>alert('No se pudo eliminar el sanatorio. Volvé a intentarlo.'); window.location.href=\"".$URL."admin/sanatorios/\"</script>";
	    exit;
	  }

	  $deleteSQL = sprintf("UPDATE tbl_sanatorio SET deleted_at=NOW() WHERE id=%s",
	                       GetSQLValueString($_GET['id'], "int"));

	  mysqli_select_db($connect, $database);
	  $Result1 = mysqli_query($connect, $deleteSQL) or die(mysqli_error());

	  echo"<script>alert('Listo, el sanatorio se eliminó. Ya no se muestra en el sitio web.'); window.location.href=\"".$URL."admin/sanatorios/\"</script>";
	}

	// ---- Inputs para partials/tabla-searchable.php ----
	$tabla_titulo        = 'Sanatorios';
	$btn_agregar_label   = 'Agregar Sanatorio';
	$btn_agregar_url     = 'admin/agregar-sanatorio.php';
	$edit_url_pattern    = 'admin/editarsanatorio/cod/{id}/';
	$delete_url_pattern  = 'admin/sanatorios.php?id={id}&borrar=si&csrf_token={csrf}';
	$delete_confirm      = '¿Querés eliminar este sanatorio? Dejará de mostrarse en el sitio web.';
	$empty_message       = 'Todavía no hay sanatorios cargados.';

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
				<?php if (isset($sanatorios)) { $rows = $sanatorios; include 'partials/tabla-searchable.php'; } ?>
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

	<script src="<?php echo $URL;?>admin/app/js/app.js"></script>

</body>
</html>
