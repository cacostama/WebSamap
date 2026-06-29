<?php
/**
 * admin/usuarios.php — ABM de usuarios del panel (admin-only).
 *
 * Listado + papelera + soft-delete. Los editores y comerciales son redirigidos
 * a home por session.php antes de llegar aca; este archivo re-verifica el
 * rol como defense in depth.
 *
 * Acciones (todas requieren samap_rol_es('admin') + samap_csrf_validar()):
 *   ?borrar=si&id=N      -> soft-delete (deleted_at = NOW())
 *   ?restaurar=si&id=N   -> restaurar (deleted_at = NULL, activo = 1)
 *   ?borrar_def=si&id=N  -> DELETE fisico (solo papelera)
 *
 * Safety:
 *   - El admin no puede borrarse a si mismo (lockout).
 *   - El admin no puede borrar al unico admin activo (lockout global).
 */
require_once('funciones/db.php');
require_once('conexion.php');

if (!isset($_SESSION['ADM_Username'])) {
	echo "<script>window.location.href=\"" . $URL . "admin/index/\"</script>";
	exit;
}
if (!samap_rol_es('admin')) {
	samap_flash_set('error', 'Solo los administradores pueden gestionar usuarios.');
	header('Location: ' . $URL . 'admin/home/');
	exit;
}

// ---- ID del usuario logueado (lo necesitamos para validar auto-acciones) ----
$current_user_id = 0;
$cu = $conexion->prepare('SELECT id FROM tbl_user WHERE userName = ?');
if ($cu) {
	$cu->bind_param('s', $_SESSION['ADM_Username']);
	$cu->execute();
	$cuRes = $cu->get_result();
	if ($cuRow = $cuRes->fetch_assoc()) {
		$current_user_id = (int) $cuRow['id'];
	}
	$cu->close();
}

$papelera = isset($_GET['papelera']) && $_GET['papelera'] === '1';

// ---- Soft-delete: marcar deleted_at ----
if (isset($_GET['borrar']) && $_GET['borrar'] === 'si' && isset($_GET['id'])) {
	if (!samap_rol_es('admin') || !samap_csrf_validar()) {
		samap_flash_set('error', 'No se pudo eliminar el usuario. Token invalido o permisos insuficientes.');
		header('Location: ' . $URL . 'admin/usuarios/');
		exit;
	}
	$id = (int) $_GET['id'];
	if ($id <= 0) {
		samap_flash_set('error', 'ID invalido.');
		header('Location: ' . $URL . 'admin/usuarios/');
		exit;
	}
	// Lockout 1: no borrarse a si mismo.
	if ($id === $current_user_id) {
		samap_flash_set('error', 'No podés eliminar tu propio usuario. Pedile a otro administrador que lo haga.');
		header('Location: ' . $URL . 'admin/usuarios/');
		exit;
	}
	// Lockout 2: no borrar al unico admin activo.
	$es_admin = 0;
	$esq = $conexion->prepare('SELECT rol FROM tbl_user WHERE id = ?');
	$esq->bind_param('i', $id);
	$esq->execute();
	$esRes = $esq->get_result();
	if ($esRes && $esRow = $esRes->fetch_assoc()) {
		$es_admin = ((string) $esRow['rol'] === 'admin') ? 1 : 0;
	}
	$esq->close();
	if ($es_admin) {
		$cnt = 0;
		$cq = $conexion->prepare("SELECT COUNT(*) AS c FROM tbl_user WHERE rol = 'admin' AND activo = 1 AND deleted_at IS NULL AND id <> ?");
		$cq->bind_param('i', $id);
		$cq->execute();
		$cRes = $cq->get_result();
		if ($cRes && $cRow = $cRes->fetch_assoc()) {
			$cnt = (int) $cRow['c'];
		}
		$cq->close();
		if ($cnt === 0) {
			samap_flash_set('error', 'No podés eliminar al unico administrador activo. Primero promové a otro usuario a admin.');
			header('Location: ' . $URL . 'admin/usuarios/');
			exit;
		}
	}
	$up = $conexion->prepare('UPDATE tbl_user SET deleted_at = NOW() WHERE id = ?');
	$up->bind_param('i', $id);
	$up->execute();
	$up->close();
	samap_flash_set('success', 'Usuario enviado a la papelera. Ya no puede iniciar sesion.');
	header('Location: ' . $URL . 'admin/usuarios/');
	exit;
}

// ---- Papelera: restaurar ----
if (isset($_GET['restaurar']) && $_GET['restaurar'] === 'si' && ($_GET['id'] ?? '') != '' && samap_rol_es('admin') && samap_csrf_validar()) {
	$id = (int) $_GET['id'];
	$up = $conexion->prepare('UPDATE tbl_user SET deleted_at = NULL, activo = 1 WHERE id = ?');
	$up->bind_param('i', $id);
	$up->execute();
	$up->close();
	samap_flash_set('success', 'Usuario restaurado. Ya puede volver a iniciar sesion.');
	header('Location: ' . $URL . 'admin/usuarios/?papelera=1');
	exit;
}

// ---- Papelera: borrar definitivamente ----
if (isset($_GET['borrar_def']) && $_GET['borrar_def'] === 'si' && ($_GET['id'] ?? '') != '' && samap_rol_es('admin') && samap_csrf_validar()) {
	$id = (int) $_GET['id'];
	if ($id === $current_user_id) {
		samap_flash_set('error', 'No podés eliminar tu propio usuario.');
		header('Location: ' . $URL . 'admin/usuarios/?papelera=1');
		exit;
	}
	$del = $conexion->prepare('DELETE FROM tbl_user WHERE id = ?');
	$del->bind_param('i', $id);
	$del->execute();
	$del->close();
	samap_flash_set('warning', 'Usuario eliminado definitivamente. No se puede recuperar.');
	header('Location: ' . $URL . 'admin/usuarios/?papelera=1');
	exit;
}

// ---- Listado ----
$where_sql = $papelera ? 'WHERE u.deleted_at IS NOT NULL' : 'WHERE u.deleted_at IS NULL';
$sql_list = "SELECT u.id, u.nombre, u.userName, u.rol, u.activo, u.ultimo_acceso, u.deleted_at
             FROM tbl_user u
             $where_sql
             ORDER BY u.id ASC";
$rows = $conexion->query($sql_list);

// ---- Conteo de papelera (para el toggle) ----
$trash_count = 0;
$tc = $conexion->query("SELECT COUNT(*) AS c FROM tbl_user WHERE deleted_at IS NOT NULL");
if ($tc && $tcRow = $tc->fetch_assoc()) {
	$trash_count = (int) $tcRow['c'];
	$tc->close();
}

$rol_etiqueta = [
	'admin'     => 'Administrador',
	'editor'    => 'Editor de contenidos',
	'comercial' => 'Comercial',
];
$rol_color = [
	'admin'     => '#f6504d', // rojo
	'editor'    => '#5d9cec', // azul
	'comercial' => '#919293', // gris
];

// ---- Inputs para partials/tabla-searchable.php ----
$tabla_titulo       = 'Usuarios';
$btn_agregar_label  = 'Agregar Usuario';
$btn_agregar_url    = 'admin/agregar-usuario.php';
$edit_url_pattern   = 'admin/editarusuario/cod/{id}/';
$delete_url_pattern = 'admin/usuarios.php?id={id}&borrar=si&csrf_token={csrf}';
$delete_confirm     = '¿Eliminar este usuario? Pasara a la papelera y no podra iniciar sesion.';
$empty_message      = 'No hay usuarios cargados.';
$slug               = 'usuarios';
$papelera_activa    = $papelera;

$URL_BASE = $URL;
$columns = [
	['th' => 'ID',     'td_html' => function($r) { return '<td>' . (int)$r['id'] . '</td>'; }],
	['th' => 'Nombre', 'td_html' => function($r) {
		$n = htmlspecialchars((string)($r['nombre'] ?? ''), ENT_QUOTES, 'UTF-8');
		return '<td>' . $n . '</td>';
	}],
	['th' => 'Usuario', 'td_html' => function($r) {
		$u = htmlspecialchars((string)($r['userName'] ?? ''), ENT_QUOTES, 'UTF-8');
		return '<td><code>' . $u . '</code></td>';
	}],
	['th' => 'Rol', 'td_html' => function($r) use ($rol_etiqueta, $rol_color) {
		$rol = (string)($r['rol'] ?? 'admin');
		$lbl = htmlspecialchars($rol_etiqueta[$rol] ?? $rol, ENT_QUOTES, 'UTF-8');
		$clr = htmlspecialchars($rol_color[$rol] ?? '#777', ENT_QUOTES, 'UTF-8');
		return '<td><span class="label" style="background:' . $clr . ';color:#fff;font-weight:600;padding:4px 8px;">' . $lbl . '</span></td>';
	}],
	['th' => 'Último acceso', 'td_html' => function($r) {
		$ua = $r['ultimo_acceso'] ?? null;
		if (empty($ua) || $ua === '0000-00-00 00:00:00') {
			return '<td><span class="text-muted">Nunca</span></td>';
		}
		$ts = strtotime((string)$ua);
		if ($ts === false) {
			return '<td><span class="text-muted">-</span></td>';
		}
		return '<td>' . htmlspecialchars(date('d/m/Y H:i', $ts), ENT_QUOTES, 'UTF-8') . '</td>';
	}],
	['th' => 'Estado', 'td_html' => function($r) {
		$activo = (int)($r['activo'] ?? 0);
		if ($activo === 1) {
			return '<td><span class="label label-success" style="font-weight:600;padding:4px 8px;">ACTIVO</span></td>';
		}
		return '<td><span class="label label-warning" style="font-weight:600;padding:4px 8px;">INACTIVO</span></td>';
	}],
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
	<title>USUARIOS -  Administrador</title>

	</script><link rel="stylesheet" href="<?php echo $URL;?>admin/app/css/bootstrap.css">

	<link rel="stylesheet" href="<?php echo $URL;?>admin/plugins/fontawesome/css/font-awesome.min.css">
	<link rel="stylesheet" href="<?php echo $URL;?>admin/plugins/animo/animate+animo.css">
	<link rel="stylesheet" href="<?php echo $URL;?>admin/plugins/csspinner/csspinner.min.css">

	<link rel="stylesheet" href="<?php echo $URL;?>admin/app/css/app.css?v=202606291705">

	<script src="<?php echo $URL;?>admin/plugins/modernizr/modernizr.js" type="application/javascript"></script>

	<script src="<?php echo $URL;?>admin/plugins/fastclick/fastclick.js" type="application/javascript"></script>
</head>
<body>

	<section class="wrapper">

		<?php include 'header.php'; ?>
		<?php include 'aside.php'; ?>

		<section>

			<section class="main-content">

				<h3><?php echo htmlspecialchars($tabla_titulo, ENT_QUOTES, 'UTF-8'); ?></h3>

				<?php if ($current_user_id === 0): ?>
					<div class="alert alert-warning" style="padding:8px 12px;">
						<em class="fa fa-exclamation-triangle"></em>
						No se pudo identificar al usuario logueado. Algunas protecciones contra auto-bloqueo no aplicaran.
					</div>
				<?php endif; ?>

				<?php include 'partials/papelera-toggle.php'; ?>
				<?php if ($rows) { $rows2 = $rows; include 'partials/tabla-searchable.php'; } ?>
			</section>

		</section>

	</section>



	<script src="<?php echo $URL;?>admin/plugins/jquery/jquery.min.js"></script>
	<script src="<?php echo $URL;?>admin/plugins/bootstrap/js/bootstrap.min.js"></script>

	<script src="<?php echo $URL;?>admin/plugins/chosen/chosen.jquery.min.js"></script>
	<script src="<?php echo $URL;?>admin/plugins/slider/js/bootstrap-slider.js"></script>
	<script src="<?php echo $URL;?>admin/plugins/filestyle/bootstrap-filestyle.min.js"></script>

	<script src="<?php echo $URL;?>admin/plugins/animo/animo.min.js"></script>

	<script src="<?php echo $URL;?>admin/plugins/sparklines/jquery.sparkline.min.js"></script>

	<script src="<?php echo $URL;?>admin/plugins/slimscroll/jquery.slimscroll.min.js"></script>

	<script src="<?php echo $URL;?>admin/plugins/flot/jquery.flot.min.js"></script>
	<script src="<?php echo $URL;?>admin/plugins/flot/jquery.flot.tooltip.min.js"></script>
	<script src="<?php echo $URL;?>admin/plugins/flot/jquery.flot.resize.min.js"></script>
	<script src="<?php echo $URL;?>admin/plugins/flot/jquery.flot.pie.min.js"></script>
	<script src="<?php echo $URL;?>admin/plugins/flot/jquery.flot.time.min.js"></script>
	<script src="<?php echo $URL;?>admin/plugins/flot/jquery.flot.categories.min.js"></script>

	<!--[if lt IE 8]><script src="js/excanvas.min.js"></script><![endif]-->
	<script src="<?php echo $URL;?>admin/plugins/datatable/media/js/jquery.dataTables.min.js"></script>
	<script src="<?php echo $URL;?>admin/plugins/datatable/extensions/datatable-bootstrap/js/dataTables.bootstrap.js"></script>
	<script src="<?php echo $URL;?>admin/plugins/datatable/extensions/datatable-bootstrap/js/dataTables.bootstrapPagination.js"></script>
	<script src="<?php echo $URL;?>admin/plugins/datatable/extensions/ColVis/js/dataTables.colVis.min.js"></script>

	<script src="<?php echo $URL;?>admin/app/js/app.js"></script>

</body>
</html>
