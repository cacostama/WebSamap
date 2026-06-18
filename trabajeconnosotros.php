<?php require_once('funciones/db.php');?>
<!DOCTYPE html>
<html lang="es">

<head>
    <!-- required meta -->
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- #favicon -->
    <link rel="shortcut icon" href="<?php echo $URL?>assets/images/favicon.png" type="image/x-icon">
    <!-- #title -->
    <title>SAMAP - Trabaje con Nosotros</title>
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

    <!-- Blog Banner Start -->
    <section class="banner wow fadeInUp" data-wow-duration="0.4s">
        <div class="container ">
            <div class="row ">
                <div class="col-lg-12">
                    <div class="banner__content">
                        <h1 class="banner__title wow fadeInLeft" data-wow-duration="1.2s">Trabaje con Nosotros</h1> 
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb wow fadeInRight" data-wow-duration="1.2s">
                                <li class="breadcrumb-item"><a href="index.html">Inicio</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Trabaje con Nosotros</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Blog Banner End -->

    <!-- Contact Us start -->
    <section class="section contact">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-10 col-xl-6">
                    <div class="section__content wow fadeInLeft" data-wow-duration="1.5s">
                        <h2 class="section__content-title wow fadeInUp" data-wow-duration="1.2s">Envienos un mensaje</h2>
                        <p class="section__content-text wow fadeInDown" data-wow-duration="1.5s">¡Conéctate con nosotros en SAMAP! Estamos aquí para responder todas tus preguntas y brindarte la información que necesitas sobre nuestros planes de seguros médicos y convenios. </p>
                        <div class="contact__info mt_40">        
                            <div class="between_part customGapThree mb_30">
                                <span class="material-symbols-outlined"> mail </span>
                                <p class="headingFive"><a href="#" class="__cf_email__">samap@samap.com.py</a></p>
                            </div>
                            <div class="between_part customGapThree mb_30">
                                <span class="material-symbols-outlined"> phone_in_talk </span>
                                <p class="headingFive">021 219 6700</p>
                            </div>
                            <div class="between_part customGapThree mb_30">
                                <span class="material-symbols-outlined"> location_on </span>
                                <p class="headingFive">Paí Pérez y Petirossi, Asunción</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6">
                    <div class="contact__form mt-5 mt-xl-0 wow fadeInRight" data-wow-duration="1.5s">
                        <form method="post" action="<?php echo $URL?>enviar.php" class="contact__form-mail text-center" novalidate="novalidate">
                            <input type="hidden" name="origen" value="trabajo">
                            <div class="in-box mb_20">
                                <input type="text" class="form-control" name="nombre" id="name" placeholder="Nombre" required>
                            </div>
                            <div class="in-box mb_20">
                                <input type="email" class="form-control" name="email" id="email" placeholder="Correo" required>
                            </div>
                            <div class="in-box mb_20">
                                <input type="tel" class="form-control" name="tel" id="phone" placeholder="Número" required>
                            </div>
                            <div class="in-box">
                                <textarea class="form-control" id="message" name="mensaje" rows="5" placeholder="Mensaje" required></textarea>
                            </div>

                            <!-- Honeypot anti-spam: oculto para humanos, los bots lo completan -->
                            <div style="position:absolute;left:-9999px;top:-9999px;" aria-hidden="true">
                                <label>No completar este campo
                                    <input type="text" name="website" tabindex="-1" autocomplete="off">
                                </label>
                            </div>

                            <div class="in-box mb_20 text-start" style="margin-top:15px;">
                                <label style="font-size:14px;display:flex;gap:8px;align-items:flex-start;">
                                    <input type="checkbox" name="consentimiento" value="1" required style="margin-top:4px;">
                                    <span>Acepto que mis datos sean utilizados para responder mi consulta, conforme a la <a href="<?php echo $URL?>privacidad/" target="_blank" rel="noopener">política de privacidad</a> (Ley 6534/20).</span>
                                </label>
                            </div>

                            <span id="msg"></span>

                            <button type="submit" class="btn_theme mt_40" name="submit" id="submit">Enviar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Contact Us End -->
    
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