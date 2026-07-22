-- ============================================================================
-- Portada editable desde el panel (seccion "Portada")
--
-- Reemplaza el hero hardcodeado de index.php. Es una tabla de una sola fila
-- (id = 1): el panel siempre edita ese registro.
--
-- Idempotente: se puede correr mas de una vez sin romper nada.
--   * CREATE TABLE IF NOT EXISTS  -> no pisa la tabla si ya existe
--   * INSERT ... ON DUPLICATE KEY -> no pisa el contenido ya cargado
--
-- Los valores iniciales son exactamente los que hoy estan escritos a mano en
-- index.php, asi que despues de correr esto la portada se ve igual que antes.
-- ============================================================================

-- IMPRESCINDIBLE: este archivo esta guardado en UTF-8 y trae tildes y la "ñ".
-- Sin esto, el cliente mysql puede asumir latin1 e insertar el texto doble
-- codificado ("Cuidándote" -> "CuidÃ¡ndote"). Con SET NAMES el script queda
-- correcto sin importar como se lo invoque (consola, Webmin o phpMyAdmin).
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `tbl_portada` (
  `id`          int NOT NULL AUTO_INCREMENT,
  `eyebrow`     varchar(150) DEFAULT NULL,
  `titulo`      varchar(200) DEFAULT NULL,
  `subtitulo`   varchar(400) DEFAULT NULL,
  `btn1_texto`  varchar(80)  DEFAULT NULL,
  `btn1_url`    varchar(300) DEFAULT NULL,
  `btn2_texto`  varchar(80)  DEFAULT NULL,
  `btn2_url`    varchar(300) DEFAULT NULL,
  `imagen`      varchar(250) DEFAULT NULL,
  `stat1_num`   varchar(50)  DEFAULT NULL,
  `stat1_label` varchar(120) DEFAULT NULL,
  `stat2_num`   varchar(50)  DEFAULT NULL,
  `stat2_label` varchar(120) DEFAULT NULL,
  `stat3_num`   varchar(50)  DEFAULT NULL,
  `stat3_label` varchar(120) DEFAULT NULL,
  `whatsapp`    varchar(30)  DEFAULT NULL,
  `updated_at`  datetime     DEFAULT NULL,
  `deleted_at`  datetime     DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `tbl_portada`
  (`id`, `eyebrow`, `titulo`, `subtitulo`,
   `btn1_texto`, `btn1_url`, `btn2_texto`, `btn2_url`,
   `imagen`,
   `stat1_num`, `stat1_label`, `stat2_num`, `stat2_label`, `stat3_num`, `stat3_label`,
   `whatsapp`, `updated_at`)
VALUES
  (1,
   'Medicina Prepaga · Sanatorio Adventista',
   'Cuidándote siempre',
   'Cobertura médica para vos y tu familia con el respaldo del Sanatorio Adventista.',
   'Solicitar información', '',
   'Conocer planes', 'planes/',
   '',
   '+40', 'años de experiencia',
   '+8.000', 'familias adheridas',
   'Respaldo', 'del Sanatorio Adventista',
   '5950982304977', NOW())
ON DUPLICATE KEY UPDATE `id` = `id`;
