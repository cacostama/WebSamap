<?php
require_once('funciones/db.php');

if (isset($_SESSION['ADM_Username'])){
	
	
	mysqli_select_db($connect, $database);
	$query_planes= "SELECT * FROM tbl_planes WHERE deleted_at IS NULL";
	$planes = mysqli_query($connect, $query_planes) or die(mysqli_error($link));
	$row_planes = mysqli_fetch_assoc($planes);
	$totalRows_planes = mysqli_num_rows($planes);
    
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


    if ((isset($_GET['borrar'])) && ($_GET['id'] != "") && samap_puede_escribir()) {
	  $deleteSQL = sprintf("UPDATE tbl_planes SET deleted_at=NOW() WHERE id=%s",
	                       GetSQLValueString($_GET['id'], "int"));

	  mysqli_select_db($connect, $database);
	  $Result1 = mysqli_query($connect, $deleteSQL) or die(mysqli_error());

	  echo"<script>alert('PLAN ELIMINADO CORRECTAMENTE!'); window.location.href=\"".$URL."admin/planes/\"</script>";
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
	<title>PLANES -  Administrador</title>

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

				<h3>Planes</h3>
				<!--<div data-toggle="notify" data-onload data-message="&lt;b&gt;New Updates Available!&lt;/b&gt; Don't forget to check them!" data-options="{&quot;status&quot;:&quot;danger&quot;, &quot;pos&quot;:&quot;top-right&quot;}" class="hidden-xs"></div>-->
				<div class="row">
						<div class="panel panel-default">
							<div class="panel-heading"><a href="<?php echo $URL?>admin/agregar-plan.php" class="btn btn-primary" >Agregar Plan</a></div>
							<div class="panel-body">
								<table id="datatable1" class="table table-striped table-hover">
									<thead>
										<tr>
											<th>ID</th>
											<th>Plan</th>
											<th>Imagen</th>
											<th>Tamaño</th>
											
											
											<th colspan="2" class="sort-alpha">Acciones</th>
										</tr>
									</thead>
									<tbody>
	                                 <?php do { ?>

										<tr class="gradeX">
											<td><?php echo $row_planes['id'];?></td>
											<td><?php echo $row_planes['titulo'];?></td>
											
											<td><img  height="30px" src="<?php echo $URL?>documentos/<?php echo $row_planes['imagen']; ?>" alt=""/></td>
											<td>850x500 px</td>
											<td width="20px"><div align="center"><a href="<?php echo $URL?>admin/editarplan/cod/<?php echo $row_planes['id']; ?>/"><img width="20px" src="<?php echo $URL?>admin/app/img/editar.png"alt=""/></a></div></td>

											<td width="20px"><div align="center"><a href="<?php echo $URL?>admin/planes.php?id=<?php echo $row_planes['id']; ?>&borrar=si" onclick="return confirm('¿Querés eliminar este registro? Dejará de mostrarse en el sitio web.');"><img width="20px" src="<?php echo $URL?>admin/app/img/borrar.png"alt=""/></a></div></td>
											
										</tr>
	                                  <?php
	                                        $row_planes = mysqli_fetch_assoc($planes);
	                                        } while ($row_planes);   //end horizontal looper 
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