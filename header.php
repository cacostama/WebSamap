<?php
    mysqli_select_db($connect, $database);
    $query_planesH = 'SELECT * FROM tbl_planes WHERE especial=0 ORDER BY id ASC';
    $planesH = mysqli_query($connect, $query_planesH) or die(mysqli_error($link));
    $row_planesH = mysqli_fetch_assoc($planesH);
    $totalRows_planesH = mysqli_num_rows($planesH);
?>
<link rel="stylesheet" href="<?php echo $URL?>assets/css/rediseno-base.css">
<header class="rd-nav" id="rdNav">
    <div class="rd-nav__inner">
        <a class="rd-nav__brand" href="<?php echo $URL?>">
            <img src="<?php echo $URL?>assets/images/logo.png" alt="SAMAP">
        </a>

        <button class="rd-nav__toggle" type="button" id="rdNavToggle" aria-label="Abrir menú" aria-controls="rdNavMenu" aria-expanded="false">
            <i class="fas fa-bars"></i>
        </button>

        <ul class="rd-nav__menu" id="rdNavMenu">
            <li><a class="rd-nav__link" href="<?php echo $URL?>">Inicio</a></li>

            <li class="rd-dropdown">
                <a class="rd-nav__link" href="<?php echo $URL?>planes/" role="button" aria-haspopup="true" aria-expanded="false">
                    Planes <i class="fas fa-chevron-down" style="font-size:.7em"></i>
                </a>
                <ul class="rd-dropdown__menu">
                    <?php do{ ?>
                        <li><a class="rd-dropdown__item" href="<?php echo $URL;?>plan-detalle/titulo/<?php echo str_replace($especiales, $correctos,$row_planesH['titulo']); ?>/cod/<?php echo $row_planesH['id']; ?>/"><?php echo $row_planesH['titulo']; ?></a></li>
                    <?php
                        $row_planesH = mysqli_fetch_assoc($planesH);
                    } while ($row_planesH);   //end horizontal looper
                    ?>
                </ul>
            </li>

            <li><a class="rd-nav__link" href="<?php echo $URL?>guiamedica/">Guía Médica</a></li>
            <li><a class="rd-nav__link" href="<?php echo $URL?>convenios/">Convenios</a></li>
            <li><a class="rd-nav__link" href="<?php echo $URL?>nosotros/">Nosotros</a></li>
            <li><a class="rd-nav__link" href="<?php echo $URL?>contactos/">Contacto</a></li>

            <li class="rd-nav__actions">
                <a href="<?php echo $URL?>contactos/" class="rd-btn rd-btn--azul">Solicitar información</a>
                <a class="rd-nav__wa" href="https://api.whatsapp.com/send?phone=5950982304977" target="_blank" rel="noopener" aria-label="WhatsApp">
                    <i class="fab fa-whatsapp"></i>
                </a>
            </li>
        </ul>

        <div class="rd-nav__actions">
            <a href="<?php echo $URL?>contactos/" class="rd-btn rd-btn--azul">Solicitar información</a>
            <a class="rd-nav__wa" href="https://api.whatsapp.com/send?phone=5950982304977" target="_blank" rel="noopener" aria-label="WhatsApp">
                <i class="fab fa-whatsapp"></i>
            </a>
        </div>

        <div class="rd-nav__overlay" id="rdNavOverlay"></div>
    </div>
</header>

<script>
(function () {
    var nav = document.getElementById('rdNav');
    var toggle = document.getElementById('rdNavToggle');
    var overlay = document.getElementById('rdNavOverlay');
    if (!nav || !toggle) return;
    function close() {
        nav.classList.remove('is-open');
        document.body.classList.remove('rd-nav-open');
        toggle.setAttribute('aria-expanded', 'false');
    }
    toggle.addEventListener('click', function () {
        var open = nav.classList.toggle('is-open');
        document.body.classList.toggle('rd-nav-open', open);
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
    if (overlay) overlay.addEventListener('click', close);
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') close();
    });
})();
</script>
