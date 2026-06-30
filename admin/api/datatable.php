<?php
/**
 * admin/api/datatable.php
 *
 * Endpoint generico para DataTables en modo serverSide. Recibe los parametros
 * estandar de DataTables (draw, start, length, search[value], order[0][column],
 * order[0][dir]) y devuelve JSON paginado.
 *
 * URL: admin/api/datatable.php?tabla=<slug>&papelera=0
 *
 * Tablas soportadas (whitelist):
 *   - guia        -> tbl_guiamedica + join especialidad + sanatorio
 *   - sanatorios  -> tbl_sanatorio + join ciudad
 *   - medicos     -> tbl_medicos
 *
 * Beneficio: el HTML inicial baja de 446 KB (guia) a ~3 KB. Cada cambio de
 * pagina/busqueda hace solo el subset visible (~25 filas).
 */
require_once(__DIR__ . '/../funciones/db.php');

header('Content-Type: application/json; charset=utf-8');

// Solo admins logueados
if (!isset($_SESSION['ADM_Username'])) {
    http_response_code(401);
    echo json_encode(['error' => 'no autenticado']);
    exit;
}

mysqli_select_db($connect, $database);

// Parametros DataTables
$draw     = isset($_GET['draw']) ? (int) $_GET['draw'] : 1;
$start    = isset($_GET['start']) ? max(0, (int) $_GET['start']) : 0;
$length   = isset($_GET['length']) ? (int) $_GET['length'] : 25;
$length   = $length <= 0 ? 25 : min(200, $length);   // tope 200/pagina
$q        = isset($_GET['search']['value']) ? trim((string) $_GET['search']['value']) : '';
$orderCol = isset($_GET['order'][0]['column']) ? (int) $_GET['order'][0]['column'] : 0;
$orderDir = isset($_GET['order'][0]['dir']) && $_GET['order'][0]['dir'] === 'desc' ? 'DESC' : 'ASC';
$papelera = isset($_GET['papelera']) && $_GET['papelera'] === '1';
$tabla    = isset($_GET['tabla']) ? (string) $_GET['tabla'] : '';

// Esquema por tabla
$schemas = [
    'guia' => [
        'tabla'   => 'tbl_guiamedica g LEFT JOIN tbl_especialidad e ON g.idEspecialidad = e.id LEFT JOIN tbl_sanatorio s ON g.idSanatorios = s.id',
        'count_from' => 'tbl_guiamedica g',
        'cols'    => [
            ['sql' => 'g.id',         'label' => 'ID',           'orderable' => true],
            ['sql' => 'g.titulo',     'label' => 'Titulo',       'orderable' => true],
            ['sql' => 'g.nombre',     'label' => 'Nombre',       'orderable' => true],
            ['sql' => 'e.nombre',     'label' => 'Especialidad', 'orderable' => true, 'alias' => 'especialidad'],
            ['sql' => 's.nombre',     'label' => 'Sanatorio',    'orderable' => true, 'alias' => 'sanatorio'],
        ],
        'where'   => $papelera ? 'g.deleted_at IS NOT NULL' : 'g.deleted_at IS NULL',
        'search_cols' => ['g.titulo', 'g.nombre', 'e.nombre', 's.nombre'],
        'edit_url'   => 'admin/editarguia/cod/{id}/',
        'delete_url' => 'admin/guia.php?id={id}&borrar=si&csrf_token={csrf}',
    ],
    'sanatorios' => [
        'tabla'   => 'tbl_sanatorio a LEFT JOIN tbl_ciudad b ON a.idCiudad = b.id',
        'count_from' => 'tbl_sanatorio a',
        'cols'    => [
            ['sql' => 'a.id',         'label' => 'ID',        'orderable' => true],
            ['sql' => 'a.nombre',     'label' => 'Nombre',    'orderable' => true],
            ['sql' => 'b.nombre',     'label' => 'Ciudad',    'orderable' => true, 'alias' => 'ciudad'],
            ['sql' => 'a.direccion',  'label' => 'Direccion', 'orderable' => true],
            ['sql' => 'a.estado',     'label' => 'Estado',    'orderable' => true, 'render' => 'estado_badge'],
        ],
        'where'   => $papelera ? 'a.deleted_at IS NOT NULL' : 'a.deleted_at IS NULL',
        'search_cols' => ['a.nombre', 'b.nombre', 'a.direccion'],
        'edit_url'   => 'admin/editarsanatorio/cod/{id}/',
        'delete_url' => 'admin/sanatorios.php?id={id}&borrar=si&csrf_token={csrf}',
    ],
    'medicos' => [
        'tabla'   => 'tbl_medicos',
        'count_from' => 'tbl_medicos',
        'cols'    => [
            ['sql' => 'id',           'label' => 'ID',         'orderable' => true],
            ['sql' => 'titulo',       'label' => 'Titulo',     'orderable' => true],
            ['sql' => 'nombre',       'label' => 'Nombre',     'orderable' => true],
            ['sql' => 'especialidad', 'label' => 'Especialidad','orderable' => true],
        ],
        'where'   => $papelera ? 'deleted_at IS NOT NULL' : 'deleted_at IS NULL',
        'search_cols' => ['titulo', 'nombre', 'especialidad'],
        'edit_url'   => 'admin/editarmedico/cod/{id}/',
        'delete_url' => 'admin/medicos.php?id={id}&borrar=si&csrf_token={csrf}',
    ],
];

if (!isset($schemas[$tabla])) {
    http_response_code(400);
    echo json_encode(['error' => 'tabla no soportada']);
    exit;
}
$sch = $schemas[$tabla];

// ---- Total de filas (sin filtros) ----
$total_q = mysqli_query($connect, "SELECT COUNT(*) AS c FROM " . $sch['count_from'] . " WHERE " . $sch['where']);
$total = $total_q ? (int) (mysqli_fetch_assoc($total_q)['c'] ?? 0) : 0;

// ---- WHERE filtrado por busqueda ----
$where_extra = '';
if ($q !== '') {
    $q_esc = mysqli_real_escape_string($connect, $q);
    $parts = [];
    foreach ($sch['search_cols'] as $col) {
        $parts[] = "$col LIKE '%$q_esc%'";
    }
    $where_extra = ' AND (' . implode(' OR ', $parts) . ')';
}

$filtered_q = mysqli_query($connect, "SELECT COUNT(*) AS c FROM " . $sch['tabla'] . " WHERE " . $sch['where'] . $where_extra);
$filtered = $filtered_q ? (int) (mysqli_fetch_assoc($filtered_q)['c'] ?? 0) : 0;

// ---- ORDER BY ----
$order_sql = '';
if (isset($sch['cols'][$orderCol]) && !empty($sch['cols'][$orderCol]['orderable'])) {
    $order_sql = ' ORDER BY ' . $sch['cols'][$orderCol]['sql'] . ' ' . $orderDir;
}

// ---- SELECT paginado ----
$select_cols = [];
foreach ($sch['cols'] as $c) {
    $alias = isset($c['alias']) ? ' AS `' . $c['alias'] . '`' : '';
    $select_cols[] = $c['sql'] . $alias;
}
$select = implode(', ', $select_cols);
$sql = "SELECT $select FROM " . $sch['tabla'] . " WHERE " . $sch['where'] . $where_extra . $order_sql . " LIMIT $start, $length";
$res = mysqli_query($connect, $sql);

$csrf = function_exists('samap_csrf_valor') ? urlencode(samap_csrf_valor()) : '';

$data = [];
while ($res && $row = mysqli_fetch_array($res, MYSQLI_NUM)) {
    // Render cells: cualquier campo con render especial
    foreach ($sch['cols'] as $i => $c) {
        $v = $row[$i] ?? '';
        if (isset($c['render']) && $c['render'] === 'estado_badge') {
            $activo = ((int) $v) === 1;
            $row[$i] = $activo
                ? '<span class="btn btn-success btn-xs">ACTIVO</span>'
                : '<span class="btn btn-danger btn-xs">INACTIVO</span>';
        } else {
            $row[$i] = htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
        }
    }
    $id = (int) $row[0];
    $edit_href   = str_replace('{id}', (string) $id, $sch['edit_url']);
    $delete_href = str_replace(['{id}', '{csrf}'], [(string) $id, $csrf], $sch['delete_url']);
    // Solo si el usuario puede escribir, muestra los botones de accion (no en papelera)
    if (!$papelera && samap_puede_escribir()) {
        $row[] = '<a href="' . $URL . htmlspecialchars($edit_href, ENT_QUOTES, 'UTF-8') . '"><img width="20" src="' . $URL . 'admin/app/img/editar.png" alt="Editar"/></a>';
        $row[] = '<a href="' . $URL . htmlspecialchars($delete_href, ENT_QUOTES, 'UTF-8') . '" class="samap-confirm" data-samap-confirm-msg="¿Eliminar este registro? Va a la papelera." data-samap-confirm-ok="Sí, eliminar" data-samap-confirm-variant="danger" onclick="return confirm(\'¿Eliminar este registro? Va a la papelera.\')"><img width="20" src="' . $URL . 'admin/app/img/borrar.png" alt="Borrar"/></a>';
    }
    $data[] = $row;
}

echo json_encode([
    'draw'            => $draw,
    'recordsTotal'    => $total,
    'recordsFiltered' => $filtered,
    'data'            => $data,
]);
