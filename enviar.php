<?php
 
// coge la librer铆a recaptcha
//require_once "recaptchalib.php";
 
?>
<?php
$fecha = date("d/m/Y H:i:s");
?>
<?php
	//if ($response != null && $response->success) {
		iconv_set_encoding("internal_encoding", "UTF-8");
		$nombre = isset($_POST['nombre']) ? $_POST['nombre'] : '';
		$tel = isset($_POST['tel']) ? $_POST['tel'] : '';
		$email = isset($_POST['email']) ? $_POST['email'] : '';
		$mensaje = isset($_POST['mensaje']) ? $_POST['mensaje'] : '';
		
		
		//-------------- E-mail ---------------//
		require_once 'class/class.phpmailer.php';
		$mail = new PHPMailer ();
 		$mail-> CharSet = 'UTF-8';
		$mail -> From = $email;
		$mail -> FromName = $nombre;
		$mail -> AddAddress ("asistente.ventas1@samap.com.py");
 		$mail -> AddBCC ("info@cloud.com.py");
		$mail -> Subject = "MENSAJE DESDE LA WEB: www.samap.com.py";
    	$mail->Body =  "DATOS PERSONALES
Fecha: $fecha		
Nombre: $nombre
Telefono: $tel
Correo Electronico: $email

MENSAJE
$mensaje
";
	$mail->IsSMTP(); 
    $mail->Host = "mail.life4web.co";  // Servidor de Salida.
    $mail->Port = "26";
    $mail->SMTPAuth = true; 
    $mail->Username = "web@life4web.co";  // Correo Electr贸nico
    $mail->Password = "Web.2024web";
 
		if(!$mail->Send()) {
              	 echo 'Erro:'.$mail->ErrorInfo;
		} else {
			echo "
			<script language='Javascript' type='text/javascript'>
				alert('Su mensaje fue enviado correctamente, gracias por completar el formulario');
			</script>
					";
            
				   
		}

echo "<script language=Javascript> location.href=\"contactos/\"; </script>";
?>