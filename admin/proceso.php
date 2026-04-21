<?php
 
// coge la librería recaptcha
$URL = 'https://'.$_SERVER['HTTP_HOST'].'/';
require_once "recaptchalib.php";
 
?>
<?php
if ($response != null && $response->success) {
	
	
	session_start();
	
	include("conexion.php");
	$usuario= $conexion->real_escape_string($_POST['usuario']);
	$contrasena= $conexion->real_escape_string($_POST['clave']);
	$proceso = $conexion->query("SELECT * FROM tbl_usuarios WHERE userName= '$usuario' AND userPass= md5('$contrasena') ");
	
	if($resultado = mysqli_fetch_array($proceso)){

		echo $_SESSION['ADM_Username'] = $usuario;
		$_SESSION['ADM_Nombre'] = $resultado['nombre_usuario'];
		$_SESSION['ADM_Userid'] = $resultado['idvendedor'];
		$_SESSION['ADM_Usertipo'] = $resultado['tipo_usuario'];
		$_SESSION['ADM_Userruc'] = $resultado['ruc'];
		$_SESSION['ADM_Uservendedor'] = $resultado['codven'];
		$_SESSION['ADM_Useremail'] = $resultado['email'];
		
		echo"<script>window.location.href=\"".$URL."admin/home/\"</script>";
	}
	else{
		
		echo"<script>window.location.href=\"".$URL."admin/index/log/error/\"</script>";	

	}
	
	
} else {
	echo"<script>window.location.href=\"".$URL."admin/index/captcha/incorrecto/\"</script>";
}
?>