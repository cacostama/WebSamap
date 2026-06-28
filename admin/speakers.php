<?php
require_once('funciones/db.php');

if (isset($_SESSION['ADM_Username'])){
	
	
	mysqli_select_db($connect, $database);
	$query_speakers = "SELECT a.id, a.nombre, a.titulo, a.intro, b.nacionalidad FROM tbl_speaker a LEFT JOIN tbl_nacionalidad b ON a.idNacionalidad= b.id";
	$speakers = mysqli_query($connect, $query_speakers) or die(mysqli_error($link));
	$row_speakers = mysqli_fetch_assoc($speakers);
	$totalRows_speakers = mysqli_num_rows($speakers);
    
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
	    echo"<script>alert('No se pudo eliminar el speaker. Volvé a intentarlo.'); window.location.href=\"".$URL."admin/speakers/\"</script>";
	    exit;
	  }

	  $deleteSQL = sprintf("DELETE FROM tbl_speaker WHERE id=%s",
	                       GetSQLValueString($_GET['id'], "int"));

	  mysqli_select_db($connect, $database);
	  $Result1 = mysqli_query($connect, $deleteSQL) or die(mysqli_error());

	  echo"<script>alert('SPEAKER ELIMINADO CORRECTAMENTE!'); window.location.href=\"".$URL."admin/speakers/\"</script>";
	}

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

				<h3>Speakers</h3>
				<!--<div data-toggle="notify" data-onload data-message="&lt;b&gt;New Updates Available!&lt;/b&gt; Don't forget to check them!" data-options="{&quot;status&quot;:&quot;danger&quot;, &quot;pos&quot;:&quot;top-right&quot;}" class="hidden-xs"></div>-->
				<div class="row">
																<div class="panel panel-default">
																	<div class="panel-heading"><a href="<?php echo $URL?>admin/agregar-speaker.php" class="btn btn-primary" >Agregar Speaker</a></div>
																	<div class="panel-body">
																		<table id="datatable1" class="table table-striped table-hover">
																			<thead>
																				<tr>
																					<th>ID</th>
																					<th>Nombre</th>
																					<th>Titulo</th>
																					<th>Intro</th>
																					<th class="sort-numeric">Nacionalidad</th>
																					
																					<th colspan="2" class="sort-alpha">Acciones</th>
																				</tr>
																			</thead>
																			<tbody>
                                                                             <?php do { // horizontal looper

                                                                             	

                                                                             ?>

																				<tr class="gradeX">
																					<td><?php echo $row_speakers['id'];?></td>
																					<td><?php echo $row_speakers['nombre'];?></td>
																					<td><?php echo $row_speakers['titulo'];?></td>
																					<td><?php echo $row_speakers['intro'];?></td>
																					<td><?php echo $row_speakers['nacionalidad'];?></td>
																					
																					<td width="20px"><div align="center"><a href="<?php echo $URL?>admin/editarspeaker/cod/<?php echo $row_speakers['id']; ?>/"><img width="20px" src="<?php echo $URL?>admin/app/img/editar.png"alt=""/></a></div></td>
																					<td width="20px"><div align="center"><a href="<?php echo $URL?>admin/speakers.php?id=<?php echo $row_speakers['id']; ?>&borrar=si&csrf_token=<?php echo urlencode(samap_csrf_valor()); ?>" onclick="return confirm('¿Querés eliminar este speaker? No se puede deshacer.');"><img width="20px" src="<?php echo $URL?>admin/app/img/borrar.png"alt=""/></a></div></td>
																					
																				</tr>
                                                                              <?php
									                                                $row_speakers = mysqli_fetch_assoc($speakers);
									                                                } while ($row_speakers);   //end horizontal looper 
									                                            ?>  
																			</tbody>
																		</table>
																	</div>
																</div>
															</div>
														</div>
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

	<script src="<?php echo $URL;?>admin/app/js/app.js"></script>

</body>
</html>