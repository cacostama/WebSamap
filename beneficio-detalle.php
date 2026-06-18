<?php require_once('funciones/db.php');?>

<?php

    $ID = $_GET['cod'];
    settype($ID, 'integer');

    mysqli_select_db($connect, $database);
    $query_blog = "SELECT * FROM tbl_servicios WHERE id= $ID AND deleted_at IS NULL";
    $blog = mysqli_query($connect, $query_blog) or die(mysqli_error($link));
    $row_blog = mysqli_fetch_assoc($blog);
    $totalRows_blog = mysqli_num_rows($blog);

    // Si no existe el beneficio, volver al listado.
    if (!$row_blog) {
        echo '<script>window.location.href="'.$URL.'beneficios/"</script>';
        exit;
    }

    // ---- Comercios adheridos (si el beneficio referencia una categoria) ----
    // Cuando tbl_servicios.categoria_id apunta a una categoria de aliados, la
    // pagina arma sola la grilla de comercios (logo + descuento + detalle).
    $cat_id = (int) ($row_blog['categoria_id'] ?? 0);
    $comercios = [];
    $cat_nombre = '';
    if ($cat_id > 0) {
        $q_com = "SELECT a.id, a.titulo, a.descuento, a.detalle, a.imagen, c.nombre AS cat_nombre
                  FROM tbl_aliados a
                  JOIN tbl_categorias_aliado c ON a.categoria_id = c.id
                  WHERE a.categoria_id = ".$cat_id."
                    AND a.deleted_at IS NULL
                    AND c.deleted_at IS NULL AND c.activo = 1
                  ORDER BY a.orden ASC, a.id ASC";
        $rs_com = mysqli_query($connect, $q_com) or die(mysqli_error($connect));
        while ($r = mysqli_fetch_assoc($rs_com)) {
            $cat_nombre = $r['cat_nombre'];
            $comercios[] = $r;
        }
    }

    // Otros beneficios (sidebar).
    mysqli_select_db($connect, $database);
    $query_blogUltimos = 'SELECT * FROM tbl_servicios WHERE deleted_at IS NULL ORDER BY id DESC LIMIT 7';
    $blogUltimos = mysqli_query($connect, $query_blogUltimos) or die(mysqli_error($link));

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
    <title>SAMAP - <?php echo htmlspecialchars($row_blog['titulo'], ENT_QUOTES, 'UTF-8'); ?></title>
    <!-- #seo -->
    <?php
        $seoTitle = htmlspecialchars($row_blog['titulo'], ENT_QUOTES, 'UTF-8').' — SAMAP Medicina Prepaga';
        $seoDesc  = 'Beneficios y descuentos exclusivos para socios de SAMAP. Conocé los comercios adheridos y aprovechá precios preferenciales.';
        $seoKeywords = 'beneficios SAMAP, descuentos socios, comercios adheridos';
        @include 'funciones/seo.php';
    ?>

    <!--  css dependencies start  -->
    <link rel="stylesheet" href="<?php echo $URL?>assets/vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo $URL?>assets/vendor/font-awesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="<?php echo $URL?>assets/vendor/animate/animate.css">
    <!--  / css dependencies end  -->

    <!-- main css -->
    <link rel="stylesheet" href="<?php echo $URL?>assets/css/style.css">
    <!-- rediseno css -->
    <link rel="stylesheet" href="<?php echo $URL?>assets/css/rediseno-base.css?v=<?php echo @filemtime(__DIR__.'/assets/css/rediseno-base.css'); ?>">
    <link rel="stylesheet" href="<?php echo $URL?>assets/css/rediseno-convenios.css?v=<?php echo @filemtime(__DIR__.'/assets/css/rediseno-convenios.css'); ?>">
    <link rel="stylesheet" href="<?php echo $URL?>assets/css/rediseno-beneficio-detalle.css?v=<?php echo @filemtime(__DIR__.'/assets/css/rediseno-beneficio-detalle.css'); ?>">

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
            <h1 class="rd-hero__title"><?php echo htmlspecialchars($row_blog['titulo'], ENT_QUOTES, 'UTF-8'); ?></h1>
            <?php if (!empty($row_blog['intro'])) { ?>
                <p class="rd-hero__lead"><?php echo htmlspecialchars($row_blog['intro'], ENT_QUOTES, 'UTF-8'); ?></p>
            <?php } ?>
            <nav class="rd-hero__crumb" aria-label="breadcrumb">
                <a href="<?php echo $URL;?>">Inicio</a> <span>/</span>
                <a href="<?php echo $URL;?>beneficios/">Beneficios</a> <span>/</span>
                <span><?php echo htmlspecialchars($row_blog['titulo'], ENT_QUOTES, 'UTF-8'); ?></span>
            </nav>
        </div>
    </section>
    <!-- Hero End -->

    <section class="rd-section">
        <div class="rd-container rd-bd">

            <!-- Columna principal -->
            <div class="rd-bd__main">

                <?php if (!empty($row_blog['imagen'])) { ?>
                <div class="rd-bd__cover">
                    <img src="<?php echo $URL?>documentos/servicios/<?php echo htmlspecialchars($row_blog['imagen'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($row_blog['titulo'], ENT_QUOTES, 'UTF-8'); ?>" loading="lazy" decoding="async">
                </div>
                <?php } ?>

                <?php if (!empty($comercios)) { ?>
                    <!-- Grilla de comercios adheridos (data-driven) -->
                    <div class="rd-bd__intro">
                        <span class="rd-eyebrow">Comercios adheridos</span>
                        <h2 class="rd-title rd-title--left"><?php echo htmlspecialchars($cat_nombre, ENT_QUOTES, 'UTF-8'); ?> con descuento</h2>
                        <p class="rd-subtitle rd-subtitle--left">Presentá tu credencial de socio SAMAP en cualquiera de estos comercios y accedé a precios preferenciales.</p>
                    </div>

                    <div class="rd-com-grid">
                        <?php foreach ($comercios as $com) { ?>
                        <article class="rd-com">
                            <div class="rd-com__head">
                                <div class="rd-com__logo">
                                    <?php if (!empty($com['imagen'])) { ?>
                                        <img src="<?php echo $URL?>documentos/aliados/<?php echo htmlspecialchars($com['imagen'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($com['titulo'], ENT_QUOTES, 'UTF-8'); ?>" loading="lazy" decoding="async">
                                    <?php } else { ?>
                                        <i class="fas fa-store"></i>
                                    <?php } ?>
                                </div>
                                <div class="rd-com__head-text">
                                    <h3 class="rd-com__name"><?php echo htmlspecialchars($com['titulo'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                    <?php if (trim((string) $com['descuento']) !== '') { ?>
                                        <span class="rd-com__badge"><?php echo htmlspecialchars($com['descuento'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    <?php } ?>
                                </div>
                            </div>
                            <?php if (trim((string) $com['detalle']) !== '') { ?>
                                <div class="rd-com__detail"><?php echo $com['detalle']; ?></div>
                            <?php } ?>
                        </article>
                        <?php } ?>
                    </div>
                <?php } else { ?>
                    <!-- Fallback: texto enriquecido del beneficio -->
                    <div class="rd-bd__richtext">
                        <?php echo $row_blog['detalle']; ?>
                    </div>
                <?php } ?>

                <div class="rd-bd__cta">
                    <a href="<?php echo $URL;?>contactos/" class="rd-btn rd-btn--azul">Quiero más información</a>
                    <a href="<?php echo $URL;?>beneficios/" class="rd-btn rd-btn--ghost">Ver todos los beneficios</a>
                </div>
            </div>

            <!-- Sidebar: Otros Beneficios -->
            <aside class="rd-bd__aside">
                <div class="rd-aside-card">
                    <h4 class="rd-aside-card__title">Otros Beneficios</h4>
                    <ul class="rd-aside-list">
                        <?php while ($u = mysqli_fetch_assoc($blogUltimos)) {
                            if ((int) $u['id'] === (int) $row_blog['id']) { continue; }
                            $link = $URL.'beneficio-detalle/titulo/'.str_replace($especiales, $correctos, $u['titulo']).'/cod/'.$u['id'].'/';
                        ?>
                        <li class="rd-aside-item">
                            <a href="<?php echo $link; ?>">
                                <span class="rd-aside-item__thumb">
                                    <?php if (!empty($u['imagen'])) { ?>
                                        <img src="<?php echo $URL?>documentos/servicios/<?php echo htmlspecialchars($u['imagen'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($u['titulo'], ENT_QUOTES, 'UTF-8'); ?>" loading="lazy" decoding="async">
                                    <?php } else { ?>
                                        <i class="fas fa-gift"></i>
                                    <?php } ?>
                                </span>
                                <span class="rd-aside-item__name"><?php echo htmlspecialchars($u['titulo'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <i class="fas fa-chevron-right rd-aside-item__arrow"></i>
                            </a>
                        </li>
                        <?php } ?>
                    </ul>
                </div>
            </aside>

        </div>
    </section>

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
    <script data-cfasync="false" src="/cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script><script src="<?php echo $URL?>assets/vendor/jquery/jquery-3.6.3.min.js"></script>
    <script src="<?php echo $URL?>assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo $URL?>assets/vendor/wow/wow.min.js"></script>
    <!--  / js dependencies end  -->

    <!-- plugins js -->
    <script src="<?php echo $URL?>assets/js/plugins.js"></script>
    <!-- main js -->
    <script src="<?php echo $URL?>assets/js/main.js"></script>

</body>

</html>
