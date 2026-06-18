-- ============================================================================
-- Migracion 003 — Categorias y descuento en aliados
-- ============================================================================
-- Hace configurable la seccion "Descuentos Exclusivos" de beneficios.php.
-- Antes las tarjetas (Farmacias/Opticas/Laboratorios + %) eran HTML fijo.
-- Ahora cada aliado tiene:
--   categoria — agrupa los aliados (Farmacias, Opticas, Laboratorios, ...).
--   descuento — texto libre por aliado (ej. "Hasta 25%"). La tarjeta de la
--               categoria muestra el tope (MAX) automaticamente.
--   orden     — orden de aparicion dentro de la categoria.
--
-- Idempotente-ish: si se reaplica, los ADD COLUMN fallaran por duplicado;
-- ejecutar sobre una DB que aun no tenga estas columnas.
-- ============================================================================

-- Importante: ejecutar en UTF-8 para no romper los acentos (ej. "Ópticas").
SET NAMES utf8mb4;

ALTER TABLE tbl_aliados
  ADD COLUMN categoria VARCHAR(60) NULL AFTER titulo,
  ADD COLUMN descuento VARCHAR(60) NULL AFTER categoria,
  ADD COLUMN orden INT NOT NULL DEFAULT 0 AFTER descuento;

-- Categorizacion inicial de los aliados existentes (revisable desde el admin).
UPDATE tbl_aliados SET categoria = 'Farmacias',    descuento = 'Hasta 25%' WHERE id IN (1, 6, 8);
UPDATE tbl_aliados SET categoria = 'Ópticas',      descuento = 'Hasta 30%' WHERE id IN (2, 3);
UPDATE tbl_aliados SET categoria = 'Laboratorios', descuento = 'Hasta 20%' WHERE id IN (5, 9);
UPDATE tbl_aliados SET categoria = 'Ortopedia',    descuento = NULL        WHERE id IN (4, 12);
UPDATE tbl_aliados SET categoria = 'Cooperativas', descuento = NULL        WHERE id IN (10, 13, 14, 15, 16, 17, 18);

-- Nueva Onda Gimnasio: antes era una tarjeta destacada hardcodeada en
-- beneficios.php. Ahora vive como aliado de la categoria Gimnasios.
INSERT INTO tbl_aliados (titulo, categoria, descuento, orden, detalle, imagen)
VALUES ('Nueva Onda Gimnasio', 'Gimnasios', 'Cuota preferencial', 0,
        'Costo preferencial en las cuotas para asegurados de SAMAP. 20% en planes para alumnos del gimnasio.',
        'nueva-onda-gimnasio.png');
