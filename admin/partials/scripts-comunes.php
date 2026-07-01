<?php
// ============================================================================
// Partial: scripts-comunes.php
// Bloque comun de <script src> que va al final del <body> de los .php del
// admin. Reemplaza a la lista de 22 scripts que cargaba cada pagina (sumando
// ~700 KB de las cuales ~440 KB eran plugins NUNCA usados).
//
// Lo que cargamos (orden importa):
//   1. modernizr  -> feature detection (~9 KB)
//   2. fastclick  -> evita el delay de 300ms en mobile (~23 KB)
//   3. jquery     -> base, requerida por bootstrap y app.js (~94 KB)
//   4. bootstrap  -> tooltips, modals, dropdowns (~28 KB)
//   5. filestyle  -> input file estilizado (4 KB) -- se usa en upload-imagen
//   6. app.js     -> bootstrap del template + nuestros wrappers defensivos
//                     (la versión actual hace if (e.fn.X) antes de llamar a
//                     plugins opcionales, asi que no rompe si faltan).
//
// Lo que SACAMOS por no usarse:
//   - jquery.flot + 5 extensiones      -> ~76 KB
//   - jquery.sparkline                  -> ~42 KB
//   - moment-with-langs                 -> ~119 KB
//   - bootstrap-datetimepicker          -> ~21 KB
//   - jquery.inputmask                  -> ~49 KB
//   - bootstrap-slider                  -> ~10 KB
//   - chosen.jquery                     -> ~26 KB
//   - jquery.slimscroll                 -> ~4 KB
//   - animo                             -> ~4 KB
//   - dataTables.colVis                 -> ~9 KB
//   - dataTables.bootstrapPagination    -> ~5 KB
//
// Las 18 pantallas de listado AGREGAN dataTables (74 KB) + dataTables.bootstrap
// (6 KB) de forma dinamica desde el partial tabla-searchable.php cuando hace
// falta. Asi las pantallas que NO son listado quedan en ~158 KB en lugar de
// ~700 KB.
//
// Cache-bust: ?v=YYYYMMDDHHMM en app.js para invalidar la copia del browser
// cuando editamos el archivo. El resto son librerias que no cambian -> cache
// fuerte (30 dias) configurado en .htaccess.
//
// USO: en admin/<pantalla>.php, antes de cerrar el body, incluir este
// partial con: include 'partials/scripts-comunes.php';
//
// Si la pantalla necesita scripts extra (Summernote para blog, modales propios)
// se incluyen DESPUES del partial.
// ============================================================================

if (!isset($URL) || !is_string($URL)) { $URL = ''; }
$e_url = htmlspecialchars($URL, ENT_QUOTES, 'UTF-8');
?>
<script src="<?= $e_url ?>admin/plugins/modernizr/modernizr.js"></script>
<script src="<?= $e_url ?>admin/plugins/fastclick/fastclick.js"></script>
<script src="<?= $e_url ?>admin/plugins/jquery/jquery.min.js"></script>
<script src="<?= $e_url ?>admin/plugins/bootstrap/js/bootstrap.min.js"></script>
<script src="<?= $e_url ?>admin/plugins/filestyle/bootstrap-filestyle.min.js"></script>
<script src="<?= $e_url ?>admin/app/js/app.js?v=202606291718"></script>
