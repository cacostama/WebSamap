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
    <title>SAMAP - Nosotros</title>
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
                        <h1 class="banner__title wow fadeInLeft" data-wow-duration="1.2s">Nosotros</h1> 
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb wow fadeInRight" data-wow-duration="1.2s">
                                <li class="breadcrumb-item"><a href="index.html">Inicio</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Nosotros</li>
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
                        <h2 class="section__header-title wow fadeInUp" data-wow-duration="1.2s">SAMAP</h2>
                        <p class=" wow fadeInDown" data-wow-duration="1.5s" style="text-align:justify" >Más de 43 años cuidando tu salud con compromiso y calidez.
<br><br>
SAMAP es medicina prepaga del Sanatorio Adventista de Asunción, y desde hace más de tres décadas acompañamos a miles de familias con un servicio médico confiable, humano y accesible.
<br><br>
Nuestra historia comenzó con el propósito de brindar una cobertura de salud basada en la excelencia médica y los valores cristianos. Con el paso del tiempo, fuimos creciendo y adaptándonos a los nuevos desafíos del sector, sin perder de vista lo más importante: el cuidado integral de nuestros asegurados.
<br><br>
Hoy, más de 8.000 personas confían en nosotros. Contamos con una red de prestadores de primer nivel en todo el país y un centro médico propio —el Sanatorio Adventista— con tecnología de vanguardia, atención personalizada y un enfoque que contempla el bienestar físico, mental y espiritual.
<br><br>
En SAMAP, creemos que la salud se cuida todos los días, con empatía, responsabilidad y cercanía. Por eso, miramos al futuro con el mismo compromiso que nos impulsó desde el principio, dispuestos a seguir siendo tu mejor respaldo en salud.
<br><br>
</p>
 
                        

    
                    </div>
                </div>
            </div>
            <div class="container">
  <div class="row">
    <!-- MISIÓN -->
    <div class="col-md-6 mb-4">
      <h3 class="section__header-title wow fadeInUp" data-wow-duration="1.2s">MISIÓN</h3>
      <p class="wow fadeInDown" data-wow-duration="1.5s" style="text-align: justify;">
        Ser una institución de salud autosustentable, dedicada a la atención y promoción de la salud física, mental, social y espiritual de nuestros asegurados, pacientes y comunidad, siguiendo el ejemplo del Señor Jesús, el Médico de los médicos.
      </p>
    </div>

    <!-- VISIÓN -->
    <div class="col-md-6 mb-4">
      <h3 class="section__header-title wow fadeInUp" data-wow-duration="1.2s">VISIÓN</h3>
      <p class="wow fadeInDown" data-wow-duration="1.5s" style="text-align: justify;">
        Ser una institución de salud con altos estándares de calidad en la prevención y tratamiento de la enfermedad, promoviendo la salud integral de nuestros asegurados, pacientes y comunidad.
      </p>
    </div>
  </div>
</div>
<div class="container mt-5">
  <h3 class="section__header-title wow fadeInUp text-center" data-wow-duration="1.2s">EQUIPO DIRECTIVO</h3>
  
  <div class="row mt-4">
    <!-- Imagen 1 -->
    <div class="col-md-3 col-sm-6 mb-4 text-center">
      <img src="https://www.samap.com.py/assets/images/EDER.jpg" alt="Miembro 1" class="img-fluid rounded shadow-sm mb-2">
  
    </div>
    
    <!-- Imagen 2 -->
    <div class="col-md-3 col-sm-6 mb-4 text-center">
      <img src="https://www.samap.com.py/assets/images/JUAN CARLOS.jpg" alt="Miembro 2" class="img-fluid rounded shadow-sm mb-2">
    
    </div>

    <!-- Imagen 3 -->
    <div class="col-md-3 col-sm-6 mb-4 text-center">
      <img src="https://www.samap.com.py/assets/images/NESTOR.jpg" alt="Miembro 3" class="img-fluid rounded shadow-sm mb-2">

    </div>

    <!-- Imagen 4 -->
    <div class="col-md-3 col-sm-6 mb-4 text-center">
      <img src="https://www.samap.com.py/assets/images/JORGE INGLES.jpg" alt="Miembro 4" class="img-fluid rounded shadow-sm mb-2">
     
    </div>


    <!-- Imagen 5 -->
    <div class="col-md-3 col-sm-6 mb-4 text-center">
      <img src="https://www.samap.com.py/assets/images/MARCOS.jpg" alt="Miembro 6" class="img-fluid rounded shadow-sm mb-2">
      
    </div>

    <!-- Imagen 6 -->
    <div class="col-md-3 col-sm-6 mb-4 text-center">
      <img src="https://www.samap.com.py/assets/images/NATALY.jpg" alt="Miembro 7" class="img-fluid rounded shadow-sm mb-2">
      
    </div>

    <!-- Imagen 7 -->
    <div class="col-md-3 col-sm-6 mb-4 text-center">
      <img src="https://www.samap.com.py/assets/images/ROCIO.jpg" alt="Miembro 8" class="img-fluid rounded shadow-sm mb-2">
     
    </div>
<!-- Imagen 8 -->
    <div class="col-md-3 col-sm-6 mb-4 text-center">
      <img src="https://www.samap.com.py/assets/images/RAQUEL.jpg" alt="Miembro 8" class="img-fluid rounded shadow-sm mb-2">
     
    </div>
  </div>
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