<?php
require_once('funciones/db.php');

if (isset($_SESSION['ADM_Username'])){


	mysqli_select_db($connect, $database);
	$query_agenda = "SELECT
							a.id,
						    a.titulo,
						    c.nombre AS speaker1,
						    d.nombre AS speaker2,
						    e.nombre AS speaker3,
						    f.nombre AS speaker4,
						    g.nombre AS speaker5,
						    h.nombre AS speaker6,
						    i.nombre AS speaker7,
						    j.nombre AS speaker8,
						    k.nombre AS speaker9,
						    l.nombre AS speaker10,
						    a.horario,
						    a.lugar,
						    b.nombre AS fecha
						FROM
						    tbl_agenda_detalle a
						LEFT JOIN tbl_agenda b ON
						    a.idAgenda = b.id
						LEFT JOIN tbl_speaker c ON
						    a.idSpeaker1 = c.id
						LEFT JOIN tbl_speaker d ON
						    a.idSpeaker2 = d.id
						LEFT JOIN tbl_speaker e ON
						    a.idSpeaker3 = e.id
						LEFT JOIN tbl_speaker f ON
						    a.idSpeaker4 = d.id
						LEFT JOIN tbl_speaker g ON
						    a.idSpeaker5 = g.id
						LEFT JOIN tbl_speaker h ON
						    a.idSpeaker6 = h.id
						LEFT JOIN tbl_speaker i ON
						    a.idSpeaker7 = i.id
						LEFT JOIN tbl_speaker j ON
						    a.idSpeaker8 = j.id
						LEFT JOIN tbl_speaker k ON
						    a.idSpeaker9 = k.id
						LEFT JOIN tbl_speaker l ON
						    a.idSpeaker10 = l.id";
	$agenda = mysqli_query($connect, $query_agenda) or die(mysqli_error($connect));

    function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "")
	{
	  $theValue = addslashes($theValue);

	  switch ($theType) {
	    case "text":
	      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
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
	    samap_flash_set('error', 'No se pudo eliminar la agenda. Volvé a intentarlo.');
	    header('Location: ' . $URL . 'admin/agenda/');
	    exit;
	  }

	  $deleteSQL = sprintf("DELETE FROM tbl_agenda_detalle WHERE id=%s",
	                       GetSQLValueString($_GET['id'], "int"));

	  mysqli_select_db($connect, $database);
	  $Result1 = mysqli_query($connect, $deleteSQL) or die(mysqli_error($connect));

	  samap_flash_set('success', 'AGENDA ELIMINADA CORRECTAMENTE!');
	  header('Location: ' . $URL . 'admin/agenda/');
	  exit;
	}

	// ---- Inputs para partials/tabla-searchable.php ----
	$tabla_titulo        = 'Agenda';
	$btn_agregar_label   = 'Agregar Agenda';
	$btn_agregar_url     = 'admin/agregar-agenda.php';
	$edit_url_pattern    = 'admin/editaragenda/cod/{id}/';
	$delete_url_pattern  = 'admin/agenda.php?id={id}&borrar=si&csrf_token={csrf}';
	$delete_confirm      = '¿Querés eliminar este registro de agenda? No se puede deshacer.';
	$empty_message       = 'Todavía no hay registros de agenda cargados.';

	$columns = [
		['th' => 'ID',      'td_html' => function($r) { return '<td>' . (int)$r['id'] . '</td>'; }],
		['th' => 'Título',  'td_html' => function($r) { return '<td>' . htmlspecialchars((string)$r['titulo'], ENT_QUOTES, 'UTF-8') . '</td>'; }],
		['th' => 'Fecha',   'td_html' => function($r) { return '<td>' . htmlspecialchars((string)($r['fecha'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>'; }],
		['th' => 'Horario', 'td_html' => function($r) { return '<td>' . htmlspecialchars((string)($r['horario'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>'; }],
		['th' => 'Lugar',   'td_html' => function($r) { return '<td>' . htmlspecialchars((string)($r['lugar'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>'; }],
		['th' => 'Speakers','td_html' => function($r) {
			$parts = [];
			for ($i = 1; $i <= 10; $i++) {
				$key = 'speaker' . $i;
				$val = (string)($r[$key] ?? '');
				if ($val !== '') {
					$parts[] = htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
				}
			}
			return '<td>' . implode('<br>', $parts) . '</td>';
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
				<?php if (isset($agenda)) { $rows = $agenda; include 'partials/tabla-searchable.php'; } ?>
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
