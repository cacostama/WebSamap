-- ============================================================================
-- Migracion 006 — Migracion de contenido: desglose de descuentos por farmacia
-- ============================================================================
-- El detalle por escalas (contado / debito / credito) vivia en el blob de
-- texto de tbl_servicios (id=3, "Descuento en Farmacias"). Con la grilla de
-- comercios (migracion 005) ese desglose pasa al campo detalle de cada aliado.
--
-- Coincidencias claras del blob original -> aliado:
--   FARMATOTAL  -> Farma Total  (id 6)
--   PUNTO FARMA -> Punto Farma  (id 8)
-- CATEDRAL y VICENTE SCAVONE no tienen aliado equivalente: quedan sin migrar.
--
-- Ejecutar en UTF-8.
-- ============================================================================

SET NAMES utf8mb4;

UPDATE tbl_aliados SET detalle =
'<p><strong>Descuentos sobre el precio público:</strong></p><ul><li>Medicamentos Nacionales: 30%</li><li>Medicamentos Importados: 20%</li><li>Medicamentos Controlados: 19%</li><li>Productos Hospitalarios: 20%</li><li>Productos Varios: 15%</li><li>Perfumería: 20%</li></ul>'
WHERE id = 6;

UPDATE tbl_aliados SET detalle =
'<p><strong>Por compras al contado (efectivo o tarjeta de débito):</strong></p><ul><li>Medicamentos Nacionales: 20% de descuento, todos los días</li><li>Medicamentos Importados: 18% de descuento, todos los días</li><li>Perfumería y Artículos Varios: 15% de descuento, todos los días</li></ul><p><strong>Por compras con tarjeta de crédito:</strong></p><ul><li>Medicamentos Nacionales, Importados, Perfumería y Artículos Varios: 15% de descuento, todos los días</li></ul>'
WHERE id = 8;
