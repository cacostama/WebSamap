<?php 

    mysqli_select_db($connect, $database);
    $query_serviciosCH = 'SELECT * FROM tbl_servicios ORDER BY id DESC';
    $serviciosCH = mysqli_query($connect, $query_serviciosCH) or die(mysqli_error($link));
    $row_serviciosCH = mysqli_fetch_assoc($serviciosCH);
    $totalRows_serviciosCH = mysqli_num_rows($serviciosCH);

?>  
<section class="footer_area">
    <div class="container">
        <div class="row gap-5 gap-lg-0 section">
            <div class="col-sm-6 col-lg-3 col-xl-3">
                <div class="company_contact wow fadeInLeft" data-wow-duration="1.2s">
                    <a href="index.html">
                        <img src="<?php echo $URL?>assets/images/footer_logo.png" alt="Logo" class="logo mb_30">
                    </a>

                    <div class="between_part customGapThree mb_30">
                        <span class="material-symbols-outlined"> mail </span>
                        <p><a href="mailto:asistente.ventas1@samap.com.py" class="__cf_email__" data-cfemail="#">asistente.ventas1@samap.com.py</a></p>
                    </div>
                    <div class="between_part customGapThree mb_30">
                        <span class="material-symbols-outlined"> phone_in_talk </span>
                        <p>021 219 6700</p>
                    </div>
                    <div class="between_part customGapThree mb_30">
                        <span class="material-symbols-outlined"> location_on </span>
                        <p>Paí Pérez y Petirossi, Asunción</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-5 col-lg-2  offset-lg-1">
                <div class="overview wow fadeInUp" data-wow-duration="1.2s">
                    <h5 class="mb_30">General</h5>
                    <ul class="footer_menu">
                        <li><a href="<?php echo $URL?>planes/">Planes</a></li>
                        <li><a href="https://api.whatsapp.com/send?phone=5950982304977">Visaciones</a></li>
                        <li><a href="<?php echo $URL?>nosotros/">Nosotros</a></li>
                        <li><a href="<?php echo $URL?>convenios/">Convenios</a></li>
                        <li><a href="<?php echo $URL?>aliados/">Alianzas</a></li>
                        <li><a href="<?php echo $URL?>contactos/">Contactos</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-sm-6 col-lg-2 col-xl-2 offset-lg-1">
                <div class="services wow fadeInDown" data-wow-duration="1.2s">
                    <h5 class="mb_30">Beneficios</h5>
                    <ul class="footer_menu"
                    	<?php do { ?>
                        	<li><a href="<?php echo $URL?>beneficios/"><?php echo $row_serviciosCH['titulo'];?></a></li>
                        <?php 
							$row_serviciosCH = mysqli_fetch_assoc($serviciosCH);
							} while ($row_serviciosCH);   //end horizontal looper 
						?>
                    </ul>
                </div>
            </div>
            <div class="col-sm-5 col-lg-4 col-xl-2 offset-sm-1s offset-lg-1">
                <div class="policy wow fadeInRight" data-wow-duration="1.2s">
                    <h5 class="mb_30">Links</h5>
                    <ul class="footer_menu">
                        <li><a target="_blank" href="http://sanatorioadventista.com.py/site/">Sanatorio Adventista</a></li>
                        <li><a target="_blank" href="https://up.adventistas.org/ ">Iglesia Adventista del Séptimo Día</a></li>
                        
                    </ul>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="footer_bottom space_between py_30">
                 <p class="copyright text-white">Copyright © <span id="copyYear"></span> <a href="#">SAMAP</a>. Todos los derechos reservados</p>
                 <div class="social">
                   <a href="https://www.facebook.com/samap.py" class="social_icon"> <i class="fa-brands fa-facebook-f"></i></a>
                  
                   <a href="https://www.instagram.com/samap.py"> <i class="fa-brands fa-instagram"></i></a>
                 </div>
            </div>
        </div>
    </div>
</section>