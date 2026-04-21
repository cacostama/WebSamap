<?php require_once('funciones/db.php');?>

<?php 
    
    $ID = $_GET['cod'];
    settype($ID, 'integer');

    mysqli_select_db($connect, $database);
    $query_blog = "SELECT * FROM tbl_blog WHERE id= $ID";
    $blog = mysqli_query($connect, $query_blog) or die(mysqli_error($link));
    $row_blog = mysqli_fetch_assoc($blog);
    $totalRows_blog = mysqli_num_rows($blog);

    mysqli_select_db($connect, $database);
    $query_blogUltimos = 'SELECT * FROM tbl_blog ORDER BY id DESC LIMIT 7';
    $blogUltimos = mysqli_query($connect, $query_blogUltimos) or die(mysqli_error($link));
    $row_blogUltimos = mysqli_fetch_assoc($blogUltimos);
    $totalRows_blogUltimos = mysqli_num_rows($blogUltimos);

    mysqli_select_db($connect, $database);
    $query_blogOtros = 'SELECT * FROM tbl_blog ORDER BY id DESC LIMIT 7,3';
    $blogOtros = mysqli_query($connect, $query_blogOtros) or die(mysqli_error($link));
    $row_blogOtros = mysqli_fetch_assoc($blogOtros);
    $totalRows_Otros = mysqli_num_rows($blogOtros);

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
                        <h1 class="banner__title wow fadeInLeft" data-wow-duration="1.2s"><?php echo $row_blog['titulo']; ?></h1> 
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb wow fadeInRight" data-wow-duration="1.2s">
                                <li class="breadcrumb-item"><a href="<?php echo $URL?>">Home</a></li>
                                <li class="breadcrumb-item"><a href="<?php echo $URL?>blogs/">Blog</a></li>
                                <li class="breadcrumb-item active" aria-current="page"><?php echo $row_blog['titulo']; ?></li>
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
                                <img width="100%" src="<?php echo $URL?>documentos/blog/<?php echo $row_blog['imagen']; ?>" alt="Image">
                            </div>
                            <div class="blog-details__content">
                                <p class="blog-details__content-meta wow fadeInRight" data-wow-duration="1.2">
                                    <span class="material-symbols-outlined"> alarm </span>
                                    <span>
                                        <?php
                                            $fecha = new DateTime($row_blog['fecha']);
                                            setlocale(LC_TIME, 'es_ES');
                                            $formato = new IntlDateFormatter('es_ES', IntlDateFormatter::SHORT, IntlDateFormatter::NONE);
                                            $formato->setPattern('d MMM y');
                                            $fecha_formateada = $formato->format($fecha);
                                            echo $fecha_formateada;
                                        ?>
                                    </span>
                                </p>
                                <h2 class="mb_30 wow fadeInUp" data-wow-duration="1.2s"><?php echo $row_blog['titulo']; ?></h2>
                                <p class="blog-details__content-text">
                                    <?php echo $row_blog['texto']; ?>
                                </p>
                            </div>
                        </div>
                        
                    </div>
                </div>

                <div class="col-md-10 col-lg-4">
                    <div class="sidebar mt-5 mt-lg-0 wow fadeInRight" data-wow-duration="1.2s">
                        
                        
                        <div class="sidebar__latest-post mb_40">
                            <h4 class="mb_30">Últimos Blogs</h4>

                            <?php do { ?>
                            <div class="sidebar__post-single">
                                <div class="latest-post__thumb">
                                    <a href="<?php echo $URL;?>blog-detalle/titulo/<?php echo str_replace($especiales, $correctos,$row_blogUltimos['titulo']); ?>/cod/<?php echo $row_blogUltimos['id']; ?>/" title="Read More">
                                        <img src="<?php echo $URL?>documentos/blog/<?php echo $row_blogUltimos['imagen']; ?>" alt="Blog">
                                    </a>
                                </div>
                                <div class="latest-post__content">
                                    <a href="<?php echo $URL;?>blog-detalle/titulo/<?php echo str_replace($especiales, $correctos,$row_blogUltimos['titulo']); ?>/cod/<?php echo $row_blogUltimos['id']; ?>/"><?php echo $row_blogUltimos['titulo']; ?></a>
                                    <p class="blog__content-meta">
                                        <span class="material-symbols-outlined"> alarm </span>
                                        <span>
                                            <?php
                                                $fecha = new DateTime($row_blogUltimos['fecha']);
                                                setlocale(LC_TIME, 'es_ES');
                                                $formato = new IntlDateFormatter('es_ES', IntlDateFormatter::SHORT, IntlDateFormatter::NONE);
                                                $formato->setPattern('d MMM y');
                                                $fecha_formateada = $formato->format($fecha);
                                                echo $fecha_formateada;
                                            ?>
                                        </span>
                                    </p>
                                </div>
                            </div>
                            <?php 
                                $row_blogUltimos = mysqli_fetch_assoc($blogUltimos);
                                } while ($row_blogUltimos);   //end horizontal looper 
                            ?>
                            
                            
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Blog End -->

    <!-- Others Blog Start-->
    <section class="section blog blog--others">
        <div class="container">
            <div class="row g-3 g-lg-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-end mb_60 wow fadeInLeft" data-wow-duration="1.2s">
                        <h2 class="section__content-title">Otros Blogs</h2>
                        <a href="<?php echo $URL?>blogs/" class="fs-4 ">Ver todos</a>
                    </div>
                </div>
                <?php do { ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="blog__wrapper wow fadeInUp" data-wow-duration="1.2s">
                            <div class="blog__single">
                                <a href="blog-details.html" class="blog__thumb">
                                    <img style="width: 100%; height: 250px; object-fit: cover; object-position: center;"  src="<?php echo $URL?>documentos/blog/<?php echo $row_blogOtros['imagen']; ?>" alt="Image">
                                </a>
                                <div class="blog__content">
                                    <p class="mb_16 fs-6">
                                        <?php
                                            $fecha = new DateTime($row_blogOtros['fecha']);
                                            setlocale(LC_TIME, 'es_ES');
                                            $formato = new IntlDateFormatter('es_ES', IntlDateFormatter::SHORT, IntlDateFormatter::NONE);
                                            $formato->setPattern('d MMM y');
                                            $fecha_formateada = $formato->format($fecha);
                                            echo $fecha_formateada;
                                        ?>
                                    </p>
                                    <h5 class="blog__content-title"><a href="<?php echo $URL;?>blog-detalle/titulo/<?php echo str_replace($especiales, $correctos,$row_blogOtros['titulo']); ?>/cod/<?php echo $row_blogOtros['id']; ?>/"><?php echo $row_blogOtros['titulo']; ?></a></h5>
                                    <a href="<?php echo $URL;?>blog-detalle/titulo/<?php echo str_replace($especiales, $correctos,$row_blogOtros['titulo']); ?>/cod/<?php echo $row_blogOtros['id']; ?>/" class="blog__content-link">Ver más</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php 
                    $row_blogOtros = mysqli_fetch_assoc($blogOtros);
                    } while ($row_blogOtros);   //end horizontal looper 
                ?>
                
            </div>
        </div>
    </section>
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