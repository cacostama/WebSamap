<?php require_once('funciones/db.php'); ?>
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
    <title>SAMAP - Política de Privacidad</title>
    <!-- #seo -->
    <?php
        $seoTitle = 'Política de Privacidad — SAMAP';
        $seoDesc  = 'Política de privacidad y tratamiento de datos personales de SAMAP, conforme a la Ley N° 6534/20 de Paraguay sobre protección de datos personales.';
        $seoKeywords = 'política de privacidad, protección de datos, Ley 6534, SAMAP, datos personales';
        include 'funciones/seo.php';
    ?>

    <!--  css dependencies start  -->
    <link rel="stylesheet" href="<?php echo $URL?>assets/vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo $URL?>assets/vendor/font-awesome/css/fontawesome.min.css">
    <!--  / css dependencies end  -->

    <!-- main css -->
    <link rel="stylesheet" href="<?php echo $URL?>assets/css/style.css">
    <!-- rediseno css -->
    <link rel="stylesheet" href="<?php echo $URL?>assets/css/rediseno-base.css?v=<?php echo @filemtime(__DIR__.'/assets/css/rediseno-base.css'); ?>">
    <link rel="stylesheet" href="<?php echo $URL?>assets/css/rediseno-nosotros.css?v=<?php echo @filemtime(__DIR__.'/assets/css/rediseno-nosotros.css'); ?>">

    <style>
        .rd-legal { padding: clamp(2rem, 5vw, 4rem) 0; }
        .rd-legal .rd-container { max-width: 820px; }
        .rd-legal h2 { color: var(--samap-azul, #274767); margin: 2rem 0 .75rem; font-size: clamp(1.15rem, 2.4vw, 1.5rem); }
        .rd-legal p, .rd-legal li { color: #444; line-height: 1.7; }
        .rd-legal ul { padding-left: 1.25rem; margin-bottom: 1rem; }
        .rd-legal li { margin-bottom: .4rem; }
        .rd-legal__updated { color: #777; font-size: .9rem; margin-top: 2.5rem; }
    </style>
</head>

<body>

    <!--  Preloader  -->
    <div id="pre_loader"></div>

    <!--header-section start-->
    <?php include 'header.php'; ?>
    <!-- header-section end -->

    <main>
        <!-- HERO -->
        <section class="rd-hero">
            <div class="rd-container">
                <ol class="rd-breadcrumb">
                    <li><a href="<?php echo $URL?>">Inicio</a></li>
                    <li class="is-active">Política de Privacidad</li>
                </ol>
                <h1 class="rd-hero__title">Política de Privacidad</h1>
                <p class="rd-hero__lead">En SAMAP protegemos tus datos personales conforme a la Ley N° 6534/20 de la República del Paraguay.</p>
            </div>
        </section>

        <section class="rd-legal">
            <div class="rd-container">
                <h2>1. Responsable del tratamiento</h2>
                <p>SAMAP S.A. (Medicina Prepaga del Sanatorio Adventista de Asunción), con domicilio en Paí Pérez y Petirossi, Asunción, Paraguay, es responsable del tratamiento de los datos personales recolectados a través de este sitio web.</p>

                <h2>2. Datos que recolectamos</h2>
                <p>Recolectamos únicamente los datos que nos proporcionás de forma voluntaria a través de nuestros formularios de contacto y suscripción, tales como:</p>
                <ul>
                    <li>Nombre y apellido.</li>
                    <li>Número de teléfono.</li>
                    <li>Correo electrónico.</li>
                    <li>El contenido de las consultas que nos envíes.</li>
                </ul>

                <h2>3. Finalidad del tratamiento</h2>
                <p>Tus datos se utilizan exclusivamente para:</p>
                <ul>
                    <li>Responder tus consultas e informarte sobre nuestros planes, coberturas y beneficios.</li>
                    <li>Contactarte para brindarte asesoramiento comercial cuando así lo solicites.</li>
                    <li>Enviarte novedades, siempre que hayas dado tu consentimiento.</li>
                </ul>

                <h2>4. Base legal y consentimiento</h2>
                <p>El tratamiento de tus datos se basa en el consentimiento que prestás al completar y enviar nuestros formularios. Podés retirar tu consentimiento en cualquier momento, sin que ello afecte la licitud del tratamiento previo.</p>

                <h2>5. Conservación y seguridad</h2>
                <p>Conservamos tus datos durante el tiempo necesario para cumplir con las finalidades indicadas y con las obligaciones legales aplicables. Aplicamos medidas técnicas y organizativas razonables para proteger tus datos contra el acceso no autorizado, la pérdida o la alteración.</p>

                <h2>6. Tratamiento de datos de salud</h2>
                <p>Los datos relativos a la salud son considerados datos sensibles. SAMAP los trata con especial cuidado y confidencialidad, conforme a la normativa vigente, y no los comparte con terceros sin tu consentimiento expreso, salvo obligación legal.</p>

                <h2>7. Tus derechos</h2>
                <p>Como titular de los datos, podés solicitar en cualquier momento:</p>
                <ul>
                    <li>El acceso a tus datos personales.</li>
                    <li>La rectificación de datos inexactos o incompletos.</li>
                    <li>La supresión de tus datos cuando ya no sean necesarios.</li>
                    <li>La oposición o limitación al tratamiento.</li>
                </ul>
                <p>Para ejercer estos derechos, escribinos a través de nuestra <a href="<?php echo $URL?>contactos/">página de contacto</a>.</p>

                <h2>8. Cambios en esta política</h2>
                <p>Podemos actualizar esta política para reflejar cambios normativos u operativos. Publicaremos cualquier modificación en esta misma página.</p>

                <p class="rd-legal__updated">Última actualización: <?php echo date('d/m/Y'); ?>.</p>
            </div>
        </section>
    </main>

    <!-- Footer Area Start -->
    <?php include 'footer.php'; ?>
    <!-- Footer Area End -->

    <!-- scroll to top -->
    <a href="#" class="scrollToTop"><i class="fas fa-angle-double-up"></i></a>

    <!-- jquery + main js -->
    <script src="<?php echo $URL?>assets/vendor/jquery/jquery-3.6.3.min.js"></script>
    <script src="<?php echo $URL?>assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo $URL?>assets/js/plugins.js"></script>
    <script src="<?php echo $URL?>assets/js/main.js"></script>

</body>

</html>
