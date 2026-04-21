<?php require_once('funciones/db.php');?>

<?php 

    mysqli_select_db($connect, $database);
    $query_planes = 'SELECT * FROM tbl_planes WHERE especial=0 ORDER BY id ASC';
    $planes = mysqli_query($connect, $query_planes) or die(mysqli_error($link));
    $row_planes = mysqli_fetch_assoc($planes);
    $totalRows_planes = mysqli_num_rows($planes);

?> 
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- required meta -->
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- #favicon -->
    <link rel="shortcut icon" href="<?php echo $URL?>assets/images/favicon.png" type="image/x-icon">
    <!-- #title -->
    <title>SAMAP - Planes</title>
    <!-- #keywords -->
    <meta name="keywords" content="pharmaceutical, Medical">
    <!-- #description -->
    <meta name="description" content="Medical HTML5 Template">

    <!--  css dependencies start  -->

    <!-- bootstrap five css -->
    <link rel="stylesheet" href="<?php echo $URL?>assets/vendor/bootstrap/css/bootstrap.min.css">
    <!-- font awesome six css -->
    <link rel="stylesheet" href="<?php echo $URL?>assets/vendor/font-awesome/css/fontawesome.min.css">
    <!-- magnific popup css -->
    <link rel="stylesheet" href="<?php echo $URL?>assets/vendor/magnific-popup/css/magnific-popup.css">
    <!-- slick css -->
    <link rel="stylesheet" href="<?php echo $URL?>assets/vendor/slick/css/slick.css">
    <!-- odometer css -->
    <link rel="stylesheet" href="<?php echo $URL?>assets/vendor/odometer/css/odometer.css">
    <!-- animate css -->
    <link rel="stylesheet" href="<?php echo $URL?>assets/vendor/animate/animate.css">
    <!-- google icons -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0">
    <!--  / css dependencies end  -->
    
    <!-- main css -->
    <link rel="stylesheet" href="<?php echo $URL?>assets/css/style.css">

</head>

<body>
    
    <!--  Preloader  -->
    <div id="pre_loader"></div>

    <!--header-section start-->
    <?php include 'header.php'; ?>
    <!-- header-section end -->

    <!-- Banner Start -->
    <section class="banner">
        <div class="container ">
            <div class="row ">
                <div class="col-lg-12">
                    <div class="banner__content">
                        <h1 class="banner__title wow fadeInLeft" data-wow-duration="1.2s">Planes</h1> 
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb wow fadeInRight" data-wow-duration="1.2s">
                                <li class="breadcrumb-item"><a href="index.html">Inicio</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Planes</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Banner End -->

    <!-- Service start -->
    <section class="section service">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12">
                    <div class="section__header">
                        <h2 class="section__header-title wow fadeInUp" data-wow-duration="1.2s">UN PLAN para cada necesidad</h2>
                        <p class=" wow fadeInDown" data-wow-duration="1.5s">Descubre la tranquilidad de estar protegido con SAMAP. Nuestra amplia gama de planes esta diseñada para adaptarse a tus requerimientos individuales, familiares y corporativos, brindandote la cobertura ideal para cada etapa de tu vida.<br><br>
En SAMAP entendemos que cada persona es única, por eso ofrecemos diferentes opciones para que encuentres el plan que se ajuste a tus necesidades. Con nuestra amplia red de los mejores prestadores a nivel nacional, obtén la seguridad y el respaldo que mereces, porque en SAMAP estamos, CUIDÁNDOTE SIEMPRE.</p>
                    </div>
                </div>
            </div>
            <div class="row gy-3 gy-md-4">

                <?php do { ?>

                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="service__card wow fadeInUp" data-wow-duration="1.2s">
                           
                                <img src="<?php echo $URL?>documentos/<?php echo $row_planes['imagen']; ?>" alt="Icon">
                            
                            <h4 class="mb_30"><?php echo $row_planes['titulo']; ?></h4>
                            <p class="mb_40" style="font-size: 15px"><?php echo $row_planes['detalle']; ?></p>
                            <a target="_blank" href="https://wa.link/gza9hk" class="service__card-read-more">Consultar</a>
                        </div>
                    </div>

                <?php 
                    $row_planes = mysqli_fetch_assoc($planes);
                    } while ($row_planes);   //end horizontal looper 
                ?>
    
            </div>
            
        </div>
    </section>
    <!-- Service End -->
 
    <!-- newsletter Start -->
    <?php include 'newsletter.php'; ?>
    <!-- newsletter End -->

    <!-- Footer Area Start -->
    <?php include 'footer.php'; ?>
    <!-- Footer Area End -->
    
    <!-- scroll to top -->
    <a href="#" class="scrollToTop"><i class="fas fa-angle-double-up"></i></a>

    <!--  js dependencies start  -->

    <!-- jquery -->
    <script data-cfasync="false" src="/cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script><script src="<?php echo $URL?>assets/vendor/jquery/jquery-3.6.3.min.js"></script>
    <!-- bootstrap five js -->
    <script src="<?php echo $URL?>assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- magnific popup js -->
    <script src="<?php echo $URL?>assets/vendor/magnific-popup/js/jquery.magnific-popup.min.js"></script>
    <!-- circular-progress-bar -->
    <script src="https://cdn.jsdelivr.net/gh/tomik23/circular-progress-bar@latest/docs/circularProgressBar.min.js"></script>
    <!-- slick js -->
    <script src="<?php echo $URL?>assets/vendor/slick/js/slick.min.js"></script>
    <!-- odometer js -->
    <script src="<?php echo $URL?>assets/vendor/odometer/js/odometer.min.js"></script>
    <!-- viewport js -->
    <script src="<?php echo $URL?>assets/vendor/viewport/viewport.jquery.js"></script>
    <!-- jquery ui js -->
    <script src="<?php echo $URL?>assets/vendor/jquery-ui/jquery-ui.min.js"></script>
    <!-- wow js -->
    <script src="<?php echo $URL?>assets/vendor/wow/wow.min.js"></script>
    
    <script src="<?php echo $URL?>assets/vendor/jquery-validate/jquery.validate.min.js"></script> 

    <!--  / js dependencies end  -->

    <!-- plugins js -->
    <script src="<?php echo $URL?>assets/js/plugins.js"></script>
    <!-- main js -->
    <script src="<?php echo $URL?>assets/js/main.js"></script>

</body>

</html>