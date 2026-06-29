<?php
/**
 * admin/backup.php — Generar y restaurar backups de la base de datos.
 *
 * Solo accesible para rol = admin (operacion destructiva sobre la DB).
 *
 * Acciones:
 *   GET  ?generar=1&csrf_token=...     -> descarga dump .sql.gz
 *   POST restaurar=1                    -> paso 1: subir archivo + mostrar confirmacion
 *   POST restaurar=ejecutar             -> paso 2: ejecutar el restore (paso 2)
 *
 * El dump intenta primero mysqldump (si esta disponible en el contenedor);
 * si falla o no existe, cae a un dump PHP puro. En ambos casos el resultado
 * se comprime con gzencode() para entregar un .sql.gz al navegador.
 *
 * El restore acepta archivos .sql o .sql.gz, valida que parezcan un dump
 * valido (keywords CREATE/INSERT/DROP) y luego corre los statements uno por
 * uno dentro de transacciones chicas (para no inflar el binlog).
 */
require_once('funciones/db.php');
require_once('conexion.php');

mysqli_select_db($connect, $database);

if (!isset($_SESSION['ADM_Username'])) {
	echo "<script>window.location.href=\"" . $URL . "admin/index/\"</script>";
	exit;
}
if (!samap_rol_es('admin')) {
	samap_flash_set('error', 'Solo los administradores pueden acceder a la seccion de backup.');
	header('Location: ' . $URL . 'admin/home/');
	exit;
}

// Limites tolerables para operaciones pesadas (memory_limit es PHP_INI_ALL,
// asi que ini_set funciona; upload/post son PHP_INI_PERDIR y se setean en
// .htaccess).
@ini_set('memory_limit', '512M');
@set_time_limit(0);

$pageOk    = '';
$pageError = '';
$viewMode  = 'main'; // main | confirm | done

// ============================================================================
// Helpers
// ============================================================================

if (!function_exists('samap_shell_has')) {
	function samap_shell_has($cmd) {
		$cmd = trim((string)$cmd);
		if ($cmd === '') return false;
		$out = trim((string)@shell_exec('command -v ' . escapeshellarg($cmd) . ' 2>/dev/null'));
		return $out !== '';
	}
}

if (!function_exists('samap_dump_mysqldump')) {
	function samap_dump_mysqldump() {
		$dbHost = getenv('DB_HOST') ?: 'db';
		$dbName = getenv('DB_NAME') ?: 'web_samap';
		$dbUser = getenv('DB_USER');
		$dbPass = getenv('DB_PASS');

		$cmd = sprintf(
			'mysqldump --host=%s --user=%s --password=%s --single-transaction --routines --triggers --default-character-set=utf8mb4 %s 2>/dev/null',
			escapeshellarg($dbHost),
			escapeshellarg($dbUser),
			escapeshellarg($dbPass),
			escapeshellarg($dbName)
		);
		return shell_exec($cmd);
	}
}

if (!function_exists('samap_dump_php')) {
	function samap_dump_php($conexion, $database) {
		$out  = "-- SAMAP DB dump (PHP fallback)\n";
		$out .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
		$out .= "-- Host: " . php_uname('n') . "\n";
		$out .= "-- Database: `" . str_replace('`', '``', (string)$database) . "`\n\n";
		$out .= "SET NAMES utf8mb4;\n";
		$out .= "SET FOREIGN_KEY_CHECKS=0;\n";
		$out .= "SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n";
		$out .= "SET time_zone = '+00:00';\n\n";

		$r = $conexion->query("SHOW TABLES");
		$tables = [];
		if ($r) {
			while ($row = $r->fetch_array()) $tables[] = $row[0];
			$r->free();
		}

		foreach ($tables as $t) {
			$tEsc = str_replace('`', '``', (string)$t);
			$out .= "--\n-- Table structure for `" . $t . "`\n--\n\n";
			$out .= "DROP TABLE IF EXISTS `" . $tEsc . "`;\n";

			$r2 = $conexion->query("SHOW CREATE TABLE `" . $tEsc . "`");
			if ($r2) {
				$row2 = $r2->fetch_assoc();
				$out .= $row2['Create Table'] . ";\n\n";
				$r2->free();
			}

			$r3 = $conexion->query("SELECT * FROM `" . $tEsc . "`");
			if ($r3 && $r3->num_rows > 0) {
				$cols = $r3->fetch_fields();
				$colNames = array_map(function($c) {
					return '`' . str_replace('`', '``', (string)$c->name) . '`';
				}, $cols);
				$colNamesList = implode(', ', $colNames);

				while ($row3 = $r3->fetch_row()) {
					$vals = array_map(function($v) use ($conexion) {
						if ($v === null) return 'NULL';
						return "'" . $conexion->real_escape_string((string)$v) . "'";
					}, $row3);
					$out .= "INSERT INTO `" . $tEsc . "` ($colNamesList) VALUES (" . implode(', ', $vals) . ");\n";
				}
				$r3->free();
				$out .= "\n";
			}
		}

		$out .= "SET FOREIGN_KEY_CHECKS=1;\n";
		$out .= "-- Dump completo.\n";
		return $out;
	}
}

if (!function_exists('samap_backup_tmp_dir')) {
	function samap_backup_tmp_dir() {
		$dir = sys_get_temp_dir() . '/samap_backups';
		if (!is_dir($dir)) { @mkdir($dir, 0700, true); }
		return $dir;
	}
}

if (!function_exists('samap_backup_tmp_purge')) {
	function samap_backup_tmp_purge() {
		if (empty($_SESSION['samap_backup_tmp_path'])) return;
		$p = $_SESSION['samap_backup_tmp_path'];
		if (is_string($p) && is_file($p)) { @unlink($p); }
		unset(
			$_SESSION['samap_backup_tmp_path'],
			$_SESSION['samap_backup_tmp_name'],
			$_SESSION['samap_backup_tmp_size'],
			$_SESSION['samap_backup_tmp_tables'],
			$_SESSION['samap_backup_tmp_inserts'],
			$_SESSION['samap_backup_tmp_preview']
		);
	}
}

if (!function_exists('samap_sql_split')) {
	/**
	 * Divide un dump SQL en statements. Es lo bastante robusto para el caso
	 * comun (CREATE/INSERT/DROP separados por ;\n). Ignora comentarios -- y
	 * lineas vacias. NO procesa BEGIN..END anidados de triggers/funciones;
	 * para eso habria que parsear DELIMITER (no lo necesitamos en v1).
	 */
	function samap_sql_split($sql) {
		$out = [];
		$lines = preg_split('/\r\n|\n|\r/', $sql);
		$buf = '';
		foreach ($lines as $line) {
			$trim = ltrim($line);
			if ($trim === '' || strpos($trim, '--') === 0) {
				continue;
			}
			$buf .= $line . "\n";
			if (substr(rtrim($line), -1) === ';') {
				$stmt = trim($buf);
				if ($stmt !== '' && $stmt !== ';') $out[] = $stmt;
				$buf = '';
			}
		}
		$tail = trim($buf);
		if ($tail !== '' && $tail !== ';') $out[] = $tail;
		return $out;
	}
}

// ============================================================================
// Accion: generar dump (GET ?generar=1)
// ============================================================================
if (isset($_GET['generar']) && $_GET['generar'] === '1') {
	if (!samap_rol_es('admin') || !samap_csrf_validar()) {
		http_response_code(403);
		die('Acceso denegado. Token invalido o permisos insuficientes.');
	}
	if (!function_exists('gzencode')) {
		http_response_code(500);
		die('La extension zlib de PHP no esta disponible; no se puede comprimir el backup.');
	}

	$dbName = preg_replace('/[^a-zA-Z0-9_]/', '', (string)(getenv('DB_NAME') ?: 'web_samap'));
	if ($dbName === '') $dbName = 'web_samap';
	$filename = $dbName . '_' . date('Y-m-d_His') . '.sql.gz';

	$sql     = '';
	$source  = 'php';
	if (samap_shell_has('mysqldump')) {
		$out = samap_dump_mysqldump();
		if (is_string($out) && trim($out) !== '') {
			$sql    = $out;
			$source = 'mysqldump';
		}
	}
	if ($sql === '') {
		$sql = samap_dump_php($conexion, $database);
	}

	while (ob_get_level() > 0) { @ob_end_clean(); }
	$gz = gzencode($sql, 9);

	header('Content-Type: application/gzip');
	header('Content-Disposition: attachment; filename="' . $filename . '"');
	header('Content-Length: ' . strlen($gz));
	header('Cache-Control: no-cache, must-revalidate');
	header('Expires: 0');
	header('X-Samap-Dump-Source: ' . $source);
	echo $gz;
	exit;
}

// ============================================================================
// Accion: restaurar — paso 1 (subir archivo, validar y mostrar confirmacion)
// ============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['restaurar'] ?? '') === '1') {
	if (!samap_rol_es('admin') || !samap_csrf_validar()) {
		$pageError = 'Token invalido o permisos insuficientes.';
	} elseif (empty($_FILES['backup'])) {
		$pageError = 'No se recibio ningun archivo.';
	} else {
		$f = $_FILES['backup'];
		switch ($f['error'] ?? UPLOAD_ERR_NO_FILE) {
			case UPLOAD_ERR_OK: break;
			case UPLOAD_ERR_INI_SIZE:
			case UPLOAD_ERR_FORM_SIZE:
				$pageError = 'El archivo supera el limite de subida del servidor (revisar upload_max_filesize).';
				break;
			case UPLOAD_ERR_PARTIAL:
				$pageError = 'El archivo se subio solo parcialmente. Reintentalo.';
				break;
			case UPLOAD_ERR_NO_FILE:
				$pageError = 'Debe seleccionar un archivo .sql o .sql.gz.';
				break;
			default:
				$pageError = 'Error al subir el archivo (codigo ' . (int)$f['error'] . ').';
		}

		if ($pageError === '') {
			if (($f['size'] ?? 0) > 200 * 1024 * 1024) {
				$pageError = 'El archivo supera el limite de 200 MB.';
			} else {
				$origName = (string)$f['name'];
				$ext      = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
				$isGz     = ($ext === 'gz');
				$baseName = $isGz ? pathinfo($origName, PATHINFO_FILENAME) : $origName;
				$baseExt  = strtolower((string)pathinfo($baseName, PATHINFO_EXTENSION));

				if (!in_array($isGz ? $baseExt : $ext, ['sql'], true)) {
					$pageError = 'Solo se permiten archivos .sql o .sql.gz (recibido: ' . htmlspecialchars($origName, ENT_QUOTES, 'UTF-8') . ').';
				} else {
					$content = @file_get_contents($f['tmp_name']);
					if ($content === false) {
						$pageError = 'No se pudo leer el archivo subido.';
					} else {
						$looksGz = strlen($content) >= 2 && substr($content, 0, 2) === "\x1f\x8b";
						if ($isGz || $looksGz) {
							if (!function_exists('gzdecode')) {
								$pageError = 'La extension zlib de PHP no esta disponible; no se puede descomprimir el archivo.';
							} else {
								$decompressed = @gzdecode($content);
								if ($decompressed === false) {
									$pageError = 'El archivo .gz no se pudo descomprimir.';
								} else {
									$content = $decompressed;
								}
							}
						}

						if ($pageError === '') {
							$sample    = substr($content, 0, 65536);
							$hasSqlKwd = preg_match('/\b(CREATE\s+TABLE|INSERT\s+INTO|DROP\s+TABLE|TRUNCATE\s+TABLE)\b/i', $sample);
							if (!$hasSqlKwd) {
								$pageError = 'El archivo no parece ser un dump SQL valido (no contiene CREATE/INSERT/DROP).';
							} else {
								samap_backup_tmp_purge();
								$tmpName = 'restore_' . session_id() . '_' . bin2hex(random_bytes(6)) . '.sql';
								$tmpPath = samap_backup_tmp_dir() . '/' . $tmpName;
								if (@file_put_contents($tmpPath, $content) === false) {
									$pageError = 'No se pudo guardar el archivo temporal en el servidor.';
								} else {
									@chmod($tmpPath, 0600);
									$tableCount  = preg_match_all('/CREATE\s+TABLE\b/i', $content);
									$insertCount = preg_match_all('/INSERT\s+INTO\b/i', $content);
									$dropCount   = preg_match_all('/DROP\s+TABLE\b/i', $content);
									$preview     = substr($content, 0, 2000);

									$_SESSION['samap_backup_tmp_path']    = $tmpPath;
									$_SESSION['samap_backup_tmp_name']    = $origName;
									$_SESSION['samap_backup_tmp_size']    = strlen($content);
									$_SESSION['samap_backup_tmp_tables']  = $tableCount;
									$_SESSION['samap_backup_tmp_inserts'] = $insertCount;
									$_SESSION['samap_backup_tmp_drops']   = $dropCount;
									$_SESSION['samap_backup_tmp_preview'] = $preview;

									$viewMode = 'confirm';
								}
							}
						}
					}
				}
			}
		}
	}
}

// ============================================================================
// Accion: restaurar — paso 2 (cancelar)
// ============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['restaurar'] ?? '') === 'cancelar') {
	if (samap_rol_es('admin') && samap_csrf_validar()) {
		samap_backup_tmp_purge();
		$pageOk = 'Restore cancelado. El archivo temporal fue descartado.';
	}
}

// ============================================================================
// Accion: restaurar — paso 3 (ejecutar el restore)
// ============================================================================
$restoreStats = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['restaurar'] ?? '') === 'ejecutar') {
	if (!samap_rol_es('admin') || !samap_csrf_validar()) {
		$pageError = 'Token invalido o permisos insuficientes.';
	} else {
		$tmpPath = (string)($_SESSION['samap_backup_tmp_path'] ?? '');
		$tmpName = (string)($_SESSION['samap_backup_tmp_name'] ?? '');
		if ($tmpPath === '' || !is_file($tmpPath)) {
			$pageError = 'La sesion de restore expiro o el archivo temporal ya no existe. Subi el archivo de nuevo.';
		} else {
			@set_time_limit(0);
			@ini_set('memory_limit', '512M');

			$sql = @file_get_contents($tmpPath);
			if ($sql === false) {
				$pageError = 'No se pudo leer el archivo temporal.';
			} else {
				$statements = samap_sql_split($sql);

				$conexion->query('SET FOREIGN_KEY_CHECKS=0');
				$conexion->query('SET autocommit = 0');

				$ok    = 0;
				$fail  = 0;
				$errMsg = '';
				$errStmt = '';
				$commitEvery = 200;

				foreach ($statements as $i => $stmt) {
					$stmt = trim($stmt);
					if ($stmt === '' || $stmt === ';') continue;
					if (preg_match('/^SET\s+SQL_MODE/i', $stmt)) continue;
					if (preg_match('/^SET\s+time_zone/i', $stmt)) continue;
					if (preg_match('/^SET\s+names/i', $stmt)) continue;
					if (preg_match('/^SET\s+FOREIGN_KEY/i', $stmt)) continue;
					if (!$conexion->query($stmt)) {
						$fail++;
						$errMsg  = $conexion->error;
						$errStmt = mb_substr($stmt, 0, 200);
						break;
					}
					$ok++;
					if (($ok % $commitEvery) === 0) {
						$conexion->commit();
						$conexion->begin_transaction();
					}
				}

				if ($fail > 0) {
					$conexion->rollback();
					$pageError = "Restore abortado en el statement #" . ($ok + 1) . ". " . htmlspecialchars($errMsg, ENT_QUOTES, 'UTF-8') . ". Se hizo rollback. Statement: " . htmlspecialchars($errStmt, ENT_QUOTES, 'UTF-8');
				} else {
					$conexion->commit();
					$pageOk = "Restore completado correctamente. $ok statements ejecutados.";
				}

				$conexion->query('SET FOREIGN_KEY_CHECKS=1');
				$conexion->query('SET autocommit = 1');

				$restoreStats = [
					'file'  => $tmpName,
					'ok'    => $ok,
					'fail'  => $fail,
					'total' => count($statements),
				];

				samap_backup_tmp_purge();
				$viewMode = 'done';
			}
		}
	}
}

// Datos para la pantalla de confirmacion
$confirm = null;
if ($viewMode === 'confirm' && !empty($_SESSION['samap_backup_tmp_path']) && is_file($_SESSION['samap_backup_tmp_path'])) {
	$confirm = [
		'name'    => (string)($_SESSION['samap_backup_tmp_name']    ?? ''),
		'size'    => (int)   ($_SESSION['samap_backup_tmp_size']    ?? 0),
		'tables'  => (int)   ($_SESSION['samap_backup_tmp_tables']  ?? 0),
		'inserts' => (int)   ($_SESSION['samap_backup_tmp_inserts'] ?? 0),
		'drops'   => (int)   ($_SESSION['samap_backup_tmp_drops']   ?? 0),
		'preview' => (string)($_SESSION['samap_backup_tmp_preview'] ?? ''),
	];
} else {
	// Si el temp file desaparecio, forzar main
	if ($viewMode === 'confirm') {
		samap_backup_tmp_purge();
		$viewMode = 'main';
		if ($pageError === '') $pageError = 'La sesion de restore expiro. Subi el archivo de nuevo.';
	}
}

// Util para formatear bytes
function samap_human_bytes($n) {
	$n = (float)$n;
	$units = ['B', 'KB', 'MB', 'GB'];
	$i = 0;
	while ($n >= 1024 && $i < count($units) - 1) { $n /= 1024; $i++; }
	return number_format($n, $i === 0 ? 0 : 1, ',', '.') . ' ' . $units[$i];
}
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
	<title>BACKUP - Administrador</title>

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

				<h3>Backup <small style="color:#888;margin-left:6px;">de la base de datos</small></h3>

				<?php if ($pageError !== ''): ?>
					<div class="alert alert-danger" style="padding:10px 14px;">
						<em class="fa fa-exclamation-triangle"></em>
						<?php echo htmlspecialchars($pageError, ENT_QUOTES, 'UTF-8'); ?>
					</div>
				<?php endif; ?>

				<?php if ($pageOk !== ''): ?>
					<div class="alert alert-success" style="padding:10px 14px;">
						<em class="fa fa-check"></em>
						<?php echo htmlspecialchars($pageOk, ENT_QUOTES, 'UTF-8'); ?>
					</div>
				<?php endif; ?>

				<?php if ($viewMode === 'main'): ?>

					<div class="row">

						<div class="col-md-6">

							<div class="panel panel-default">
								<div class="panel-heading">
									<em class="fa fa-download"></em> Generar backup
								</div>
								<div class="panel-body">
									<p style="margin-top:0;">
										Genera un dump completo de la base de datos
										(<code><?php echo htmlspecialchars($database, ENT_QUOTES, 'UTF-8'); ?></code>)
										y lo descarga como archivo <code>.sql.gz</code>.
									</p>
									<p class="text-muted" style="font-size:12px;">
										El archivo incluye todas las tablas, sus datos, triggers y rutinas
										(donde aplique). El proceso se ejecuta del lado del servidor y no
										interrumpe el funcionamiento normal del sitio.
									</p>
									<p>
										<a class="btn btn-primary"
										   href="<?php echo htmlspecialchars($URL, ENT_QUOTES, 'UTF-8'); ?>admin/backup/?generar=1&amp;csrf_token=<?php echo urlencode(samap_csrf_valor()); ?>">
											<em class="fa fa-download"></em> Generar backup ahora
										</a>
									</p>
									<p class="text-muted" style="font-size:11px;margin-bottom:0;">
										Nombre del archivo: <code>web_samap_YYYY-MM-DD_HHMMSS.sql.gz</code>
									</p>
								</div>
							</div>

						</div>

						<div class="col-md-6">

							<div class="panel panel-default">
								<div class="panel-heading">
									<em class="fa fa-upload"></em> Restaurar backup
								</div>
								<div class="panel-body">
									<div class="alert alert-warning" style="padding:8px 12px;font-size:12px;">
										<em class="fa fa-exclamation-triangle"></em>
										<strong>Operacion destructiva.</strong>
										Restaura la base de datos desde un archivo <code>.sql</code> o
										<code>.sql.gz</code>. Se borraran los datos actuales y se
										reemplazaran por los del archivo. Max 200 MB.
									</div>
									<form method="post" enctype="multipart/form-data"
									      action="<?php echo htmlspecialchars($URL, ENT_QUOTES, 'UTF-8'); ?>admin/backup/">
										<?php echo samap_csrf_field(); ?>
										<input type="hidden" name="restaurar" value="1">

										<div class="form-group">
											<label for="backup_file" class="control-label">Archivo de backup</label>
											<input type="file" name="backup" id="backup_file" accept=".sql,.gz,.sql.gz" required class="filestyle" data-buttonText="Seleccionar archivo">
											<span class="help-block">Formatos aceptados: <code>.sql</code> o <code>.sql.gz</code>. Tamano maximo: 200 MB.</span>
										</div>

										<button type="submit" class="btn btn-danger"
										        onclick="return confirm('Vas a subir un archivo de backup. Se te pedira confirmacion antes de restaurar. Continuar?');">
											<em class="fa fa-upload"></em> Subir y revisar
										</button>
										<a href="<?php echo htmlspecialchars($URL, ENT_QUOTES, 'UTF-8'); ?>admin/home/" class="btn btn-default">Cancelar</a>
									</form>
								</div>
							</div>

						</div>

					</div>

					<div class="row">
						<div class="col-md-12">
							<div class="panel panel-default">
								<div class="panel-heading">
									<em class="fa fa-clock-o"></em> Backups automaticos
								</div>
								<div class="panel-body">
									<p style="margin-top:0;margin-bottom:0;">
										<em class="fa fa-info-circle text-muted"></em>
										Los backups automaticos se realizan via cron en el servidor.
										Esta funcionalidad se encuentra en configuracion del lado del
										equipo de infraestructura y no esta expuesta al panel por el
										momento. Para restaurar un backup automatico, contactarse
										con el equipo de sistemas.
									</p>
								</div>
							</div>
						</div>
					</div>

				<?php elseif ($viewMode === 'confirm' && $confirm !== null): ?>

					<div class="row">
						<div class="col-md-10 col-md-offset-1">

							<div class="panel panel-warning">
								<div class="panel-heading">
									<em class="fa fa-exclamation-triangle"></em> Confirmar restauracion
								</div>
								<div class="panel-body">

									<div class="alert alert-danger" style="padding:10px 14px;">
										<em class="fa fa-warning"></em>
										Estas a punto de restaurar la base de datos desde el archivo
										<strong><?php echo htmlspecialchars($confirm['name'], ENT_QUOTES, 'UTF-8'); ?></strong>.
										Esto <strong>borrara todos los datos actuales</strong> y los
										reemplazara por los del archivo.
									</div>

									<dl class="dl-horizontal" style="margin:0 0 14px 0;">
										<dt style="text-align:left;width:180px;">Archivo</dt>
										<dd style="margin-left:200px;">
											<code><?php echo htmlspecialchars($confirm['name'], ENT_QUOTES, 'UTF-8'); ?></code>
										</dd>
										<dt style="text-align:left;width:180px;">Tamano del SQL</dt>
										<dd style="margin-left:200px;"><?php echo htmlspecialchars(samap_human_bytes($confirm['size']), ENT_QUOTES, 'UTF-8'); ?></dd>
										<dt style="text-align:left;width:180px;"><code>CREATE TABLE</code></dt>
										<dd style="margin-left:200px;"><?php echo (int)$confirm['tables']; ?></dd>
										<dt style="text-align:left;width:180px;"><code>DROP TABLE</code></dt>
										<dd style="margin-left:200px;"><?php echo (int)$confirm['drops']; ?></dd>
										<dt style="text-align:left;width:180px;"><code>INSERT INTO</code></dt>
										<dd style="margin-left:200px;"><?php echo (int)$confirm['inserts']; ?></dd>
									</dl>

									<p style="font-size:12px;color:#888;margin-bottom:6px;">Primeras lineas del archivo:</p>
									<pre style="background:#f7f7f7;border:1px solid #e1e1e1;padding:10px;max-height:260px;overflow:auto;font-size:12px;"><?php echo htmlspecialchars($confirm['preview'], ENT_QUOTES, 'UTF-8'); ?></pre>

									<form method="post"
									      action="<?php echo htmlspecialchars($URL, ENT_QUOTES, 'UTF-8'); ?>admin/backup/"
									      style="display:inline-block;margin-right:6px;">
										<?php echo samap_csrf_field(); ?>
										<input type="hidden" name="restaurar" value="ejecutar">
										<button type="submit" class="btn btn-danger"
										        onclick="return confirm('CONFIRMAR: Se borraran los datos actuales y se reemplazaran por los del archivo. Esta accion NO se puede deshacer. Continuar?');">
											<em class="fa fa-bolt"></em> Confirmar y restaurar
										</button>
									</form>

									<form method="post"
									      action="<?php echo htmlspecialchars($URL, ENT_QUOTES, 'UTF-8'); ?>admin/backup/"
									      style="display:inline-block;">
										<?php echo samap_csrf_field(); ?>
										<input type="hidden" name="restaurar" value="cancelar">
										<button type="submit" class="btn btn-default">
											<em class="fa fa-times"></em> Cancelar
										</button>
									</form>

								</div>
							</div>

						</div>
					</div>

				<?php elseif ($viewMode === 'done' && $restoreStats !== null): ?>

					<div class="row">
						<div class="col-md-10 col-md-offset-1">
							<div class="panel <?php echo $restoreStats['fail'] > 0 ? 'panel-danger' : 'panel-success'; ?>">
								<div class="panel-heading">
									<em class="fa fa-<?php echo $restoreStats['fail'] > 0 ? 'times-circle' : 'check-circle'; ?>"></em>
									Resultado del restore
								</div>
								<div class="panel-body">
									<dl class="dl-horizontal" style="margin:0 0 14px 0;">
										<dt style="text-align:left;width:180px;">Archivo</dt>
										<dd style="margin-left:200px;"><code><?php echo htmlspecialchars($restoreStats['file'], ENT_QUOTES, 'UTF-8'); ?></code></dd>
										<dt style="text-align:left;width:180px;">Statements ejecutados</dt>
										<dd style="margin-left:200px;"><?php echo (int)$restoreStats['ok']; ?></dd>
										<dt style="text-align:left;width:180px;">Fallos</dt>
										<dd style="margin-left:200px;"><?php echo (int)$restoreStats['fail']; ?></dd>
									</dl>
									<?php if ($restoreStats['fail'] > 0): ?>
										<div class="alert alert-danger" style="padding:8px 12px;">
											<em class="fa fa-exclamation-triangle"></em>
											Se realizo rollback. La base de datos quedo como estaba antes del restore.
										</div>
									<?php else: ?>
										<p>La base de datos fue restaurada correctamente. La sesion actual del administrador sigue activa pero
										   algunas pantallas pueden mostrar datos cacheados; recomienda cerrar y reabrir el navegador.</p>
									<?php endif; ?>
									<a href="<?php echo htmlspecialchars($URL, ENT_QUOTES, 'UTF-8'); ?>admin/backup/" class="btn btn-primary">
										<em class="fa fa-arrow-left"></em> Volver a backup
									</a>
								</div>
							</div>
						</div>
					</div>

				<?php endif; ?>

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


	<script src="<?php echo $URL;?>admin/app/js/app.js"></script>

</body>
</html>
