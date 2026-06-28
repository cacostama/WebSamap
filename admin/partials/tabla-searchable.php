<?php
// ============================================================================
// Partial: tabla-searchable.php
// Tabla buscable + exportable a CSV, con DataTables ES, botonera y acciones.
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
//
// Salida:
//   <div class="row"> <div class="panel panel-default"> con
//     - panel-heading: [Exportar CSV] ............... [+ Agregar]
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
$table_id           = isset($table_id)           ? (string)$table_id           : 'datatable1';
$datatables_options = (isset($datatables_options) && is_array($datatables_options))
                        ? $datatables_options
                        : ['pageLength' => 25];
$columns            = (isset($columns) && is_array($columns)) ? $columns : [];

$e_url      = htmlspecialchars($URL, ENT_QUOTES, 'UTF-8');
$e_titulo   = htmlspecialchars($tabla_titulo, ENT_QUOTES, 'UTF-8');
$e_btn_lbl  = htmlspecialchars($btn_agregar_label, ENT_QUOTES, 'UTF-8');
$e_btn_url  = htmlspecialchars($btn_agregar_url, ENT_QUOTES, 'UTF-8');
$e_table_id = htmlspecialchars($table_id, ENT_QUOTES, 'UTF-8');
$e_empty    = htmlspecialchars($empty_message, ENT_QUOTES, 'UTF-8');

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
?>
<div class="row">
    <div class="panel panel-default">
        <div class="panel-heading clearfix">
            <div class="pull-left" style="margin-right:10px;">
                <button id="btn-export-csv-<?= $e_table_id ?>" class="btn btn-default" type="button">
                    <em class="fa fa-download"></em> Exportar CSV
                </button>
            </div>
            <div class="pull-right">
                <a href="<?= $e_url . $e_btn_url ?>" class="btn btn-primary">
                    <em class="fa fa-plus"></em> <?= $e_btn_lbl ?>
                </a>
            </div>
        </div>
        <div class="panel-body">
            <table id="<?= $e_table_id ?>" class="table table-striped table-hover">
                <thead>
                    <tr>
                        <?php foreach ($columns as $col): ?>
                            <th><?= htmlspecialchars((string)($col['th'] ?? ''), ENT_QUOTES, 'UTF-8') ?></th>
                        <?php endforeach; ?>
                        <th class="sort-alpha">Editar</th>
                        <th class="sort-alpha">Borrar</th>
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
                        ?>
                            <tr>
                                <?php foreach ($columns as $col):
                                    $cb = isset($col['td_html']) && is_callable($col['td_html'])
                                        ? $col['td_html']($r)
                                        : '<td></td>';
                                    echo $cb;
                                endforeach; ?>
                                <td width="20px"><div align="center"><a href="<?= $e_url . $e_edit_url ?>"><img width="20px" src="<?= $e_url ?>admin/app/img/editar.png" alt="Editar"/></a></div></td>
                                <td width="20px"><div align="center"><a href="<?= $e_url . $e_delete_url ?>" onclick="return confirm(<?= json_encode($delete_confirm, JSON_UNESCAPED_UNICODE) ?>);"><img width="20px" src="<?= $e_url ?>admin/app/img/borrar.png" alt="Borrar"/></a></div></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>

                    <?php if ($row_count === 0): ?>
                        <tr><td colspan="<?= (int)(count($columns) + 2) ?>" style="text-align:center;color:#888;padding:18px;"><?= $e_empty ?></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="panel-footer">
            Total: <?= (int)$row_count ?> registros
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
                                if (idx === last) return;     // Borrar
                                if (idx === last - 1) return; // Editar
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
