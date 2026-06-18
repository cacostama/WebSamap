<?php
	require_once('funciones/db.php');
	

$login   = $_GET['log'] ?? '';
$captcha = $_GET['captcha'] ?? '';
header( 'X-Frame-Options: SAMEORIGIN' );

?>
<?php
    if (isset($_POST['usuario'], $_POST['clave'])){

        // Usar las entradas CRUDAS (sin el escape de db.php): el escape altera
        // la contrasena real y romperia password_verify().
        $usuario     = $POST_RAW['usuario'] ?? '';
        $contrasena  = $POST_RAW['clave']   ?? '';

        // ---- #6 CSRF: rechazar POST sin token valido ----
        if (!samap_csrf_validar()) {
            echo"<script>window.location.href=\"".$URL."admin/index/log/error/\"</script>";
        }
        // ---- #6 Rate-limit: bloquear tras 5 intentos fallidos / 15 min por IP ----
        else if (samap_login_bloqueado()) {
            $login = 'bloqueado';
        }
        else if(!empty($usuario) && !empty($contrasena)){

            include("conexion.php");

            // Buscar SOLO por usuario; la verificacion de clave se hace en PHP.
            $stmt = $conexion->prepare("SELECT * FROM tbl_user WHERE userName = ?");
            $stmt->bind_param('s', $usuario);
            $stmt->execute();
            $proceso = $stmt->get_result();

            $passOk    = false;
            $resultado = mysqli_fetch_array($proceso);

            if ($resultado) {
                $hashGuardado = (string)$resultado['userPass'];

                if (preg_match('/^\$2[aby]\$/', $hashGuardado)) {
                    // Hash moderno (bcrypt).
                    $passOk = password_verify($contrasena, $hashGuardado);
                } else {
                    // ---- #5 Hash legacy MD5: validar y migrar a bcrypt al vuelo ----
                    if (hash_equals($hashGuardado, md5($contrasena))) {
                        $passOk = true;
                        $nuevoHash = password_hash($contrasena, PASSWORD_BCRYPT);
                        if ($up = $conexion->prepare("UPDATE tbl_user SET userPass = ? WHERE id = ?")) {
                            $up->bind_param('si', $nuevoHash, $resultado['id']);
                            $up->execute();
                        }
                    }
                }
            }

            if ($passOk) {
                samap_login_limpiar();          // reset del contador de la IP
                session_regenerate_id(true);    // #7 anti session-fixation
                $_SESSION['ADM_Username'] = $usuario;
                $_SESSION['ADM_Nombre']   = $resultado['nombre'];
                echo"<script>window.location.href=\"".$URL."admin/home/\"</script>";
            } else {
                samap_login_registrar_fallo();  // suma intento fallido para la IP
                echo"<script>window.location.href=\"".$URL."admin/index/log/error/\"</script>";
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="es">
<head>

	<meta charset="utf-8">
	<meta http-equiv="x-ua-compatible" content="ie=edge">
<meta http-equiv="Content-Language" content="es"/>
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
	<meta name="description" content="">
	<meta name="keywords" content="">
	<meta name="author" content="">
	<title>Sistema de Administración</title>

	<link rel="stylesheet" href="<?php echo $URL;?>admin/app/css/bootstrap.css">

	<link rel="stylesheet" href="<?php echo $URL;?>admin/plugins/fontawesome/css/font-awesome.min.css">
	<link rel="stylesheet" href="<?php echo $URL;?>admin/plugins/animo/animate+animo.css">
	<link rel="stylesheet" href="<?php echo $URL;?>admin/plugins/csspinner/csspinner.min.css">

	<link rel="stylesheet" href="<?php echo $URL;?>admin/app/css/app.css">
	<script src="<?php echo $URL;?>admin/plugins/modernizr/modernizr.js" type="application/javascript"></script>

	<script src="<?php echo $URL;?>admin/plugins/fastclick/fastclick.js" type="application/javascript"></script>
</head>
<body>

	<div style="height: 100%; padding: 50px 0; background-image: url(<?php echo $URL;?>admin/app/img/fondo.png); background-size: cover; background-color: #2c3037" class="row row-table">
		<div class="col-lg-4 col-md-6 col-sm-8 col-xs-12 align-middle">

			<div data-toggle="play-animation" data-play="fadeInUp" data-offset="0" class="panel panel-default panel-flat">
				<p class="text-center mb-lg">
					<br>
					
				</p>
				<p class="text-center mb-lg">
					<strong>REGÍSTRESE PARA CONTINUAR.</strong>
				</p>
				<div class="panel-body">
					<form action="" method="post" name="form1" id="form1" onsubmit="MM_validateForm('usuario','','R','clave','','R');return document.MM_returnValue"><?php echo samap_csrf_field(); ?>
						
						<div class="form-group has-feedback">
							<input name="usuario" type="text" placeholder="Ingrese su Usuario" class="form-control">
							<span class="fa fa-envelope form-control-feedback text-muted"></span>
						</div>
						<div class="form-group has-feedback">
							<input name="clave" type="password" placeholder="Contraseña"  autocomplete="off" class="form-control">
							
							
							<span class="fa fa-lock form-control-feedback text-muted"></span>
						</div>
						<div class="clearfix">
							<?php if ($login == "bloqueado" ) {
                                    echo "<div class='alert alert-danger alert-dismissable'>
                                            <button type='button' class='close' data-dismiss='alert'>&times;</button>
                                            <strong>Demasiados intentos.</strong> Espere unos minutos antes de volver a intentar.
                                          </div>
                                          ";
                                } ?>
                                <?php if ($login == "error" ) {
                                    echo "<div class='alert alert-warning alert-dismissable'>
                                            <button type='button' class='close' data-dismiss='alert'>&times;</button>
                                            <strong>¡Cuidado!</strong> Usuario y/o Contraseña Incorrecto/s
                                          </div>
                                          ";
                                } else { 
                                } ?>
                                 <?php if ($captcha == "incorrecto" ) { 
                                    echo "<div class='alert alert-warning alert-dismissable'>
                                            <button type='button' class='close' data-dismiss='alert'>&times;</button>
                                            <strong>ReCaptcha</strong>  Incorrecto
                                          </div>
                                          ";
                                } else { 
                                } ?>
                          
                        <div class="g-recaptcha" data-sitekey="6LejN1QpAAAAAKQ528NxxqvZc7qkC_vj_MB3jHK6"></div><br>

							<button type="submit" class="btn btn-block btn-primary">Iniciar sesión</button>
						</form>
					</div>
				</div>

			</div>
		</div>


		<script src='https://www.google.com/recaptcha/api.js'></script>
		<script src="<?php echo $URL;?>admin/plugins/jquery/jquery.min.js"></script>
		<script src="<?php echo $URL;?>admin/plugins/bootstrap/js/bootstrap.min.js"></script>

		<script src="<?php echo $URL;?>admin/plugins/animo/animo.min.js"></script>

		<script src="<?php echo $URL;?>admin/app/js/pages.js"></script>

	</body>
	</html>