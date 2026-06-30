<?php
/**
 * encryption.php — Helpers de encriptacion PII compartidos por TODO el sitio.
 *
 * Vive fuera de admin/funciones/seguridad.php a proposito: enviar.php
 * (que procesa el formulario publico) necesita encriptar el lead ANTES de
 * guardarlo, pero NO debe incluir admin/funciones/db.php (que abre la DB
 * admin y arranca la sesion del panel). Este archivo es ligero y sin
 * dependencias de $_SESSION ni de la conexion admin.
 *
 * Cumplimiento Ley 6534/20 (Paraguay, proteccion de datos personales).
 * Las inserciones nuevas en tbl_leads encriptan nombre / email / telefono
 * con AES-256-GCM. La clave vive en la variable de entorno LEAD_ENC_KEY
 * (32 bytes hex = 64 chars, o cualquier string >= 32 bytes que se hashea
 * a SHA-256). El blob almacenado es:
 *   [12 bytes IV][N bytes ciphertext][16 bytes GCM tag]
 *
 * Ver admin/funciones/seguridad.php para la version que se usa dentro
 * del panel admin (samap_encrypt / samap_decrypt / samap_data_hash /
 * samap_get_lead_field son aliases, comportamiento identico).
 */

if (!function_exists('samap_encrypt_key')) {
	function samap_encrypt_key() {
		$k = getenv('LEAD_ENC_KEY');
		if (!is_string($k) || $k === '') {
			return false;
		}
		if (strlen($k) === 64 && ctype_xdigit($k)) {
			return hex2bin($k);
		}
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
		$iv  = random_bytes(12);
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
		return hash('sha256', 'samap:' . strtolower(trim((string)$value)));
	}
}
