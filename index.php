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
    $query_medicos = "SELECT * FROM tbl_medicos WHERE imagen IS NOT NULL AND imagen <> '' ORDER BY id DESC";
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
<html lang="es">

<head>
    <!-- required meta -->
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- #favicon -->
    <link rel="shortcut icon" href="<?php echo $URL?>assets/images/favicon.png" type="image/x-icon">
    <!-- #title -->
    <title>SAMAP - Medicina Prepaga - Sanatorio Adventista</title>
    <!-- #seo -->
    <?php
        $seoTitle = 'SAMAP — Medicina Prepaga del Sanatorio Adventista';
        $seoDesc  = 'SAMAP, medicina prepaga del Sanatorio Adventista de Asunción. Más de 35 años cuidando la salud de las familias paraguayas con planes a tu medida.';
        include 'funciones/seo.php';
    ?>

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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- main css -->
    <link rel="stylesheet" href="<?php echo $URL?>assets/css/style.css">
    <!-- rediseno css -->
    <link rel="stylesheet" href="<?php echo $URL?>assets/css/rediseno-base.css?v=<?php echo @filemtime(__DIR__.'/assets/css/rediseno-base.css'); ?>">
    <link rel="stylesheet" href="<?php echo $URL?>assets/css/rediseno-home.css?v=<?php echo @filemtime(__DIR__.'/assets/css/rediseno-home.css'); ?>">

</head>

<body>

    <!--  Preloader  -->
    <div id="pre_loader"></div>

    <!--header-section start-->
    <?php include 'header.php'; ?>
    <!-- header-section end -->
   
    <!-- Hero Section Start (rediseño) -->
    <section class="rd-hero">
        <div class="rd-container">
            <div class="rd-hero__grid">
                <div class="rd-hero__content">
                    <span class="rd-hero__eyebrow">Medicina Prepaga · Sanatorio Adventista</span>
                    <h1 class="rd-hero__title">Cuidándote siempre</h1>
                    <p class="rd-hero__subtitle">Cobertura médica para vos y tu familia con el respaldo del Sanatorio Adventista.</p>
                    <div class="rd-hero__cta">
                        <a href="https://api.whatsapp.com/send?phone=5950982304977" class="rd-btn rd-btn--wa">
                            <i class="fa-brands fa-whatsapp"></i> Solicitar información
                        </a>
                        <a href="<?php echo $URL?>planes/" class="rd-btn rd-btn--azul">Conocer planes</a>
                    </div>
                </div>
                <div class="rd-hero__media">
                    <img src="<?php echo $URL?>documentos/slider/03.jpg" alt="Familia con cobertura médica SAMAP" fetchpriority="high" decoding="async">
                </div>
            </div>

            <!-- Mini-stats -->
            <div class="rd-stats">
                <div class="rd-stats__item">
                    <span class="rd-stats__num">+40</span>
                    <span class="rd-stats__label">años de experiencia</span>
                </div>
                <div class="rd-stats__item">
                    <span class="rd-stats__num">+8.000</span>
                    <span class="rd-stats__label">familias adheridas</span>
                </div>
                <div class="rd-stats__item">
                    <span class="rd-stats__num">Respaldo</span>
                    <span class="rd-stats__label">del Sanatorio Adventista</span>
                </div>
            </div>
        </div>
    </section>
    <!--Hero Section End -->
    
    <!-- ¿Por qué elegir SAMAP? start (rediseño) -->
    <section class="rd-section rd-why">
        <div class="rd-container">
            <div class="rd-why__header">
                <span class="rd-section__eyebrow">Beneficios</span>
                <h2 class="rd-title">¿Por qué elegir SAMAP?</h2>
                <p class="rd-subtitle">Atención integral para tu salud, con un enfoque dedicado y profesionales comprometidos.</p>
            </div>
            <div class="rd-grid rd-why__grid">
                <div class="rd-card rd-why__card">
                    <div class="rd-why__icon"><i class="fa-solid fa-hospital"></i></div>
                    <h4>Respaldo Médico</h4>
                    <p>El aval del Sanatorio Adventista en cada plan.</p>
                </div>
                <div class="rd-card rd-why__card">
                    <div class="rd-why__icon"><i class="fa-solid fa-truck-medical"></i></div>
                    <h4>Emergencias</h4>
                    <p>Atención de urgencias cuando más lo necesitás.</p>
                </div>
                <div class="rd-card rd-why__card">
                    <div class="rd-why__icon"><i class="fa-solid fa-user-doctor"></i></div>
                    <h4>Especialistas</h4>
                    <p>Una amplia red de profesionales calificados.</p>
                </div>
                <div class="rd-card rd-why__card">
                    <div class="rd-why__icon"><i class="fa-solid fa-handshake"></i></div>
                    <h4>Convenios</h4>
                    <p>Acceso a sanatorios y centros de todo el país.</p>
                </div>
                <div class="rd-card rd-why__card">
                    <div class="rd-why__icon"><i class="fa-solid fa-gift"></i></div>
                    <h4>Beneficios Exclusivos</h4>
                    <p>Descuentos y ventajas pensadas para vos.</p>
                </div>
                <div class="rd-card rd-why__card">
                    <div class="rd-why__icon"><i class="fa-solid fa-bolt"></i></div>
                    <h4>Gestión Ágil</h4>
                    <p>Trámites simples y respuestas rápidas.</p>
                </div>
            </div>
            <p class="rd-why__closing">Un plan para <span>cada etapa</span> de la vida.</p>
        </div>
    </section>
    <!-- ¿Por qué elegir SAMAP? end -->

    <!-- Planes por etapa start (rediseño, datos de tbl_planes) -->
    <section class="rd-section rd-planes">
        <div class="rd-container">
            <div class="rd-why__header">
                <span class="rd-section__eyebrow">Nuestros planes</span>
                <h2 class="rd-title">Un plan para cada etapa de la vida</h2>
                <p class="rd-subtitle">Elegí la cobertura que mejor se adapta a vos y a tu familia.</p>
            </div>
            <div class="rd-grid rd-planes__grid">
                <?php do { ?>
                    <div class="rd-card rd-plan__card">
                        <a href="<?php echo $URL;?>plan-detalle/titulo/<?php echo str_replace($especiales, $correctos,$row_planes['titulo']); ?>/cod/<?php echo $row_planes['id']; ?>/" class="rd-plan__thumb">
                            <img src="<?php echo $URL?>documentos/<?php echo $row_planes['imagen']; ?>" alt="<?php echo $row_planes['titulo']; ?>" loading="lazy" decoding="async">
                        </a>
                        <div class="rd-plan__body">
                            <h4 class="rd-plan__name"><?php echo $row_planes['titulo']; ?></h4>
                            <p class="rd-plan__text">Cobertura pensada para acompañarte en cada momento.</p>
                            <a href="<?php echo $URL;?>plan-detalle/titulo/<?php echo str_replace($especiales, $correctos,$row_planes['titulo']); ?>/cod/<?php echo $row_planes['id']; ?>/" class="rd-btn rd-btn--azul rd-btn--sm">Ver plan</a>
                        </div>
                    </div>
                <?php
                    $row_planes = mysqli_fetch_assoc($planes);
                    } while ($row_planes);   //end horizontal looper
                ?>
            </div>
        </div>
    </section>
    <!-- Planes por etapa End -->

    <!-- Banda buscador guía médica start (rediseño) -->
    <section class="rd-band rd-band--medicos">
        <div class="rd-container">
            <div class="rd-band__inner">
                <div class="rd-band__text">
                    <h2 class="rd-band__title">Encontrá tu médico en segundos</h2>
                    <p class="rd-band__subtitle">Buscá por especialidad, médico o centro en nuestra Guía Médica.</p>
                </div>
                <form class="rd-band__search" action="<?php echo $URL?>guiamedica/" method="get">
                    <i class="fa-solid fa-magnifying-glass rd-band__search-icon"></i>
                    <input type="text" name="buscar" class="rd-band__input" placeholder="Especialidad, médico o centro...">
                    <button type="submit" class="rd-btn rd-btn--wa">Buscar</button>
                </form>
            </div>
        </div>
    </section>
    <!-- Banda buscador guía médica End -->


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
                                <img src="<?php echo $URL?>assets/images/review.png" class="author mb_12" alt="image" loading="lazy" decoding="async">
                                <h5 class="font_600">Mrttino Pal</h5>
                            </div>
                        </div>
                        <div class="testimonial__slider-card text-center wow fadeInUp" data-wow-duration="1.2s" data-wow-delay="0.3s">
                            <p class="mb_20">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi facilisi nisl velit, amet eget mattis Id all off the most popular think libero. </p>
                            <div class="testimonial__slider-card-author">
                                <img src="<?php echo $URL?>assets/images/review2.png" class="author mb_12" alt="image" loading="lazy" decoding="async">
                                <h5 class="font_600">Robert Fox</h5>
                            </div>
                        </div>
                        <div class="testimonial__slider-card text-center wow fadeInUp" data-wow-duration="1.2s" data-wow-delay="0.6s">
                            <p class="mb_20">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi facilisi nisl velit, amet eget mattis Id all off the most popular think libero. </p>
                            <div class="testimonial__slider-card-author">
                                <img src="<?php echo $URL?>assets/images/review3.png" class="author mb_12" alt="image" loading="lazy" decoding="async">
                                <h5 class="font_600">Alina Mardin</h5>
                            </div>
                        </div>
                        <div class="testimonial__slider-card text-center wow fadeInUp" data-wow-duration="1.2s" data-wow-delay="0.9s">
                            <p class="mb_20">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi facilisi nisl velit, amet eget mattis Id all off the most popular think libero. </p>
                            <div class="testimonial__slider-card-author">
                                <img src="<?php echo $URL?>assets/images/review.png" class="author mb_12" alt="image" loading="lazy" decoding="async">
                                <h5 class="font_600">Mrttino Pal</h5>
                            </div>
                        </div>
                        <div class="testimonial__slider-card text-center wow fadeInUp" data-wow-duration="1.2s" data-wow-delay="1.2s">
                            <p class="mb_20">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi facilisi nisl velit, amet eget mattis Id all off the most popular think libero. </p>
                            <div class="testimonial__slider-card-author">
                                <img src="<?php echo $URL?>assets/images/review2.png" class="author mb_12" alt="image" loading="lazy" decoding="async">
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
                        <h2 class="section__header-title wow fadeInUp" data-wow-duration="1.2s">MANTENETE INFORMADO</h2>
                        <p class="section__header-content wow fadeInDown" data-wow-duration="1.5s">En SAMAP, nos esforzamos por mantenerte informado y motivado en tu viaje hacia un estilo de vida más saludable. Descubre nuevas perspectivas y conocimientos prácticos para mejorar tu bienestar. Tu salud, nuestra prioridad.</p>
                    </div>
                </div>
            </div>
            <div class="row gy-4 rd-hscroll">
                <?php do { ?>
                <div class="col-12 col-md-6">
                    <div class="blog-articles__card wow fadeInUp" data-wow-duration="1.2s">
                        <a href="<?php echo $URL;?>blog-detalle/titulo/<?php echo str_replace($especiales, $correctos,$row_blog['titulo']); ?>/cod/<?php echo $row_blog['id']; ?>/" class="blog-articles__card-thumb">
                            <img style="width: 100%; height: 400px; object-fit: cover; object-position: center;" src="<?php echo $URL?>documentos/blog/<?php echo $row_blog['imagen']; ?>" alt="Image" loading="lazy" decoding="async">
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
<script>
function mostrarCobertura() {
  Swal.fire({
    title: 'Cobertura del Seguro',
    html: `
      <div style="text-align: left; max-height: 500px; overflow-y: auto; font-size: 14px;">
        <table style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif;">
          <tr style="background-color: #2f558e; color: white;">
            <th colspan="2" style="padding: 8px; text-align: left;">MONTO MÁXIMO GLOBAL</th>
            <th style="padding: 8px; text-align: right;">USD 35.000 ✅</th>
          </tr>
          <tr style="background-color: #e6f2f0;">
            <td colspan="2">Asistencia médica por accidente</td>
            <td style="text-align: right;">Hasta USD 35.000 ✅</td>
          </tr>
          <tr>
            <td colspan="2">Asistencia médica por enfermedad no preexistente (incluye COVID)</td>
            <td style="text-align: right;">Hasta USD 35.000 ✅</td>
          </tr>
          <tr style="background-color: #e6f2f0;">
            <td colspan="2">Primera atención por enfermedad preexistente</td>
            <td style="text-align: right;">Hasta USD 3.000 ✅</td>
          </tr>
          <tr>
            <td colspan="2">Odontología de urgencia</td>
            <td style="text-align: right;">Hasta USD 3.000 ✅</td>
          </tr>
          <tr style="background-color: #e6f2f0;">
            <td colspan="2">Medicamentos ambulatorios</td>
            <td style="text-align: right;">Hasta USD 5.000 ✅</td>
          </tr>
          <tr>
            <td colspan="2">Medicamentos en caso de hospitalización</td>
            <td style="text-align: right;">Incluido ✅</td>
          </tr>
        </table>

        <p style="margin-top: 15px;"><strong>Validez máxima por viaje:</strong> 30 <strong>DÍAS</strong></p>

        <hr style="margin: 15px 0;">

        <p><strong>Validez territorial:</strong> INTERNACIONAL</p>
        <p><strong>Limitaciones por edad:</strong> N/A</p>

          <a href="https://www.assistcard.com/document/459" style="color: blue; font-weight: bold;" target="_blank">DESCARGAR CONDICIONES GENERALES</a> 
          
      </div>
    `,
    width: 850,
    confirmButtonText: 'Cerrar',
    showCloseButton: true
  });
}
</script>
</body>

</html>