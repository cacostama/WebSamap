<?php
/**
 * admin/leads.php — Inbox de leads (formularios de contacto / trabaje con nosotros).
 *
 * Persistencia: enviar.php hace INSERT en tbl_leads despues de validar; esta
 * pantalla los lista con filtros, permite cambiar estado, editar notas y borrar.
 *
 * PII: a partir de la migracion 012 los campos sensibles (nombre, email,
 * telefono) se encriptan en columnas VARBINARY con AES-256-GCM. Esta pagina
 * los desencripta transparentemente. Filas pre-migration (sin _enc) caen
 * a los valores en claro via samap_get_lead_field(). El telefono se muestra
 * enmascarado (***-***-XXXX) y requiere click explicito en "Ver" para
 * revelar el valor completo, que queda registrado en tbl_audit_log.
 *
 * Acciones (todas requieren samap_puede_escribir() + samap_csrf_validar()):
 *   ?cambiar_estado=1&id=N&estado=...   -> UPDATE estado
 *   ?borrar=si&id=N                    -> DELETE FROM tbl_leads WHERE id=N
 *   ?guardar_nota=1&id=N&notas=...     -> UPDATE notas
 *
 * Filtros (GET, no requieren CSRF — son solo lectura):
 *   ?filtro_estado=  (nuevo|contactado|cerrado|spam)
 *   ?filtro_origen=  (contacto|trabajo)
 *   ?q=              (busqueda libre en nombre/email/mensaje — la busqueda
 *                     por email exacto se hace contra data_hash)
 */
require_once('funciones/db.php');
require_once('conexion.php');

if (!isset($_SESSION['ADM_Username'])) {
	echo "<script>window.location.href=\"" . $URL . "admin/home/\"</script>";
	exit;
}

mysqli_select_db($connect, $database);

// ============================================================================
// Acciones de escritura (POST/GET con CSRF)
// ============================================================================
$mensaje_accion = '';

if (isset($_GET['cambiar_estado']) && isset($_GET['id']) && isset($_GET['estado'])) {
	if (!samap_puede_escribir() || !samap_csrf_validar()) {
		$mensaje_accion = 'No se pudo cambiar el estado. Token invalido o permisos insuficientes.';
	} else {
		$id     = (int) $_GET['id'];
		$estados_validos = ['nuevo', 'contactado', 'cerrado', 'spam'];
		$estado = (string) $_GET['estado'];
		if ($id > 0 && in_array($estado, $estados_validos, true)) {
			// Snapshot del estado anterior para el audit log.
			$estado_anterior = null;
			if ($s = $conexion->prepare("SELECT estado FROM tbl_leads WHERE id = ?")) {
				$s->bind_param('i', $id);
				$s->execute();
				$r = $s->get_result();
				if ($r && ($row = $r->fetch_assoc())) { $estado_anterior = (string)$row['estado']; }
				$s->close();
			}
			$stmt = $conexion->prepare("UPDATE tbl_leads SET estado = ? WHERE id = ?");
			if ($stmt) {
				$stmt->bind_param('si', $estado, $id);
				$stmt->execute();
				$stmt->close();
				$mensaje_accion = 'Estado actualizado.';
				@samap_audit_log('update', 'tbl_leads', $id, "Cambió estado del lead #$id: " . ($estado_anterior ?? '?') . " -> $estado", ['estado' => $estado_anterior], ['estado' => $estado]);
			}
		} else {
			$mensaje_accion = 'Estado o id invalido.';
		}
	}
}

if (isset($_GET['guardar_nota']) && isset($_GET['id']) && isset($_GET['notas'])) {
	if (!samap_puede_escribir() || !samap_csrf_validar()) {
		$mensaje_accion = 'No se pudo guardar la nota. Token invalido o permisos insuficientes.';
	} else {
		$id    = (int) $_GET['id'];
		$notas = trim((string) $_GET['notas']);
		if ($id > 0) {
			$stmt = $conexion->prepare("UPDATE tbl_leads SET notas = ? WHERE id = ?");
			if ($stmt) {
				$stmt->bind_param('si', $notas, $id);
				$stmt->execute();
				$stmt->close();
				$mensaje_accion = 'Nota guardada.';
			}
		}
	}
}

if (isset($_GET['borrar']) && $_GET['borrar'] === 'si' && isset($_GET['id'])) {
	if (!samap_puede_escribir() || !samap_csrf_validar()) {
		$mensaje_accion = 'No se pudo eliminar el lead. Token invalido o permisos insuficientes.';
	} else {
		$id = (int) $_GET['id'];
		if ($id > 0) {
			$stmt = $conexion->prepare("DELETE FROM tbl_leads WHERE id = ?");
			if ($stmt) {
				$stmt->bind_param('i', $id);
				$stmt->execute();
				$stmt->close();
				$mensaje_accion = 'Lead eliminado.';
			}
		}
	}
}

// ============================================================================
// Reveal de telefono (PII) — endpoint AJAX para el boton "Ver" en la tabla.
// Devuelve JSON con el telefono desencriptado. Cada llamada genera una fila
// en tbl_audit_log con accion='view_pii'.
// ============================================================================
if (isset($_GET['ver_tel']) && $_GET['ver_tel'] === '1' && isset($_GET['id'])) {
	header('Content-Type: application/json; charset=utf-8');
	if (!samap_csrf_validar()) {
		http_response_code(403);
		echo json_encode(['ok' => false, 'error' => 'csrf']);
		exit;
	}
	$id = (int) $_GET['id'];
	$tel_revealed = '';
	if ($id > 0) {
		$stmt = $conexion->prepare('SELECT telefono_enc, telefono FROM tbl_leads WHERE id = ?');
		if ($stmt) {
			$stmt->bind_param('i', $id);
			$stmt->execute();
			$res = $stmt->get_result();
			if ($res && ($r = $res->fetch_assoc())) {
				$tel_revealed = samap_get_lead_field($r, 'telefono_enc', 'telefono');
			}
			$stmt->close();
		}
		@samap_audit_log('view_pii', 'tbl_leads', $id, "Revelo telefono del lead #$id (Ley 6534/20 audit)");
	}
	echo json_encode([
		'ok'  => true,
		'id'  => $id,
		'tel' => $tel_revealed,
	]);
	exit;
}

// ============================================================================
// Filtros
// ============================================================================
$filtro_estado = isset($_GET['filtro_estado']) ? (string) $_GET['filtro_estado'] : '';
$filtro_origen = isset($_GET['filtro_origen']) ? (string) $_GET['filtro_origen'] : '';
$q             = isset($_GET['q']) ? trim((string) $_GET['q']) : '';

$estados_validos = ['nuevo', 'contactado', 'cerrado', 'spam'];
$origenes_validos = ['contacto', 'trabajo'];

$where  = [];
$params = [];
$types  = '';

if (in_array($filtro_estado, $estados_validos, true)) {
	$where[] = 'estado = ?';
	$params[] = $filtro_estado;
	$types .= 's';
}
if (in_array($filtro_origen, $origenes_validos, true)) {
	$where[] = 'origen = ?';
	$params[] = $filtro_origen;
	$types .= 's';
}
if ($q !== '') {
	// Busqueda libre: si parece un email, match exacto por data_hash
	// (no requiere desencriptar todas las filas). Si no, fallback a LIKE
	// sobre los campos en claro (legacy + nombre + mensaje).
	if (filter_var($q, FILTER_VALIDATE_EMAIL)) {
		$hash = samap_data_hash($q);
		$where[] = 'data_hash = ?';
		$params[] = $hash;
		$types .= 's';
	} else {
		$where[] = '(nombre LIKE ? OR mensaje LIKE ?)';
		$like = '%' . $q . '%';
		$params[] = $like; $params[] = $like;
		$types .= 'ss';
	}
}

$where_sql = empty($where) ? '' : 'WHERE ' . implode(' AND ', $where);

// Listamos las columnas necesarias explicitamente. nombre / email /
// telefono son las legacy en claro (filas pre-migration); nombre_enc /
// email_enc / telefono_enc son las encriptadas (filas nuevas). La UI las
// lee con samap_get_lead_field() que prioriza _enc y cae a plain.
$sql_list = "SELECT id, origen, nombre, nombre_enc, email, email_enc, telefono, telefono_enc, mensaje, ip, user_agent, estado, notas, created_at, updated_at, data_hash FROM tbl_leads $where_sql ORDER BY created_at DESC, id DESC LIMIT 500";
$stmt = $conexion->prepare($sql_list);
if ($stmt) {
	if (!empty($params)) {
		$stmt->bind_param($types, ...$params);
	}
	$stmt->execute();
	$leads = $stmt->get_result();
} else {
	$leads = false;
}

// ============================================================================
// Conteos por estado (para los chips del header)
// ============================================================================
$counts = [
	'nuevo' => 0, 'contactado' => 0, 'cerrado' => 0, 'spam' => 0, 'total' => 0,
];
$res = mysqli_query($connect, "SELECT estado, COUNT(*) AS c FROM tbl_leads GROUP BY estado");
if ($res) {
	while ($r = mysqli_fetch_assoc($res)) {
		$counts[$r['estado']] = (int) $r['c'];
		$counts['total']     += (int) $r['c'];
	}
	mysqli_free_result($res);
}

$estado_label = [
	'nuevo'      => 'Nuevo',
	'contactado' => 'Contactado',
	'cerrado'    => 'Cerrado',
	'spam'       => 'Spam',
];
$estado_color = [
	'nuevo'      => '#f6504d',
	'contactado' => '#ffc61d',
	'cerrado'    => '#4ac18e',
	'spam'       => '#919293',
];
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
	<title>LEADS -  Administrador</title>

	</script><link rel="stylesheet" href="<?php echo $URL;?>admin/app/css/bootstrap.css">

	<link rel="stylesheet" href="<?php echo $URL;?>admin/plugins/fontawesome/css/font-awesome.min.css">
	<link rel="stylesheet" href="<?php echo $URL;?>admin/plugins/animo/animate+animo.css">
	<link rel="stylesheet" href="<?php echo $URL;?>admin/plugins/csspinner/csspinner.min.css">

	<link rel="stylesheet" href="<?php echo $URL;?>admin/app/css/app.css?v=202606291705">
</head>
<body>

	<section class="wrapper">

		<?php include 'header.php'; ?>
		<?php include 'aside.php'; ?>

		<section>

			<section class="main-content">

				<h3>Leads</h3>

				<?php if ($mensaje_accion !== ''): ?>
					<div class="alert alert-info" style="padding:8px 12px;"><?php echo htmlspecialchars($mensaje_accion, ENT_QUOTES, 'UTF-8'); ?></div>
				<?php endif; ?>

				<div class="row" style="margin-bottom:12px;">
					<div class="col-sm-12">
						<span class="label label-danger" style="font-size:13px;padding:6px 12px;margin-right:4px;">NUEVO: <?= (int)$counts['nuevo'] ?></span>
						<span class="label label-warning" style="font-size:13px;padding:6px 12px;margin-right:4px;">CONTACTADO: <?= (int)$counts['contactado'] ?></span>
						<span class="label label-success" style="font-size:13px;padding:6px 12px;margin-right:4px;">CERRADO: <?= (int)$counts['cerrado'] ?></span>
						<span class="label label-default" style="font-size:13px;padding:6px 12px;margin-right:4px;">SPAM: <?= (int)$counts['spam'] ?></span>
						<span class="pull-right" style="color:#888;font-size:12px;line-height:30px;">Total: <?= (int)$counts['total'] ?> leads</span>
					</div>
				</div>

				<div class="row" style="margin-bottom:12px;">
					<div class="col-sm-12">
						<form id="samap-filtros-leads" class="form-inline" method="get" action="<?= htmlspecialchars($URL, ENT_QUOTES, 'UTF-8') ?>admin/leads/">
							<select name="filtro_estado" class="form-control input-sm" data-samap-autosubmit>
								<option value="">Todos los estados</option>
								<?php foreach ($estados_validos as $e): ?>
									<option value="<?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?>" <?= $filtro_estado === $e ? 'selected' : '' ?>><?= htmlspecialchars($estado_label[$e], ENT_QUOTES, 'UTF-8') ?></option>
								<?php endforeach; ?>
							</select>
							<select name="filtro_origen" class="form-control input-sm" style="margin-left:8px;" data-samap-autosubmit>
								<option value="">Todos los orígenes</option>
								<?php foreach ($origenes_validos as $o): ?>
									<option value="<?= htmlspecialchars($o, ENT_QUOTES, 'UTF-8') ?>" <?= $filtro_origen === $o ? 'selected' : '' ?>><?= htmlspecialchars(ucfirst($o), ENT_QUOTES, 'UTF-8') ?></option>
								<?php endforeach; ?>
							</select>
							<input type="text" name="q" value="<?= htmlspecialchars($q, ENT_QUOTES, 'UTF-8') ?>" placeholder="Buscar por nombre, email o mensaje" class="form-control input-sm" style="margin-left:8px;width:280px;">
							<button type="submit" class="btn btn-primary btn-sm" style="margin-left:8px;">Filtrar</button>
							<a href="<?= htmlspecialchars($URL, ENT_QUOTES, 'UTF-8') ?>admin/leads/" class="btn btn-default btn-sm" style="margin-left:4px;" title="Limpiar todos los filtros" data-samap-limpiar-filtros>Limpiar filtros</a>
						</form>
						<script>
						(function(){
							// Auto-submit del form al cambiar selects con data-samap-autosubmit.
							// El input de busqueda libre espera Enter o click en Filtrar.
							var form = document.getElementById('samap-filtros-leads');
							if (!form) return;
							form.querySelectorAll('[data-samap-autosubmit]').forEach(function(el){
								el.addEventListener('change', function(){ form.submit(); });
							});
							var limpiar = form.querySelector('[data-samap-limpiar-filtros]');
							if (limpiar) {
								limpiar.addEventListener('click', function(e){
									// Si los filtros estan vacios, igual limpiamos el campo q.
									form.querySelectorAll('select').forEach(function(s){ s.value = ''; });
									var qi = form.querySelector('input[name="q"]'); if (qi) qi.value = '';
								});
							}
						})();
						</script>
					</div>
				</div>

				<div class="row">
					<div class="panel panel-default">
						<div class="panel-heading clearfix">
							<div class="pull-left">
								<strong>Listado de leads</strong>
								<small class="text-muted">(max 500)</small>
							</div>
						</div>
						<div class="panel-body">
							<table class="table table-striped table-hover">
								<thead>
									<tr>
										<th>ID</th>
										<th>Fecha</th>
										<th>Origen</th>
										<th>Nombre</th>
										<th>Email</th>
										<th>Teléfono</th>
										<th>Mensaje</th>
										<th>Estado</th>
										<th>Notas</th>
										<th>Acciones</th>
									</tr>
								</thead>
								<tbody>
									<?php if ($leads && $leads->num_rows > 0): ?>
										<?php while ($row = $leads->fetch_assoc()):
											$id = (int) $row['id'];
											$estado_actual = (string) $row['estado'];
											$csrf = samap_csrf_valor();
											$e_csrf = htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8');
											$e_id = htmlspecialchars((string)$id, ENT_QUOTES, 'UTF-8');
											$e_origen = htmlspecialchars((string)$row['origen'], ENT_QUOTES, 'UTF-8');

											// PII: desencriptar con fallback a columnas legacy.
											$nombre_plain = samap_get_lead_field($row, 'nombre_enc', 'nombre');
											$email_plain  = samap_get_lead_field($row, 'email_enc',  'email');
											$tel_plain    = samap_get_lead_field($row, 'telefono_enc', 'telefono');
											$e_nombre = htmlspecialchars($nombre_plain, ENT_QUOTES, 'UTF-8');
											$e_email  = htmlspecialchars($email_plain,  ENT_QUOTES, 'UTF-8');
											$e_tel    = htmlspecialchars($tel_plain,    ENT_QUOTES, 'UTF-8');
											// Mascara del telefono: solo ultimos 4 digitos visibles.
											$tel_masked = '';
											if ($tel_plain !== '') {
												$tel_digits = preg_replace('/\D+/', '', $tel_plain);
												$tel_masked = strlen($tel_digits) >= 4
													? '***-***-' . substr($tel_digits, -4)
													: '***';
											}
											$e_tel_masked = htmlspecialchars($tel_masked, ENT_QUOTES, 'UTF-8');

											$mensaje_full  = (string)$row['mensaje'];
											$mensaje_trunc = mb_strlen($mensaje_full) > 80 ? mb_substr($mensaje_full, 0, 80) . '…' : $mensaje_full;
											$e_mensaje = htmlspecialchars($mensaje_trunc, ENT_QUOTES, 'UTF-8');
											$fecha_fmt = '';
											if (!empty($row['created_at'])) {
												$ts = strtotime((string)$row['created_at']);
												if ($ts !== false) { $fecha_fmt = date('d/m/Y H:i', $ts); }
											}
											$color = $estado_color[$estado_actual] ?? '#777';
											$label = $estado_label[$estado_actual] ?? $estado_actual;
											$notas_full = (string)($row['notas'] ?? '');
											$e_notas = htmlspecialchars($notas_full, ENT_QUOTES, 'UTF-8');
										?>
											<tr>
												<td><?= $e_id ?></td>
												<td><?= htmlspecialchars($fecha_fmt, ENT_QUOTES, 'UTF-8') ?></td>
												<td><?= $e_origen ?></td>
												<td><?= $e_nombre !== '' ? $e_nombre : '<span class="text-muted">-</span>' ?></td>
												<td>
													<a href="mailto:<?= $e_email ?>" title="Ver email completo"><?= $e_email !== '' ? $e_email : '<span class="text-muted">-</span>' ?></a>
												</td>
												<td>
													<?php if ($tel_plain !== ''): ?>
														<span class="samap-tel-masked" data-lead-id="<?= $e_id ?>" data-tel="<?= $e_tel ?>" data-tel-masked="<?= $e_tel_masked ?>"><?= $e_tel_masked ?></span>
														<a href="#" class="samap-tel-toggle btn btn-xs btn-default" data-lead-id="<?= $e_id ?>" style="margin-left:4px;" title="Ver telefono completo (queda registrado en auditoria)"><em class="fa fa-eye"></em> Ver</a>
													<?php else: ?>
														<span class="text-muted">-</span>
													<?php endif; ?>
												</td>
												<td title="<?= htmlspecialchars($mensaje_full, ENT_QUOTES, 'UTF-8') ?>" style="max-width:280px;"><?= $e_mensaje ?></td>
												<td>
													<form method="get" action="<?= htmlspecialchars($URL, ENT_QUOTES, 'UTF-8') ?>admin/leads/" style="display:inline;">
														<input type="hidden" name="id" value="<?= $e_id ?>">
														<input type="hidden" name="cambiar_estado" value="1">
														<input type="hidden" name="csrf_token" value="<?= $e_csrf ?>">
														<select name="estado" onchange="this.form.submit()" class="form-control input-xs" style="width:auto;display:inline-block;background:<?= htmlspecialchars($color, ENT_QUOTES, 'UTF-8') ?>;color:#fff;border-color:<?= htmlspecialchars($color, ENT_QUOTES, 'UTF-8') ?>;font-weight:600;">
															<?php foreach ($estados_validos as $opt): ?>
																<option value="<?= htmlspecialchars($opt, ENT_QUOTES, 'UTF-8') ?>" <?= $estado_actual === $opt ? 'selected' : '' ?>><?= htmlspecialchars($estado_label[$opt], ENT_QUOTES, 'UTF-8') ?></option>
															<?php endforeach; ?>
														</select>
													</form>
												</td>
												<td style="max-width:200px;">
													<a href="#" data-toggle="modal" data-target="#notasModal<?= $e_id ?>" title="Editar notas">
														<?= $e_notas !== '' ? mb_strlen($notas_full) > 30 ? htmlspecialchars(mb_substr($notas_full, 0, 30) . '…', ENT_QUOTES, 'UTF-8') : $e_notas : '<em class="fa fa-pencil text-muted"></em>' ?>
													</a>
													<div class="modal fade" id="notasModal<?= $e_id ?>" tabindex="-1" role="dialog" aria-hidden="true">
														<div class="modal-dialog">
															<div class="modal-content">
																<form method="get" action="<?= htmlspecialchars($URL, ENT_QUOTES, 'UTF-8') ?>admin/leads/">
																	<div class="modal-header">
																		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
																		<h4 class="modal-title">Notas — Lead #<?= $e_id ?> (<?= $e_nombre ?>)</h4>
																	</div>
																	<div class="modal-body">
																		<input type="hidden" name="id" value="<?= $e_id ?>">
																		<input type="hidden" name="guardar_nota" value="1">
																		<input type="hidden" name="csrf_token" value="<?= $e_csrf ?>">
																		<textarea name="notas" class="form-control" rows="6" placeholder="Notas internas..."><?= $e_notas ?></textarea>
																	</div>
																	<div class="modal-footer">
																		<button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
																		<button type="submit" class="btn btn-primary">Guardar</button>
																	</div>
																</form>
															</div>
														</div>
													</div>
												</td>
												<td style="white-space:nowrap;">
													<a href="<?= htmlspecialchars($URL, ENT_QUOTES, 'UTF-8') ?>admin/leads/?cambiar_estado=1&id=<?= $e_id ?>&estado=spam&csrf_token=<?= $e_csrf ?>" class="btn btn-xs btn-default" title="Marcar como spam" onclick="return confirm('¿Marcar como spam?')"><em class="fa fa-ban"></em></a>
													<a href="<?= htmlspecialchars($URL, ENT_QUOTES, 'UTF-8') ?>admin/leads/?borrar=si&id=<?= $e_id ?>&csrf_token=<?= $e_csrf ?>" class="btn btn-xs btn-danger" title="Borrar" onclick="return confirm('¿Eliminar este lead? No se puede deshacer.')"><em class="fa fa-trash"></em></a>
												</td>
											</tr>
										<?php endwhile; ?>
									<?php else: ?>
										<tr><td colspan="10" style="text-align:center;color:#888;padding:18px;">No hay leads con esos filtros.</td></tr>
									<?php endif; ?>
								</tbody>
							</table>
						</div>
					</div>
				</div>

			</section>

		</section>

	</section>
	<?php include 'partials/scripts-comunes.php'; ?>

	<script>
	(function(){
		// Reveal del telefono: hace GET al endpoint AJAX del mismo archivo
		// (?ver_tel=1&id=N&csrf_token=...) que registra view_pii en audit log
		// y devuelve JSON con el telefono desencriptado.
		var csrf = <?php echo json_encode(samap_csrf_valor()); ?>;
		document.querySelectorAll('.samap-tel-toggle').forEach(function(btn){
			btn.addEventListener('click', function(e){
				e.preventDefault();
				var id = btn.getAttribute('data-lead-id');
				var span = document.querySelector('.samap-tel-masked[data-lead-id="' + id + '"]');
				if (!span) return;
				var url = <?php echo json_encode($URL); ?> + 'admin/leads/?ver_tel=1&id=' + encodeURIComponent(id) + '&csrf_token=' + encodeURIComponent(csrf);
				btn.innerHTML = '<em class="fa fa-spinner fa-spin"></em>';
				fetch(url, { credentials: 'same-origin' })
					.then(function(r){ return r.json(); })
					.then(function(j){
						if (j && j.ok && j.tel) {
							span.textContent = j.tel;
							span.setAttribute('data-tel-revealed', '1');
							btn.outerHTML = '<em class="fa fa-check text-muted" title="Telefono revelado (registrado en auditoria)"></em>';
						} else {
							btn.innerHTML = '<em class="fa fa-eye"></em> Ver';
							alert('No se pudo obtener el telefono.');
						}
					})
					.catch(function(){
						btn.innerHTML = '<em class="fa fa-eye"></em> Ver';
						alert('Error de red al revelar el telefono.');
					});
			});
		});
	})();
	</script>

</body>
</html>
