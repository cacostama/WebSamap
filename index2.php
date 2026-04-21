<?php require_once('funciones/db.php');?>

<?php 
    
    mysqli_select_db($connect, $database);
    $query_slider = 'SELECT * FROM tbl_slider ORDER BY id DESC';
    $slider = mysqli_query($connect, $query_slider) or die(mysqli_error($link));
    $row_slider = mysqli_fetch_assoc($slider);
    $totalRows_slider = mysqli_num_rows($slider);

    mysqli_select_db($connect, $database);
    $query_slider2 = 'SELECT * FROM tbl_slider ORDER BY id DESC';
    $slider2 = mysqli_query($connect, $query_slider2) or die(mysqli_error($link));
    $row_slider2 = mysqli_fetch_assoc($slider2);
    $totalRows_slider2 = mysqli_num_rows($slider2);


    mysqli_select_db($connect, $database);
    $query_planes = 'SELECT * FROM tbl_planes WHERE especial=0 ORDER BY id ASC';
    $planes = mysqli_query($connect, $query_planes) or die(mysqli_error($link));
    $row_planes = mysqli_fetch_assoc($planes);
    $totalRows_planes = mysqli_num_rows($planes);

    mysqli_select_db($connect, $database);
    $query_servicios = 'SELECT * FROM tbl_servicios ORDER BY id DESC';
    $servicios = mysqli_query($connect, $query_servicios) or die(mysqli_error($link));
    $row_servicios = mysqli_fetch_assoc($servicios);
    $totalRows_servicios = mysqli_num_rows($servicios);

    mysqli_select_db($connect, $database);
    $query_medicos = 'SELECT * FROM tbl_medicos ORDER BY id DESC';
    $medicos = mysqli_query($connect, $query_medicos) or die(mysqli_error($link));
    $row_medicos = mysqli_fetch_assoc($medicos);
    $totalRows_medicos = mysqli_num_rows($medicos);

    mysqli_select_db($connect, $database);
    $query_blog = 'SELECT * FROM tbl_blog ORDER BY id DESC LIMIT 2';
    $blog = mysqli_query($connect, $query_blog) or die(mysqli_error($link));
    $row_blog = mysqli_fetch_assoc($blog);
    $totalRows_blog = mysqli_num_rows($blog);

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
    <title>SAMAP - Medicina Prepaga - Sanatorio Adventista</title>
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
   
    <!-- Hero Section Start --><br><br>
    <section class="hero-area">
        <div style="margin-top: 120px;" >
            <div class="row align-items-center">
                
                <div class="col-xl-12">
                    <div id="carouselExampleControls" class="carousel slide" data-bs-ride="carousel">
  <div class="carousel-inner">
    <div class="carousel-item active">
    <a href="https://www.samap.com.py/planes/" >
      <img src="documentos/slider/web01.jpg" class="d-block w-100" alt="...">
    </a>
    </div>
    <div class="carousel-item">
    <a href="https://www.samap.com.py/planes/" >
      <img src="documentos/slider/web02.jpg" class="d-block w-100" alt="...">
    </a>
    </div>
    <div class="carousel-item">
    <a href="https://www.samap.com.py/planes/" >
      <img src="documentos/slider/web03.jpg" class="d-block w-100" alt="...">
    </a>
    </div>
    <div class="carousel-item">
    <a href="https://www.samap.com.py/planes/" >
      <img src="documentos/slider/web04.jpg" class="d-block w-100" alt="...">
    </a>
    </div>
  </div>
  <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleControls" data-bs-slide="prev">
    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    <span class="visually-hidden">Previous</span>
  </button>
  <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleControls" data-bs-slide="next">
    <span class="carousel-control-next-icon" aria-hidden="true"></span>
    <span class="visually-hidden">Next</span>
  </button>
</div>
                </div>
            </div>
        </div>
    </section>
    <!--Hero Section End -->

    <!-- Countdown Start -->
    <section class="section countdown wow fadeInUp" data-wow-duration="1.2s">
        <div class="container">
            <div class="row align-items-center gy-3 gy-lg-0">
                <div class="col-6 col-sm-6 col-lg-3">
                    <div class="countdown__part wow fadeInUp" data-wow-duration="1.2s">
                        <h3 class="theme_color d-inline-flex align-items-center mb_30"><span class="odometer" data-odometer-final="50"></span> <span>+</span></h3>
                        <div class="fs-4">Convenios</div>
                    </div>
                </div>
                <div class="col-6 col-sm-6 col-lg-3">
                    <div class="countdown__part wow fadeInUp" data-wow-duration="1.2s" data-wow-delay="0.3s">                            
                        <h3 class="theme_color mb_30"><span class="odometer" data-odometer-final="8000"></span> <span>+</span></h3>
                        <div class="fs-4">Adheridos</div>
                    </div>
                </div>
                <div class="col-6 col-sm-6 col-lg-3">
                    <div class="countdown__part wow fadeInUp" data-wow-duration="1.2s" data-wow-delay="0.6s">
                        <h3 class="theme_color mb_30"><span class="odometer" data-odometer-final="30"></span> <span>+</span></h3>
                        <div class="fs-4">Años de Experiencia</div>
                    </div>
                </div>
                <div class="col-6 col-sm-6 col-lg-3">
                    <div class="countdown__part wow fadeInUp" data-wow-duration="1.2s" data-wow-delay="0.9s">
                        <h3 class="theme_color mb_30"><span class="odometer" data-odometer-final="50"></span> <span>+</span></h3>
                        <div class="fs-4">Especialidades</div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Countdown End -->
    
    <!-- about us start -->
    <section class="section about">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="about__single-thumb wow fadeInLeft" data-wow-duration="1.5s">
                        <img src="<?php echo $URL?>assets/images/about_img.png" alt="Image">
                    </div>
                </div>
                <div class="col-md-10 col-lg-6">
                    <div class="section__content">
                        <div class="section__content-sub-title word d-inline-flex">
                            <!--
                            <span data-wow-duration="1.1s" data-wow-delay="0.1s" class="letter headingFour wow fadeInDown">S</span>
                            <span data-wow-duration="1.1s" data-wow-delay="0.2s" class="letter headingFour wow fadeInDown">A</span>
                            <span data-wow-duration="1.1s" data-wow-delay="0.3s" class="letter headingFour wow fadeInDown">M</span>
                            <span data-wow-duration="1.1s" data-wow-delay="0.4s" class="letter headingFour wow fadeInDown">A</span>
                            <span data-wow-duration="1.1s" data-wow-delay="0.5s" class="letter headingFour wow fadeInDown">P</span>

                            <span data-wow-duration="1.1s" data-wow-delay="0.6s" class="letter headingFour wow fadeInDown">&nbsp;U</span>
                            <span data-wow-duration="1.1s" data-wow-delay="0.7s" class="letter headingFour wow fadeInDown">s</span>
-->

                        </div>
                        <h2 class="section__content-title wow fadeInUp" data-wow-duration="1.3s">UN PLAN para cada necesidad <span class="theme_color">SAMAP</span> </h2>
                        <p class="section__content-text wow fadeInDown" data-wow-duration="1.5s"> Nuestra amplia gama de planes está diseñada para adaptarse a tus requerimientos individuales, brindándote la cobertura perfecta que necesitas en cada etapa de tu vida.</p>
                        <div class="section__content-inner wow fadeInRight" data-wow-duration="1.5s">
                            <ul>
                            
                            	<li><span class="material-symbols-outlined"> done </span> ALFA AMBULATORIO </li>
                            	<li><span class="material-symbols-outlined"> done </span> KIDS </li>
                            	<li><span class="material-symbols-outlined"> done </span> ALFA </li>
                            	<li><span class="material-symbols-outlined"> done </span> SENIOR </li>
                            	
                            	<li><span class="material-symbols-outlined"> done </span> BETA </li>
                            	
                            	<li><span class="material-symbols-outlined"> done </span> MATERNO </li>
                            	<li><span class="material-symbols-outlined"> done </span> SEVEN </li>
                               <!-- <?php do { ?>

                                    <li><span class="material-symbols-outlined"> done </span><?php echo $row_planes['titulo']; ?></li>

                                <?php 
                                    $row_planes = mysqli_fetch_assoc($planes);
                                    } while ($row_planes);   //end horizontal looper 
                                ?> -->
                            </ul>
                        </div>
                        <a href="<?php echo $URL?>planes/" class="btn_theme btn_dark_border mt_40 wow fadeInLeft" data-wow-duration="1.5s">Más información</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- about us end -->

    <!-- call to action start -->
    <section class="section call-to-action" data-background="<?php echo $URL?>assets/images/call_to_action.png">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-7">
                    <div class="section__content text-center">
                        <h2 class="section__content-title text-white wow fadeInUp" data-wow-duration="1.2s">GUÍA MÉDICA SAMAP</h2>
                        <p class="headingThree text-white wow fadeInDown" data-wow-duration="1.5s">Consultá los servicios que ofrecemos</p>
                        <div class="section__content-cta customGapThree">
                            <a href="<?php echo $URL?>guiamedica/" class="btn_theme btn_light wow fadeInLeft" data-wow-duration="1.2s">VER GUÍA MEDICA</a>
                            <a href="https://api.whatsapp.com/send?phone=5950982304977" class="btn_theme btn_light_border wow fadeInRight" data-wow-duration="1.2s">VISACIONES</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- call to action End -->

    <!-- Service start -->
    <section class="section service wow fadeInUp" data-wow-duration="0.4s">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12">
                    <div class="section__header">
                        <div class="section__header-sub-title word d-inline-flex">
                            <span data-wow-delay=".1s" class="letter headingFour wow fadeInDown">B</span>
                            <span data-wow-delay="0.2s" class="letter headingFour wow fadeInDown">e</span>
                            <span data-wow-delay="0.3s" class="letter headingFour wow fadeInDown">n</span>
                            <span data-wow-delay="0.4s" class="letter headingFour wow fadeInDown">e</span>
                            <span data-wow-delay="0.5s" class="letter headingFour wow fadeInDown">f</span>
                            <span data-wow-delay="0.6s" class="letter headingFour wow fadeInDown">i</span>
                            <span data-wow-delay="0.7s" class="letter headingFour wow fadeInDown">c</span>
                            <span data-wow-delay="0.8s" class="letter headingFour wow fadeInDown">i</span>
                            <span data-wow-delay="0.9s" class="letter headingFour wow fadeInDown">o</span>
                            <span data-wow-delay="1.0s" class="letter headingFour wow fadeInDown">s</span>
                        </div>
                        <h2 class="section__header-title wow fadeInUp" data-wow-duration="1.2s">Nuestros Beneficios</h2>
                        <p class="section__header-content wow fadeInDown" data-wow-duration="1.5s">Con un enfoque dedicado y profesionales comprometidos, te ofrecemos atención integral para tu salud. </p>
                    </div>
                </div>
            </div>
            <div class="row gy-3 gy-md-4">
                
                <?php do { ?>
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="service__card wow fadeInUp" data-wow-duration="1.2s" data-wow-delay="0.3s">
                            <div class="service__card-icon mb_40 mx-auto">
                                <img src="<?php echo $URL?>documentos/servicios/<?php echo$row_servicios['imagen'];?>" alt="Icon">
                            </div>
                            <h4 class="mb_30"><?php echo$row_servicios['titulo'];?></h4>
                            <!--<p class="mb_40"><?php echo$row_servicios['intro'];?></p> -->
                            <a href="#" class="service__card-read-more">Más info</a>
                        </div>
                    </div>
                <?php 
                    $row_servicios = mysqli_fetch_assoc($servicios);
                    } while ($row_servicios);   //end horizontal looper 
                ?>
                
            </div>
            <div class="col-12 mx-auto">
                <div class="section__cta wow fadeInLeft" data-wow-duration="1.2s">
                    <a href="<?php echo $URL?>beneficios/" class="btn_theme">Ver más</a>
                </div>
            </div>
        </div>
    </section>
    <!-- Service End -->

    <!-- Team start -->
    <section class="section team">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12">
                    <div class="section__header text-center">
                        <div class="section__header-sub-title word d-inline-flex">
                            <span data-wow-delay=".1s" class="letter text-white headingFour wow fadeInDown">D</span>
                            <span data-wow-delay="0.2s" class="letter text-white headingFour wow fadeInDown">o</span>
                            <span data-wow-delay="0.3s" class="letter text-white headingFour wow fadeInDown">c</span>
                            <span data-wow-delay="0.4s" class="letter text-white headingFour wow fadeInDown">t</span>
                            <span data-wow-delay="0.5s" class="letter text-white headingFour wow fadeInDown">o</span>
                            <span data-wow-delay="0.6s" class="letter text-white headingFour wow fadeInDown">r</span>
                        </div>
                        <h2 class="section__header-title text-white wow fadeInUp" data-wow-duration="1.2s">MÉDICOS SAMAP</h2>
                        <p class="section__header-content text-white wow fadeInDown" data-wow-duration="1.5s">En SAMAP, entendemos la importancia de elegir a los profesionales adecuados para tu salud. Nuestra red de médicos altamente calificados y comprometidos está aquí para brindarte la atención que mereces. </p>
                    </div>
                </div>
                <div class="col-12">
                    <div class="team__slider">
                        <?php do { ?>
                            <div class="team__slider-card text-center wow fadeInUp" data-wow-duration="1.2s">
                                <div class="team__slider-card-thumb">
                                    <a href="doctor-details.html">
                                        <div style="width: 300px; height: 350px; overflow: hidden;">
                                            <img style="width: 100%; height: 100%; object-fit: cover; object-position: center;" src="<?php echo $URL?>documentos/medicos/<?php echo$row_medicos['imagen'];?>" alt="Image">
                                        </div>
                                    </a>
                                </div>
                                <div class="" style=" width: 100%; /* Esto mantiene la proporción cuadrada */  background-color: #274266; position: relative; padding:10px;">
                                    <h5 class="text-white" style="font-size: 15px;"><?php echo$row_medicos['titulo'];?> <?php echo$row_medicos['nombre'];?></h5>
                                    <p class="text-white mb_12" style="font-size: 13px;"><?php echo$row_medicos['especialidad'];?><br>
                                    <a href="tel:+122-212-2155" class="text-white" style="font-size: 15px;">Teléfono: (021) 219 6700</a></p>
                                </div>
                            </div>
                        <?php 
                            $row_medicos = mysqli_fetch_assoc($medicos);
                            } while ($row_medicos);   //end horizontal looper 
                        ?>
                        

                    </div>
                </div>
                <div class="col-12">
                    <div class="slider-navigation wow fadeInLeft" data-wow-duration="1.2s">
                        <button class="prev-team pagination-button">
                            <i class="fa-solid fa-angle-left"></i>
                        </button>
                        <div class="news__dots"></div>
                        
                        <button class="next-team pagination-button">
                            <i class="fa-solid fa-angle-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>
   <!-- Team End -->

    <!-- Testimonial start 
    <section class="section testimonial wow fadeInUp" data-wow-duration="0.4s">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12">
                    <div class="section__header text-center">
                        <div class="section__header-sub-title word d-inline-flex">
                            <span data-wow-delay="0.1s" class="letter headingFour wow fadeInDown">T</span>
                            <span data-wow-delay="0.2s" class="letter headingFour wow fadeInDown">e</span>
                            <span data-wow-delay="0.3s" class="letter headingFour wow fadeInDown">s</span>
                            <span data-wow-delay="0.4s" class="letter headingFour wow fadeInDown">t</span>
                            <span data-wow-delay="0.5s" class="letter headingFour wow fadeInDown">i</span>
                            <span data-wow-delay="0.6s" class="letter headingFour wow fadeInDown">m</span>
                            <span data-wow-delay="0.7s" class="letter headingFour wow fadeInDown">o</span>
                            <span data-wow-delay="0.8s" class="letter headingFour wow fadeInDown">n</span>
                            <span data-wow-delay="0.9s" class="letter headingFour wow fadeInDown">i</span>
                            <span data-wow-delay="1s" class="letter headingFour wow fadeInDown">a</span>
                            <span data-wow-delay="1.1s" class="letter headingFour wow fadeInDown">l</span>
                        </div>
                        <h2 class="section__header-title wow fadeInUp" data-wow-duration="1.2s">What Our Client’s Say</h2>
                        <p class="section__header-content wow fadeInDown" data-wow-duration="1.5s">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Odio mauris congue sollicitudin nunc viverra ante neque suspendisse.</p>
                    </div>
                </div>
                <div class="col-12">
                    <div class="testimonial__slider">
                        <div class="testimonial__slider-card text-center wow fadeInUp" data-wow-duration="1.2s">
                            <p class="mb_20">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi facilisi nisl velit, amet eget mattis Id all off the most popular think libero. </p>
                            <div class="testimonial__slider-card-author">
                                <img src="<?php echo $URL?>assets/images/review.png" class="author mb_12" alt="image">
                                <h5 class="font_600">Mrttino Pal</h5>
                            </div>
                        </div>
                        <div class="testimonial__slider-card text-center wow fadeInUp" data-wow-duration="1.2s" data-wow-delay="0.3s">
                            <p class="mb_20">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi facilisi nisl velit, amet eget mattis Id all off the most popular think libero. </p>
                            <div class="testimonial__slider-card-author">
                                <img src="<?php echo $URL?>assets/images/review2.png" class="author mb_12" alt="image">
                                <h5 class="font_600">Robert Fox</h5>
                            </div>
                        </div>
                        <div class="testimonial__slider-card text-center wow fadeInUp" data-wow-duration="1.2s" data-wow-delay="0.6s">
                            <p class="mb_20">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi facilisi nisl velit, amet eget mattis Id all off the most popular think libero. </p>
                            <div class="testimonial__slider-card-author">
                                <img src="<?php echo $URL?>assets/images/review3.png" class="author mb_12" alt="image">
                                <h5 class="font_600">Alina Mardin</h5>
                            </div>
                        </div>
                        <div class="testimonial__slider-card text-center wow fadeInUp" data-wow-duration="1.2s" data-wow-delay="0.9s">
                            <p class="mb_20">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi facilisi nisl velit, amet eget mattis Id all off the most popular think libero. </p>
                            <div class="testimonial__slider-card-author">
                                <img src="<?php echo $URL?>assets/images/review.png" class="author mb_12" alt="image">
                                <h5 class="font_600">Mrttino Pal</h5>
                            </div>
                        </div>
                        <div class="testimonial__slider-card text-center wow fadeInUp" data-wow-duration="1.2s" data-wow-delay="1.2s">
                            <p class="mb_20">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi facilisi nisl velit, amet eget mattis Id all off the most popular think libero. </p>
                            <div class="testimonial__slider-card-author">
                                <img src="<?php echo $URL?>assets/images/review2.png" class="author mb_12" alt="image">
                                <h5 class="font_600">Robert Fox</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>-->
    <!-- Testimonial End -->
    
    <!-- blog-articles Start -->
    <section class="section blog-articles">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-12">
                    <div class="section__header">
                        <div class="section__header-sub-title word d-inline-flex">
                            <span data-wow-delay="0.1s" class="letter headingFour wow fadeInDown">B</span>
                            <span data-wow-delay="0.2s" class="letter headingFour wow fadeInDown">l</span>
                            <span data-wow-delay="0.3s" class="letter headingFour wow fadeInDown">o</span>
                            <span data-wow-delay="0.4s" class="letter headingFour wow fadeInDown">g</span>
                        </div>
                        <h2 class="section__header-title wow fadeInUp" data-wow-duration="1.2s">Nuestros últimos artículos</h2>
                        <p class="section__header-content wow fadeInDown" data-wow-duration="1.5s">En SAMAP, nos esforzamos por mantenerte informado y motivado en tu viaje hacia un estilo de vida más saludable. Descubre nuevas perspectivas y conocimientos prácticos para mejorar tu bienestar. Tu salud, nuestra prioridad.</p>
                    </div>
                </div>
            </div>
            <div class="row gy-4">
                <?php do { ?>
                <div class="col-12 col-md-6">
                    <div class="blog-articles__card wow fadeInUp" data-wow-duration="1.2s">
                        <a href="<?php echo $URL;?>blog-detalle/titulo/<?php echo str_replace($especiales, $correctos,$row_blog['titulo']); ?>/cod/<?php echo $row_blog['id']; ?>/" class="blog-articles__card-thumb">
                            <img style="width: 100%; height: 400px; object-fit: cover; object-position: center;" src="<?php echo $URL?>documentos/blog/<?php echo $row_blog['imagen']; ?>" alt="Image">
                        </a>
                        <div class="blog-articles__card-content">

                            <p class="fs-6 mb_16">
                                
                                <?php
                                    $fecha = new DateTime($row_blog['fecha']);
                                    setlocale(LC_TIME, 'es_ES');
                                    $formato = new IntlDateFormatter('es_ES', IntlDateFormatter::SHORT, IntlDateFormatter::NONE);
                                    $formato->setPattern('d MMM y');
                                    $fecha_formateada = $formato->format($fecha);
                                    echo $fecha_formateada;
                                ?>

                            </p>
                            <h5 class="mb_30">
                                <a href="<?php echo $URL;?>blog-detalle/titulo/<?php echo str_replace($especiales, $correctos,$row_blog['titulo']); ?>/cod/<?php echo $row_blog['id']; ?>/"><?php echo $row_blog['titulo']; ?></a>
                            </h5>
                            <a href="<?php echo $URL;?>blog-detalle/titulo/<?php echo str_replace($especiales, $correctos,$row_blog['titulo']); ?>/cod/<?php echo $row_blog['id']; ?>/" class="blog-articles__card-more">Ver más</a>
                        </div>
                    </div>
                </div>
               <?php 
                    $row_blog = mysqli_fetch_assoc($blog);
                    } while ($row_blog);   //end horizontal looper 
                ?>
                <div class="col-12">
                    <div class="section__cta wow fadeInLeft" data-wow-duration="1.2s">
                        <a href="<?php echo $URL?>blogs/" class="btn_theme">Ver más</a>
                    </div>
                </div>
            </div>
        </div>
    </section> 
    <!-- blog-articles end -->

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