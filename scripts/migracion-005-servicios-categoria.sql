-- ============================================================================
-- Migracion 005 — Servicios/Beneficios vinculables a una categoria de aliados
-- ============================================================================
-- El detalle de un beneficio (ej. "Descuento en Farmacias") dejaba de ser un
-- blob de texto pegado a mano. Ahora un servicio puede referenciar una
-- categoria de aliados (tbl_categorias_aliado). Cuando esta seteada, la pagina
-- publica beneficio-detalle.php arma sola la grilla de comercios adheridos
-- (logo + nombre + descuento + detalle) tomando los datos de tbl_aliados.
--
-- Si categoria_id queda NULL, el beneficio sigue mostrando su texto enriquecido
-- (para beneficios que no son una lista de comercios: Assist Card, Sanatorio
-- Propio, etc.).
--
-- Importante: ejecutar en UTF-8 para no romper acentos.
-- ============================================================================

SET NAMES utf8mb4;

ALTER TABLE tbl_servicios ADD COLUMN categoria_id INT NULL AFTER imagen;

-- Vincula el beneficio "Descuento en Farmacias" con la categoria Farmacias.
UPDATE tbl_servicios s
  JOIN tbl_categorias_aliado c
    ON c.nombre = 'Farmacias' COLLATE utf8mb4_0900_ai_ci
  SET s.categoria_id = c.id
  WHERE s.titulo LIKE '%Farmacia%' AND s.deleted_at IS NULL;
