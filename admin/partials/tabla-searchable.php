<?php
// ============================================================================
// Partial: tabla-searchable.php
// Tabla buscable + exportable a CSV, con DataTables ES, botonera y acciones.
// Soporta vista de papelera (registros soft-deleted) cuando $papelera_activa=true.
// Soporta seleccion multiple y acciones masivas cuando $enable_bulk=true.
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
//   $enable_bulk         bool    default false  si true, muestra checkboxes + barra de
//                                 acciones masivas (Borrar/Exportar seleccionados).
//   $bulk_delete_msg     string  default '¿Borrar los registros seleccionados?'
//   $ordenable           bool    default false  si true, las filas del tbody son draggables
//                                 (Sortable.js). Agrega una primera columna con handle "≡"
//                                 y guarda el nuevo orden via AJAX a ?reordenar=si.
//                                 Requiere que la tabla tenga una columna 'orden' (o que
//                                 el padre pase \$reordenar_column para el UPDATE).
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
$enable_bulk        = !empty($enable_bulk) && !$papelera_activa && function_exists('samap_puede_escribir') && samap_puede_escribir();
$bulk_delete_msg    = isset($bulk_delete_msg)    ? (string)$bulk_delete_msg    : '¿Borrar los registros seleccionados? Dejarán de mostrarse en el sitio web.';
$ordenable          = !empty($ordenable) && !$papelera_activa && function_exists('samap_puede_escribir') && samap_puede_escribir();

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
$e_bulk_msg = htmlspecialchars($bulk_delete_msg, ENT_QUOTES, 'UTF-8');

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
$csrf_raw = function_exists('samap_csrf_valor') ? samap_csrf_valor() : '';

// URL del endpoint de borrado masivo. El padre debe manejar ?borrar_masivo=si
// con validacion de CSRF y permisos; el partial solo dispara la URL.
$bulk_url = $URL . 'admin/' . $slug . '/?borrar_masivo=si&csrf_token=' . $csrf;
?>
<?php /* Token CSRF accesible para scripts/tests que necesiten construir
          URLs de borrado/restaurar manualmente (sobre todo en modo
          serverSide donde los links no estan en el HTML inicial). */ ?>
<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_raw, ENT_QUOTES, 'UTF-8') ?>" id="samap-csrf-token">
<div class="row">
    <div class="panel panel-default">
        <div class="panel-heading clearfix">
            <div class="pull-left" style="margin-right:10px;">
                <button id="btn-export-csv-<?= $e_table_id ?>" class="btn btn-default" type="button">
                    <em class="fa fa-download"></em> Exportar CSV
                </button>
                <?php if ($enable_bulk): ?>
                <button id="btn-export-selected-<?= $e_table_id ?>" class="btn btn-default" type="button" style="margin-left:4px;">
                    <em class="fa fa-download"></em> Exportar seleccionados
                </button>
                <?php endif; ?>
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
        <?php if ($enable_bulk): ?>
        <div id="samap-bulk-bar-<?= $e_table_id ?>" class="panel-body" style="display:none;padding:10px 15px;background:#f4f6f8;border-bottom:1px solid #d8dee5;">
            <span style="margin-right:12px;">
                <strong id="samap-bulk-count-<?= $e_table_id ?>">0</strong> seleccionados
            </span>
            <button id="btn-bulk-delete-<?= $e_table_id ?>" type="button" class="btn btn-danger btn-sm">
                <em class="fa fa-trash"></em> Borrar seleccionados
            </button>
            <button id="btn-bulk-cancel-<?= $e_table_id ?>" type="button" class="btn btn-default btn-sm" style="margin-left:4px;">
                Cancelar
            </button>
        </div>
        <?php endif; ?>
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
                        <?php if ($ordenable): ?>
                            <th width="30px" style="width:30px;" title="Arrastrar para reordenar"></th>
                        <?php endif; ?>
                        <?php if ($enable_bulk): ?>
                            <th width="30px" style="width:30px;"><input type="checkbox" id="dt-select-all-<?= $e_table_id ?>" aria-label="Seleccionar todos"></th>
                        <?php endif; ?>
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
                            <tr data-id="<?= $e_rid ?>">
                                <?php if ($ordenable): ?>
                                    <td class="drag-handle" style="cursor:move;text-align:center;color:#888;font-size:18px;line-height:1;" title="Arrastrar para reordenar">&#x2261;</td>
                                <?php endif; ?>
                                <?php if ($enable_bulk): ?>
                                    <td><input type="checkbox" class="dt-row-select dt-row-select-<?= $e_table_id ?>" value="<?= $e_rid ?>" aria-label="Seleccionar fila"></td>
                                <?php endif; ?>
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

                    <?php if ($row_count === 0 && empty($ajax_url)): /* en modo serverSide, DataTables muestra "Cargando..." y rellena via AJAX */ ?>
                        <tr><td colspan="<?= (int)(count($columns) + ($ordenable ? 1 : 0) + ($enable_bulk ? 1 : 0) + ($show_edit ? 1 : 0) + ($show_delete ? 1 : ($papelera_activa ? 2 : 0))) ?>" style="text-align:center;color:#888;padding:18px;">
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
            <?php if (!empty($ajax_url)): /* en serverSide DataTables ya muestra "X de Y registros" en su info */ ?>
                <small style="color:#888;">Listado paginado del lado del servidor (más rápido para tablas grandes).</small>
            <?php elseif ($papelera_activa): ?>
                Registros en papelera: <strong><?= (int)$row_count ?></strong>
            <?php else: ?>
                Total: <?= (int)$row_count ?> registros
            <?php endif; ?>
        </div>

        <script>
        (function() {
            var tableId   = <?= json_encode($table_id, JSON_UNESCAPED_UNICODE) ?>;
            var csvName   = <?= json_encode($csv_filename, JSON_UNESCAPED_UNICODE) ?>;
            var pageLen   = <?= (int)($datatables_options['pageLength'] ?? 25) ?>;
            var orderOpt  = <?= json_encode($datatables_options['order'] ?? [], JSON_UNESCAPED_UNICODE) ?>;
            var exportBtnId = 'btn-export-csv-' + tableId;
            var isPapelera = <?= $e_papelera ?>;
            var baseUrl   = <?= json_encode($e_url, JSON_UNESCAPED_UNICODE) ?>;
            var ajaxUrl   = <?= json_encode(isset($ajax_url) ? (string)$ajax_url : '', JSON_UNESCAPED_UNICODE) ?>;

            // jQuery lo carga el padre al final del <body>, no aca. NO podemos
            // emitir <script src="dataTables"> en el partial porque correria
            // antes de jQuery. Y NO podemos cargar jQuery aca porque eso lo
            // dispararia DOS veces, reseteando $.fn y rompiendo plugins ya
            // inicializados (slider, slimScroll, etc).
            //
            // Estrategia: polleamos por window.jQuery, y cuando exista, cargamos
            // dataTables y dataTables.bootstrap *dinamicamente* via <script>
            // inyectados al head. Una vez cargados, inicializamos la tabla.
            function loadScript(src, cb) {
                var s = document.createElement('script');
                s.src = src;
                s.async = false;            // mantiene orden de ejecucion
                s.onload = cb;
                s.onerror = function() { /* silencioso: si falla, la tabla queda como HTML plano */ };
                document.head.appendChild(s);
            }
            function ensureDataTables(cb) {
                if (typeof window.jQuery.fn.dataTable !== 'undefined') { cb(); return; }
                loadScript(baseUrl + 'admin/plugins/datatable/media/js/jquery.dataTables.min.js', function() {
                    loadScript(baseUrl + 'admin/plugins/datatable/extensions/datatable-bootstrap/js/dataTables.bootstrap.js', cb);
                });
            }
            function tryInit(attempts) {
                attempts = attempts || 0;
                if (typeof window.jQuery === 'undefined') {
                    if (attempts > 200) { return; }
                    return setTimeout(function(){ tryInit(attempts + 1); }, 50);
                }
                ensureDataTables(runInit);
            }

            function runInit() {
                var $ = window.jQuery;
                $(function() {
                    var cfg = {
                        pageLength: pageLen,
                        order: orderOpt,
                        language: { url: baseUrl + 'admin/plugins/datatable/i18n/Spanish.json' },
                        columnDefs: [
                            { orderable: false, targets: -1 },
                            { orderable: false, targets: -2 }
                        ]
                    };
                    // Si el padre paso $ajax_url, activar modo serverSide.
                    // El tbody del HTML quedo vacio; DataTables hace AJAX cada
                    // vez que el usuario pagina/busca/ordena, trayendo solo el
                    // subset visible. Baja el peso inicial de listados grandes
                    // (guia=326 filas, sanatorios=210) a ~25 filas por request.
                    if (ajaxUrl) {
                        cfg.serverSide = true;
                        cfg.processing = true;
                        cfg.ajax = { url: ajaxUrl, type: 'GET' };
                    }
                    var t = $('#' + tableId).DataTable(cfg);

                    // Helpers para exportar CSV (reusado por "todo" y "seleccionados").
                    function buildCsv(rowsSubset) {
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
                        rowsSubset.each(function() {
                            var row = [];
                            var $cells = $(this.node()).find('td');
                            var last = $cells.length - 1;
                            $cells.each(function(idx) {
                                // Skipeamos la columna de checkbox (primera td en modo bulk)
                                if (idx === 0 && $(this).find('input.dt-row-select').length) return;
                                if (idx === last) return;
                                if (idx === last - 1) return;
                                var text = $(this).text().trim().replace(/"/g, '""');
                                row.push('"' + text + '"');
                            });
                            csv.push(row.join(','));
                        });
                        return csv;
                    }
                    function downloadCsv(csv, suffix) {
                        var blob = new Blob(["\uFEFF" + csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
                        var link = document.createElement('a');
                        link.href = URL.createObjectURL(blob);
                        link.download = csvName + (suffix || '') + '_' + new Date().toISOString().slice(0, 10) + '.csv';
                        link.style.display = 'none';
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    }

                    $('#' + exportBtnId).on('click', function() {
                        downloadCsv(buildCsv(t.rows({ search: 'applied' })), '');
                    });

                    // ---- Acciones masivas (Feature 12) ----
                    var bulkEnabled = <?= $enable_bulk ? 'true' : 'false' ?>;
                    if (bulkEnabled) {
                        var selectAll = $('#dt-select-all-' + tableId);
                        var bar       = $('#samap-bulk-bar-' + tableId);
                        var countEl   = $('#samap-bulk-count-' + tableId);
                        var delBtn    = $('#btn-bulk-delete-' + tableId);
                        var cancelBtn = $('#btn-bulk-cancel-' + tableId);
                        var expSelBtn = $('#btn-export-selected-' + tableId);
                        var bulkUrl   = <?= json_encode($bulk_url, JSON_UNESCAPED_UNICODE) ?>;
                        var bulkMsg   = <?= json_encode($bulk_delete_msg, JSON_UNESCAPED_UNICODE) ?>;

                        function updateBulk() {
                            var checked = $('.dt-row-select-' + tableId + ':checked');
                            var n = checked.length;
                            countEl.text(n);
                            bar.toggle(n > 0);
                            // Reflejamos el estado del header en la columna bulk
                            // para todas las filas visibles (las no-visibles quedan
                            // como el usuario las dejo, comportamiento estandar).
                            var visible = $('.dt-row-select-' + tableId, $('#' + tableId).DataTable().rows({ search: 'applied' }).nodes());
                            if (visible.length && visible.filter(':checked').length === visible.length) {
                                selectAll.prop('checked', true);
                            } else {
                                selectAll.prop('checked', false);
                            }
                        }
                        // Click en cualquier row-select.
                        $(document).on('change', '.dt-row-select-' + tableId, updateBulk);
                        // Click en el header "select all".
                        selectAll.on('change', function() {
                            var checked = this.checked;
                            $('.dt-row-select-' + tableId).each(function(){
                                this.checked = checked;
                            });
                            updateBulk();
                        });
                        // Despues de cada redibujo de DataTables (paginacion, busqueda)
                        // recalculamos el estado del header.
                        t.on('draw', updateBulk);
                        updateBulk();

                        cancelBtn.on('click', function() {
                            $('.dt-row-select-' + tableId).prop('checked', false);
                            selectAll.prop('checked', false);
                            updateBulk();
                        });

                        delBtn.on('click', function() {
                            var ids = $('.dt-row-select-' + tableId + ':checked').map(function() { return this.value; }).get();
                            if (!ids.length) return;
                            if (!confirm(bulkMsg + ' (' + ids.length + ' registros)')) return;
                            var url = bulkUrl + '&ids[]=' + ids.map(encodeURIComponent).join('&ids[]=');
                            window.location.href = url;
                        });

                        expSelBtn.on('click', function() {
                            var checked = $('.dt-row-select-' + tableId + ':checked');
                            if (!checked.length) {
                                alert('Seleccioná al menos una fila.');
                                return;
                            }
                            // Subset = filas de DataTables cuyo checkbox esta tildado.
                            var subset = t.rows().filter(function(idx) {
                                var node = this.node();
                                return node && $(node).find('.dt-row-select-' + tableId).is(':checked');
                            });
                            downloadCsv(buildCsv(subset), '_seleccionados');
                        });
                    }

                    // ---- Drag & drop reorder (Feature 13) ----
                    var ordenableEnabled = <?= $ordenable ? 'true' : 'false' ?>;
                    if (ordenableEnabled) {
                        // Necesitamos Sortable.js. Lo cargamos en runtime (mismo
                        // patron que DataTables en este partial) y luego init.
                        function loadSortable(cb) {
                            if (typeof window.Sortable !== 'undefined') { cb(); return; }
                            var s = document.createElement('script');
                            s.src = baseUrl + 'admin/plugins/sortablejs/Sortable.min.js';
                            s.async = false;
                            s.onload = cb;
                            s.onerror = function() { console.error('No se pudo cargar Sortable.min.js'); };
                            document.head.appendChild(s);
                        }
                        function initSortable() {
                            // Re-attach despues de cada draw de DataTables (pagina / sort / search)
                            function attach() {
                                var tbod = $('#' + tableId + ' tbody');
                                if (!tbod.length) return;
                                if (tbod[0].__sortableInstance) { tbod[0].__sortableInstance.destroy(); }
                                tbod[0].__sortableInstance = window.Sortable.create(tbod[0], {
                                    animation: 150,
                                    handle: '.drag-handle',
                                    ghostClass: 'samap-sortable-ghost',
                                    chosenClass: 'samap-sortable-chosen',
                                    dragClass: 'samap-sortable-drag',
                                    onEnd: function() {
                                        var ids = tbod[0].querySelectorAll('tr[data-id]');
                                        var newIds = [];
                                        for (var i = 0; i < ids.length; i++) {
                                            var v = ids[i].getAttribute('data-id');
                                            if (v) { newIds.push(v); }
                                        }
                                        if (!newIds.length) return;
                                        saveOrder(newIds);
                                    }
                                });
                            }
                            var ordenUrl = <?= json_encode($URL . 'admin/' . $slug . '/?reordenar=si', JSON_UNESCAPED_UNICODE) ?>;
                            var csrf     = <?= json_encode($csrf_raw, JSON_UNESCAPED_UNICODE) ?>;
                            function saveOrder(ids) {
                                var fd = new FormData();
                                fd.append('csrf_token', csrf);
                                for (var i = 0; i < ids.length; i++) { fd.append('ids[]', ids[i]); }
                                fetch(ordenUrl, { method: 'POST', body: fd, credentials: 'same-origin' })
                                    .then(function(r){ return r.json().catch(function(){ return {ok:false}; }); })
                                    .then(function(j){
                                        if (j && j.ok) {
                                            // Toast feedback (reutilizamos el helper que header.php ya crea)
                                            if (window.samapFlash) {
                                                window.samapFlash('success', 'Orden actualizado.');
                                            }
                                        } else {
                                            if (window.samapFlash) { window.samapFlash('error', 'No se pudo guardar el nuevo orden.'); }
                                        }
                                    })
                                    .catch(function(){
                                        if (window.samapFlash) { window.samapFlash('error', 'Error de red al guardar el orden.'); }
                                    });
                            }
                            attach();
                            t.on('draw', attach);
                        }
                        loadSortable(initSortable);
                    }
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
