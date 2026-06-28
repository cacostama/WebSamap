<?php
require_once('funciones/db.php');

if (isset($_SESSION['ADM_Username'])){

	mysqli_select_db($connect, $database);
	$query_categorias = "SELECT c.*, (SELECT COUNT(*) FROM tbl_aliados a WHERE a.categoria_id = c.id AND a.deleted_at IS NULL) AS aliados
	                     FROM tbl_categorias_aliado c
	                     WHERE c.deleted_at IS NULL
	                     ORDER BY c.orden ASC, c.nombre ASC";
	$categorias = mysqli_query($connect, $query_categorias) or die(mysqli_error($connect));
	$row_categorias = mysqli_fetch_assoc($categorias);
	$totalRows_categorias = mysqli_num_rows($categorias);

	if ((isset($_GET['borrar'])) && ($_GET['id'] != "")) {
	  if (!samap_puede_escribir() || !samap_csrf_validar()) {
	    echo"<script>alert('No se pudo eliminar la categoría. Volvé a intentarlo.'); window.location.href=\"".$URL."admin/categorias/\"</script>";
	    exit;
	  }
	  $id = (int) $_GET['id'];
	  $deleteSQL = "UPDATE tbl_categorias_aliado SET deleted_at=NOW() WHERE id=".$id;
	  mysqli_select_db($connect, $database);
	  $Result1 = mysqli_query($connect, $deleteSQL) or die(mysqli_error($connect));
	  echo"<script>alert('Listo, la categoría se eliminó. Ya no se muestra en el sitio web.'); window.location.href=\"".$URL."admin/categorias/\"</script>";
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
	<title>CATEGORÍAS DE ALIADOS -  Administrador</title>

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

				<h3>Categorías de Aliados</h3>
				<p style="color:#888;margin-top:-.5rem;">Definí las categorías que agrupan a los comercios adheridos en la sección "Descuentos Exclusivos" del sitio.</p>
				<div class="row">
						<div class="panel panel-default">
							<div class="panel-heading"><a href="<?php echo $URL?>admin/agregar-categoria.php" class="btn btn-primary" >Agregar Categoría</a></div>
							<div class="panel-body">
								<table id="datatable1" class="table table-striped table-hover">
									<thead>
										<tr>
											<th>Orden</th>
											<th>Nombre</th>
											<th>Ícono</th>
											<th>Color</th>
											<th>Comercios</th>
											<th>Estado</th>
											<th colspan="2" class="sort-alpha">Acciones</th>
										</tr>
									</thead>
									<tbody>
	                                 <?php if ($totalRows_categorias > 0) { do { ?>

										<tr class="gradeX">
											<td><?php echo (int) $row_categorias['orden'];?></td>
											<td><?php echo htmlspecialchars($row_categorias['nombre'], ENT_QUOTES, 'UTF-8');?></td>
											<td><i class="fa <?php echo htmlspecialchars($row_categorias['icono'], ENT_QUOTES, 'UTF-8');?>"></i> <small style="color:#999;"><?php echo htmlspecialchars($row_categorias['icono'], ENT_QUOTES, 'UTF-8');?></small></td>
											<td>
												<?php if (!empty($row_categorias['color'])) { ?>
													<span style="display:inline-block;width:16px;height:16px;border-radius:3px;vertical-align:middle;background:<?php echo htmlspecialchars($row_categorias['color'], ENT_QUOTES, 'UTF-8');?>"></span>
													<?php echo htmlspecialchars($row_categorias['color'], ENT_QUOTES, 'UTF-8');?>
												<?php } else { ?>
													<span style="color:#bbb;">Color por defecto</span>
												<?php } ?>
											</td>
											<td><?php echo (int) $row_categorias['aliados'];?></td>
											<td>
												<?php if ($row_categorias['activo']==1){?>
													<div class="btn btn-success btn-xs">ACTIVA</div>
												<?php } else {?>
													<div class="btn btn-danger btn-xs">OCULTA</div>
												<?php } ?>
											</td>

											<td width="20px"><div align="center"><a href="<?php echo $URL?>admin/editarcategoria/cod/<?php echo $row_categorias['id']; ?>/"><img width="20px" src="<?php echo $URL?>admin/app/img/editar.png"alt=""/></a></div></td>

											<td width="20px"><div align="center"><a href="<?php echo $URL?>admin/categorias.php?id=<?php echo $row_categorias['id']; ?>&borrar=si&csrf_token=<?php echo urlencode(samap_csrf_valor()); ?>" onclick="return confirm('¿Querés eliminar esta categoría? Los comercios que la usaban quedarán sin categoría.');"><img width="20px" src="<?php echo $URL?>admin/app/img/borrar.png"alt=""/></a></div></td>

										</tr>
	                                  <?php
	                                        $row_categorias = mysqli_fetch_assoc($categorias);
	                                        } while ($row_categorias); } else { ?><tr><td colspan="8" style="text-align:center;color:#888;padding:18px;">Todavía no hay categorías cargadas.</td></tr><?php }
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
	<script src="<?php echo $URL;?>admin/plugins/slimscroll/jquery.slimscroll.min.js"></script>
	<script src="<?php echo $URL;?>admin/app/js/app.js"></script>

</body>
</html>
