-- ============================================================================
-- Migracion 002 — Soft-delete + Roles de usuario
-- ============================================================================
-- #8 Soft-delete: agrega `deleted_at` a las tablas de contenido. El admin
--    ya no borra fisicamente; marca la fila con NOW() y las lecturas filtran
--    `deleted_at IS NULL`. Recuperable (basta con poner deleted_at = NULL).
-- #9 Roles: agrega `rol` a tbl_user (admin | editor | comercial).
--
-- Idempotente-ish: si se reaplica, los ADD COLUMN fallaran por duplicado;
-- ejecutar sobre una DB que aun no tenga estas columnas.
-- ============================================================================

ALTER TABLE tbl_aliados      ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL;
ALTER TABLE tbl_blog         ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL;
ALTER TABLE tbl_ciudad       ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL;
ALTER TABLE tbl_convenios    ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL;
ALTER TABLE tbl_especialidad ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL;
ALTER TABLE tbl_guiamedica   ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL;
ALTER TABLE tbl_medicos      ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL;
ALTER TABLE tbl_planes       ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL;
ALTER TABLE tbl_sanatorio    ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL;
ALTER TABLE tbl_servicios    ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL;
ALTER TABLE tbl_slider       ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL;

-- Roles: admin (todo) | editor (contenido) | comercial (solo lectura).
ALTER TABLE tbl_user         ADD COLUMN rol VARCHAR(20) NOT NULL DEFAULT 'admin';
