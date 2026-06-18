<?php
require_once('funciones/db.php');

if (isset($_SESSION['ADM_Username'])){
	
	
	mysqli_select_db($connect, $database);
	$query_servicios= "SELECT * FROM tbl_servicios WHERE deleted_at IS NULL";
	$servicios = mysqli_query($connect, $query_servicios) or die(mysqli_error($link));
	$row_servicios = mysqli_fetch_assoc($servicios);
	$totalRows_servicios = mysqli_num_rows($servicios);
    
    
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
	  $deleteSQL = sprintf("UPDATE tbl_servicios SET deleted_at=NOW() WHERE id=%s",
	                       GetSQLValueString($_GET['id'], "int"));

	  mysqli_select_db($connect, $database);
	  $Result1 = mysqli_query($connect, $deleteSQL) or die(mysqli_error($connect));

	  echo"<script>alert('Listo, el servicio se eliminó. Ya no se muestra en el sitio web.'); window.location.href=\"".$URL."admin/servicios/\"</script>";
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
	<title>SERVICIOS -  Administrador</title>

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

				<h3>Servicios</h3>
				<!--<div data-toggle="notify" data-onload data-message="&lt;b&gt;New Updates Available!&lt;/b&gt; Don't forget to check them!" data-options="{&quot;status&quot;:&quot;danger&quot;, &quot;pos&quot;:&quot;top-right&quot;}" class="hidden-xs"></div>-->
				<div class="row">
						<div class="panel panel-default">
							<div class="panel-heading"><a href="<?php echo $URL?>admin/agregar-servicio.php" class="btn btn-primary" >Agregar Servicio</a></div>
							<div class="panel-body">
								<table id="datatable1" class="table table-striped table-hover">
									<thead>
										<tr>
											<th>ID</th>
											
											<th>Convenio</th>
											<th>Imagen</th>
											
											
											
											<th colspan="2" class="sort-alpha">Acciones</th>
										</tr>
									</thead>
									<tbody>
	                                 <?php if ($totalRows_servicios > 0) { do { ?>

										<tr class="gradeX">
											<td><?php echo $row_servicios['id'];?></td>
											
											<td><?php echo $row_servicios['titulo'];?></td>
											
											<td><img  height="30px" src="<?php echo $URL?>documentos/servicios/<?php echo $row_servicios['imagen']; ?>" alt=""/></td>
											
											<td width="20px"><div align="center"><a href="<?php echo $URL?>admin/editarservicio/cod/<?php echo $row_servicios['id']; ?>/"><img width="20px" src="<?php echo $URL?>admin/app/img/editar.png"alt=""/></a></div></td>

											<td width="20px"><div align="center"><a href="<?php echo $URL?>admin/servicios.php?id=<?php echo $row_servicios['id']; ?>&borrar=si" onclick="return confirm('¿Querés eliminar este registro? Dejará de mostrarse en el sitio web.');"><img width="20px" src="<?php echo $URL?>admin/app/img/borrar.png"alt=""/></a></div></td>
											
										</tr>
	                                  <?php
	                                        $row_servicios = mysqli_fetch_assoc($servicios);
	                                        } while ($row_servicios); } else { ?><tr><td colspan="7" style="text-align:center;color:#888;padding:18px;">Todavía no hay registros cargados.</td></tr><?php }   //end horizontal looper 
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