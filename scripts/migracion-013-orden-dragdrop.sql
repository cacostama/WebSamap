-- ============================================================================
-- Migracion 013 — Columna 'orden' para reordenamiento drag&drop (Feature 13)
-- ============================================================================
-- Las 5 listados del admin que se pueden reordenar (aliados, apoyan, slider,
-- categorias, blog) necesitan una columna 'orden' para guardar el nuevo orden
-- que el usuario defina arrastrando las filas en el panel.
--
-- Aliados y Categorias ya tenian la columna desde migracion 003/004. Acá
-- agregamos la columna a las 3 tablas restantes, inicializamos el orden al ID
-- del registro (para que el orden por default sea el orden de insercion) y
-- creamos un indice para que los SELECT ... ORDER BY orden sean rapidos
-- incluso en tablas grandes.
-- ============================================================================

SET NAMES utf8mb4;

-- tbl_slider: ya tiene imagen, nombre, etc. Le sumamos 'orden'.
ALTER TABLE tbl_slider
  ADD COLUMN orden INT NOT NULL DEFAULT 0 AFTER url,
  ADD INDEX idx_slider_orden (orden);

-- tbl_apoyan: sponsors del sitio publico.
ALTER TABLE tbl_apoyan
  ADD COLUMN orden INT NOT NULL DEFAULT 0 AFTER imagen,
  ADD INDEX idx_apoyan_orden (orden);

-- tbl_blog: el orden actual es por id DESC. Migramos al orden explicito.
ALTER TABLE tbl_blog
  ADD COLUMN orden INT NOT NULL DEFAULT 0 AFTER imagen,
  ADD INDEX idx_blog_orden (orden);

-- Inicializamos el orden al id (los mas recientes aparecen primero).
UPDATE tbl_slider SET orden = id WHERE orden = 0;
UPDATE tbl_apoyan SET orden = id WHERE orden = 0;
UPDATE tbl_blog   SET orden = id WHERE orden = 0;

-- Aliados y categorias ya tenian la columna, pero les damos un default
-- consistente: orden = id, asi los listados quedan iguales a como se ven hoy.
UPDATE tbl_aliados             SET orden = id WHERE orden = 0;
UPDATE tbl_categorias_aliado   SET orden = id WHERE orden = 0;
