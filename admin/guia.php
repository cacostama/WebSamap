<?php
require_once('funciones/db.php');

if (isset($_SESSION['ADM_Username'])){


	mysqli_select_db($connect, $database);
	$query_guia = "SELECT
					    a.id,
					    a.idEspecialidad,
					    a.idSanatorios,
					    a.titulo,
					    a.nombre AS medico,
					    a.cv,
					    b.nombre AS especialidad,
					    c.nombre AS sanatorio,
					    c.direccion,
					    c.telefono,
					    d.nombre AS ciudad
					FROM
					    tbl_guiamedica a
					LEFT JOIN tbl_especialidad b ON
					    a.idEspecialidad = b.id
					LEFT JOIN tbl_sanatorio c ON
					    a.idSanatorios = c.id
					LEFT JOIN tbl_ciudad d ON
					    c.idCiudad = d.id
					WHERE a.deleted_at IS NULL";
	$guia = mysqli_query($connect, $query_guia) or die(mysqli_error($connect));


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

	$columns = [
		['th' => 'ID',            'td_html' => function($r) { return '<td>' . (int)$r['id'] . '</td>'; }],
		['th' => 'Médico',        'td_html' => function($r) {
			$t = htmlspecialchars((string)($r['titulo'] ?? ''), ENT_QUOTES, 'UTF-8');
			$n = htmlspecialchars((string)($r['medico'] ?? ''), ENT_QUOTES, 'UTF-8');
			return '<td>' . $t . ' ' . $n . '</td>';
		}],
		['th' => 'Especialidad',  'td_html' => function($r) { return '<td>' . htmlspecialchars((string)($r['especialidad'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>'; }],
		['th' => 'Sanatorio',     'td_html' => function($r) { return '<td>' . htmlspecialchars((string)($r['sanatorio'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>'; }],
		['th' => 'Ciudad',        'td_html' => function($r) { return '<td>' . htmlspecialchars((string)($r['ciudad'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>'; }],
		['th' => 'Dirección',     'td_html' => function($r) { return '<td>' . htmlspecialchars((string)($r['direccion'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>'; }],
		['th' => 'Teléfono',      'td_html' => function($r) { return '<td>' . htmlspecialchars((string)($r['telefono'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>'; }],
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
				<?php if (isset($guia)) { $rows = $guia; include 'partials/tabla-searchable.php'; } ?>
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


	<!--[if lt IE 8]><script src="js/excanvas.min.js"></script><![endif]-->
	<script src="<?php echo $URL;?>admin/plugins/datatable/media/js/jquery.dataTables.min.js"></script>
	<script src="<?php echo $URL;?>admin/plugins/datatable/extensions/datatable-bootstrap/js/dataTables.bootstrap.js"></script>
	<script src="<?php echo $URL;?>admin/plugins/datatable/extensions/datatable-bootstrap/js/dataTables.bootstrapPagination.js"></script>
	<script src="<?php echo $URL;?>admin/plugins/datatable/extensions/ColVis/js/dataTables.colVis.min.js"></script>

	<script src="<?php echo $URL;?>admin/app/js/app.js?v=202606291718"></script>

</body>
</html>
