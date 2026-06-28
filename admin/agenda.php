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
	$agenda = mysqli_query($connect, $query_agenda) or die(mysqli_error($link));
	$row_agenda = mysqli_fetch_assoc($agenda);
	$totalRows_agenda = mysqli_num_rows($agenda);
    
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
	  $deleteSQL = sprintf("DELETE FROM tbl_agenda_detalle WHERE id=%s",
	                       GetSQLValueString($_GET['id'], "int"));

	  mysqli_select_db($connect, $database);
	  $Result1 = mysqli_query($connect, $deleteSQL) or die(mysqli_error());

	  echo"<script>alert('AGENDA ELIMINADA CORRECTAMENTE!'); window.location.href=\"".$URL."admin/agenda/\"</script>";
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

				<h3>Agenda</h3>
				<!--<div data-toggle="notify" data-onload data-message="&lt;b&gt;New Updates Available!&lt;/b&gt; Don't forget to check them!" data-options="{&quot;status&quot;:&quot;danger&quot;, &quot;pos&quot;:&quot;top-right&quot;}" class="hidden-xs"></div>-->
				<div class="row">
																<div class="panel panel-default">
																	<div class="panel-heading"><a href="<?php echo $URL?>admin/agregar-agenda.php" class="btn btn-primary" >Agregar Agenda</a></div>
																	<div class="panel-body">
																		<table id="datatable1" class="table table-striped table-hover">
																			<thead>
																				<tr>
																					<th>ID</th>
																					<th>Titulo</th>
																					<th>Fecha</th>
																					<th>Horario</th>
																					<th>Lugar</th>
																					<th>Speaker/s</th>
																					<th colspan="2" class="sort-alpha">Acciones</th>
																				</tr>
																			</thead>
																			<tbody>
                                                                             <?php do { // horizontal looper

                                                                             	

                                                                             ?>

																				<tr class="gradeX">
																					<td><?php echo $row_agenda['id'];?></td>
																					<td><?php echo $row_agenda['titulo'];?></td>
																					<td><?php echo $row_agenda['fecha'];?></td>
																					<td><?php echo $row_agenda['horario'];?></td>
																					<td><?php echo $row_agenda['lugar'];?></td>
																					<td>
																						<?php echo $row_agenda['speaker1']; ?>
																						<?php echo $row_agenda['speaker2'];?>
																						<?php echo $row_agenda['speaker3'];?>
																						<?php echo $row_agenda['speaker4'];?>
																						<?php echo $row_agenda['speaker5'];?>
																						<?php echo $row_agenda['speaker6'];?>
																						<?php echo $row_agenda['speaker7'];?>
																						<?php echo $row_agenda['speaker8'];?>
																						<?php echo $row_agenda['speaker9'];?>
																						<?php echo $row_agenda['speaker10'];?>
																					</td>
																					
																					<td width="20px"><div align="center"><a href="<?php echo $URL?>admin/editaragenda/cod/<?php echo $row_agenda['id']; ?>/"><img width="20px" src="<?php echo $URL?>admin/app/img/editar.png"alt=""/></a></div></td>
																					<td width="20px"><div align="center"><a href="<?php echo $URL?>admin/agenda.php?id=<?php echo $row_agenda['id']; ?>&borrar=si"><img width="20px" src="<?php echo $URL?>admin/app/img/borrar.png"alt=""/></a></div></td>
																					
																				</tr>
                                                                              <?php
									                                                $row_agenda = mysqli_fetch_assoc($agenda);
									                                                } while ($row_agenda);   //end horizontal looper 
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

	<script src="<?php echo $URL;?>admin/plugins/flot/jquery.flot.min.js"></script>
	<script src="<?php echo $URL;?>admin/plugins/flot/jquery.flot.tooltip.min.js"></script>
	<script src="<?php echo $URL;?>admin/plugins/flot/jquery.flot.resize.min.js"></script>
	<script src="<?php echo $URL;?>admin/plugins/flot/jquery.flot.pie.min.js"></script>
	<script src="<?php echo $URL;?>admin/plugins/flot/jquery.flot.time.min.js"></script>
	<script src="<?php echo $URL;?>admin/plugins/flot/jquery.flot.categories.min.js"></script>

	<!--[if lt IE 8]><script src="js/excanvas.min.js"></script><![endif]
	<script src="<?php echo $URL;?>admin/plugins/datatable/media/js/jquery.dataTables.min.js"></script>
	<script src="<?php echo $URL;?>admin/plugins/datatable/extensions/datatable-bootstrap/js/dataTables.bootstrap.js"></script>
	<script src="<?php echo $URL;?>admin/plugins/datatable/extensions/datatable-bootstrap/js/dataTables.bootstrapPagination.js"></script>
	<script src="<?php echo $URL;?>admin/plugins/datatable/extensions/ColVis/js/dataTables.colVis.min.js"></script>

	<script src="<?php echo $URL;?>admin/app/js/app.js"></script>-->

	<script src="<?php echo $URL;?>admin/app/js/app.js"></script>
</body>
</html>