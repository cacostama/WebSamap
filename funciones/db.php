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



// URL protocol-relative: los assets/links cargan siempre con el mismo
// esquema que la pagina (http en local, https detras de un tunel/proxy
// como tunnelmole, que no siempre envia X-Forwarded-Proto). Evita el
// bloqueo de "mixed content" al compartir el sitio por HTTPS.
$URL = '//'.$_SERVER['HTTP_HOST'].'/';

$ruta = '../documentos/';
$ruta_marca = '../documentos/marca/';
$ruta_categoria = '../documentos/categoria/';
$rutaPrensa = '../noticias/';
$RutaBannerPromo = '../documentos/promociones/';
$ruta_banners = '../documentos/banners/';
$rutaSlider = '../img/slider/slider-2/';




$especiales = array('á', 'Á', 'é', 'É', 'í', 'Í', 'ó', 'Ó', 'ú', 'Ú', 'ñ', 'Ñ', ' ', '/', '"', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '#', '&-xf3;', ',', '¿', '?');
$correctos   = array('a', 'A', 'e', 'E', 'i', 'I', 'o', 'O', 'u', 'U', 'n', 'N', '-', '-', '-', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '-', 'o', '', '', '');
$especialesTooltip = array('"');
$correctosTooltip  = array('&#34');
$especialesComparar = array('16px', '60%');
$correctosComparar   = array('11px', '99%');
$especialesKEY = array(' ', '"', '/');
$correctosKEY   = array(', ', ' ', ', '); 
$especialesMETA = array('<span style="font-weight: 600;">', '</span>', '•', '&nbsp;', '<br>', '<span style="font-weight: bold;">', '<div>', '</div>', '<table class="table table-bordered" style="background-color: rgb(255, 255, 255); width: 804px;">', '<tbody>', '</tbody>', '</table>');
$correctosMETA   = array(' ', '', '', '', '', '', '', '', '', '', '', '')




?>