<?php
require_once('funciones/db.php');

if (isset($_SESSION['ADM_Username'])){


	mysqli_select_db($connect, $database);
	$query_speakers = "SELECT a.id, a.nombre, a.titulo, a.intro, b.nacionalidad FROM tbl_speaker a LEFT JOIN tbl_nacionalidad b ON a.idNacionalidad= b.id";
	$speakers = mysqli_query($connect, $query_speakers) or die(mysqli_error($connect));

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
	    samap_flash_set('error', 'No se pudo eliminar el speaker. Volvé a intentarlo.');
	    header('Location: ' . $URL . 'admin/speakers/');
	    exit;
	  }

	  $deleteSQL = sprintf("DELETE FROM tbl_speaker WHERE id=%s",
	                       GetSQLValueString($_GET['id'], "int"));

	  mysqli_select_db($connect, $database);
	  $Result1 = mysqli_query($connect, $deleteSQL) or die(mysqli_error($connect));

	  samap_flash_set('success', 'SPEAKER ELIMINADO CORRECTAMENTE!');
	  header('Location: ' . $URL . 'admin/speakers/');
	  exit;
	}

	// ---- Inputs para partials/tabla-searchable.php ----
	$tabla_titulo        = 'Speakers';
	$btn_agregar_label   = 'Agregar Speaker';
	$btn_agregar_url     = 'admin/agregar-speaker.php';
	$edit_url_pattern    = 'admin/editarspeaker/cod/{id}/';
	$delete_url_pattern  = 'admin/speakers.php?id={id}&borrar=si&csrf_token={csrf}';
	$delete_confirm      = '¿Querés eliminar este speaker? No se puede deshacer.';
	$empty_message       = 'Todavía no hay speakers cargados.';

	$columns = [
		['th' => 'ID',            'td_html' => function($r) { return '<td>' . (int)$r['id'] . '</td>'; }],
		['th' => 'Nombre',        'td_html' => function($r) { return '<td>' . htmlspecialchars((string)$r['nombre'], ENT_QUOTES, 'UTF-8') . '</td>'; }],
		['th' => 'Título',        'td_html' => function($r) { return '<td>' . htmlspecialchars((string)$r['titulo'], ENT_QUOTES, 'UTF-8') . '</td>'; }],
		['th' => 'Intro',         'td_html' => function($r) { return '<td>' . htmlspecialchars((string)($r['intro'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>'; }],
		['th' => 'Nacionalidad',  'td_html' => function($r) { return '<td>' . htmlspecialchars((string)($r['nacionalidad'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>'; }],
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
				<?php if (isset($speakers)) { $rows = $speakers; include 'partials/tabla-searchable.php'; } ?>
			</section>

		</section>

	</section>
	<?php include 'partials/scripts-comunes.php'; ?>

</body>
</html>
