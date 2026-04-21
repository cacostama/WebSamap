<?php require_once('funciones/db.php');?>
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<?php 
    
    $ID = $_GET['cod'];
    settype($ID, 'integer');

	mysqli_select_db($connect, $database);
    $query_plan = "SELECT * FROM tbl_planes WHERE id= $ID";
    $plan = mysqli_query($connect, $query_plan) or die(mysqli_error($connect));
    $row_plan = mysqli_fetch_assoc($plan);
    $totalRows_plan = mysqli_num_rows($plan);
/**/
    mysqli_select_db($connect, $database);
    $query_planes = 'SELECT * FROM tbl_planes ORDER BY id DESC';
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
    <title>SAMAP - <?php echo $row_blog['titulo']; ?></title>
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

    <!-- Blog Banner Section Start -->
    <section class="banner">
        <div class="container ">
            <div class="row ">
                <div class="col-lg-12">
                    <div class="banner__content">
                        <h1 class="banner__title wow fadeInLeft" data-wow-duration="1.2s"><?php echo $row_plan['titulo']; ?></h1> 
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb wow fadeInRight" data-wow-duration="1.2s">
                                <li class="breadcrumb-item"><a href="<?php echo $URL?>">Home</a></li>
                                <li class="breadcrumb-item"><a href="<?php echo $URL?>planes/">Planes</a></li>
                                <li class="breadcrumb-item active" aria-current="page"><?php echo $row_plan['titulo']; ?></li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Blog Banner Section End -->
    
    <!-- Blog Start -->
    <section class="section blog-details wow fadeInUp" data-wow-duration="0.4s">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="blog-details__wrapper">
                        <div class="blog-details__inner">
                            <div class="blog-details__thumb wow fadeInUp" data-wow-duration="1.2">
    							<div style="text-align: center;">
                                <img width="100%" src="<?php echo $URL?>documentos/<?php echo $row_plan['imagen']; ?>" alt="Image">
    </div>
                            </div>
                            <div class="blog-details__content">
                                
                                <h2 class="mb_30 wow fadeInUp" data-wow-duration="1.2s"><?php echo $row_plan['titulo']; ?></h2>
                                <p class="blog-details__content-text">
                                    <?php echo $row_plan['detalle']; ?>
                                </p>
                            </div>
                        </div>
                        
                    </div>
                </div>

                <div class="col-md-10 col-lg-4">
                    <div class="sidebar mt-5 mt-lg-0 wow fadeInRight" data-wow-duration="1.2s">
                        
                        
                        <!----><div class="sidebar__latest-post mb_40">
                            <h4 class="mb_30">Otros Planes</h4>

                            <?php do { ?>
                            <div class="sidebar__post-single">
                                <div class="latest-post__thumb">
                                    <a href="<?php echo $URL;?>plan-detalle/titulo/<?php echo str_replace($especiales, $correctos,$row_planes['titulo']); ?>/cod/<?php echo $row_planes['id']; ?>/" title="Read More">
                                        <img src="<?php echo $URL?>documentos/<?php echo $row_planes['imagen']; ?>" alt="Blog">
                                    </a>
                                </div>
                                <div class="latest-post__content">
                                    <a href="<?php echo $URL;?>plan-detalle/titulo/<?php echo str_replace($especiales, $correctos,$row_planes['titulo']); ?>/cod/<?php echo $row_planes['id']; ?>/"><?php echo $row_planes['titulo']; ?></a>
                                   
                                </div>
                            </div>
                            <?php 
                                $row_planes = mysqli_fetch_assoc($planes);
                                } while ($row_planes);   //end horizontal looper 
                            ?>
                            
                            
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Blog End -->

    <!-- Others Blog Start-->
   
    <!-- Others Blog end-->

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