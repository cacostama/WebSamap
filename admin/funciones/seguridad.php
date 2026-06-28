<?php

// ============================================================================
// Helpers de seguridad compartidos por el panel admin.
// Se incluye desde funciones/db.php DESPUES de arrancar la sesion (session.php),
// asi que aca ya hay $_SESSION disponible.
//
//   - CSRF: token por sesion + helpers para imprimir el campo y validarlo.
//   - Rate-limit de login: throttling por IP basado en archivos temporales,
//     para frenar ataques de fuerza bruta sin depender de la DB.
// ============================================================================

// ---- CSRF ----
if (!function_exists('samap_csrf_token')) {
	function samap_csrf_token() {
		if (empty($_SESSION['csrf_token'])) {
			$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
		}
		return $_SESSION['csrf_token'];
	}

	// Imprime el <input hidden> listo para pegar dentro de cualquier <form>.
	function samap_csrf_field() {
		return '<input type="hidden" name="csrf_token" value="'
			. htmlspecialchars(samap_csrf_token(), ENT_QUOTES) . '">';
	}

	// Devuelve solo el valor (para URLs/links de borrado por GET).
	function samap_csrf_valor() {
		return samap_csrf_token();
	}

	// Valida el token recibido (POST o GET) contra el de la sesion.
	function samap_csrf_validar() {
		$tok = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
		return !empty($_SESSION['csrf_token'])
			&& is_string($tok)
			&& hash_equals($_SESSION['csrf_token'], $tok);
	}
}

// ---- Rate-limit de login (5 intentos fallidos / 15 min por IP) ----
if (!function_exists('samap_login_bloqueado')) {
	function samap_login_archivo() {
		$dir = sys_get_temp_dir() . '/samap_login';
		if (!is_dir($dir)) { @mkdir($dir, 0700, true); }
		$ip = $_SERVER['REMOTE_ADDR'] ?? 'desconocida';
		return $dir . '/' . md5($ip) . '.json';
	}

	function samap_login_estado() {
		$f = samap_login_archivo();
		if (!is_file($f)) return ['intentos' => 0, 'ts' => 0];
		$d = json_decode((string)@file_get_contents($f), true);
		return is_array($d) ? $d : ['intentos' => 0, 'ts' => 0];
	}

	// True si la IP supero el maximo dentro de la ventana de tiempo.
	function samap_login_bloqueado() {
		$ventana = 900; // 15 minutos
		$max     = 5;
		$d = samap_login_estado();
		if (($d['ts'] ?? 0) + $ventana < time()) return false; // ventana vencida
		return ($d['intentos'] ?? 0) >= $max;
	}

	// Suma un intento fallido (reinicia el contador si la ventana ya vencio).
	function samap_login_registrar_fallo() {
		$ventana = 900;
		$d = samap_login_estado();
		if (($d['ts'] ?? 0) + $ventana < time()) {
			$d = ['intentos' => 0, 'ts' => time()];
		}
		$d['intentos'] = ($d['intentos'] ?? 0) + 1;
		$d['ts'] = time();
		@file_put_contents(samap_login_archivo(), json_encode($d), LOCK_EX);
	}

	// Login exitoso -> borra el contador de la IP.
	function samap_login_limpiar() {
		$f = samap_login_archivo();
		if (is_file($f)) @unlink($f);
	}
}

// ---- #9 Roles (admin | editor | comercial) ----
if (!function_exists('samap_rol')) {
	// Rol del usuario logueado (default admin para sesiones viejas sin rol).
	function samap_rol() {
		return $_SESSION['ADM_Rol'] ?? 'admin';
	}

	// True si el rol actual esta entre los permitidos.
	function samap_rol_es(/* ...$roles */) {
		return in_array(samap_rol(), func_get_args(), true);
	}

	// True si el rol puede crear/editar/borrar contenido (admin o editor).
	function samap_puede_escribir() {
		return samap_rol_es('admin', 'editor');
	}
}

// ---- Flash messages (toast no-bloqueante) ----
// Reemplaza el patron viejo de "alert() + window.location.href" con un mensaje
// en sesion que la pagina destino (la que recibe el header('Location:')) renderiza
// como toast en el top-right. Auto-dismiss a los 5s, cierre manual con la X.
if (!function_exists('samap_flash_set')) {
	function samap_flash_set($tipo, $msg) {
		if (session_status() !== PHP_SESSION_ACTIVE) return;
		if (!isset($_SESSION['samap_flash']) || !is_array($_SESSION['samap_flash'])) {
			$_SESSION['samap_flash'] = [];
		}
		$_SESSION['samap_flash'][] = [
			'tipo' => (string)$tipo,
			'msg'  => (string)$msg,
		];
	}

	function samap_flash_get() {
		if (empty($_SESSION['samap_flash']) || !is_array($_SESSION['samap_flash'])) return [];
		$f = $_SESSION['samap_flash'];
		unset($_SESSION['samap_flash']);
		return $f;
	}

	function samap_flash_render() {
		$flashes = samap_flash_get();
		if (empty($flashes)) return '';

		$bg = [
			'success' => '#4ac18e',
			'error'   => '#f6504d',
			'warning' => '#ffc61d',
			'info'    => '#00afd1',
		];
		$color_text = [
			'success' => '#fff',
			'error'   => '#fff',
			'warning' => '#333',
			'info'    => '#fff',
		];
		$icon = [
			'success' => 'check',
			'error'   => 'times',
			'warning' => 'exclamation-triangle',
			'info'    => 'info-circle',
		];

		$html  = '<div id="samap-toasts" style="position:fixed;top:70px;right:20px;z-index:99999;display:flex;flex-direction:column;gap:8px;">';
		foreach ($flashes as $f) {
			$tipo  = (string)($f['tipo'] ?? 'info');
			$msg   = (string)($f['msg']  ?? '');
			$bg_c  = $bg[$tipo]     ?? '#888';
			$fg_c  = $color_text[$tipo] ?? '#fff';
			$ic    = $icon[$tipo]   ?? 'info';
			$msg_e = htmlspecialchars($msg, ENT_QUOTES, 'UTF-8');
			$html .= '<div class="samap-toast" data-samap-toast style="background:' . $bg_c . ';color:' . $fg_c . ';padding:12px 18px;border-radius:4px;box-shadow:0 4px 12px rgba(0,0,0,.2);min-width:280px;max-width:420px;display:flex;align-items:center;gap:10px;animation:samapToastIn 0.3s ease-out;">';
			$html .= '<em class="fa fa-' . $ic . '" style="font-size:20px;"></em>';
			$html .= '<span style="flex:1;font-size:14px;">' . $msg_e . '</span>';
			$html .= '<button type="button" class="samap-toast-close" aria-label="Cerrar" style="background:transparent;border:none;color:inherit;font-size:18px;cursor:pointer;padding:0 4px;line-height:1;">&times;</button>';
			$html .= '</div>';
		}
		$html .= '</div>';
		$html .= '<script>(function(){'
			. 'var s=document.createElement("style");'
			. 's.textContent="@keyframes samapToastIn{from{transform:translateX(20px);opacity:0}to{transform:translateX(0);opacity:1}}";'
			. 'document.head.appendChild(s);'
			. 'document.querySelectorAll("[data-samap-toast] .samap-toast-close").forEach(function(b){'
			. 'b.addEventListener("click",function(){var t=b.closest("[data-samap-toast]");if(t)t.remove();});'
			. '});'
			. 'setTimeout(function(){'
			. 'document.querySelectorAll("[data-samap-toast]").forEach(function(el){'
			. 'el.style.transition="opacity 0.3s,transform 0.3s";'
			. 'el.style.opacity="0";'
			. 'el.style.transform="translateX(20px)";'
			. 'setTimeout(function(){el.remove();},300);'
			. '});'
			. '},5000);'
			. '})();</script>';
		return $html;
	}
}

// ---- #11 Audit log ----
if (!function_exists('samap_audit_log')) {
	// Inserta una fila en tbl_audit_log. Tolerante a fallos: si el insert
	// falla (tabla inexistente, DB caida, FK rota) NO rompe la operacion
	// principal. Pensado para llamarse como side-effect de cada CRUD.
	function samap_audit_log($accion, $entidad = '', $entidad_id = 0, $descripcion = '', $datos_anteriores = null, $datos_nuevos = null) {
		$user  = isset($_SESSION['ADM_Username']) ? (string)$_SESSION['ADM_Username'] : 'anonymous';
		$rol   = samap_rol();
		$ip    = isset($_SERVER['REMOTE_ADDR']) ? (string)$_SERVER['REMOTE_ADDR'] : '';
		$ua    = isset($_SERVER['HTTP_USER_AGENT']) ? substr((string)$_SERVER['HTTP_USER_AGENT'], 0, 250) : '';
		$prev  = $datos_anteriores !== null ? json_encode($datos_anteriores, JSON_UNESCAPED_UNICODE) : null;
		$new   = $datos_nuevos    !== null ? json_encode($datos_nuevos,    JSON_UNESCAPED_UNICODE) : null;
		$desc  = substr((string)$descripcion, 0, 500);
		$ent   = substr((string)$entidad,    0, 60);

		global $connect, $database;
		if (!isset($connect) || !($connect instanceof mysqli)) { return; }
		@mysqli_select_db($connect, $database);
		$stmt = @$connect->prepare('INSERT INTO tbl_audit_log (usuario, rol, accion, entidad, entidad_id, descripcion, datos_anteriores, datos_nuevos, ip, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
		if (!$stmt) { return; }
		$stmt->bind_param('ssssisssss', $user, $rol, $accion, $ent, $entidad_id, $desc, $prev, $new, $ip, $ua);
		@$stmt->execute();
		@$stmt->close();
	}
}

if (!function_exists('samap_audit_purge')) {
	// Housekeeping: borra filas con mas de $dias dias. Pensado para llamarse
	// desde un cron externo. No se invoca automaticamente.
	function samap_audit_purge($dias = 90) {
		global $connect, $database;
		if (!isset($connect) || !($connect instanceof mysqli)) { return 0; }
		$dias = max(1, (int)$dias);
		@mysqli_select_db($connect, $database);
		$sql = 'DELETE FROM tbl_audit_log WHERE created_at < (NOW() - INTERVAL ' . $dias . ' DAY)';
		$res = @$connect->query($sql);
		return $res ? (int)$connect->affected_rows : 0;
	}
}

// ---- Uploads de imagen ----
if (!function_exists('samap_guardar_imagen_upload')) {
	function samap_guardar_imagen_upload($campo, $directorio, $requerido = false) {
		if (empty($_FILES[$campo]) || ($_FILES[$campo]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
			if ($requerido) {
				throw new RuntimeException('Debe seleccionar una imagen.');
			}
			return '';
		}

		$archivo = $_FILES[$campo];
		if (($archivo['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
			throw new RuntimeException('No se pudo recibir la imagen.');
		}
		if (($archivo['size'] ?? 0) > 5 * 1024 * 1024) {
			throw new RuntimeException('La imagen supera el limite de 5 MB.');
		}

		$permitidos = [
			'image/jpeg' => 'jpg',
			'image/png'  => 'png',
			'image/webp' => 'webp',
		];
		$finfo = new finfo(FILEINFO_MIME_TYPE);
		$mime = $finfo->file($archivo['tmp_name']);
		if (!isset($permitidos[$mime]) || @getimagesize($archivo['tmp_name']) === false) {
			throw new RuntimeException('Formato de imagen no permitido. Use JPG, PNG o WEBP.');
		}

		if (!is_dir($directorio) && !@mkdir($directorio, 0755, true)) {
			throw new RuntimeException('No se pudo preparar el directorio de destino.');
		}

		$base = pathinfo((string)$archivo['name'], PATHINFO_FILENAME);
		$base = preg_replace('/[^a-zA-Z0-9_-]+/', '-', iconv('UTF-8', 'ASCII//TRANSLIT', $base) ?: $base);
		$base = trim($base, '-_') ?: 'imagen';
		$nombre = strtolower($base) . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $permitidos[$mime];
		$destino = rtrim($directorio, '/\\') . DIRECTORY_SEPARATOR . $nombre;

		if (!move_uploaded_file($archivo['tmp_name'], $destino)) {
			throw new RuntimeException('No se pudo guardar la imagen.');
		}
		return $nombre;
	}
}
?>
