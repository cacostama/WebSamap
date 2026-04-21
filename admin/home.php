<?php
require_once('funciones/db.php');

if (isset($_SESSION['ADM_Username'])){
	
} else{

	echo"<script>window.location.href=\"".$URL."admin/index/\"</script>";

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
	<title>HOME -  Administrador</title>

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

				<h3>Escritorio</h3>
				<!--<div data-toggle="notify" data-onload data-message="&lt;b&gt;New Updates Available!&lt;/b&gt; Don't forget to check them!" data-options="{&quot;status&quot;:&quot;danger&quot;, &quot;pos&quot;:&quot;top-right&quot;}" class="hidden-xs"></div>-->
				<div class="row">

					<div class="col-md-12">

						<div class="row">
						</div>
						<div class="row">

							<div class="col-md-4">
								<a href="<?php echo $URL?>admin/slider/">
								<div data-toggle="play-animation" data-play="fadeInLeft" data-offset="0" data-delay="1400" class="panel widget bg-success" style="background: #27AE60;">
									<div class="panel-body">
										<div class="text-right text">
											<em class="fa fa-tasks fa-2x"></em>
										</div>
										<h3 class="mt0">Sliders</h3>
										<p class="text">Administre los Sliders que se verán en el sito</p>
										<div class="progress progress-striped progress-xs">
											
										</div>
									</div>
								</div>
								</a>
							</div>

							<div class="col-md-4">

								<a href="<?php echo $URL?>admin/planes/">
								<div data-toggle="play-animation" data-play="fadeInLeft" data-offset="0" data-delay="1400" class="panel widget bg-success" style="background: #27AE60;">
									<div class="panel-body">
										<div class="text-right text">
											<em class="fa fa-users fa-2x"></em>
										</div>
										<h3 class="mt0">Planes</h3>
										<p class="text">Administre los Planes del sitio</p>
										<div class="progress progress-striped progress-xs">
											
										</div>
									</div>
								</div>
								</a>

							</div>
							<div class="col-md-4">
								<a href="<?php echo $URL?>admin/convenios/">
								<div data-toggle="play-animation" data-play="fadeInLeft" data-offset="0" data-delay="1400" class="panel widget bg-success" style="background: #27AE60;">
									<div class="panel-body">
										<div class="text-right text">
											<em class="fa fa-file-text fa-2x"></em>
										</div>
										<h3 class="mt0">Convenios</h3>
										<p class="text">Administre los Convenios</p>
										<div class="progress progress-striped progress-xs">
											
										</div>
									</div>
								</div>

							</div>
							<div class="col-md-4">
								<a href="<?php echo $URL?>admin/servicios/">
								<div data-toggle="play-animation" data-play="fadeInLeft" data-offset="0" data-delay="1400" class="panel widget bg-success" style="background: #27AE60;">
									<div class="panel-body">
										<div class="text-right text">
											<em class="fa fa-star fa-2x"></em>
										</div>
										<h3 class="mt0">Servicios</h3>
										<p class="text">Administre los Servicios que se verán en el sito</p>
										<div class="progress progress-striped progress-xs">
											
										</div>
									</div>
								</div>
								</a>
							</div>
							<div class="col-md-4">
								<a href="<?php echo $URL?>admin/medicos/">
								<div data-toggle="play-animation" data-play="fadeInLeft" data-offset="0" data-delay="1400" class="panel widget bg-success" style="background: #27AE60;">
									<div class="panel-body">
										<div class="text-right text">
											<em class="fa fa-tasks fa-2x"></em>
										</div>
										<h3 class="mt0">Médicos</h3>
										<p class="text">Administre los Médicos que se verán en el sito</p>
										<div class="progress progress-striped progress-xs">
											
										</div>
									</div>
								</div>
								</a>
							</div>

							<div class="col-md-4">
								<a href="<?php echo $URL?>admin/guia/">
								<div data-toggle="play-animation" data-play="fadeInLeft" data-offset="0" data-delay="1400" class="panel widget bg-success" style="background: #27AE60;">
									<div class="panel-body">
										<div class="text-right text">
											<em class="fa fa-tasks fa-2x"></em>
										</div>
										<h3 class="mt0">Guía Médica</h3>
										<p class="text">Administre la Guía Médica</p>
										<div class="progress progress-striped progress-xs">
											
										</div>
									</div>
								</div>
								</a>
							</div>

							<div class="col-md-4">
								<a href="<?php echo $URL?>admin/blogs/">
								<div data-toggle="play-animation" data-play="fadeInLeft" data-offset="0" data-delay="1400" class="panel widget bg-success" style="background: #27AE60;">
									<div class="panel-body">
										<div class="text-right text">
											<em class="fa fa-tasks fa-2x"></em>
										</div>
										<h3 class="mt0">Blogs</h3>
										<p class="text">Administre los Blogs que se verán en el sito</p>
										<div class="progress progress-striped progress-xs">
											
										</div>
									</div>
								</div>
								</a>
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
	<!--[if lt IE 8]><script src="js/excanvas.min.js"></script><![endif]-->


	<script src="<?php echo $URL;?>admin/app/js/app.js"></script>

</body>
</html>