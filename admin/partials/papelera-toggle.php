<?php
// ============================================================================
// Partial: papelera-toggle.php
// Renderiza el banner de papelera o el link "Mostrar papelera (N)" encima
// de la tabla, segun el estado. Lo unico que necesita el padre es haber
// pasado el query (para poder contar) y haber definido $slug + $URL.
//
// Variables esperadas (definidas en el padre ANTES del include):
//   $papelera      bool    (true si estamos viendo la papelera)
//   $trash_count   int     cantidad de registros en papelera (solo cuando $papelera=false)
//   $slug          string  slug del padre (ej: 'planes', 'slider')
//   $URL           string  base protocol-relative (provista por db.php)
//
// Salida:
//   - Si $papelera: un banner amarillo de "estas viendo la papelera" con un
//     boton "Volver a registros activos".
//   - Si !$papelera y $trash_count>0: un link discreto "Mostrar papelera (N)".
//   - En cualquier otro caso: nada.
// ============================================================================

if (!isset($papelera))    { $papelera = false; }
$trash_count = isset($trash_count) ? (int)$trash_count : 0;
if (!isset($URL))         { $URL = ''; }
$slug = isset($slug) ? (string)$slug : '';

$e_url  = htmlspecialchars($URL, ENT_QUOTES, 'UTF-8');
$e_slug = htmlspecialchars($slug, ENT_QUOTES, 'UTF-8');

if ($papelera) {
    ?>
<div class="alert alert-warning" style="background:#ffc61d;color:#333;border:none;padding:10px 15px;border-radius:4px;margin-bottom:15px;">
    <strong><em class="fa fa-trash"></em> Estás viendo la papelera.</strong>
    Los registros están ocultos del sitio web pero pueden restaurarse.
    <a href="<?= $e_url ?>admin/<?= $e_slug ?>/" class="btn btn-primary btn-xs" style="margin-left:12px;">
        <em class="fa fa-arrow-left"></em> Volver a registros activos
    </a>
</div>
    <?php
} else {
    if ($trash_count > 0) {
        ?>
<div style="margin-bottom:10px;">
    <a href="<?= $e_url ?>admin/<?= $e_slug ?>/?papelera=1" class="btn btn-sm btn-default">
        <em class="fa fa-trash"></em> Mostrar papelera (<?= (int)$trash_count ?>)
    </a>
</div>
        <?php
    }
}
?>
