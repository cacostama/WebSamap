<?php
// Inicializar la sesión.
// Si está usando session_name("algo"), ¡no lo olvide ahora!
session_start();
$URL = '//'.$_SERVER['HTTP_HOST'].'/';

// Logout auditable: dejamos constancia del usuario que cerro sesion ANTES de
// vaciar $_SESSION. Cargamos db.php (que es seguro - logout.php esta en la
// whitelist de paginas publicas de session.php) para tener la conexion
// $connect que usa samap_audit_log.
require_once(__DIR__ . '/funciones/db.php');

$logout_user = isset($_SESSION['ADM_Username']) ? (string)$_SESSION['ADM_Username'] : 'anonymous';
@samap_audit_log('logout', '', 0, "Cierre de sesión: " . $logout_user);

// Destruir todas las variables de sesión.
$_SESSION = array();

// Si se desea destruir la sesión completamente, borre también la cookie de sesión.
// Nota: ¡Esto destruirá la sesión, y no la información de la sesión!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finalmente, destruir la sesión.
session_destroy();
echo"<script>window.location.href=\"".$URL."admin/\"</script>";
