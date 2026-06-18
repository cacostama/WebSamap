-- ============================================================================
-- Migracion 004 — ABM de categorias de aliados (Fase 2)
-- ============================================================================
-- Las categorias dejan de ser una lista fija en codigo. Ahora viven en
-- tbl_categorias_aliado y Marketing las administra (nombre, icono FA, color,
-- orden, activo). tbl_aliados referencia la categoria por id (categoria_id).
--
-- La columna de texto tbl_aliados.categoria (migracion 003) queda DEPRECADA
-- pero no se elimina, para no romper datos historicos ni reimportaciones.
--
-- Importante: ejecutar en UTF-8 para no romper los acentos (ej. "Opticas").
-- ============================================================================

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS tbl_categorias_aliado (
  id INT NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(60) NOT NULL,
  icono VARCHAR(60) NOT NULL DEFAULT 'fa-tags',   -- clase Font Awesome (ej. fa-glasses)
  color VARCHAR(20) NULL,                          -- hex opcional para acento (ej. #6CA3AB)
  orden INT NOT NULL DEFAULT 0,
  activo TINYINT NOT NULL DEFAULT 1,
  deleted_at DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO tbl_categorias_aliado (nombre, icono, color, orden) VALUES
  ('Farmacias',    'fa-prescription-bottle-alt', NULL, 1),
  ('Ópticas',      'fa-glasses',                 NULL, 2),
  ('Laboratorios', 'fa-vials',                   NULL, 3),
  ('Gimnasios',    'fa-dumbbell',                NULL, 4),
  ('Cooperativas', 'fa-handshake',               NULL, 5),
  ('Ortopedia',    'fa-wheelchair',              NULL, 6),
  ('Otros',        'fa-tags',                    NULL, 7);

ALTER TABLE tbl_aliados ADD COLUMN categoria_id INT NULL AFTER titulo;

-- Enlaza los aliados existentes (categoria texto -> categoria_id).
UPDATE tbl_aliados a
  JOIN tbl_categorias_aliado c
    ON a.categoria = c.nombre COLLATE utf8mb4_0900_ai_ci
  SET a.categoria_id = c.id;
