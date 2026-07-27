<?php
	// Credenciales por variables de entorno (docker-compose / /etc/samap/env)
	// con fallback a los valores de produccion. El fallback existe porque el
	// mod_php de produccion no expone getenv(DB_*); sin el, el panel se cae con
	// HTTP 500. En local/Docker las env vars ganan y apuntan a la DB del dev.
	$conexion = new mysqli(
		getenv('DB_HOST') ?: 'localhost',
		getenv('DB_USER') ?: 'webadmin',
		getenv('DB_PASS') ?: 's2m2p.m2st3r',
		getenv('DB_NAME') ?: 'web_samap_v2'
	);
?>
