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
