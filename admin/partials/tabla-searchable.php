<?php
// ============================================================================
// Partial: tabla-searchable.php
// Tabla buscable + exportable a CSV, con DataTables ES, botonera y acciones.
// Soporta vista de papelera (registros soft-deleted) cuando $papelera_activa=true.
//
// Variables esperadas (definidas en el padre ANTES del include):
//   $tabla_titulo        string  default 'Listado'
//   $btn_agregar_label   string  default 'Agregar'
//   $btn_agregar_url     string  default '#'   ruta relativa (se prepende $URL)
//   $rows                mysqli_result  REQUERIDO  (se hace data_seek(0))
//   $columns             array   REQUERIDO  [['th' => 'ID', 'td_html' => function($r){...}], ...]
//   $edit_url_pattern    string  con placeholder {id}     ej: 'admin/editarx/cod/{id}/'
//   $delete_url_pattern  string  con placeholders {id} {csrf}
//   $delete_confirm      string  mensaje del confirm() (idioma humano)
//   $empty_message       string  default 'Todavia no hay registros cargados.'
//   $table_id            string  default 'datatable1'
//   $datatables_options  array   default ['pageLength' => 25, 'order' => []]
//   $URL                 string  REQUERIDO  base protocol-relative (provista por db.php)
//   $slug                string  default 'listado'  slug del padre (para links papelera)
//   $papelera_activa     bool    default false  si true, muestra la papelera
//
// Salida:
//   <div class="row"> <div class="panel panel-default"> con
//     - panel-heading: [Exportar CSV] ............... [+ Agregar] | [Volver] en papelera
//     - panel-body: <table id="$table_id"> con thead/tbody (incluye 2 columnas Acciones)
//     - panel-footer: Total: N registros
//     - <script src> jquery.dataTables + bootstrap
//     - <script> init DataTable() + handler de Exportar CSV
// ============================================================================

if (!isset($URL) || !is_string($URL)) {
    $URL = '';
}

$tabla_titulo       = isset($tabla_titulo)       ? (string)$tabla_titulo       : 'Listado';
$btn_agregar_label  = isset($btn_agregar_label)  ? (string)$btn_agregar_label  : 'Agregar';
$btn_agregar_url    = isset($btn_agregar_url)    ? (string)$btn_agregar_url    : '#';
$edit_url_pattern   = isset($edit_url_pattern)   ? (string)$edit_url_pattern   : '';
$delete_url_pattern = isset($delete_url_pattern) ? (string)$delete_url_pattern : '';
$delete_confirm     = isset($delete_confirm)     ? (string)$delete_confirm     : '¿Eliminar este registro?';
$empty_message      = isset($empty_message)      ? (string)$empty_message      : 'Todavia no hay registros cargados.';
$table_id           = isset($table_id)           ? (string)$table_id           : 'samap-tabla';
$datatables_options = (isset($datatables_options) && is_array($datatables_options))
                        ? $datatables_options
                        : ['pageLength' => 25];
$columns            = (isset($columns) && is_array($columns)) ? $columns : [];
$slug               = isset($slug)               ? (string)$slug               : 'listado';
$papelera_activa    = !empty($papelera_activa);

// En papelera el "agregar" y el "borrar" no aplican. El padre debe pasar
// edit_url_pattern vacio si tampoco quiere editar; nosotros igual lo dejamos
// caer si la papelera esta activa.
$show_edit   = ($edit_url_pattern   !== '' && !$papelera_activa);
$show_delete = ($delete_url_pattern !== '' && !$papelera_activa);

$e_url      = htmlspecialchars($URL, ENT_QUOTES, 'UTF-8');
$e_slug     = htmlspecialchars($slug, ENT_QUOTES, 'UTF-8');
$e_titulo   = htmlspecialchars($tabla_titulo, ENT_QUOTES, 'UTF-8');
$e_btn_lbl  = htmlspecialchars($btn_agregar_label, ENT_QUOTES, 'UTF-8');
$e_btn_url  = htmlspecialchars($btn_agregar_url, ENT_QUOTES, 'UTF-8');
$e_table_id = htmlspecialchars($table_id, ENT_QUOTES, 'UTF-8');
$e_empty    = htmlspecialchars($empty_message, ENT_QUOTES, 'UTF-8');
$e_papelera = $papelera_activa ? '1' : '0';

// Nombre del archivo CSV: solo [a-z0-9_], derivado del titulo.
$csv_filename = strtolower(preg_replace('/[^a-z0-9]+/i', '_', $tabla_titulo));
$csv_filename = trim($csv_filename, '_');
if ($csv_filename === '') {
    $csv_filename = 'listado';
}

// Iteramos el result set desde el principio. Si el padre ya hizo fetch_assoc()
// una vez, el puntero esta avanzado; lo reseteamos para no perder la primera fila.
// Tambien aprovechamos para contar manualmente (mysqli_num_rows no es confiable
// si la consulta ya fue consumida).
$row_count = 0;
$has_rows  = (isset($rows) && $rows instanceof mysqli_result);
if ($has_rows) {
    mysqli_data_seek($rows, 0);
}

// CSRF para los links de restaurar / borrar definitivamente (papelera)
$csrf = function_exists('samap_csrf_valor') ? urlencode(samap_csrf_valor()) : '';
?>
<div class="row">
    <div class="panel panel-default">
        <div class="panel-heading clearfix">
            <div class="pull-left" style="margin-right:10px;">
                <button id="btn-export-csv-<?= $e_table_id ?>" class="btn btn-default" type="button">
                    <em class="fa fa-download"></em> Exportar CSV
                </button>
            </div>
            <?php if ($papelera_activa): ?>
                <div class="pull-left" style="margin-right:10px;">
                    <a href="<?= $e_url ?>admin/<?= $e_slug ?>/" class="btn btn-primary">
                        <em class="fa fa-arrow-left"></em> Volver a registros activos
                    </a>
                </div>
                <div class="pull-right">
                    <span style="color:#888;line-height:34px;">
                        <em class="fa fa-trash"></em> Total en papelera: <strong><?= (int)$row_count ?></strong>
                    </span>
                </div>
            <?php else: ?>
                <div class="pull-right">
                    <a href="<?= $e_url . $e_btn_url ?>" class="btn btn-primary">
                        <em class="fa fa-plus"></em> <?= $e_btn_lbl ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
        <?php if ($papelera_activa): ?>
        <div class="panel-body" style="padding-top:0;padding-bottom:0;">
            <div class="alert alert-warning" style="background:#ffc61d;color:#333;border:none;padding:10px 15px;border-radius:4px;margin:10px 0;">
                <strong><em class="fa fa-trash"></em> Estás viendo la papelera.</strong>
                Los registros están ocultos del sitio web pero pueden restaurarse.
            </div>
        </div>
        <?php endif; ?>
        <div class="panel-body">
            <table id="<?= $e_table_id ?>" class="table table-striped table-hover">
                <thead>
                    <tr>
                        <?php foreach ($columns as $col): ?>
                            <th><?= htmlspecialchars((string)($col['th'] ?? ''), ENT_QUOTES, 'UTF-8') ?></th>
                        <?php endforeach; ?>
                        <?php if ($show_edit): ?>
                            <th class="sort-alpha">Editar</th>
                        <?php endif; ?>
                        <?php if ($show_delete): ?>
                            <th class="sort-alpha">Borrar</th>
                        <?php elseif ($papelera_activa): ?>
                            <th class="sort-alpha">Restaurar</th>
                            <th class="sort-alpha">Borrar def.</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($has_rows): ?>
                        <?php while ($r = mysqli_fetch_assoc($rows)):
                            $row_count++;
                            $rid = (string)($r['id'] ?? '');
                            $edit_url   = str_replace('{id}',   $rid, $edit_url_pattern);
                            $delete_url = str_replace(
                                ['{id}', '{csrf}'],
                                [$rid, urlencode(samap_csrf_valor())],
                                $delete_url_pattern
                            );
                            $e_edit_url   = htmlspecialchars($edit_url, ENT_QUOTES, 'UTF-8');
                            $e_delete_url = htmlspecialchars($delete_url, ENT_QUOTES, 'UTF-8');
                            $e_rid        = htmlspecialchars($rid, ENT_QUOTES, 'UTF-8');
                            $restaurar_url   = $e_url . 'admin/' . $e_slug . '/?restaurar=si&id=' . $e_rid . '&csrf_token=' . $csrf;
                            $borrar_def_url  = $e_url . 'admin/' . $e_slug . '/?borrar_def=si&id=' . $e_rid . '&csrf_token=' . $csrf;
                        ?>
                            <tr>
                                <?php foreach ($columns as $col):
                                    $cb = isset($col['td_html']) && is_callable($col['td_html'])
                                        ? $col['td_html']($r)
                                        : '<td></td>';
                                    echo $cb;
                                endforeach; ?>
                                <?php if ($show_edit): ?>
                                    <td width="20px"><div align="center"><a href="<?= $e_url . $e_edit_url ?>"><img width="20px" src="<?= $e_url ?>admin/app/img/editar.png" alt="Editar"/></a></div></td>
                                <?php endif; ?>
                                <?php if ($show_delete): ?>
                                    <td width="20px"><div align="center"><a href="<?= $e_url . $e_delete_url ?>" class="samap-confirm" data-samap-confirm-msg="<?= htmlspecialchars($delete_confirm, ENT_QUOTES, 'UTF-8') ?>" data-samap-confirm-ok="Sí, eliminar" data-samap-confirm-variant="danger" onclick="return confirm(<?= json_encode($delete_confirm, JSON_UNESCAPED_UNICODE) ?>);"><img width="20px" src="<?= $e_url ?>admin/app/img/borrar.png" alt="Borrar"/></a></div></td>
                                <?php elseif ($papelera_activa): ?>
                                    <td width="20px"><div align="center"><a href="<?= $restaurar_url ?>" title="Restaurar" class="samap-confirm" data-samap-confirm-msg="¿Restaurar este registro? Volverá a mostrarse en el sitio web." data-samap-confirm-ok="Sí, restaurar" data-samap-confirm-variant="primary" onclick="return confirm(<?= json_encode('¿Restaurar este registro? Volverá a mostrarse en el sitio web.', JSON_UNESCAPED_UNICODE) ?>);"><em class="fa fa-undo" style="font-size:18px;color:#01b6ad;"></em></a></div></td>
                                    <td width="20px"><div align="center"><a href="<?= $borrar_def_url ?>" title="Borrar definitivamente" class="samap-confirm" data-samap-confirm-msg="¿Borrar DEFINITIVAMENTE? Esta acción no se puede deshacer." data-samap-confirm-ok="Sí, borrar definitivamente" data-samap-confirm-variant="danger" onclick="return confirm(<?= json_encode('¿Borrar DEFINITIVAMENTE? Esta acción no se puede deshacer.', JSON_UNESCAPED_UNICODE) ?>);"><em class="fa fa-times-circle" style="font-size:18px;color:#f6504d;"></em></a></div></td>
                                <?php endif; ?>
                            </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>

                    <?php if ($row_count === 0): ?>
                        <tr><td colspan="<?= (int)(count($columns) + ($show_edit ? 1 : 0) + ($show_delete ? 1 : ($papelera_activa ? 2 : 0))) ?>" style="text-align:center;color:#888;padding:18px;">
                            <?php if ($papelera_activa): ?>
                                La papelera está vacía.
                            <?php else: ?>
                                <?= $e_empty ?>
                            <?php endif; ?>
                        </td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="panel-footer">
            <?php if ($papelera_activa): ?>
                Registros en papelera: <strong><?= (int)$row_count ?></strong>
            <?php else: ?>
                Total: <?= (int)$row_count ?> registros
            <?php endif; ?>
        </div>

        <script src="<?= $e_url ?>admin/plugins/datatable/media/js/jquery.dataTables.min.js"></script>
        <script src="<?= $e_url ?>admin/plugins/datatable/extensions/datatable-bootstrap/js/dataTables.bootstrap.js"></script>
        <script>
        (function() {
            var tableId   = <?= json_encode($table_id, JSON_UNESCAPED_UNICODE) ?>;
            var csvName   = <?= json_encode($csv_filename, JSON_UNESCAPED_UNICODE) ?>;
            var pageLen   = <?= (int)($datatables_options['pageLength'] ?? 25) ?>;
            var orderOpt  = <?= json_encode($datatables_options['order'] ?? [], JSON_UNESCAPED_UNICODE) ?>;
            var exportBtnId = 'btn-export-csv-' + tableId;
            var isPapelera = <?= $e_papelera ?>;

            // El partial se incluye ANTES del <script src="jquery.min.js"> del
            // padre, asi que jQuery todavia no esta cargado cuando este bloque
            // se ejecuta. Polleamos hasta que aparezca y luego inicializamos.
            function tryInit(attempts) {
                attempts = attempts || 0;
                if (typeof window.jQuery === 'undefined') {
                    if (attempts > 200) { return; }
                    return setTimeout(function(){ tryInit(attempts + 1); }, 50);
                }
                if (typeof window.jQuery.fn.dataTable === 'undefined') {
                    if (attempts > 200) { return; }
                    return setTimeout(function(){ tryInit(attempts + 1); }, 50);
                }
                runInit();
            }

            function runInit() {
                var $ = window.jQuery;
                $(function() {
                    // En papelera: ocultar la barra de busqueda (no tiene sentido
                    // sobre un set pequeno y casi siempre ordenado por fecha).
                    var t = $('#' + tableId).DataTable({
                        pageLength: pageLen,
                        order: orderOpt,
                        language: { url: '//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json' },
                        columnDefs: [
                            { orderable: false, targets: -1 },
                            { orderable: false, targets: -2 }
                        ]
                    });
                    $('#' + exportBtnId).on('click', function() {
                        var csv = [];
                        var headers = [];
                        t.columns().every(function(idx) {
                            if (this.data().length === 0) return;
                            if (this.context[0].orderable === false) return;
                            var h = t.column(idx).header().textContent.trim().replace(/"/g, '""');
                            if (h === '') return;
                            headers.push('"' + h + '"');
                        });
                        csv.push(headers.join(','));
                        t.rows({ search: 'applied' }).every(function() {
                            var row = [];
                            var $cells = $(this.node()).find('td');
                            var last = $cells.length - 1;
                            $cells.each(function(idx) {
                                if (idx === last) return;     // Ultima accion (Borrar / Borrar def.)
                                if (idx === last - 1) return; // Penultima (Editar / Restaurar)
                                var text = $(this).text().trim().replace(/"/g, '""');
                                row.push('"' + text + '"');
                            });
                            csv.push(row.join(','));
                        });
                        var blob = new Blob(["\uFEFF" + csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
                        var link = document.createElement('a');
                        link.href = URL.createObjectURL(blob);
                        link.download = csvName + '_' + new Date().toISOString().slice(0, 10) + '.csv';
                        link.style.display = 'none';
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    });
                });
            }

            tryInit(0);
        })();
        </script>
    </div>
</div>

<?php
// ----------------------------------------------------------------------------
// Modal de confirmacion (reemplaza al confirm() nativo del navegador).
// Se renderiza UNA sola vez por pagina (defendido con SAMAP_CONFIRM_MODAL_OK).
// Cualquier <a class="samap-confirm" data-samap-confirm-msg="..." data-samap-confirm-ok="..." data-samap-confirm-variant="danger|primary">
// abre este modal con el mensaje correspondiente y al confirmar navega al href.
// El onclick="return confirm()" sigue como fallback si por algun motivo el JS
// del modal no se carga (capture-phase + stopImmediatePropagation lo silencia
// cuando todo funciona normalmente).
// ----------------------------------------------------------------------------
if (!defined('SAMAP_CONFIRM_MODAL_RENDERED')) {
    define('SAMAP_CONFIRM_MODAL_RENDERED', true);
?>
<div id="samap-confirm-overlay" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.55);z-index:99998;align-items:center;justify-content:center;">
    <div role="dialog" aria-modal="true" aria-labelledby="samap-confirm-title" style="background:#fff;border-radius:6px;box-shadow:0 8px 32px rgba(0,0,0,0.35);max-width:480px;width:92%;font-family:Helvetica,Arial,sans-serif;overflow:hidden;">
        <div id="samap-confirm-title" style="background:#274767;color:#fff;padding:14px 20px;font-size:16px;font-weight:bold;display:flex;align-items:center;gap:10px;">
            <em class="fa fa-question-circle" style="font-size:22px;"></em>
            <span>Confirmar acción</span>
        </div>
        <div id="samap-confirm-body" style="padding:22px 20px;color:#2F2E2D;font-size:15px;line-height:1.45;">
            ¿Estás seguro?
        </div>
        <div style="padding:14px 20px;background:#f4f6f8;display:flex;justify-content:flex-end;gap:10px;border-top:1px solid #d8dee5;">
            <button type="button" id="samap-confirm-cancel" style="background:#fff;color:#2F2E2D;border:1px solid #c0c7d0;padding:8px 18px;border-radius:4px;font-size:14px;cursor:pointer;">Cancelar</button>
            <a href="#" id="samap-confirm-ok" style="background:#f6504d;color:#fff;border:none;padding:8px 18px;border-radius:4px;font-size:14px;cursor:pointer;text-decoration:none;display:inline-block;font-weight:bold;">Aceptar</a>
        </div>
    </div>
</div>
<script>
(function() {
    var overlay = document.getElementById('samap-confirm-overlay');
    var body    = document.getElementById('samap-confirm-body');
    var okBtn   = document.getElementById('samap-confirm-ok');
    var cancel  = document.getElementById('samap-confirm-cancel');
    if (!overlay || !okBtn || !cancel) { return; }

    function open(msg, okLabel, variant, href) {
        body.textContent = msg || '¿Estás seguro?';
        okBtn.textContent = okLabel || 'Aceptar';
        okBtn.href = href;
        okBtn.style.background = (variant === 'primary') ? '#274767' : '#f6504d';
        overlay.style.display = 'flex';
        setTimeout(function(){ okBtn.focus(); }, 50);
    }
    function close() { overlay.style.display = 'none'; okBtn.href = '#'; }

    cancel.addEventListener('click', function(e){ e.preventDefault(); close(); });
    overlay.addEventListener('click', function(e){ if (e.target === overlay) close(); });
    document.addEventListener('keydown', function(e){
        if (e.key === 'Escape' && overlay.style.display === 'flex') { close(); }
    });

    // Intercepta los clicks en CAPTURE phase para correr ANTES que el onclick
    // inline (que es el fallback). stopImmediatePropagation evita que el
    // onclick="return confirm()" se evalue cuando el modal esta disponible.
    document.addEventListener('click', function(e) {
        var a = e.target.closest && e.target.closest('a.samap-confirm');
        if (!a) { return; }
        e.preventDefault();
        e.stopImmediatePropagation();
        open(
            a.getAttribute('data-samap-confirm-msg') || a.getAttribute('title') || '¿Estás seguro?',
            a.getAttribute('data-samap-confirm-ok')  || 'Aceptar',
            a.getAttribute('data-samap-confirm-variant') || 'danger',
            a.getAttribute('href')
        );
    }, true);
})();
</script>
<?php } ?>
