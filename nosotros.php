<?php require_once('funciones/db.php');?>

<?php 

    mysqli_select_db($connect, $database);
    $query_planes = 'SELECT * FROM tbl_planes WHERE especial=0 AND deleted_at IS NULL ORDER BY id ASC';
    $planes = mysqli_query($connect, $query_planes) or die(mysqli_error($link));
    $row_planes = mysqli_fetch_assoc($planes);
    $totalRows_planes = mysqli_num_rows($planes);

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
    <title>SAMAP - Nosotros</title>
    <!-- #seo -->
    <?php
        $seoTitle = 'Nosotros — SAMAP Medicina Prepaga';
        $seoDesc  = 'Más de 40 años cuidando la salud de las familias paraguayas, con el respaldo del Sanatorio Adventista de Asunción. Conocé nuestra historia y valores.';
        $seoKeywords = 'sobre SAMAP, historia, misión, visión, Sanatorio Adventista de Asunción';
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
    
    <!-- main css -->
    <link rel="stylesheet" href="<?php echo $URL?>assets/css/style.css">

    <!-- rediseño -->
    <link rel="stylesheet" href="<?php echo $URL?>assets/css/rediseno-base.css?v=<?php echo @filemtime(__DIR__.'/assets/css/rediseno-base.css'); ?>">
    <link rel="stylesheet" href="<?php echo $URL?>assets/css/rediseno-nosotros.css?v=<?php echo @filemtime(__DIR__.'/assets/css/rediseno-nosotros.css'); ?>">

</head>

<body>
    
    <!--  Preloader  -->
    <div id="pre_loader"></div>

    <!--header-section start-->
    <?php include 'header.php'; ?>
    <!-- header-section end -->

    <!-- Rediseño Nosotros Start -->
    <main class="rd-nosotros">

      <!-- HERO -->
      <section class="rd-hero">
        <div class="rd-container">
          <ol class="rd-breadcrumb">
            <li><a href="<?php echo $URL?>">Inicio</a></li>
            <li class="is-active">Nosotros</li>
          </ol>
          <h1 class="rd-hero__title">NOSOTROS</h1>
          <p class="rd-hero__lead">Más de 40 años cuidando la salud de miles de familias paraguayas, con el respaldo del Sanatorio Adventista de Asunción.</p>
        </div>
      </section>

      <!-- NUESTRA HISTORIA -->
      <section class="rd-section rd-section--center">
        <div class="rd-container">
          <h2 class="rd-title">Nuestra Historia</h2>
          <p class="rd-subtitle">Un camino de crecimiento constante al servicio del bienestar de nuestros asegurados.</p>
          <div class="rd-timeline">
            <div class="rd-timeline__item">
              <span class="rd-timeline__year">1983</span>
              <h3>Nace SAMAP</h3>
              <p>Comenzamos con el respaldo del Sanatorio Adventista de Asunción, con el propósito de brindar una cobertura basada en la excelencia médica y valores cristianos.</p>
            </div>
            <div class="rd-timeline__item">
              <span class="rd-timeline__year">1995</span>
              <h3>Más servicios y red</h3>
              <p>Ampliamos nuestros servicios y la red de atención, llegando a más ciudades con prestadores de primer nivel en todo el país.</p>
            </div>
            <div class="rd-timeline__item">
              <span class="rd-timeline__year">2005</span>
              <h3>Nuevas coberturas</h3>
              <p>Incorporamos nuevas coberturas y beneficios, adaptándonos a las necesidades de cada etapa de la vida de nuestros socios.</p>
            </div>
            <div class="rd-timeline__item">
              <span class="rd-timeline__year">Hoy</span>
              <h3>Miles de familias</h3>
              <p>Miles de familias confían en SAMAP. Seguimos creciendo con el mismo compromiso humano, responsable y cercano de siempre.</p>
            </div>
          </div>
        </div>
      </section>

      <!-- MISIÓN Y VISIÓN -->
      <section class="rd-section rd-section--alt">
        <div class="rd-container">
          <div class="rd-grid-2">
            <article class="rd-card">
              <div class="rd-card__icon"><i class="fas fa-bullseye"></i></div>
              <h3>Misión</h3>
              <p>Ser una institución de salud autosustentable, dedicada a la atención y promoción de la salud física, mental, social y espiritual de nuestros asegurados, pacientes y comunidad, siguiendo el ejemplo del Señor Jesús, el Médico de los médicos.</p>
            </article>
            <article class="rd-card">
              <div class="rd-card__icon"><i class="fas fa-eye"></i></div>
              <h3>Visión</h3>
              <p>Ser una institución de salud con altos estándares de calidad en la prevención y tratamiento de la enfermedad, promoviendo la salud integral de nuestros asegurados, pacientes y comunidad.</p>
            </article>
          </div>
        </div>
      </section>

      <!-- NUESTROS VALORES -->
      <section class="rd-section rd-section--center">
        <div class="rd-container">
          <h2 class="rd-title">Nuestros Valores</h2>
          <p class="rd-subtitle">Los principios que guían cada decisión y cada gesto de cuidado.</p>
          <div class="rd-values">
            <div class="rd-value">
              <div class="rd-value__icon"><i class="fas fa-handshake"></i></div>
              <h4>Compromiso</h4>
              <p>Cuidamos tu salud todos los días, con responsabilidad.</p>
            </div>
            <div class="rd-value">
              <div class="rd-value__icon"><i class="fas fa-hand-holding-heart"></i></div>
              <h4>Servicio</h4>
              <p>Atención humana y cálida en cada contacto.</p>
            </div>
            <div class="rd-value">
              <div class="rd-value__icon"><i class="fas fa-award"></i></div>
              <h4>Excelencia</h4>
              <p>Altos estándares de calidad médica y tecnología.</p>
            </div>
            <div class="rd-value">
              <div class="rd-value__icon"><i class="fas fa-shield-heart"></i></div>
              <h4>Integridad</h4>
              <p>Transparencia y ética en todo lo que hacemos.</p>
            </div>
            <div class="rd-value">
              <div class="rd-value__icon"><i class="fas fa-people-group"></i></div>
              <h4>Cercanía</h4>
              <p>Acompañamos a cada familia como propia.</p>
            </div>
          </div>
        </div>
      </section>

      <!-- STATS -->
      <section class="rd-section rd-band">
        <div class="rd-container">
          <div class="rd-stats">
            <div class="rd-stat">
              <div class="rd-stat__num"><span>+40</span></div>
              <span class="rd-stat__label">años de experiencia</span>
            </div>
            <div class="rd-stat">
              <div class="rd-stat__num"><span>+120.000</span></div>
              <span class="rd-stat__label">asegurados</span>
            </div>
            <div class="rd-stat">
              <div class="rd-stat__num"><span>+1.200</span></div>
              <span class="rd-stat__label">especialistas</span>
            </div>
            <div class="rd-stat">
              <div class="rd-stat__num"><span>+500</span></div>
              <span class="rd-stat__label">convenios</span>
            </div>
          </div>
        </div>
      </section>

      <!-- EQUIPO MÉDICO -->
      <section class="rd-section">
        <div class="rd-container">
          <div class="rd-team">
            <div class="rd-team__media">
              <img src="<?php echo $URL?>documentos/home-bg-1.png" alt="Equipo médico de SAMAP" loading="lazy" onerror="this.src='<?php echo $URL?>assets/images/EDER.jpg'">
            </div>
            <div class="rd-team__text">
              <h2 class="rd-title">Un equipo que cuida de vos</h2>
              <p>SAMAP es medicina prepaga del Sanatorio Adventista de Asunción. Desde hace más de tres décadas acompañamos a miles de familias con un servicio médico confiable, humano y accesible.</p>
              <p>Contamos con una red de prestadores de primer nivel en todo el país y un centro médico propio —el Sanatorio Adventista— con tecnología de vanguardia, atención personalizada y un enfoque que contempla el bienestar físico, mental y espiritual. Creemos que la salud se cuida todos los días, con empatía, responsabilidad y cercanía.</p>
            </div>
          </div>
        </div>
      </section>

    </main>
    <!-- Rediseño Nosotros End -->
 
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