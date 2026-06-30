<?php
/**
 * admin/auditoria.php — Visor del log de auditoria del panel admin.
 *
 * Lee tbl_audit_log paginada 50/registros por pagina, con filtros por
 * usuario, accion, entidad y rango de fechas, mas busqueda libre en
 * descripcion. Permite exportar el resultado filtrado a CSV.
 *
 * Acceso: solo admin (los editores y comerciales no tienen por que ver la
 * trazabilidad del panel).
 */

require_once('funciones/db.php');
require_once('conexion.php');

if (!isset($_SESSION['ADM_Username'])) {
	echo "<script>window.location.href=\"" . $URL . "admin/home/\"</script>";
	exit;
}

if (!samap_rol_es('admin')) {
	samap_flash_set('error', 'No tenés permiso para ver la auditoría del panel.');
	header('Location: ' . $URL . 'admin/home/');
	exit;
}

mysqli_select_db($connect, $database);

// ============================================================================
// Filtros (GET)
// ============================================================================
$f_usuario   = trim((string)($_GET['usuario']  ?? ''));
$f_accion    = trim((string)($_GET['accion']   ?? ''));
$f_entidad   = trim((string)($_GET['entidad']  ?? ''));
$f_desde     = trim((string)($_GET['desde']    ?? ''));
$f_hasta     = trim((string)($_GET['hasta']    ?? ''));
$f_q         = trim((string)($_GET['q']        ?? ''));
$exportar    = isset($_GET['exportar']) && $_GET['exportar'] === 'csv';

$where  = [];
$params = [];
$types  = '';

// usuario: coincidencia exacta (los valores son cortos)
if ($f_usuario !== '') { $where[] = 'usuario = ?'; $params[] = $f_usuario; $types .= 's'; }
if ($f_accion  !== '') { $where[] = 'accion  = ?'; $params[] = $f_accion;  $types .= 's'; }
if ($f_entidad !== '') { $where[] = 'entidad = ?'; $params[] = $f_entidad; $types .= 's'; }

// Rango de fechas (YYYY-MM-DD). Compara contra created_at (que es timestamp).
if ($f_desde !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $f_desde)) {
	$where[] = 'created_at >= ?';
	$params[] = $f_desde . ' 00:00:00';
	$types .= 's';
}
if ($f_hasta !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $f_hasta)) {
	$where[] = 'created_at <= ?';
	$params[] = $f_hasta . ' 23:59:59';
	$types .= 's';
}
if ($f_q !== '') {
	$where[] = 'descripcion LIKE ?';
	$params[] = '%' . $f_q . '%';
	$types .= 's';
}

$where_sql = empty($where) ? '' : 'WHERE ' . implode(' AND ', $where);

// ============================================================================
// Conteos para los chips del header
// ============================================================================
$total_count    = 0;
$hoy_count      = 0;
$semana_count   = 0;
$fallas_count   = 0;

$rc = mysqli_query($connect, "SELECT COUNT(*) AS c FROM tbl_audit_log");
if ($rc) { $total_count  = (int) mysqli_fetch_assoc($rc)['c']; }

$rc = mysqli_query($connect, "SELECT COUNT(*) AS c FROM tbl_audit_log WHERE created_at >= CURDATE()");
if ($rc) { $hoy_count    = (int) mysqli_fetch_assoc($rc)['c']; }

$rc = mysqli_query($connect, "SELECT COUNT(*) AS c FROM tbl_audit_log WHERE created_at >= (NOW() - INTERVAL 7 DAY)");
if ($rc) { $semana_count = (int) mysqli_fetch_assoc($rc)['c']; }

$rc = mysqli_query($connect, "SELECT COUNT(*) AS c FROM tbl_audit_log WHERE accion IN ('login_fail','hard_delete')");
if ($rc) { $fallas_count = (int) mysqli_fetch_assoc($rc)['c']; }

// ============================================================================
// Dropdown data: usuarios / acciones / entidades distintas
// ============================================================================
$usuarios_opts = [];
$rc = mysqli_query($connect, "SELECT DISTINCT usuario FROM tbl_audit_log ORDER BY usuario ASC");
if ($rc) { while ($r = mysqli_fetch_assoc($rc)) { $usuarios_opts[] = (string)$r['usuario']; } }

$entidades_opts = [];
$rc = mysqli_query($connect, "SELECT DISTINCT entidad FROM tbl_audit_log WHERE entidad <> '' ORDER BY entidad ASC");
if ($rc) { while ($r = mysqli_fetch_assoc($rc)) { $entidades_opts[] = (string)$r['entidad']; } }

$acciones_opts = ['login', 'login_fail', 'logout', 'insert', 'update', 'delete', 'restore', 'hard_delete', 'view', 'export'];

// ============================================================================
// Exportar CSV (debe ir ANTES de cualquier salida HTML)
// ============================================================================
if ($exportar) {
	$filename = 'auditoria_' . date('Ymd_His') . '.csv';
	header('Content-Type: text/csv; charset=utf-8');
	header('Content-Disposition: attachment; filename=' . $filename);
	header('Pragma: no-cache');
	header('Expires: 0');

	$out = fopen('php://output', 'w');
	// BOM para que Excel respete acentos
	fwrite($out, "\xEF\xBB\xBF");
	fputcsv($out, ['id', 'created_at', 'usuario', 'rol', 'accion', 'entidad', 'entidad_id', 'descripcion', 'datos_anteriores', 'datos_nuevos', 'ip', 'user_agent']);

	$sql = "SELECT id, created_at, usuario, rol, accion, entidad, entidad_id, descripcion, datos_anteriores, datos_nuevos, ip, user_agent FROM tbl_audit_log $where_sql ORDER BY created_at DESC, id DESC";
	$stmt = $conexion->prepare($sql);
	if ($stmt) {
		if (!empty($params)) { $stmt->bind_param($types, ...$params); }
		$stmt->execute();
		$res = $stmt->get_result();
		while ($row = $res->fetch_assoc()) {
			fputcsv($out, [
				$row['id'],
				$row['created_at'],
				$row['usuario'],
				$row['rol'],
				$row['accion'],
				$row['entidad'],
				$row['entidad_id'],
				$row['descripcion'],
				$row['datos_anteriores'] ?? '',
				$row['datos_nuevos']    ?? '',
				$row['ip'],
				$row['user_agent'],
			]);
		}
		$stmt->close();
	}
	fclose($out);
	@samap_audit_log('export', 'tbl_audit_log', 0, "Exportó CSV de auditoría (" . count($params) . " filtros)");
	exit;
}

// ============================================================================
// Paginacion (50/pag)
// ============================================================================
$page    = max(1, (int)($_GET['page'] ?? 1));
$perpage = 50;
$offset  = ($page - 1) * $perpage;

// Total filtrado
$total_filtrado = 0;
$count_sql = "SELECT COUNT(*) AS c FROM tbl_audit_log $where_sql";
$cs = $conexion->prepare($count_sql);
if ($cs) {
	if (!empty($params)) { $cs->bind_param($types, ...$params); }
	$cs->execute();
	$cr = $cs->get_result();
	if ($cr) { $total_filtrado = (int)$cr->fetch_assoc()['c']; }
	$cs->close();
}
$total_pages = max(1, (int)ceil($total_filtrado / $perpage));

// Datos
$rows = [];
$sql = "SELECT id, created_at, usuario, rol, accion, entidad, entidad_id, descripcion, datos_anteriores, datos_nuevos, ip, user_agent
        FROM tbl_audit_log $where_sql
        ORDER BY created_at DESC, id DESC
        LIMIT $perpage OFFSET $offset";
$stmt = $conexion->prepare($sql);
if ($stmt) {
	if (!empty($params)) { $stmt->bind_param($types, ...$params); }
	$stmt->execute();
	$res = $stmt->get_result();
	while ($r = $res->fetch_assoc()) { $rows[] = $r; }
	$stmt->close();
}

// Estilo de la accion: badge con color segun tipo.
$accion_color = [
	'login'        => 'success',
	'login_fail'   => 'danger',
	'logout'       => 'default',
	'insert'       => 'primary',
	'update'       => 'warning',
	'delete'       => 'warning',
	'restore'      => 'info',
	'hard_delete'  => 'danger',
	'view'         => 'default',
	'export'       => 'info',
];

// URL base para los links de paginacion / export
$qs = $_GET;
unset($qs['page']);
$base_qs = http_build_query($qs);
$export_url = $URL . 'admin/auditoria/?' . $base_qs . ($base_qs !== '' ? '&' : '') . 'exportar=csv';
?>
<!DOCTYPE html>
<html lang="es">
<head>

	<meta charset="utf-8">
	<meta http-equiv="x-ua-compatible" content="ie=edge">
	<meta http-equiv="Content-Language" content="es"/>
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
	<meta name="description" content="">
	<meta name="keywords" content="">
	<meta name="author" content="">
	<title>AUDITORÍA -  Administrador</title>

	<link rel="stylesheet" href="<?php echo $URL;?>admin/app/css/bootstrap.css">
	<link rel="stylesheet" href="<?php echo $URL;?>admin/plugins/fontawesome/css/font-awesome.min.css">
	<link rel="stylesheet" href="<?php echo $URL;?>admin/plugins/animo/animate+animo.css">
	<link rel="stylesheet" href="<?php echo $URL;?>admin/plugins/csspinner/csspinner.min.css">
	<link rel="stylesheet" href="<?php echo $URL;?>admin/app/css/app.css?v=202606291705">

	<style>
		.aud-json { display: none; white-space: pre-wrap; word-break: break-all; background: #f8f8f8; padding: 8px; border: 1px solid #eee; border-radius: 3px; max-height: 240px; overflow: auto; font-family: monospace; font-size: 11px; margin: 4px 0 0; }
		.aud-toggle { cursor: pointer; color: #888; font-size: 11px; text-decoration: underline; }
		.aud-toggle:hover { color: #274767; }
	</style>
</head>
<body>

	<section class="wrapper">

		<?php include 'header.php'; ?>
		<?php include 'aside.php'; ?>

		<section>

			<section class="main-content">

				<h3>Auditoría del panel</h3>
				<p style="color:#888;margin-top:-.5rem;">Registro de todas las acciones críticas (CRUD sobre contenido, cambios de estado de leads, login/logout y cambios de contraseña).</p>

				<!-- Chips de resumen -->
				<div class="row" style="margin-bottom:14px;">
					<div class="col-sm-12">
						<span class="label label-default" style="font-size:13px;padding:6px 12px;margin-right:4px;">Total: <?= (int)$total_count ?></span>
						<span class="label label-info"    style="font-size:13px;padding:6px 12px;margin-right:4px;">Hoy: <?= (int)$hoy_count ?></span>
						<span class="label label-primary" style="font-size:13px;padding:6px 12px;margin-right:4px;">Última semana: <?= (int)$semana_count ?></span>
						<span class="label label-danger"  style="font-size:13px;padding:6px 12px;margin-right:4px;">Fallos/borrados duros: <?= (int)$fallas_count ?></span>
						<span class="pull-right" style="color:#888;font-size:12px;line-height:30px;">Mostrando <?= count($rows) ?> de <?= (int)$total_filtrado ?> filtrados</span>
					</div>
				</div>

				<!-- Filtros -->
				<div class="row" style="margin-bottom:12px;">
					<div class="col-sm-12">
						<form class="form-inline" method="get" action="<?= htmlspecialchars($URL, ENT_QUOTES, 'UTF-8') ?>admin/auditoria/">
							<select name="usuario" class="form-control input-sm" style="margin-right:4px;">
								<option value="">Todos los usuarios</option>
								<?php foreach ($usuarios_opts as $u): ?>
									<option value="<?= htmlspecialchars($u, ENT_QUOTES, 'UTF-8') ?>" <?= $f_usuario === $u ? 'selected' : '' ?>><?= htmlspecialchars($u, ENT_QUOTES, 'UTF-8') ?></option>
								<?php endforeach; ?>
							</select>
							<select name="accion" class="form-control input-sm" style="margin-right:4px;">
								<option value="">Todas las acciones</option>
								<?php foreach ($acciones_opts as $a): ?>
									<option value="<?= htmlspecialchars($a, ENT_QUOTES, 'UTF-8') ?>" <?= $f_accion === $a ? 'selected' : '' ?>><?= htmlspecialchars($a, ENT_QUOTES, 'UTF-8') ?></option>
								<?php endforeach; ?>
							</select>
							<select name="entidad" class="form-control input-sm" style="margin-right:4px;">
								<option value="">Todas las entidades</option>
								<?php foreach ($entidades_opts as $e): ?>
									<option value="<?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?>" <?= $f_entidad === $e ? 'selected' : '' ?>><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></option>
								<?php endforeach; ?>
							</select>
							<input type="date" name="desde" value="<?= htmlspecialchars($f_desde, ENT_QUOTES, 'UTF-8') ?>" class="form-control input-sm" placeholder="Desde" style="margin-right:4px;">
							<input type="date" name="hasta" value="<?= htmlspecialchars($f_hasta, ENT_QUOTES, 'UTF-8') ?>" class="form-control input-sm" placeholder="Hasta" style="margin-right:4px;">
							<input type="text" name="q" value="<?= htmlspecialchars($f_q, ENT_QUOTES, 'UTF-8') ?>" placeholder="Buscar en descripción..." class="form-control input-sm" style="margin-right:4px;width:220px;">
							<button type="submit" class="btn btn-primary btn-sm" style="margin-right:4px;">Filtrar</button>
							<a href="<?= htmlspecialchars($URL, ENT_QUOTES, 'UTF-8') ?>admin/auditoria/" class="btn btn-default btn-sm" style="margin-right:4px;">Limpiar</a>
							<a href="<?= htmlspecialchars($export_url, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-success btn-sm" style="margin-left:6px;"><em class="fa fa-download"></em> Exportar CSV</a>
						</form>
					</div>
				</div>

				<!-- Tabla -->
				<div class="row">
					<div class="panel panel-default">
						<div class="panel-heading clearfix">
							<div class="pull-left">
								<strong>Eventos</strong>
								<?php if ($total_filtrado !== $total_count): ?>
									<small class="text-muted">(filtrado: <?= (int)$total_filtrado ?> de <?= (int)$total_count ?>)</small>
								<?php endif; ?>
							</div>
						</div>
						<div class="panel-body" style="padding:0;">
							<table class="table table-striped table-hover" style="margin-bottom:0;">
								<thead>
									<tr>
										<th style="width:150px;">Fecha</th>
										<th style="width:120px;">Usuario</th>
										<th style="width:80px;">Rol</th>
										<th style="width:110px;">Acción</th>
										<th style="width:160px;">Entidad</th>
										<th style="width:60px;">ID</th>
										<th>Descripción</th>
										<th style="width:140px;">IP</th>
									</tr>
								</thead>
								<tbody>
									<?php if (empty($rows)): ?>
										<tr><td colspan="8" style="text-align:center;color:#888;padding:24px;">No hay eventos con esos filtros.</td></tr>
									<?php else: ?>
										<?php foreach ($rows as $r):
											$acc = (string)$r['accion'];
											$col = $accion_color[$acc] ?? 'default';
											$ts  = (string)$r['created_at'];
											$ts_fmt = '';
											if ($ts !== '' && ($ep = strtotime($ts)) !== false) { $ts_fmt = date('d/m/Y H:i:s', $ep); }
											$id_e      = (int)$r['entidad_id'];
											$ent_e     = htmlspecialchars((string)$r['entidad'], ENT_QUOTES, 'UTF-8');
											$desc_e    = htmlspecialchars((string)$r['descripcion'], ENT_QUOTES, 'UTF-8');
											$user_e    = htmlspecialchars((string)$r['usuario'], ENT_QUOTES, 'UTF-8');
											$rol_e     = htmlspecialchars((string)$r['rol'], ENT_QUOTES, 'UTF-8');
											$ip_e      = htmlspecialchars((string)$r['ip'], ENT_QUOTES, 'UTF-8');
											$prev_json = (string)($r['datos_anteriores'] ?? '');
											$new_json  = (string)($r['datos_nuevos']    ?? '');
											$has_json  = ($prev_json !== '' || $new_json !== '');
										?>
											<tr>
												<td><small style="font-family:monospace;"><?= htmlspecialchars($ts_fmt, ENT_QUOTES, 'UTF-8') ?></small></td>
												<td><strong><?= $user_e ?></strong></td>
												<td><small class="text-muted"><?= $rol_e ?></small></td>
												<td><span class="label label-<?= htmlspecialchars($col, ENT_QUOTES, 'UTF-8') ?>" style="font-size:11px;"><?= htmlspecialchars($acc, ENT_QUOTES, 'UTF-8') ?></span></td>
												<td><small><?= $ent_e ?><?= $id_e > 0 ? ' <strong>#' . $id_e . '</strong>' : '' ?></small></td>
												<td></td>
												<td>
													<?= $desc_e ?>
													<?php if ($has_json): ?>
														<br><span class="aud-toggle" onclick="var j=this.nextElementSibling; var v=(j.style.display==='block'); j.style.display=v?'none':'block'; this.textContent=v?'▼ Ver JSON':'▲ Ocultar JSON';">▼ Ver JSON</span>
														<pre class="aud-json"><?php
															if ($prev_json !== '') { echo "ANTERIOR:\n" . htmlspecialchars($prev_json, ENT_QUOTES, 'UTF-8') . "\n\n"; }
															if ($new_json  !== '') { echo "NUEVO:\n"     . htmlspecialchars($new_json,  ENT_QUOTES, 'UTF-8'); }
														?></pre>
													<?php endif; ?>
												</td>
												<td><small style="font-family:monospace;color:#888;"><?= $ip_e ?></small></td>
											</tr>
										<?php endforeach; ?>
									<?php endif; ?>
								</tbody>
							</table>
						</div>
						<?php if ($total_pages > 1): ?>
							<div class="panel-footer">
								<ul class="pagination" style="margin:0;">
									<?php
									$base_url = $URL . 'admin/auditoria/?' . $base_qs . ($base_qs !== '' ? '&' : '');
									$prev = max(1, $page - 1);
									$next = min($total_pages, $page + 1);
									?>
									<li class="<?= $page <= 1 ? 'disabled' : '' ?>"><a href="<?= htmlspecialchars($base_url . 'page=' . $prev, ENT_QUOTES, 'UTF-8') ?>">&laquo;</a></li>
									<?php
									$start = max(1, $page - 2);
									$end   = min($total_pages, $page + 2);
									if ($start > 1) { echo '<li><a href="' . htmlspecialchars($base_url . 'page=1', ENT_QUOTES, 'UTF-8') . '">1</a></li>'; if ($start > 2) echo '<li class="disabled"><span>…</span></li>'; }
									for ($i = $start; $i <= $end; $i++) {
										$cls = $i === $page ? 'active' : '';
										echo '<li class="' . $cls . '"><a href="' . htmlspecialchars($base_url . 'page=' . $i, ENT_QUOTES, 'UTF-8') . '">' . $i . '</a></li>';
									}
									if ($end < $total_pages) { if ($end < $total_pages - 1) echo '<li class="disabled"><span>…</span></li>'; echo '<li><a href="' . htmlspecialchars($base_url . 'page=' . $total_pages, ENT_QUOTES, 'UTF-8') . '">' . $total_pages . '</a></li>'; }
									?>
									<li class="<?= $page >= $total_pages ? 'disabled' : '' ?>"><a href="<?= htmlspecialchars($base_url . 'page=' . $next, ENT_QUOTES, 'UTF-8') ?>">&raquo;</a></li>
									<li class="disabled"><span style="border:none;background:transparent;color:#888;padding:6px 12px;">Página <?= (int)$page ?> de <?= (int)$total_pages ?></span></li>
								</ul>
							</div>
						<?php endif; ?>
					</div>
				</div>

			</section>

		</section>

	</section>
	<?php include 'partials/scripts-comunes.php'; ?>
</body>
</html>
