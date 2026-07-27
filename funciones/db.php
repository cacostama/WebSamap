<?php

//error_reporting(0);

date_default_timezone_set('America/Asuncion');

// Security headers para el sitio publico. El root .htaccess ya setea
// HSTS/X-Frame-Options/Referrer-Policy/Permissions-Policy/CSP via mod_headers
// y 'Header always unset X-Powered-By', pero PHP agrega el header
// "X-Powered-By" via SAPI en runtime y mod_headers no siempre puede removerlo
// en todas las versiones de Apache. Por seguridad lo apagamos aca tambien
// (idempotente: si el header no existe, no falla). Los demas headers se
// setean en .htaccess para evitar duplicados.
if (!headers_sent()) {
	header_remove('X-Powered-By');
}

// Credenciales por variables de entorno (docker-compose / /etc/samap/env) con
// fallback a los valores de produccion. El fallback existe porque el mod_php de
// produccion no expone getenv(DB_*); sin el, el sitio publico se cae con HTTP
// 500. En local/Docker las env vars ganan y apuntan a la DB del dev.
$hostname = getenv('DB_HOST') ?: 'localhost';
$database = getenv('DB_NAME') ?: 'web_samap_v2';
$username = getenv('DB_USER') ?: 'webadmin';
$password = getenv('DB_PASS') ?: 's2m2p.m2st3r';
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
$correctosMETA   = array(' ', '', '', '', '', '', '', '', '', '', '', '');


/**
 * URL de imagen de blog con fallback. Si el articulo no tiene imagen,
 * devuelve un placeholder existente en vez de apuntar a la carpeta
 * "documentos/blog/" (que devuelve 403 y muestra la imagen rota).
 */
/**
 * Contenido de la portada (hero de index.php) editable desde el panel.
 *
 * Devuelve SIEMPRE un array completo: arranca de los valores por defecto
 * (los mismos que antes estaban escritos a mano en index.php) y encima
 * aplica lo que haya en tbl_portada. Por eso el sitio sigue funcionando
 * igual aunque la tabla todavia no exista -- importante para poder subir
 * los PHP antes de correr la migracion sin romper la home.
 *
 * Cachea en estatica: header.php, index.php y footer.php lo llaman, pero
 * la consulta se hace una sola vez por request.
 */
if (!function_exists('samap_portada')) {
    function samap_portada() {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }

        $defaults = [
            'eyebrow'     => 'Medicina Prepaga · Sanatorio Adventista',
            'titulo'      => 'Cuidándote siempre',
            'subtitulo'   => 'Cobertura médica para vos y tu familia con el respaldo del Sanatorio Adventista.',
            'btn1_texto'  => 'Solicitar información',
            'btn1_url'    => '',
            'btn2_texto'  => 'Conocer planes',
            'btn2_url'    => 'planes/',
            'imagen'      => '',
            'stat1_num'   => '+40',
            'stat1_label' => 'años de experiencia',
            'stat2_num'   => '+8.000',
            'stat2_label' => 'familias adheridas',
            'stat3_num'   => 'Respaldo',
            'stat3_label' => 'del Sanatorio Adventista',
            'whatsapp'    => '5950982304977',
        ];

        $cache = $defaults;

        global $connect, $database;
        if ($connect) {
            // try/catch y NO "@": desde PHP 8.1 mysqli reporta los errores
            // lanzando mysqli_sql_exception, y el operador @ no suprime
            // excepciones. Si la tabla todavia no existe (deploy de los PHP
            // antes de correr la migracion) hay que atrapar la excepcion,
            // si no la home se cae con un fatal error.
            try {
                mysqli_select_db($connect, $database);
                // Primero SHOW TABLES (nunca falla) en vez de tirar el SELECT
                // y atrapar el error: una consulta fallida deja la conexion en
                // estado de error y mysqli vuelve a lanzar esa excepcion en la
                // siguiente llamada, aunque sea de otra consulta sin relacion.
                $chk = mysqli_query($connect, "SHOW TABLES LIKE 'tbl_portada'");
                if ($chk && mysqli_num_rows($chk) > 0) {
                    $res = mysqli_query($connect, 'SELECT * FROM tbl_portada WHERE id = 1 AND deleted_at IS NULL LIMIT 1');
                    if ($res && ($row = mysqli_fetch_assoc($res))) {
                        foreach ($defaults as $k => $_) {
                            if (isset($row[$k]) && trim((string)$row[$k]) !== '') {
                                $cache[$k] = $row[$k];
                            }
                        }
                    }
                }
            } catch (Throwable $e) {
                // Sin tabla: se usan los valores por defecto y el sitio sigue.
            }
        }

        return $cache;
    }
}

/**
 * URL de WhatsApp comercial. El numero sale de la portada (editable desde
 * el panel) en vez de estar repetido a mano en header/footer/index.
 */
if (!function_exists('samap_whatsapp_url')) {
    function samap_whatsapp_url() {
        $p = samap_portada();
        $tel = preg_replace('/\D+/', '', (string)$p['whatsapp']);
        return 'https://api.whatsapp.com/send?phone=' . $tel;
    }
}

/**
 * URL de la imagen del hero, con fallback a la imagen que se usaba antes.
 */
if (!function_exists('samap_img_portada')) {
    function samap_img_portada() {
        global $URL;
        $p = samap_portada();
        $img = trim((string)$p['imagen']);
        if ($img === '') {
            return $URL . 'documentos/slider/03.jpg';
        }
        return $URL . 'documentos/portada/' . $img;
    }
}

/**
 * Quita las imagenes incrustadas en el cuerpo del articulo. La foto del
 * blog es SIEMPRE la del campo "Imagen" (columna imagen), que se muestra
 * arriba del detalle. Si el editor dejo un <img> pegado en el texto, se
 * veria una segunda imagen duplicada: aca la sacamos al renderizar, sin
 * tocar el contenido guardado en la base.
 */
if (!function_exists('samap_sin_imagenes')) {
    function samap_sin_imagenes($html) {
        if ($html === null || $html === '') {
            return $html;
        }
        // <img ...> y <figure>...</figure> (Summernote envuelve algunas imagenes)
        $html = preg_replace('#<figure\b[^>]*>.*?</figure>#is', '', $html);
        $html = preg_replace('#<img\b[^>]*>#i', '', $html);
        return $html;
    }
}

if (!function_exists('samap_img_blog')) {
    function samap_img_blog($imagen) {
        global $URL;
        $imagen = trim((string)$imagen);
        if ($imagen === '') {
            return $URL . 'assets/images/blog_articles.png';
        }
        return $URL . 'documentos/blog/' . $imagen;
    }
}


?>