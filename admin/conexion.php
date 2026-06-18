<?php
	// Credenciales SOLO por variables de entorno (definidas en docker-compose /
	// /etc/samap/env). Nunca hardcodear usuario/clave en el repositorio.
	$DB_USER = getenv('DB_USER');
	$DB_PASS = getenv('DB_PASS');
	if ($DB_USER === false || $DB_USER === '' || $DB_PASS === false) {
		http_response_code(500);
		die('Configuracion de base de datos faltante. Defina DB_USER y DB_PASS en el entorno.');
	}
	$conexion = new mysqli(
		getenv('DB_HOST') ?: 'db',
		$DB_USER,
		$DB_PASS,
		getenv('DB_NAME') ?: 'web_samap'
	);
?>
