<?php

	// Reject malformed session IDs early to avoid PHP warnings leaking file paths
	if (isset($_COOKIE['PHPSESSID']) && !preg_match('/^[a-zA-Z0-9,-]{1,128}$/', (string)$_COOKIE['PHPSESSID'])) {
		unset($_COOKIE['PHPSESSID']);
	}

	// ---- Endurecimiento de la cookie de sesion ----
	// Solo aplica si la sesion aun no arranco (evita warnings).
	if (session_status() === PHP_SESSION_NONE) {
		$secure = (
			(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
			(isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
		);
		session_set_cookie_params([
			'lifetime' => 0,
			'path'     => '/',
			'httponly' => true,      // la cookie no es accesible desde JS (anti-XSS)
			'secure'   => $secure,   // solo viaja por HTTPS cuando corresponde
			'samesite' => 'Lax',     // mitiga CSRF en navegacion cross-site
		]);
		session_start();
	}

	// ---- Expiracion por inactividad (2 horas) ----
	$TIEMPO_INACTIVIDAD = 7200;
	if (isset($_SESSION['ADM_last_activity']) &&
		(time() - $_SESSION['ADM_last_activity']) > $TIEMPO_INACTIVIDAD) {
		$_SESSION = [];
		session_destroy();
	} else {
		$_SESSION['ADM_last_activity'] = time();
	}

	// ---- Guard central de autenticacion ----
	// Todas las paginas del admin incluyen db.php -> session.php. Aca bloqueamos
	// del lado del SERVIDOR (header + exit) cualquier pagina que requiera sesion.
	// Las paginas publicas (login / logout) van en la whitelist.
	$ADM_paginas_publicas = ['index.php', 'logout.php', 'conexion.php', 'recuperar.php', 'restablecer.php'];
	$ADM_script_actual = basename($_SERVER['SCRIPT_NAME'] ?? '');

	if (!in_array($ADM_script_actual, $ADM_paginas_publicas, true) &&
		empty($_SESSION['ADM_Username'])) {
		$destino = (isset($URL) ? $URL : '/') . 'admin/index/';
		header('Location: ' . $destino);
		exit;
	}

	// ---- #9 Rol "comercial" = solo lectura ----
	// Bloquea del lado del servidor las pantallas de alta/edicion (agregar-* /
	// editar*). Los borrados (via ?borrar) se frenan ademas en cada listado.
	if (!empty($_SESSION['ADM_Username']) &&
		($_SESSION['ADM_Rol'] ?? 'admin') === 'comercial' &&
		preg_match('/^(agregar|editar)/i', $ADM_script_actual)) {
		$destino_home = (isset($URL) ? $URL : '/') . 'admin/home/';
		header('Location: ' . $destino_home);
		exit;
	}
?>
