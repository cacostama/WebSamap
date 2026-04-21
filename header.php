<?php
    mysqli_select_db($connect, $database);
    $query_planesH = 'SELECT * FROM tbl_planes WHERE especial=0 ORDER BY id ASC';
    $planesH = mysqli_query($connect, $query_planesH) or die(mysqli_error($link));
    $row_planesH = mysqli_fetch_assoc($planesH);
    $totalRows_planesH = mysqli_num_rows($planesH);
?>
<header class="header_area">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <nav class="navbar navbar-expand-lg nav-shadow" id="#navbar">
                    <a class="navbar-brand" href="<?php echo $URL?>"><img src="<?php echo $URL?>assets/images/logo.png" class="logo" alt="logo"></a>
                    <button class="navbar-toggler collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-content">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="collapse navbar-collapse justify-content-center " id="navbar-content">
                        <ul class="navbar-nav mb-lg-0">
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo $URL?>nosotros/">Nosotros</a>
                            </li>
							<li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"> Planes </a>
                                <ul class="dropdown-menu">
									<?php do{ ?>
                                		<li><a class="dropdown-item" href="<?php echo $URL;?>plan-detalle/titulo/<?php echo str_replace($especiales, $correctos,$row_planesH['titulo']); ?>/cod/<?php echo $row_planesH['id']; ?>/"><?php echo $row_planesH['titulo']; ?></a></li>
                      				<?php 
                                    	$row_planesH = mysqli_fetch_assoc($planesH);
                                    	} while ($row_planesH);   //end horizontal looper 
                                	?>
                                </ul>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="https://api.whatsapp.com/send?phone=5950982304977">Visaciones</a>
                            </li>
                            <!--<li class="nav-item">
                                <a target="_blank" class="nav-link" href="https://7you.app/career/">Trabaje con Nosotros</a>
                            </li>-->
                            <li class="nav-item">
                                <a class="nav-link" href="https://api.whatsapp.com/send?phone=595972520828" target="_blank">Facturación Electrónica</a>
                            </li>

<!--
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"> Facturacion </a>
                                <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="doctor.html">Doctor's</a></li>
                                <li><a class="dropdown-item" href="doctor-details.html">Doctor's Details</a></li>
                                <li><a class="dropdown-item" href="service-details.html">Service Details</a></li>
                                <li><a class="dropdown-item" href="blog-details.html">Blog Details</a></li>
                                <li><a class="dropdown-item" href="gallery.html">Gallery</a></li>
                                <li><a class="dropdown-item" href="coming-soon.html">Coming Soon</a></li>
                                <li><a class="dropdown-item" href="404-error.html">404 Error</a></li>
                                </ul>
                            </li>
-->

                        </ul> 
                    </div>
                    <div class="btn_nav">
                        <a href="<?php echo $URL?>contactos/" class="btn_theme">Contactos</a>
                    </div>
                </nav>
            </div>
        </div>
    </div>
</header>