-- ============================================================================
-- Migracion 005 - Ampliar tbl_user con campos para ABM de usuarios del panel.
-- ============================================================================
-- Origen: hasta ahora el unico usuario era el admin sembrado. Para delegar
-- tareas al equipo (editor / comercial) y para auditoria minima necesitamos:
--   * activo        -> baja logica (no permitir login sin borrar fisico)
--   * ultimo_acceso -> ultima vez que el usuario inicio sesion
--   * deleted_at    -> papelera (consistente con el resto de las tablas)
--   * email         -> contacto / futura recuperacion de contrasena
--
-- MySQL 8.0 no soporta `ADD COLUMN IF NOT EXISTS` directamente. Usamos
-- INFORMATION_SCHEMA + PREPARE/EXECUTE para que la migration sea idempotente
-- (re-ejecutable sin abortar si las columnas ya existen).
-- ============================================================================

SET NAMES utf8mb4;

-- activo: 1 = habilitado, 0 = deshabilitado (no puede iniciar sesion).
SET @col_activo = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'web_samap'
      AND TABLE_NAME   = 'tbl_user'
      AND COLUMN_NAME  = 'activo'
);
SET @sql_activo = IF(
    @col_activo = 0,
    'ALTER TABLE tbl_user ADD COLUMN activo TINYINT(1) NOT NULL DEFAULT 1',
    'SELECT 1'
);
PREPARE stmt FROM @sql_activo; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ultimo_acceso: timestamp del ultimo login exitoso. NULL = nunca.
SET @col_last = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'web_samap'
      AND TABLE_NAME   = 'tbl_user'
      AND COLUMN_NAME  = 'ultimo_acceso'
);
SET @sql_last = IF(
    @col_last = 0,
    'ALTER TABLE tbl_user ADD COLUMN ultimo_acceso TIMESTAMP NULL DEFAULT NULL',
    'SELECT 1'
);
PREPARE stmt FROM @sql_last; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- deleted_at: papelera consistente con las demas tablas (medicos, planes, etc).
SET @col_del = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'web_samap'
      AND TABLE_NAME   = 'tbl_user'
      AND COLUMN_NAME  = 'deleted_at'
);
SET @sql_del = IF(
    @col_del = 0,
    'ALTER TABLE tbl_user ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL',
    'SELECT 1'
);
PREPARE stmt FROM @sql_del; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- email: contacto del usuario. Opcional por ahora (los usuarios existentes
-- no lo tienen). Largo 200 para coincidir con tbl_leads.email.
SET @col_email = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'web_samap'
      AND TABLE_NAME   = 'tbl_user'
      AND COLUMN_NAME  = 'email'
);
SET @sql_email = IF(
    @col_email = 0,
    'ALTER TABLE tbl_user ADD COLUMN email VARCHAR(200) NULL DEFAULT NULL',
    'SELECT 1'
);
PREPARE stmt FROM @sql_email; EXECUTE stmt; DEALLOCATE PREPARE stmt;
