<?php
/**
 * Migration 007 -- Backfill + limpieza de PII plaintext en tbl_leads.
 *
 * Contexto: la migration 006 agregó las columnas *_enc con AES-256-GCM, pero
 * las filas existentes quedaron solo en plaintext. Esta migration:
 *   1. Recorre filas con (nombre IS NOT NULL OR email IS NOT NULL OR telefono IS NOT NULL)
 *      y nombre_enc / email_enc / telefono_enc en NULL.
 *   2. Encripta los plaintext y los guarda en las columnas _enc.
 *   3. Calcula data_hash a partir del email (para busqueda determinista).
 *   4. Limpia las columnas plaintext (UPDATE SET nombre=NULL, email=NULL, telefono=NULL).
 *
 * Idempotente: si ya hay _enc, no toca esa fila. Tolera filas con
 * algunos campos NULL (encripta solo lo que tiene valor).
 *
 * USO:
 *   docker exec samap-web php /var/www/html/docker/mysql/init/migrations/007-backfill-leads-encrypt.php
 *
 * Ley 6534/20 (Paraguay): tras esta migration, ninguna fila de tbl_leads
 * tiene PII directamente identificable visible en disco.
 */
require_once __DIR__ . '/../../../../encryption.php';

$dbHost = getenv('DB_HOST') ?: 'db';
$dbName = getenv('DB_NAME') ?: 'web_samap';
$dbUser = getenv('DB_USER');
$dbPass = getenv('DB_PASS');

if ($dbUser === false || $dbPass === false) {
    fwrite(STDERR, "DB_USER y DB_PASS deben estar en el entorno.\n");
    exit(1);
}

$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($conn->connect_errno) {
    fwrite(STDERR, "DB connect error: " . $conn->connect_error . "\n");
    exit(1);
}
$conn->set_charset('utf8');

// Filas a procesar: tienen plaintext y AUN no estan encriptadas (o solo
// parcialmente). Procesamos todas las que tengan algun plaintext y al menos
// una columna enc en NULL.
$sql = "SELECT id, nombre, email, telefono, nombre_enc, email_enc, telefono_enc
        FROM tbl_leads
        WHERE (nombre IS NOT NULL OR email IS NOT NULL OR telefono IS NOT NULL)";
$res = $conn->query($sql);
if (!$res) {
    fwrite(STDERR, "Query error: " . $conn->error . "\n");
    exit(1);
}

$total = 0;
$encrypted = 0;
$cleaned = 0;
$skipped = 0;

while ($row = $res->fetch_assoc()) {
    $total++;
    $id = (int) $row['id'];
    $nombre   = $row['nombre'];
    $email    = $row['email'];
    $telefono = $row['telefono'];
    $nEnc = $row['nombre_enc'];
    $eEnc = $row['email_enc'];
    $tEnc = $row['telefono_enc'];

    // Si no falta nada por encriptar (todos los enc estan o el plaintext es NULL), skip a limpieza.
    $needs_enc = false;
    if ($nombre !== null   && $nEnc === null) { $needs_enc = true; }
    if ($email !== null    && $eEnc === null) { $needs_enc = true; }
    if ($telefono !== null && $tEnc === null) { $needs_enc = true; }

    if ($needs_enc) {
        try {
            $nuevo_nEnc = ($nombre !== null   && $nEnc === null) ? samap_encrypt((string)$nombre)   : null;
            $nuevo_eEnc = ($email !== null    && $eEnc === null) ? samap_encrypt((string)$email)    : null;
            $nuevo_tEnc = ($telefono !== null && $tEnc === null) ? samap_encrypt((string)$telefono) : null;
            $hash       = ($email !== null) ? samap_data_hash((string)$email) : null;

            $sets = [];
            $params = [];
            $types = '';
            if ($nuevo_nEnc !== null) { $sets[] = 'nombre_enc   = ?'; $params[] = $nuevo_nEnc; $types .= 's'; }
            if ($nuevo_eEnc !== null) { $sets[] = 'email_enc    = ?'; $params[] = $nuevo_eEnc; $types .= 's'; }
            if ($nuevo_tEnc !== null) { $sets[] = 'telefono_enc = ?'; $params[] = $nuevo_tEnc; $types .= 's'; }
            if ($hash       !== null) { $sets[] = 'data_hash    = ?'; $params[] = $hash;       $types .= 's'; }

            if (!empty($sets)) {
                $upd = "UPDATE tbl_leads SET " . implode(', ', $sets) . " WHERE id = ?";
                $params[] = $id;
                $types .= 'i';
                $stmt = $conn->prepare($upd);
                $stmt->bind_param($types, ...$params);
                $stmt->execute();
                $stmt->close();
                $encrypted++;
            }
        } catch (Throwable $e) {
            fwrite(STDERR, "id=$id encryption failed: " . $e->getMessage() . "\n");
            continue;
        }
    }

    // Ya esta encriptado -> limpiar plaintext (Ley 6534/20)
    $clean = $conn->prepare("UPDATE tbl_leads SET nombre = NULL, email = NULL, telefono = NULL WHERE id = ?");
    if ($clean) {
        $clean->bind_param('i', $id);
        $clean->execute();
        if ($clean->affected_rows > 0) $cleaned++;
        $clean->close();
    }
}

fwrite(STDOUT, "Procesadas: $total\n");
fwrite(STDOUT, "Encriptadas: $encrypted\n");
fwrite(STDOUT, "Plaintext limpiadas: $cleaned\n");

$conn->close();
