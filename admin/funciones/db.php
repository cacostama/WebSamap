<?php

//error_reporting(0);
date_default_timezone_set('America/Asuncion');

$hostname = getenv('DB_HOST') ?: 'db';
$database = getenv('DB_NAME') ?: 'web_samap';
$username = getenv('DB_USER');
$password = getenv('DB_PASS');
if ($username === false || $username === '' || $password === false) {
    http_response_code(500);
    die('Configuracion de base de datos faltante. Defina DB_USER y DB_PASS en el entorno.');
}
$connect = mysqli_connect($hostname, $username, $password) or mysqli_error($connect);
mysqli_set_charset($connect, 'utf8');

// ---- Mitigacion central de SQL Injection ----
// Las pantallas de carga/edicion del panel interpolan $_POST/$_GET directo en
// las queries (codigo legacy). Aca escapamos TODA la entrada una sola vez, de
// forma recursiva, para neutralizar inyecciones sin reescribir cada archivo.
// (El fix ideal a futuro son prepared statements; esto cierra el agujero ya.)
if (!function_exists('samap_escapar_recursivo')) {
    function samap_escapar_recursivo(&$data, $conn) {
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                samap_escapar_recursivo($data[$k], $conn);
            } else {
                $data[$k] = mysqli_real_escape_string($conn, (string)$v);
            }
        }
    }
}
// Copia CRUDA de las entradas ANTES de escapar. El escape altera comillas y
// backslashes, lo que romperia password_verify()/login con contrasenas que
// tengan caracteres especiales. El login usa estas copias sin tocar.
$POST_RAW = $_POST;
$GET_RAW  = $_GET;
samap_escapar_recursivo($_POST, $connect);
samap_escapar_recursivo($_GET, $connect);
// Tambien el NOMBRE de los archivos subidos (se usa en rutas y queries).
if (!empty($_FILES)) {
    foreach ($_FILES as $campo => $info) {
        if (isset($info['name'])) {
            if (is_array($info['name'])) {
                samap_escapar_recursivo($_FILES[$campo]['name'], $connect);
            } else {
                $_FILES[$campo]['name'] = mysqli_real_escape_string($connect, (string)$info['name']);
            }
        }
    }
}



// URL protocol-relative: los assets/links cargan siempre con el mismo
// esquema que la pagina (http en local, https detras de un tunel/proxy
// como tunnelmole, que no siempre envia X-Forwarded-Proto). Evita el
// bloqueo de "mixed content" al compartir el sitio por HTTPS.
$URL = '//'.$_SERVER['HTTP_HOST'].'/';

$ruta = '../documentos/';
$ruta_marca = '../documentos/marca/';
$ruta_categoria = '../documentos/categoria/';
$rutaPrensa = '../noticias/';
$RutaBannerPromo = '../images/promociones/';
$ruta_banners = '../images/';
$rutaSpeaker = '../documentos/speaker/';
$rutaSponsor = '../documentos/sponsor/';
$rutaBandera = '../documentos/bandera/';

$rutaGaleria = '../documentos/galeria/';

///
$rutaPlan = '../documentos/';
$rutaSlider = '../documentos/slider/';
$rutaServicios = '../documentos/servicios/';
$rutaMedico = '../documentos/medicos/';
$rutaBlog = '../documentos/blog/';
$rutaAliados= '../documentos/aliados/';


$especiales = array('á', 'Á', 'é', 'É', 'í', 'Í', 'ó', 'Ó', 'ú', 'Ú', 'ñ', 'Ñ', ' ', '/', '"', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '#', '&-xf3;');
$correctos   = array('a', 'A', 'e', 'E', 'i', 'I', 'o', 'O', 'u', 'U', 'n', 'N', '-', '-', '-', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '-', 'o');


$especialesTooltip = array('"');
$correctosTooltip  = array('&#34');

$especialesComparar = array('16px', '60%');
$correctosComparar   = array('11px', '99%');

require_once('session.php');
require_once('seguridad.php');


#header("Content-Security-Policy: default-src 'self'");
#header("X-Content-Security-Policy: default-src 'self'");
//header("Cache-Control: max-age=2592000");

