<?php require_once('funciones/db.php');?>

<?php 

    mysqli_select_db($connect, $database);
    $query_especialidad = 'SELECT * FROM tbl_especialidad WHERE estado=1 AND deleted_at IS NULL ORDER BY nombre ASC';
    $especialidad = mysqli_query($connect, $query_especialidad) or die(mysqli_error($link));
    $row_especialidad = mysqli_fetch_assoc($especialidad);
    $totalRows_especialidad = mysqli_num_rows($especialidad);

    mysqli_select_db($connect, $database);
    $query_ciudad = 'SELECT * FROM tbl_ciudad WHERE estado=1 AND deleted_at IS NULL ORDER BY nombre ASC';
    $ciudad = mysqli_query($connect, $query_ciudad) or die(mysqli_error($link));
    $row_ciudad = mysqli_fetch_assoc($ciudad);
    $totalRows_ciudad = mysqli_num_rows($ciudad);

    // Inicializacion de variables para evitar warnings cuando no hay busqueda
    $VARespecialidad = '';
    $VACiudad = '';
    $VAMedico = '';
    $row_especialidad2 = null;
    $row_ciudad2 = null;
    $editFormAction = $_SERVER['PHP_SELF'] ?? '';

    if ((isset($_POST["MM_search"])) && ($_POST["MM_search"] == "form2")) {

        // #2 SQLi: los IDs van a SQL en contexto numerico (sin comillas), donde el
        // escapeo de db.php no protege. Casteamos a entero; mantenemos '' cuando
        // no hay seleccion para no romper la logica de ramas de abajo (!= "").
        $VARespecialidad = ($_POST["Especialidad"] ?? '') !== '' ? (int) $_POST["Especialidad"] : '';
        $VACiudad        = ($_POST["Ciudad"] ?? '') !== '' ? (int) $_POST["Ciudad"] : '';
        $VAMedico        = $_POST["medico"] ?? ''; // usado solo en LIKE entre comillas (ya escapado por db.php)

        if ($VARespecialidad!='') {
            mysqli_select_db($connect, $database);
            $query_especialidad2 = "SELECT * FROM tbl_especialidad WHERE estado=1 AND deleted_at IS NULL AND id=$VARespecialidad ORDER BY nombre ASC";
            $especialidad2 = mysqli_query($connect, $query_especialidad2) or die(mysqli_error($link));
            $row_especialidad2 = mysqli_fetch_assoc($especialidad2);
            $totalRows_especialidad2 = mysqli_num_rows($especialidad2);
        }
        
        if ($VACiudad!='') {
            mysqli_select_db($connect, $database);
            $query_ciudad2 = "SELECT * FROM tbl_ciudad WHERE estado=1 AND deleted_at IS NULL AND id=$VACiudad ORDER BY nombre ASC";
            $ciudad2 = mysqli_query($connect, $query_ciudad2) or die(mysqli_error($link));
            $row_ciudad2 = mysqli_fetch_assoc($ciudad2);
            $totalRows_ciudad2 = mysqli_num_rows($ciudad2);
        }

        if ($VARespecialidad!= "" && $VACiudad=="" && $VAMedico=="") {
            
            mysqli_select_db($connect, $database);
            $query_guia = "SELECT
                                a.id,
                                a.idEspecialidad,
                                a.idSanatorios,
                                a.titulo,
                                a.nombre AS medico,
                                a.cv,
                                b.nombre AS especialidad,
                                c.nombre AS sanatorio,
                                c.direccion,
                                c.telefono,
                                d.nombre AS ciudad
                            FROM
                                tbl_guiamedica a
                            LEFT JOIN tbl_especialidad b ON
                                a.idEspecialidad = b.id
                            LEFT JOIN tbl_sanatorio c ON
                                a.idSanatorios = c.id
                            LEFT JOIN tbl_ciudad d ON
                                c.idCiudad = d.id
                            WHERE a.deleted_at IS NULL AND a.idEspecialidad= $VARespecialidad ORDER BY
    b.nombre ASC,
    CASE WHEN c.nombre LIKE '%Sanatorio Adventista%' THEN 0 ELSE 1 END,
    c.nombre ASC";
            $guia = mysqli_query($connect, $query_guia) or die(mysqli_error($link));
            $row_guia = mysqli_fetch_assoc($guia);
             $totalRows_guia = mysqli_num_rows($guia);

        } else if ($VARespecialidad!= "" && $VACiudad!="" && $VAMedico=="") {

            mysqli_select_db($connect, $database);
            $query_guia = "SELECT
                                a.id,
                                a.idEspecialidad,
                                a.idSanatorios,
                                a.titulo,
                                a.nombre AS medico,
                                a.cv,
                                b.nombre AS especialidad,
                                c.nombre AS sanatorio,
                                c.direccion,
                                c.telefono,
                                d.nombre AS ciudad
                            FROM
                                tbl_guiamedica a
                            LEFT JOIN tbl_especialidad b ON
                                a.idEspecialidad = b.id
                            LEFT JOIN tbl_sanatorio c ON
                                a.idSanatorios = c.id
                            LEFT JOIN tbl_ciudad d ON
                                c.idCiudad = d.id
                            WHERE a.deleted_at IS NULL AND a.idEspecialidad= $VARespecialidad AND c.idCiudad= $VACiudad ORDER BY
    b.nombre ASC,
    CASE WHEN c.nombre LIKE '%Sanatorio Adventista%' THEN 0 ELSE 1 END,
    c.nombre ASC";
            $guia = mysqli_query($connect, $query_guia) or die(mysqli_error($link));
            $row_guia = mysqli_fetch_assoc($guia);
            $totalRows_guia = mysqli_num_rows($guia);

        } else if ($VARespecialidad!= "" && $VACiudad!="" && $VAMedico!="") {

            mysqli_select_db($connect, $database);
            $query_guia = "SELECT
                                a.id,
                                a.idEspecialidad,
                                a.idSanatorios,
                                a.titulo,
                                a.nombre AS medico,
                                a.cv,
                                b.nombre AS especialidad,
                                c.nombre AS sanatorio,
                                c.direccion,
                                c.telefono,
                                d.nombre AS ciudad
                            FROM
                                tbl_guiamedica a
                            LEFT JOIN tbl_especialidad b ON
                                a.idEspecialidad = b.id
                            LEFT JOIN tbl_sanatorio c ON
                                a.idSanatorios = c.id
                            LEFT JOIN tbl_ciudad d ON
                                c.idCiudad = d.id
                            WHERE a.deleted_at IS NULL AND a.idEspecialidad= $VARespecialidad AND c.idCiudad= $VACiudad AND a.nombre like '%$VAMedico%' ORDER BY
    b.nombre ASC,
    CASE WHEN c.nombre LIKE '%Sanatorio Adventista%' THEN 0 ELSE 1 END,
    c.nombre ASC";
            $guia = mysqli_query($connect, $query_guia) or die(mysqli_error($link));
            $row_guia = mysqli_fetch_assoc($guia);
            $totalRows_guia = mysqli_num_rows($guia);

        } else if ($VARespecialidad== "" && $VACiudad!="" && $VAMedico=="") {

            mysqli_select_db($connect, $database);
            $query_guia = "SELECT
                                a.id,
                                a.idEspecialidad,
                                a.idSanatorios,
                                a.titulo,
                                a.nombre AS medico,
                                a.cv,
                                b.nombre AS especialidad,
                                c.nombre AS sanatorio,
                                c.direccion,
                                c.telefono,
                                d.nombre AS ciudad
                            FROM
                                tbl_guiamedica a
                            LEFT JOIN tbl_especialidad b ON
                                a.idEspecialidad = b.id
                            LEFT JOIN tbl_sanatorio c ON
                                a.idSanatorios = c.id
                            LEFT JOIN tbl_ciudad d ON
                                c.idCiudad = d.id
                            WHERE a.deleted_at IS NULL AND c.idCiudad= $VACiudad ORDER BY
    b.nombre ASC,
    CASE WHEN c.nombre LIKE '%Sanatorio Adventista%' THEN 0 ELSE 1 END,
    c.nombre ASC";
            $guia = mysqli_query($connect, $query_guia) or die(mysqli_error($link));
            $row_guia = mysqli_fetch_assoc($guia);
            $totalRows_guia = mysqli_num_rows($guia);


        } else if ($VARespecialidad== "" && $VACiudad!="" && $VAMedico!="") {

            mysqli_select_db($connect, $database);
            $query_guia = "SELECT
                                a.id,
                                a.idEspecialidad,
                                a.idSanatorios,
                                a.titulo,
                                a.nombre AS medico,
                                a.cv,
                                b.nombre AS especialidad,
                                c.nombre AS sanatorio,
                                c.direccion,
                                c.telefono,
                                d.nombre AS ciudad
                            FROM
                                tbl_guiamedica a
                            LEFT JOIN tbl_especialidad b ON
                                a.idEspecialidad = b.id
                            LEFT JOIN tbl_sanatorio c ON
                                a.idSanatorios = c.id
                            LEFT JOIN tbl_ciudad d ON
                                c.idCiudad = d.id
                            WHERE a.deleted_at IS NULL AND c.idCiudad= $VACiudad AND a.nombre like '%$VAMedico%' ORDER BY
    b.nombre ASC,
    CASE WHEN c.nombre LIKE '%Sanatorio Adventista%' THEN 0 ELSE 1 END,
    c.nombre ASC";
            $guia = mysqli_query($connect, $query_guia) or die(mysqli_error($link));
            $row_guia = mysqli_fetch_assoc($guia);
            $totalRows_guia = mysqli_num_rows($guia);

        } else if ($VARespecialidad== "" && $VACiudad=="" && $VAMedico!="") {

            mysqli_select_db($connect, $database);
            $query_guia = "SELECT
                                a.id,
                                a.idEspecialidad,
                                a.idSanatorios,
                                a.titulo,
                                a.nombre AS medico,
                                a.cv,
                                b.nombre AS especialidad,
                                c.nombre AS sanatorio,
                                c.direccion,
                                c.telefono,
                                d.nombre AS ciudad
                            FROM
                                tbl_guiamedica a
                            LEFT JOIN tbl_especialidad b ON
                                a.idEspecialidad = b.id
                            LEFT JOIN tbl_sanatorio c ON
                                a.idSanatorios = c.id
                            LEFT JOIN tbl_ciudad d ON
                                c.idCiudad = d.id
                            WHERE a.deleted_at IS NULL AND a.nombre like '%$VAMedico%' ORDER BY
    b.nombre ASC,
    CASE WHEN c.nombre LIKE '%Sanatorio Adventista%' THEN 0 ELSE 1 END,
    c.nombre ASC";
            $guia = mysqli_query($connect, $query_guia) or die(mysqli_error($link));
            $row_guia = mysqli_fetch_assoc($guia);
            $totalRows_guia = mysqli_num_rows($guia);

        } else if ($VARespecialidad!= "" && $VACiudad=="" && $VAMedico!="") {

            mysqli_select_db($connect, $database);
            $query_guia = "SELECT
                                a.id,
                                a.idEspecialidad,
                                a.idSanatorios,
                                a.titulo,
                                a.nombre AS medico,
                                a.cv,
                                b.nombre AS especialidad,
                                c.nombre AS sanatorio,
                                c.direccion,
                                c.telefono,
                                d.nombre AS ciudad
                            FROM
                                tbl_guiamedica a
                            LEFT JOIN tbl_especialidad b ON
                                a.idEspecialidad = b.id
                            LEFT JOIN tbl_sanatorio c ON
                                a.idSanatorios = c.id
                            LEFT JOIN tbl_ciudad d ON
                                c.idCiudad = d.id
                            WHERE a.deleted_at IS NULL AND a.idEspecialidad= $VARespecialidad AND a.nombre like '%$VAMedico%' ORDER BY
    b.nombre ASC,
    CASE WHEN c.nombre LIKE '%Sanatorio Adventista%' THEN 0 ELSE 1 END,
    c.nombre ASC";
            $guia = mysqli_query($connect, $query_guia) or die(mysqli_error($link));
            $row_guia = mysqli_fetch_assoc($guia);
            $totalRows_guia = mysqli_num_rows($guia);
        } else{

            mysqli_select_db($connect, $database);
            $query_guia = 'SELECT
                                a.id,
                                a.idEspecialidad,
                                a.idSanatorios,
                                a.titulo,
                                a.nombre AS medico,
                                a.cv,
                                b.nombre AS especialidad,
                                c.nombre AS sanatorio,
                                c.direccion,
                                c.telefono,
                                d.nombre AS ciudad
                            FROM
                                tbl_guiamedica a
                            LEFT JOIN tbl_especialidad b ON
                                a.idEspecialidad = b.id
                            LEFT JOIN tbl_sanatorio c ON
                                a.idSanatorios = c.id
                            LEFT JOIN tbl_ciudad d ON
                                c.idCiudad = d.id WHERE a.deleted_at IS NULL ORDER BY
    b.nombre ASC,
    CASE WHEN c.nombre LIKE "%Sanatorio Adventista%" THEN 0 ELSE 1 END,
    c.nombre ASC';
            $guia = mysqli_query($connect, $query_guia) or die(mysqli_error($link));
            $row_guia = mysqli_fetch_assoc($guia);
            $totalRows_guia = mysqli_num_rows($guia);
         
        }

    } else{

        mysqli_select_db($connect, $database);
        $query_guia = 'SELECT
                            a.id,
                            a.idEspecialidad,
                            a.idSanatorios,
                            a.titulo,
                            a.nombre AS medico,
                            a.cv,
                            b.nombre AS especialidad,
                            c.nombre AS sanatorio,
                            c.direccion,
                            c.telefono,
                            d.nombre AS ciudad
                        FROM
                            tbl_guiamedica a
                        LEFT JOIN tbl_especialidad b ON
                            a.idEspecialidad = b.id
                        LEFT JOIN tbl_sanatorio c ON
                            a.idSanatorios = c.id
                        LEFT JOIN tbl_ciudad d ON
                            c.idCiudad = d.id WHERE a.deleted_at IS NULL ORDER BY
    b.nombre ASC,
    CASE WHEN c.nombre LIKE "%Sanatorio Adventista%" THEN 0 ELSE 1 END,
    c.nombre ASC';
        $guia = mysqli_query($connect, $query_guia) or die(mysqli_error($link));
        $row_guia = mysqli_fetch_assoc($guia);
        $totalRows_guia = mysqli_num_rows($guia);

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
    <title>SAMAP - Guía Médica</title>
    <!-- #seo -->
    <?php
        $seoTitle = 'Guía Médica — SAMAP';
        $seoDesc  = 'Buscá profesionales y especialidades en la guía médica de SAMAP. Encontrá tu médico por especialidad, ciudad y sanatorio. Descargá la guía en PDF.';
        $seoKeywords = 'guía médica SAMAP, médicos, especialidades, prestadores, sanatorios Paraguay';
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

</head>

<body>
    
    <!--  Preloader  -->
    <div id="pre_loader"></div>

    <!--header-section start-->
    <?php include 'header.php'; ?>
    <!-- header-section end -->

    <!-- Banner Start -->
    <section class="banner">
        
    </section>
    <!-- Banner End -->

    <!-- Service start -->
    <section class="section service">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12">
                    <div class="section__header">
                        <h2 class="section__header-title wow fadeInUp" data-wow-duration="1.2s">Guía Médica</h2>
                        <p class=" wow fadeInDown" data-wow-duration="1.5s">Encuentre a su médico aquí: <br><br></p>

                        <form class="form-horizontal" action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data" name="form2" id="form2">
                            <div class="row gy-3 gy-md-4">
                                <div class="col-12 col-md-6 col-lg-3">
                                    <select name="Especialidad" class="form-select" aria-label="Default select example">
                                      <option selected value="" >Especialidad</option>

                                        <?php do { ?>
                                          <option value="<?php echo$row_especialidad['id'];?>" <?php if ($row_especialidad['id'] == $VARespecialidad) { echo "selected=\"selected\""; } ?>><?php echo$row_especialidad['nombre'];?></option>
                                          
                                        <?php 
                                            $row_especialidad = mysqli_fetch_assoc($especialidad);
                                            } while ($row_especialidad);   //end horizontal looper 
                                        ?>
                                    </select>
                                </div>
                                <div class="col-12 col-md-6 col-lg-3">
                                    <select name="Ciudad" class="form-select" aria-label="Default select example">
                                      <option selected value="">Ciudad</option>
                                      <?php do { ?>
                                          <option value="<?php echo$row_ciudad['id'];?>" <?php if ($row_ciudad['id'] == $VACiudad) { echo "selected=\"selected\""; } ?>><?php echo$row_ciudad['nombre'];?></option>
                                          
                                        <?php 
                                            $row_ciudad = mysqli_fetch_assoc($ciudad);
                                            } while ($row_ciudad);   //end horizontal looper 
                                        ?>
                                    </select>
                                </div>
                                <div class="col-12 col-md-6 col-lg-5">
                                    <input type="text" name="medico" value='<?php if ($VAMedico != '') { echo "$VAMedico"; } ?>' class="form-control" style="border: var(--bs-border-width) solid var(--bs-border-color);" id="formGroupExampleInput" placeholder="Médico">


                                </div>
                                <div class="col-12 col-md-6 col-lg-1">
                                    <button type="submit" class="btn_theme " style="padding: 7px 37px;" name="submit" id="submit">Buscar</button>
                                 </div>
                            </div>
                            <input type="hidden" name="MM_search" value="form2" />
                        </form>
                    </div>
                </div>
            </div>
            <div class="row gy-3 gy-md-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2" style="margin-bottom:8px;">
                    <p class="wow fadeInDown m-0" data-wow-duration="1.5s">
                    <strong style='color: red'><?php echo $totalRows_guia;?> </strong> Resultado/s encontrado/s para:
                    <strong style='color: red'><?php echo $row_especialidad2['nombre'] ?? '';?>, </strong>
                    <strong style='color: red'><?php echo $row_ciudad2['nombre'] ?? '';?>, </strong>
                    <strong style='color: red'><?php echo $VAMedico; ?> </strong>
                    </p>
                    <a href="<?php echo $URL?>guiamedica-pdf.php?esp=<?php echo urlencode($VARespecialidad);?>&amp;ciu=<?php echo urlencode($VACiudad);?>&amp;med=<?php echo urlencode($VAMedico);?>"
                       target="_blank" rel="noopener"
                       class="btn_theme"
                       style="padding:9px 20px; white-space:nowrap; display:inline-flex; align-items:center; gap:8px;">
                        <i class="fas fa-file-pdf"></i> Descargar PDF
                    </a>
                </div>
                <table width="100%" border="0" cellspacing="10" cellpadding="10" class="table table-striped table-hover">

                    <?php if ($totalRows_guia > 0) { do { ?>

                      <tr >
                        <td style="border: solid 1px;">
                            <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin: 15px;">
                              <tr>
                                <td><strong style="color:#274266"><?php echo$row_guia['titulo'];?> <?php echo$row_guia['medico'];?></strong> </td>
                              </tr>
                              <tr>
                                <td><strong style="color:#274266"><em><?php echo$row_guia['especialidad'];?></em></strong></td>
                              </tr>
                              <tr>
                                <td>
                                    <strong>Lugar de Prest.:</strong> <?php echo$row_guia['sanatorio'];?> -  <strong>Dirección:</strong> <?php echo$row_guia['direccion'];?> - <strong>Teléfono:</strong> <?php echo$row_guia['telefono'];?>
                                </td>
                              </tr>
                            </table>
                        </td>
                      </tr>

                    <?php 
                        $row_guia = mysqli_fetch_assoc($guia);
                        } while ($row_guia); } //end horizontal looper
                    ?>

                </table>

                
    
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