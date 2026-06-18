<?php

    mysqli_select_db($connect, $database);
    $query_serviciosCH = 'SELECT * FROM tbl_servicios WHERE deleted_at IS NULL ORDER BY id DESC';
    $serviciosCH = mysqli_query($connect, $query_serviciosCH) or die(mysqli_error($link));
    $row_serviciosCH = mysqli_fetch_assoc($serviciosCH);
    $totalRows_serviciosCH = mysqli_num_rows($serviciosCH);

?>

<!-- ===== CTA final ===== -->
<section class="rd-footer-cta">
    <div class="rd-footer-cta__inner">
        <h2>¿Querés más información?</h2>
        <p>Contactanos y un asesor te ayuda a encontrar el plan ideal para vos y tu familia.</p>
        <div class="rd-footer-cta__btns">
            <a class="rd-btn rd-btn--wa" href="https://api.whatsapp.com/send?phone=5950982304977" target="_blank" rel="noopener">
                <i class="fab fa-whatsapp"></i> WhatsApp
            </a>
            <a class="rd-btn rd-btn--azul" href="tel:+595212196700">
                <i class="fas fa-phone"></i> Llamar
            </a>
            <a class="rd-btn rd-btn--azul" href="<?php echo $URL?>contactos/">
                <i class="fas fa-envelope"></i> Contactar
            </a>
        </div>
    </div>
</section>

<!-- ===== Footer ===== -->
<footer class="rd-footer">
    <div class="rd-footer__inner">
        <div class="rd-footer__brand">
            <a href="<?php echo $URL?>">
                <img src="<?php echo $URL?>assets/images/footer_logo.png" alt="SAMAP" loading="lazy" decoding="async">
            </a>
            <ul class="rd-footer__contact">
                <li>
                    <i class="fas fa-phone"></i>
                    <a href="tel:+595212196700">021 219 6700</a>
                </li>
                <li>
                    <i class="fas fa-map-marker-alt"></i>
                    <span>Paí Pérez y Petirossi, Asunción</span>
                </li>
            </ul>
        </div>

        <div class="rd-footer__col">
            <h5>Accesos rápidos</h5>
            <ul class="rd-footer__menu">
                <li><a href="<?php echo $URL?>planes/">Planes</a></li>
                <li><a href="<?php echo $URL?>guiamedica/">Guía Médica</a></li>
                <li><a href="<?php echo $URL?>convenios/">Convenios</a></li>
                <li><a href="<?php echo $URL?>aliados/">Alianzas</a></li>
                <li><a href="<?php echo $URL?>nosotros/">Nosotros</a></li>
                <li><a href="<?php echo $URL?>contactos/">Contacto</a></li>
            </ul>
        </div>

        <div class="rd-footer__col">
            <h5>Nuestras redes</h5>
            <div class="rd-footer__social">
                <a href="https://www.facebook.com/samap.py" target="_blank" rel="noopener" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                <a href="https://www.instagram.com/samap.py" target="_blank" rel="noopener" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                <a href="https://api.whatsapp.com/send?phone=5950982304977" target="_blank" rel="noopener" aria-label="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
            </div>
            <ul class="rd-footer__menu" style="margin-top:1.5rem">
                <li><a href="http://sanatorioadventista.com.py/site/" target="_blank" rel="noopener">Sanatorio Adventista</a></li>
                <li><a href="https://up.adventistas.org/" target="_blank" rel="noopener">Iglesia Adventista del Séptimo Día</a></li>
            </ul>
        </div>
    </div>

    <div class="rd-footer__bottom">
        <div class="rd-footer__bottom-inner">
            <p class="copyright">Copyright © <span id="copyYear"></span> <a href="<?php echo $URL?>">SAMAP</a>. Todos los derechos reservados · <a href="<?php echo $URL?>privacidad/">Política de Privacidad</a></p>
        </div>
    </div>
</footer>

<!-- WhatsApp flotante (todas las páginas) -->
<a class="rd-wa-float" href="https://api.whatsapp.com/send?phone=5950982304977" target="_blank" rel="noopener" aria-label="Escribinos por WhatsApp">
    <i class="fab fa-whatsapp"></i>
</a>
