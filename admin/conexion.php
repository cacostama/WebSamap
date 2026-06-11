<?php
	$conexion = new mysqli(
		getenv('DB_HOST') ?: 'db',
		getenv('DB_USER') ?: 'webadmin',
		getenv('DB_PASS') ?: 's2m2p.m2st3r',
		getenv('DB_NAME') ?: 'web_samap'
	);
?>