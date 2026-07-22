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
    <title>SAMAP - Planes</title>
    <!-- #seo -->
    <?php
        $seoTitle = 'Planes de Salud — SAMAP Medicina Prepaga';
        $seoDesc  = 'Conocé los planes de salud de SAMAP: individuales, familiares y corporativos. Cobertura integral con la mejor red de prestadores del país.';
        $seoKeywords = 'planes de salud, medicina prepaga, cobertura médica, SAMAP, plan familiar, plan individual';
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
    <link rel="stylesheet" href="<?php echo $URL?>assets/css/style.css?v=<?php echo @filemtime(__DIR__."/assets/css/style.css"); ?>">
    <!-- rediseno css -->
    <link rel="stylesheet" href="<?php echo $URL?>assets/css/rediseno-base.css?v=<?php echo @filemtime(__DIR__.'/assets/css/rediseno-base.css'); ?>">
    <link rel="stylesheet" href="<?php echo $URL?>assets/css/rediseno-convenios.css?v=<?php echo @filemtime(__DIR__.'/assets/css/rediseno-convenios.css'); ?>">

</head>

<body>
    
    <!--  Preloader  -->
    <div id="pre_loader"></div>

    <!--header-section start-->
    <?php include 'header.php'; ?>
    <!-- header-section end -->

    <div class="rd-cv">
    <!-- Hero Start -->
    <section class="rd-hero">
        <div class="rd-container rd-hero__inner">
            <h1 class="rd-hero__title">Nuestros <span>Planes</span></h1>
            <p class="rd-hero__lead">Una amplia gama de planes pensados para cada etapa de tu vida: individuales, familiares y corporativos. Encontrá la cobertura ideal con la mejor red de prestadores.</p>
            <nav class="rd-hero__crumb" aria-label="breadcrumb">
                <a href="<?php echo $URL;?>">Inicio</a> <span>/</span> <span>Planes</span>
            </nav>
        </div>
    </section>
    <!-- Hero End -->

    <!-- Planes (datos DB: tbl_planes) Start -->
    <section class="rd-section">
        <div class="rd-container">
            <div class="rd-section__header">
                <span class="rd-eyebrow">Cuidándote siempre</span>
                <h2 class="rd-title">Un plan para cada necesidad</h2>
                <p class="rd-subtitle">En SAMAP entendemos que cada persona es única. Por eso ofrecemos distintas opciones para que encuentres el plan que mejor se ajuste a vos y a tu familia.</p>
            </div>

            <div class="rd-grid">
                <?php if ($totalRows_planes > 0) { do { ?>
                    <article class="rd-card">
                        <div class="rd-card__logo">
                            <img src="<?php echo $URL?>documentos/<?php echo $row_planes['imagen']; ?>" alt="<?php echo $row_planes['titulo']; ?>">
                        </div>
                        <h3 class="rd-card__title"><?php echo $row_planes['titulo']; ?></h3>
                        <p class="rd-card__text"><?php echo $row_planes['detalle']; ?></p>
                        <?php $waPlan = trim($row_planes['url']); ?>
                        <a target="_blank" rel="noopener" href="<?php echo ($waPlan !== '' && $waPlan !== 'https://') ? htmlspecialchars($waPlan) : 'https://wa.link/gza9hk'; ?>" class="rd-card__link">Consultar</a>
                        <?php $anexoPlan = trim($row_planes['anexo']); if ($anexoPlan !== '' && is_file(__DIR__ . '/documentos/planes/' . $anexoPlan)) { ?>
                            <a href="<?php echo $URL?>documentos/planes/<?php echo rawurlencode($anexoPlan); ?>" download class="rd-card__anexo">
                                <i class="fas fa-file-pdf"></i> Descargar anexo
                            </a>
                        <?php } ?>
                    </article>
                <?php
                    $row_planes = mysqli_fetch_assoc($planes);
                    } while ($row_planes); }
                else { ?>
                    <p class="rd-empty">Pronto publicaremos nuestros planes.</p>
                <?php } ?>
            </div>
        </div>
    </section>
    <!-- Planes End -->
    </div><!-- /.rd-cv -->

 
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
    <script src="<?php echo $URL?>assets/vendor/jquery/jquery-3.6.3.min.js"></script>
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
    <script src="<?php echo $URL?>assets/js/plugins.js?v=<?php echo @filemtime(__DIR__."/assets/js/plugins.js"); ?>"></script>
    <!-- main js -->
    <script src="<?php echo $URL?>assets/js/main.js"></script>

</body>

</html>