<?php require_once('funciones/db.php');?>

<?php 

    mysqli_select_db($connect, $database);
    $query_convenios = 'SELECT * FROM tbl_convenios WHERE deleted_at IS NULL ORDER BY id DESC';
    $convenios = mysqli_query($connect, $query_convenios) or die(mysqli_error($link));
    $row_convenios = mysqli_fetch_assoc($convenios);
    $totalRows_convenios = mysqli_num_rows($convenios);

    // ---- Descuentos Exclusivos: aliados agrupados por categoria (DB) ----
    // Las categorias (nombre, icono, color, orden) las administra Marketing
    // desde el admin (tbl_categorias_aliado). Aca solo se leen.
    $query_aliados = "SELECT a.id, a.titulo, a.descuento, a.detalle, a.imagen,
                             c.id AS cat_id, c.nombre AS cat_nombre,
                             c.icono AS cat_icono, c.color AS cat_color, c.orden AS cat_orden
                      FROM tbl_aliados a
                      JOIN tbl_categorias_aliado c ON a.categoria_id = c.id
                      WHERE a.deleted_at IS NULL
                        AND c.deleted_at IS NULL
                        AND c.activo = 1
                      ORDER BY c.orden ASC, c.nombre ASC, a.orden ASC, a.id ASC";
    $aliados = mysqli_query($connect, $query_aliados) or die(mysqli_error($connect));

    // Agrupa por categoria: $rd_categorias[<cat_id>] = [nombre, icono, color, tope, items]
    $rd_categorias = [];
    while ($a = mysqli_fetch_assoc($aliados)) {
        $cid = $a['cat_id'];
        if (!isset($rd_categorias[$cid])) {
            $rd_categorias[$cid] = [
                'nombre' => $a['cat_nombre'],
                'icono'  => $a['cat_icono'] !== '' ? $a['cat_icono'] : 'fa-tags',
                'color'  => $a['cat_color'],
                'tope'   => '',
                'items'  => [],
            ];
        }
        $rd_categorias[$cid]['items'][] = $a;
        if ($rd_categorias[$cid]['tope'] === '' && trim((string) $a['descuento']) !== '') {
            $rd_categorias[$cid]['tope'] = $a['descuento'];
        }
    }


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
    <title>SAMAP - Convenios</title>
    <!-- #seo -->
    <?php
        $seoTitle = 'Convenios — SAMAP Medicina Prepaga';
        $seoDesc  = 'Red de convenios y prestadores de SAMAP. Accedé a descuentos y servicios exclusivos para vos y tu familia en todo el país.';
        $seoKeywords = 'convenios SAMAP, prestadores, red médica, descuentos, beneficios socios';
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
            <h1 class="rd-hero__title">Convenios <span>y Beneficios</span></h1>
            <p class="rd-hero__lead">Accedé a una amplia red de convenios nacionales, asistencia internacional al viajero y descuentos exclusivos pensados para vos y tu familia.</p>
            <nav class="rd-hero__crumb" aria-label="breadcrumb">
                <a href="<?php echo $URL;?>">Inicio</a> <span>/</span> <span>Convenios</span>
            </nav>
        </div>
    </section>
    <!-- Hero End -->

    <!-- Convenios Nacionales (datos DB: tbl_convenios) Start -->
    <section class="rd-section">
        <div class="rd-container">
            <div class="rd-section__header">
                <span class="rd-eyebrow">Red SAMAP</span>
                <h2 class="rd-title">Convenios Nacionales</h2>
                <p class="rd-subtitle">Comercios, instituciones y aliados que acompañan tu bienestar con un enfoque dedicado y profesionales comprometidos.</p>
            </div>

            <div class="rd-grid">
                <?php if ($totalRows_convenios > 0) { do { ?>
                    <article class="rd-card">
                        <div class="rd-card__logo">
                            <img src="<?php echo $URL?>documentos/<?php echo $row_convenios['imagen'];?>" alt="<?php echo $row_convenios['titulo'];?>" loading="lazy" decoding="async">
                        </div>
                        <h3 class="rd-card__title"><?php echo $row_convenios['titulo'];?></h3>
                        <p class="rd-card__text"><?php echo $row_convenios['ciudad'];?></p>
                        <a href="<?php echo $URL;?>beneficio-detalle/titulo/<?php echo str_replace($especiales, $correctos,$row_convenios['titulo']); ?>/cod/<?php echo $row_convenios['id']; ?>/" class="rd-card__link">Más info</a>
                    </article>
                <?php
                    $row_convenios = mysqli_fetch_assoc($convenios);
                    } while ($row_convenios); }   //end horizontal looper
                else { ?>
                    <p class="rd-empty">Pronto sumaremos nuevos convenios.</p>
                <?php } ?>
            </div>

            <div class="rd-cv__cta">
                <a href="<?php echo $URL;?>convenios/" class="rd-btn rd-btn--azul">Ver todos los convenios</a>
            </div>
        </div>
    </section>
    <!-- Convenios Nacionales End -->

    <!-- Beneficios Internacionales (Assist Card) Start -->
    <section class="rd-section rd-section--celeste">
        <div class="rd-container">
            <div class="rd-assist">
                <div class="rd-assist__icon"><i class="fas fa-plane-departure"></i></div>
                <div class="rd-assist__body">
                    <span class="rd-assist__eyebrow">Beneficios Internacionales</span>
                    <h2 class="rd-assist__title">Assist Card — Asistencia al viajero</h2>
                    <p class="rd-assist__text">Viajá tranquilo con cobertura de asistencia médica internacional. Estés donde estés, contás con el respaldo de SAMAP y la red global de Assist Card.</p>
                    <a href="<?php echo $URL;?>contactos/" class="rd-btn rd-btn--azul">Conocer beneficio</a>
                </div>
            </div>
        </div>
    </section>
    <!-- Beneficios Internacionales End -->

    <!-- Descuentos Exclusivos Start -->
    <section class="rd-section">
        <div class="rd-container">
            <div class="rd-section__header">
                <span class="rd-eyebrow">Solo para socios</span>
                <h2 class="rd-title">Descuentos Exclusivos</h2>
                <p class="rd-subtitle">Aprovechá precios preferenciales en una red de comercios y servicios de salud aliados.</p>
            </div>

            <?php if (!empty($rd_categorias)) { ?>
            <p class="rd-disc-hint">Tocá una categoría para ver los comercios adheridos.</p>
            <div class="rd-disc-grid">
                <?php foreach ($rd_categorias as $info) {
                    $cat   = $info['nombre'];
                    $icono = $info['icono'];
                    $color = trim((string) $info['color']);
                    $tope  = $info['tope'];
                    $items = $info['items'];
                    $iconStyle = $color !== '' ? ' style="background:' . htmlspecialchars($color, ENT_QUOTES, 'UTF-8') . '1a;color:' . htmlspecialchars($color, ENT_QUOTES, 'UTF-8') . ';"' : '';
                ?>
                <details class="rd-disc">
                    <summary class="rd-disc__summary">
                        <div class="rd-disc__icon"<?php echo $iconStyle; ?>><i class="fas <?php echo htmlspecialchars($icono, ENT_QUOTES, 'UTF-8'); ?>"></i></div>
                        <h3 class="rd-disc__name"><?php echo htmlspecialchars($cat, ENT_QUOTES, 'UTF-8'); ?></h3>
                        <?php if ($tope !== '') { ?>
                            <span class="rd-disc__pct"><?php echo htmlspecialchars($tope, ENT_QUOTES, 'UTF-8'); ?><small>de descuento</small></span>
                        <?php } else { ?>
                            <span class="rd-disc__pct rd-disc__pct--soft">Beneficios para socios</span>
                        <?php } ?>
                        <span class="rd-disc__count"><?php echo count($items); ?> <?php echo count($items) === 1 ? 'comercio' : 'comercios'; ?> <i class="fas fa-chevron-down rd-disc__chev"></i></span>
                    </summary>
                    <ul class="rd-disc__list">
                        <?php foreach ($items as $it) { ?>
                        <li class="rd-disc__item">
                            <?php if (!empty($it['imagen'])) { ?>
                            <span class="rd-disc__item-logo">
                                <img src="<?php echo $URL?>documentos/aliados/<?php echo htmlspecialchars($it['imagen'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($it['titulo'], ENT_QUOTES, 'UTF-8'); ?>" loading="lazy" decoding="async">
                            </span>
                            <?php } ?>
                            <span class="rd-disc__item-body">
                                <span class="rd-disc__item-name"><?php echo htmlspecialchars($it['titulo'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <?php if (trim((string) $it['descuento']) !== '') { ?>
                                    <span class="rd-disc__item-pct"><?php echo htmlspecialchars($it['descuento'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <?php } ?>
                            </span>
                        </li>
                        <?php } ?>
                    </ul>
                </details>
                <?php } ?>
                <article class="rd-disc rd-disc--more">
                    <div class="rd-disc__icon"><i class="fas fa-ellipsis-h"></i></div>
                    <h3 class="rd-disc__name">Y más</h3>
                    <p class="rd-disc__sub">Nuevos aliados cada mes</p>
                </article>
            </div>
            <?php } ?>

            <div class="rd-cv__cta">
                <a href="<?php echo $URL;?>beneficios/" class="rd-btn rd-btn--ghost">Ver todos los beneficios</a>
            </div>
        </div>
    </section>
    <!-- Descuentos Exclusivos End -->

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