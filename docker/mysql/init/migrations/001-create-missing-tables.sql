-- ============================================================================
-- Migracion 001 — Crear las 8 tablas faltantes referenciadas por admin/
-- ============================================================================
-- Origen: Phase 1 verification encontro que el seed (backup_samap.sql) solo
-- contiene 13 tablas, pero admin/*.php referencia 8 mas. Esta migration las
-- crea con un esquema minimo pero suficiente para que las pantallas del panel
-- (listados, alta, edicion, borrado) funcionen sin errores de columna
-- inexistente.
--
-- Convenciones aplicadas (alineadas con tablas ya existentes en web_samap):
--   * id int NOT NULL AUTO_INCREMENT PRIMARY KEY
--   * Columnas de texto: varchar(250) o text segun uso (summernote = text)
--   * created_at / updated_at / deleted_at (soft delete)
--   * InnoDB + utf8 / utf8_spanish_ci (la conexion de admin/funciones/db.php
--     fuerza utf8 con mysqli_set_charset)
--
-- IMPORTANTE: las pantallas de admin/*.php interpolan $_POST/$_GET directo en
-- las queries (codigo legacy sin prepared statements). El esquema no
-- soluciona eso; db.php ya mitiga con samap_escapar_recursivo().
-- ============================================================================

SET NAMES utf8;

-- ----------------------------------------------------------------------------
-- tbl_agenda — Fechas / jornadas del evento. tbl_agenda_detalle.idAgenda
-- apunta a esta tabla (alias "Fechas" en el panel, ver fechas.php).
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_agenda` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(250) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- ----------------------------------------------------------------------------
-- tbl_agenda_detalle — Bloques del cronograma (una fila por actividad).
-- Referenciada por agenda.php, agregar-agenda.php, editaragenda.php.
-- idSpeaker1..10 referencian tbl_speaker.id (relacion opcional).
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_agenda_detalle` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idAgenda` int DEFAULT NULL,
  `titulo` varchar(250) DEFAULT NULL,
  `horario` varchar(100) DEFAULT NULL,
  `lugar` varchar(250) DEFAULT NULL,
  `texto` text DEFAULT NULL,
  `idSpeaker1` int DEFAULT NULL,
  `idSpeaker2` int DEFAULT NULL,
  `idSpeaker3` int DEFAULT NULL,
  `idSpeaker4` int DEFAULT NULL,
  `idSpeaker5` int DEFAULT NULL,
  `idSpeaker6` int DEFAULT NULL,
  `idSpeaker7` int DEFAULT NULL,
  `idSpeaker8` int DEFAULT NULL,
  `idSpeaker9` int DEFAULT NULL,
  `idSpeaker10` int DEFAULT NULL,
  `imagen` varchar(250) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_agenda_detalle_idAgenda` (`idAgenda`),
  KEY `idx_agenda_detalle_idSpeaker1` (`idSpeaker1`),
  KEY `idx_agenda_detalle_idSpeaker2` (`idSpeaker2`),
  KEY `idx_agenda_detalle_idSpeaker3` (`idSpeaker3`),
  KEY `idx_agenda_detalle_idSpeaker4` (`idSpeaker4`),
  KEY `idx_agenda_detalle_idSpeaker5` (`idSpeaker5`),
  KEY `idx_agenda_detalle_idSpeaker6` (`idSpeaker6`),
  KEY `idx_agenda_detalle_idSpeaker7` (`idSpeaker7`),
  KEY `idx_agenda_detalle_idSpeaker8` (`idSpeaker8`),
  KEY `idx_agenda_detalle_idSpeaker9` (`idSpeaker9`),
  KEY `idx_agenda_detalle_idSpeaker10` (`idSpeaker10`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- ----------------------------------------------------------------------------
-- tbl_galeria — Contenedor / categoria de fotos. tbl_fotos.galeria_id apunta
-- aca. Referenciada por galeria.php y agregar-fotos.php.
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_galeria` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(250) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- ----------------------------------------------------------------------------
-- tbl_nacionalidad — Nacionalidades de los speakers, con bandera. Referenciada
-- por nacionalidad.php, speakers.php (idNacionalidad) y editarspeaker.php.
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_nacionalidad` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nacionalidad` varchar(250) DEFAULT NULL,
  `bandera` varchar(250) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- ----------------------------------------------------------------------------
-- tbl_speaker — Disertantes. Referenciada por speakers.php, agenda*.php
-- (idSpeaker1..10), editaragenda.php. idNacionalidad -> tbl_nacionalidad.id.
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_speaker` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(250) DEFAULT NULL,
  `titulo` varchar(250) DEFAULT NULL,
  `intro` varchar(500) DEFAULT NULL,
  `texto` text DEFAULT NULL,
  `linkedin` varchar(500) DEFAULT NULL,
  `ig` varchar(500) DEFAULT NULL,
  `fb` varchar(500) DEFAULT NULL,
  `tw` varchar(500) DEFAULT NULL,
  `web` varchar(500) DEFAULT NULL,
  `idNacionalidad` int DEFAULT NULL,
  `imagen` varchar(250) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_speaker_idNacionalidad` (`idNacionalidad`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- ----------------------------------------------------------------------------
-- tbl_sponsor — Patrocinadores del evento. Referenciada por sponsors.php.
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_sponsor` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titulo` varchar(250) DEFAULT NULL,
  `URL` varchar(500) DEFAULT NULL,
  `imagen` varchar(250) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- ----------------------------------------------------------------------------
-- tbl_apoyan — Instituciones que "apoyan" el evento. Misma estructura que
-- tbl_sponsor pero tabla independiente. Referenciada por apoyan.php.
-- (Nota: apoyan.php lee $row_sponsor['URl'] (l lowercase) en la vista — es un
-- bug de la pantalla, no del esquema. La columna real es URL.)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_apoyan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titulo` varchar(250) DEFAULT NULL,
  `URL` varchar(500) DEFAULT NULL,
  `imagen` varchar(250) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- ----------------------------------------------------------------------------
-- tbl_fotos — Fotos individuales, agrupadas en una galeria.
-- Referenciada por agregar-fotos.php (INSERT con columnas
-- nombre, descripcion, ruta, galeria_id). galeria_id -> tbl_galeria.id.
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_fotos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(250) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `ruta` varchar(500) DEFAULT NULL,
  `galeria_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_fotos_galeria_id` (`galeria_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;
