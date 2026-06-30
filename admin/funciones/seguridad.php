<?php

// ============================================================================
// Helpers de seguridad compartidos por el panel admin.
// Se incluye desde funciones/db.php DESPUES de arrancar la sesion (session.php),
// asi que aca ya hay $_SESSION disponible.
//
//   - CSRF: token por sesion + helpers para imprimir el campo y validarlo.
//   - Rate-limit de login: throttling por IP basado en archivos temporales,
//     para frenar ataques de fuerza bruta sin depender de la DB.
//   - Encriptacion PII (Ley 6534/20): AES-256-GCM con clave en env var.
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

// ---- Rate-limit generico (5 intentos / 15 min / IP) ----
// Version configurable por "bucket" del mismo mecanismo de throttling que
// usa samap_login_*. Asi el mismo codigo sirve para login, formulario de
// contacto publico, recuperacion de password, etc., con limites
// independientes por endpoint.
if (!function_exists('samap_rl_archivo')) {
	function samap_rl_archivo($bucket, $ip) {
		$dir = sys_get_temp_dir() . '/samap_rl';
		if (!is_dir($dir)) { @mkdir($dir, 0700, true); }
		$safe = preg_replace('/[^a-z0-9_-]/i', '', (string)$bucket);
		if ($safe === '') { $safe = 'default'; }
		return $dir . '/' . $safe . '_' . md5((string)$ip) . '.json';
	}
}

if (!function_exists('samap_rl_estado')) {
	function samap_rl_estado($bucket, $ip) {
		$f = samap_rl_archivo($bucket, $ip);
		if (!is_file($f)) return ['intentos' => 0, 'ts' => 0];
		$d = json_decode((string)@file_get_contents($f), true);
		return is_array($d) ? $d : ['intentos' => 0, 'ts' => 0];
	}
}

if (!function_exists('samap_rl_bloqueado')) {
	function samap_rl_bloqueado($bucket, $ip, $max = 5, $ventana = 900) {
		$d = samap_rl_estado($bucket, $ip);
		if (($d['ts'] ?? 0) + $ventana < time()) return false; // ventana vencida
		return ($d['intentos'] ?? 0) >= $max;
	}
}

if (!function_exists('samap_rl_registrar_fallo')) {
	function samap_rl_registrar_fallo($bucket, $ip, $ventana = 900) {
		$d = samap_rl_estado($bucket, $ip);
		if (($d['ts'] ?? 0) + $ventana < time()) {
			$d = ['intentos' => 0, 'ts' => time()];
		}
		$d['intentos'] = ($d['intentos'] ?? 0) + 1;
		$d['ts'] = time();
		@file_put_contents(samap_rl_archivo($bucket, $ip), json_encode($d), LOCK_EX);
	}
}

if (!function_exists('samap_rl_limpiar')) {
	function samap_rl_limpiar($bucket, $ip) {
		$f = samap_rl_archivo($bucket, $ip);
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
	/**
	 * Recibe un upload, lo valida y guarda en disco. Si GD esta disponible y
	 * la imagen es JPEG/PNG/WebP, la REDIMENSIONA al ancho maximo seteado en
	 * $max_width (default 1600px) preservando aspect ratio y la RECOMPRIME
	 * con calidad razonable (85). Eso baja una foto de celular tipica (3 MB,
	 * 4032x3024) a 200-400 KB sin perdida visible.
	 *
	 * Genera tambien variantes thumb (640) y small (320) si $variantes=true.
	 * Las variantes se nombran <base>-640.<ext> y <base>-320.<ext>.
	 *
	 * Retorna el nombre del archivo principal (sin path).
	 */
	function samap_guardar_imagen_upload($campo, $directorio, $requerido = false, $max_width = 1600, $variantes = true) {
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
		// Limite generoso: aceptamos hasta 10 MB de upload, despues comprimimos.
		if (($archivo['size'] ?? 0) > 10 * 1024 * 1024) {
			throw new RuntimeException('La imagen supera el limite de 10 MB.');
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
		$ext  = $permitidos[$mime];
		$slug = strtolower($base) . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(4));
		$nombre = $slug . '.' . $ext;
		$destino = rtrim($directorio, '/\\') . DIRECTORY_SEPARATOR . $nombre;

		// Intentar resize via GD. Si GD no esta, fallback a move_uploaded_file
		// sin tocar la imagen (comportamiento legacy preservado).
		$resized = false;
		if (extension_loaded('gd') && ($ext === 'jpg' || $ext === 'png' || $ext === 'webp')) {
			try {
				samap_resize_imagen($archivo['tmp_name'], $destino, $ext, $max_width);
				$resized = true;
				// Generar variantes
				if ($variantes) {
					$variante_paths = [
						640 => rtrim($directorio, '/\\') . DIRECTORY_SEPARATOR . $slug . '-640.' . $ext,
						320 => rtrim($directorio, '/\\') . DIRECTORY_SEPARATOR . $slug . '-320.' . $ext,
					];
					foreach ($variante_paths as $w => $vpath) {
						@samap_resize_imagen($destino, $vpath, $ext, $w);
					}
				}
			} catch (Throwable $e) {
				$resized = false;
			}
		}

		if (!$resized) {
			if (!move_uploaded_file($archivo['tmp_name'], $destino)) {
				throw new RuntimeException('No se pudo guardar la imagen.');
			}
		}
		return $nombre;
	}
}

if (!function_exists('samap_resize_imagen')) {
	/**
	 * Resize via GD. Lee $src, redimensiona a max $max_width preservando
	 * aspect ratio (si la original es mas chica, NO la agranda), y guarda
	 * en $dst con compresion segun el formato $ext (jpg|png|webp).
	 *
	 * Mantiene transparencia en PNG/WebP. Calidad JPEG: 85. PNG: 6/9.
	 *
	 * Throws si no puede leer el src.
	 */
	function samap_resize_imagen($src, $dst, $ext, $max_width = 1600) {
		$info = @getimagesize($src);
		if ($info === false) {
			throw new RuntimeException('No se pudo leer la imagen origen.');
		}
		list($w, $h) = $info;

		switch ($ext) {
			case 'jpg':  $img = @imagecreatefromjpeg($src); break;
			case 'png':  $img = @imagecreatefrompng($src);  break;
			case 'webp':
				if (!function_exists('imagecreatefromwebp')) {
					throw new RuntimeException('GD sin soporte WebP.');
				}
				$img = @imagecreatefromwebp($src);
				break;
			default: throw new RuntimeException('Formato no soportado: ' . $ext);
		}
		if (!$img) {
			throw new RuntimeException('No se pudo decodificar la imagen.');
		}

		// Si la imagen es mas chica o igual al target, copiamos sin resize.
		if ($w <= $max_width) {
			$new = $img;
		} else {
			$ratio = $max_width / $w;
			$new_w = $max_width;
			$new_h = (int) round($h * $ratio);
			$new = imagecreatetruecolor($new_w, $new_h);
			// Preservar transparencia para PNG y WebP
			if ($ext === 'png' || $ext === 'webp') {
				imagealphablending($new, false);
				imagesavealpha($new, true);
				$transparent = imagecolorallocatealpha($new, 0, 0, 0, 127);
				imagefilledrectangle($new, 0, 0, $new_w, $new_h, $transparent);
			}
			imagecopyresampled($new, $img, 0, 0, 0, 0, $new_w, $new_h, $w, $h);
		}

		// Guardar al destino con compresion
		switch ($ext) {
			case 'jpg':  $ok = @imagejpeg($new, $dst, 85);   break;
			case 'png':  $ok = @imagepng($new, $dst, 6);     break;
			case 'webp': $ok = @imagewebp($new, $dst, 85);   break;
			default:     $ok = false;
		}

		if ($new !== $img) imagedestroy($new);
		imagedestroy($img);

		if (!$ok) {
			throw new RuntimeException('No se pudo escribir la imagen procesada.');
		}
	}
}

// ---- #12 Encriptacion PII (Ley 6534/20) ----
// AES-256-GCM con clave derivada de la variable de entorno LEAD_ENC_KEY.
//   * Formato del blob: [12 bytes IV][N bytes ciphertext][16 bytes GCM tag]
//   * IV aleatorio por operacion (random_bytes(12)) -> no deterministico
//   * data_hash: SHA-256 deterministico de un valor normalizado (lowercase +
//     trim + prefijo de namespace). Permite buscar por email sin desencriptar
//     todas las filas (indice en data_hash).
//
// Clave (LEAD_ENC_KEY):
//   * Hex de 64 chars (32 bytes) -> se usa como binario directo.
//   * O cualquier string >= 32 bytes -> se hashea a SHA-256 (32 bytes).
//   * La clave NUNCA se loguea ni se persiste. Si no esta definida o es
//     demasiado corta, samap_encrypt() lanza RuntimeException.
//
// Compatibilidad: samap_get_lead_field() cae al valor en claro (columna
// legacy) si la columna _enc esta NULL, asi que las filas pre-migration
// siguen siendo visibles en el panel.
if (!function_exists('samap_encrypt_key')) {
	function samap_encrypt_key() {
		$k = getenv('LEAD_ENC_KEY');
		if (!is_string($k) || $k === '') {
			return false;
		}
		// Hex de 64 chars -> binario de 32 bytes directo.
		if (strlen($k) === 64 && ctype_xdigit($k)) {
			return hex2bin($k);
		}
		// Cualquier string >= 32 chars -> SHA-256 para llegar a 32 bytes.
		if (strlen($k) >= 32) {
			return hash('sha256', $k, true);
		}
		return false;
	}
}

if (!function_exists('samap_encrypt')) {
	function samap_encrypt($plaintext) {
		$key = samap_encrypt_key();
		if ($key === false) {
			throw new RuntimeException('LEAD_ENC_KEY no configurada o demasiado corta (min 32 bytes / 64 hex).');
		}
		$iv = random_bytes(12);
		$tag = '';
		$ciphertext = openssl_encrypt((string)$plaintext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag, '', 16);
		if ($ciphertext === false) {
			throw new RuntimeException('openssl_encrypt fallo: ' . openssl_error_string());
		}
		return $iv . $ciphertext . $tag;
	}
}

if (!function_exists('samap_decrypt')) {
	function samap_decrypt($blob) {
		if ($blob === null || $blob === '') {
			return '';
		}
		$key = samap_encrypt_key();
		if ($key === false) {
			return '';
		}
		$blob = (string)$blob;
		// 12 IV + 0+ ciphertext + 16 tag = min 28 bytes.
		if (strlen($blob) < 28) {
			return '';
		}
		$iv         = substr($blob, 0, 12);
		$tag        = substr($blob, -16);
		$ciphertext = substr($blob, 12, -16);
		$plain = openssl_decrypt($ciphertext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
		return $plain === false ? '' : $plain;
	}
}

if (!function_exists('samap_data_hash')) {
	function samap_data_hash($value) {
		// Hash deterministico para busqueda sin desencriptar. El prefijo
		// 'samap:' evita colisiones con hashes que usen el mismo input
		// normalizado en otros sistemas.
		return hash('sha256', 'samap:' . strtolower(trim((string)$value)));
	}
}

if (!function_exists('samap_get_lead_field')) {
	// Lee un campo PII del row priorizando la columna _enc. Si esta NULL
	// (filas legacy pre-migration), cae al valor en claro de la columna
	// original. Asi el panel sigue funcionando con las 5 filas existentes
	// sin intervencion manual.
	function samap_get_lead_field($row, $encCol, $plainCol) {
		$enc = isset($row[$encCol]) ? $row[$encCol] : null;
		if (is_string($enc) && $enc !== '') {
			$dec = samap_decrypt($enc);
			if ($dec !== '') {
				return $dec;
			}
		}
		return isset($row[$plainCol]) ? (string)$row[$plainCol] : '';
	}
}
?>
