-- ============================================================================
-- Migracion 012 (file: 006) - Encriptacion a nivel aplicacion para tbl_leads.
-- ============================================================================
-- Ley 6534/20 de Proteccion de Datos Personales (Paraguay) exige encriptacion
-- en reposo para datos personales sensibles. Los leads del formulario de
-- contacto capturan nombre / email / telefono / mensaje que son PII directa
-- (y salud-adjacent por ser leads de medicina prepaga).
--
-- Estrategia: encriptacion AES-256-GCM aplicada en PHP con openssl_encrypt.
-- La clave vive en una variable de entorno (LEAD_ENC_KEY), no en la DB ni en
-- el codigo. El blob encriptado se guarda como VARBINARY(255) y se compone de:
--   [12 bytes IV][N bytes ciphertext][16 bytes GCM tag]
--
-- Ademas se agrega data_hash CHAR(64) con SHA-256 del email normalizado
-- (lowercased + trim + prefijo de namespace). Esto permite buscar un lead
-- por email sin tener que desencriptar todas las filas: el hash es
-- deterministico e irreversible.
--
-- Compatibilidad hacia atras: las columnas originales (email, telefono,
-- nombre) NO se borran. Las filas anteriores a esta migration siguen con
-- sus valores en claro (legacy) y la UI las muestra como fallback cuando
-- la columna _enc esta en NULL. Las inserciones nuevas se encriptan y los
-- campos en claro quedan NULL -> la fuente de verdad pasa a ser la columna
-- _enc.
--
-- Idempotencia: MySQL 8.0 no soporta `ADD COLUMN IF NOT EXISTS`. Usamos
-- INFORMATION_SCHEMA + PREPARE/EXECUTE (mismo patron que la migration 005).
-- ============================================================================

SET NAMES utf8mb4;

-- email_enc: blob AES-256-GCM del email. Hasta 255 bytes.
SET @col = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'web_samap'
      AND TABLE_NAME   = 'tbl_leads'
      AND COLUMN_NAME  = 'email_enc'
);
SET @sql = IF(
    @col = 0,
    'ALTER TABLE tbl_leads ADD COLUMN email_enc VARBINARY(255) NULL AFTER email',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- telefono_enc: blob AES-256-GCM del telefono.
SET @col = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'web_samap'
      AND TABLE_NAME   = 'tbl_leads'
      AND COLUMN_NAME  = 'telefono_enc'
);
SET @sql = IF(
    @col = 0,
    'ALTER TABLE tbl_leads ADD COLUMN telefono_enc VARBINARY(255) NULL AFTER telefono',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- nombre_enc: blob AES-256-GCM del nombre.
SET @col = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'web_samap'
      AND TABLE_NAME   = 'tbl_leads'
      AND COLUMN_NAME  = 'nombre_enc'
);
SET @sql = IF(
    @col = 0,
    'ALTER TABLE tbl_leads ADD COLUMN nombre_enc VARBINARY(255) NULL AFTER nombre',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- data_hash: SHA-256 hex del email normalizado (lower+trim) con prefijo de
-- namespace. Sirve para buscar por email sin desencriptar toda la tabla.
SET @col = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'web_samap'
      AND TABLE_NAME   = 'tbl_leads'
      AND COLUMN_NAME  = 'data_hash'
);
SET @sql = IF(
    @col = 0,
    'ALTER TABLE tbl_leads ADD COLUMN data_hash CHAR(64) NULL AFTER nombre_enc',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Indice sobre data_hash (busqueda por email). Solo se crea si la columna
-- existe (lo cual acabamos de garantizar arriba).
SET @idx = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = 'web_samap'
      AND TABLE_NAME   = 'tbl_leads'
      AND INDEX_NAME   = 'idx_leads_data_hash'
);
SET @sql = IF(
    @idx = 0,
    'CREATE INDEX idx_leads_data_hash ON tbl_leads(data_hash)',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Las columnas originales (nombre, email) eran NOT NULL por diseno legacy
-- (todo lead tenia que tener nombre y email). Con la encriptacion pasamos
-- a guardar el dato sensible en _enc, y dejamos el original NULL en filas
-- nuevas. Las filas pre-migration (en claro) se siguen mostrando via el
-- fallback en samap_get_lead_field().
SET @col = (
    SELECT IS_NULLABLE FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'web_samap'
      AND TABLE_NAME   = 'tbl_leads'
      AND COLUMN_NAME  = 'email'
);
SET @sql = IF(
    @col <> 'YES',
    'ALTER TABLE tbl_leads MODIFY COLUMN email VARCHAR(200) NULL DEFAULT NULL',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col = (
    SELECT IS_NULLABLE FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'web_samap'
      AND TABLE_NAME   = 'tbl_leads'
      AND COLUMN_NAME  = 'nombre'
);
SET @sql = IF(
    @col <> 'YES',
    'ALTER TABLE tbl_leads MODIFY COLUMN nombre VARCHAR(200) NULL DEFAULT NULL',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
