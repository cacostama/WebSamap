-- ============================================================================
-- SAMAP — Creación de esquema desde cero (base de datos nueva)
-- ============================================================================
-- Este script crea las 24 tablas que la aplicación usa hoy, con TODAS las
-- columnas ya fusionadas (estado final: dump base + todas las migraciones).
--
-- Uso (sobre la base NUEVA y vacía, NO sobre la de producción):
--     mysql -u samap_v2 -p web_samap_v2 < crear_tablas_samap.sql
--
-- Notas:
--   * NO contiene datos. Crea solo la estructura (tablas + índices).
--   * NO trae hardcodeada la base 'web_samap': corre en la base a la que
--     apuntes al invocar mysql. Seguro para web_samap_v2.
--   * Las 12 tablas "base" conservan su engine/charset original (MyISAM/latin1
--     e InnoDB/utf8mb4) para reproducir exactamente el esquema probado en test.
--   * Las tablas nuevas usan InnoDB + utf8.
--   * Para cargar contenido (planes, médicos, guía médica, etc.) ver el paso
--     de datos al final de este archivo.
-- ============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================================
-- 1) TABLAS DE CONTENIDO (base + soft-delete + columnas de features)
-- ============================================================================

-- tbl_aliados: aliados/beneficios. + categoria_id, categoria, descuento, orden, deleted_at
CREATE TABLE `tbl_aliados` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titulo` varchar(250) DEFAULT NULL,
  `categoria_id` int DEFAULT NULL,
  `categoria` varchar(60) DEFAULT NULL,
  `descuento` varchar(60) DEFAULT NULL,
  `orden` int NOT NULL DEFAULT 0,
  `detalle` text,
  `imagen` varchar(250) DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_aliados_orden` (`orden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- tbl_blog: notas del blog. + orden, deleted_at
CREATE TABLE `tbl_blog` (
  `id` int NOT NULL AUTO_INCREMENT,
  `fecha` date DEFAULT NULL,
  `titulo` varchar(250) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `intro` varchar(250) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `texto` longtext CHARACTER SET latin1 COLLATE latin1_general_ci,
  `imagen` varchar(250) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `orden` int NOT NULL DEFAULT 0,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_blog_orden` (`orden`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- tbl_ciudad: catálogo de ciudades. + deleted_at
CREATE TABLE `tbl_ciudad` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(250) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `estado` int DEFAULT '1',
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- tbl_convenios: convenios. + deleted_at
CREATE TABLE `tbl_convenios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ciudad` varchar(100) DEFAULT NULL,
  `titulo` varchar(250) NOT NULL,
  `detalle` text NOT NULL,
  `imagen` varchar(250) NOT NULL,
  `url` varchar(250) NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- tbl_especialidad: catálogo de especialidades. + deleted_at
CREATE TABLE `tbl_especialidad` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(250) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `estado` int DEFAULT '1',
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- tbl_guiamedica: guía médica. + deleted_at
CREATE TABLE `tbl_guiamedica` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idEspecialidad` int NOT NULL,
  `idSanatorios` int NOT NULL,
  `titulo` varchar(10) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `nombre` varchar(150) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `cv` text CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- tbl_medicos: médicos destacados. + deleted_at
CREATE TABLE `tbl_medicos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titulo` varchar(250) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `nombre` varchar(250) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `especialidad` varchar(250) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `imagen` varchar(250) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- tbl_planes: planes de cobertura. + deleted_at
CREATE TABLE `tbl_planes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `especial` int NOT NULL DEFAULT '0',
  `titulo` varchar(250) NOT NULL,
  `detalle` text NOT NULL,
  `imagen` varchar(250) NOT NULL,
  `url` varchar(250) NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- tbl_sanatorio: sanatorios/sedes. + deleted_at
CREATE TABLE `tbl_sanatorio` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idCiudad` int DEFAULT NULL,
  `nombre` varchar(250) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `direccion` varchar(250) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `telefono` varchar(250) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `estado` int DEFAULT '1',
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- tbl_servicios: servicios/beneficios. + categoria_id, deleted_at
CREATE TABLE `tbl_servicios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titulo` varchar(250) NOT NULL,
  `intro` varchar(250) DEFAULT NULL,
  `detalle` text NOT NULL,
  `imagen` varchar(250) NOT NULL,
  `categoria_id` int DEFAULT NULL,
  `url` varchar(250) NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- tbl_slider: slider del home. + orden, deleted_at
CREATE TABLE `tbl_slider` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(250) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `imagen` varchar(250) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `url` varchar(250) DEFAULT NULL,
  `orden` int NOT NULL DEFAULT 0,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_slider_orden` (`orden`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- tbl_user: usuarios del panel. + rol, activo, ultimo_acceso, email, deleted_at
CREATE TABLE `tbl_user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(250) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `userName` varchar(250) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `userPass` varchar(250) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `rol` varchar(20) NOT NULL DEFAULT 'admin',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `ultimo_acceso` timestamp NULL DEFAULT NULL,
  `email` varchar(200) DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- ============================================================================
-- 2) CATEGORÍAS DE ALIADOS (ABM de categorías)
-- ============================================================================
CREATE TABLE `tbl_categorias_aliado` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(60) NOT NULL,
  `icono` varchar(60) NOT NULL DEFAULT 'fa-tags',
  `color` varchar(20) NULL,
  `orden` int NOT NULL DEFAULT 0,
  `activo` tinyint NOT NULL DEFAULT 1,
  `deleted_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ============================================================================
-- 3) EVENTOS / AGENDA / SPEAKERS / GALERÍA
-- ============================================================================

CREATE TABLE `tbl_agenda` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(250) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

CREATE TABLE `tbl_agenda_detalle` (
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

CREATE TABLE `tbl_galeria` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(250) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

CREATE TABLE `tbl_fotos` (
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

CREATE TABLE `tbl_nacionalidad` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nacionalidad` varchar(250) DEFAULT NULL,
  `bandera` varchar(250) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

CREATE TABLE `tbl_speaker` (
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

CREATE TABLE `tbl_sponsor` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titulo` varchar(250) DEFAULT NULL,
  `URL` varchar(500) DEFAULT NULL,
  `imagen` varchar(250) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- tbl_apoyan: instituciones que apoyan. + orden (drag&drop)
CREATE TABLE `tbl_apoyan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titulo` varchar(250) DEFAULT NULL,
  `URL` varchar(500) DEFAULT NULL,
  `imagen` varchar(250) DEFAULT NULL,
  `orden` int NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_apoyan_orden` (`orden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- ============================================================================
-- 4) LEADS (formularios de contacto, con encriptación app-level Ley 6534/20)
-- ============================================================================
-- Las columnas *_enc guardan el blob AES-256-GCM (IV+ciphertext+tag). Las
-- columnas en claro quedan NULL en registros nuevos (fallback para legacy).
-- data_hash = SHA-256 del email normalizado (búsqueda sin desencriptar).
CREATE TABLE `tbl_leads` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `origen`     VARCHAR(20)  NOT NULL DEFAULT 'contacto',
  `nombre`     VARCHAR(200) NULL DEFAULT NULL,
  `nombre_enc` VARBINARY(255) NULL,
  `email`      VARCHAR(200) NULL DEFAULT NULL,
  `email_enc`  VARBINARY(255) NULL,
  `telefono`   VARCHAR(50)  DEFAULT NULL,
  `telefono_enc` VARBINARY(255) NULL,
  `data_hash`  CHAR(64)     NULL,
  `mensaje`    TEXT         NOT NULL,
  `ip`         VARCHAR(45)  DEFAULT NULL,
  `user_agent` VARCHAR(255) DEFAULT NULL,
  `estado`     ENUM('nuevo','contactado','cerrado','spam') NOT NULL DEFAULT 'nuevo',
  `notas`      TEXT         DEFAULT NULL,
  `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP    NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_leads_estado`    (`estado`),
  KEY `idx_leads_origen`    (`origen`),
  KEY `idx_leads_created`   (`created_at`),
  KEY `idx_leads_data_hash` (`data_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- ============================================================================
-- 5) TOKENS DE USUARIO (reset password / verify email)
-- ============================================================================
CREATE TABLE `tbl_user_token` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`    INT UNSIGNED NOT NULL,
  `token`      VARCHAR(128) NOT NULL,
  `tipo`       ENUM('reset_password','verify_email') NOT NULL DEFAULT 'reset_password',
  `expires_at` TIMESTAMP    NOT NULL,
  `used_at`    TIMESTAMP    NULL DEFAULT NULL,
  `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip`         VARCHAR(45)  DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_token`    (`token`),
  KEY        `idx_user_tipo` (`user_id`, `tipo`),
  KEY        `idx_expires`   (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- ============================================================================
-- 6) AUDITORÍA DEL PANEL (append-only)
-- ============================================================================
CREATE TABLE `tbl_audit_log` (
  `id`               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `usuario`          VARCHAR(60)  NOT NULL,
  `rol`              VARCHAR(20)  NOT NULL DEFAULT '',
  `accion`           VARCHAR(20)  NOT NULL,
  `entidad`          VARCHAR(60)  NOT NULL DEFAULT '',
  `entidad_id`       INT          NOT NULL DEFAULT 0,
  `descripcion`      VARCHAR(500) NOT NULL DEFAULT '',
  `datos_anteriores` JSON         NULL,
  `datos_nuevos`     JSON         NULL,
  `ip`               VARCHAR(45)  NOT NULL DEFAULT '',
  `user_agent`       VARCHAR(255) NOT NULL DEFAULT '',
  `created_at`       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_usuario` (`usuario`),
  KEY `idx_accion`  (`accion`),
  KEY `idx_entidad` (`entidad`, `entidad_id`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- FIN DEL ESQUEMA — 24 tablas creadas.
-- ============================================================================
-- Datos: este script NO carga contenido. Para que el sitio no quede vacío y
-- para poder entrar al panel, después de crear el esquema tenés dos opciones:
--
--   A) Cargar el contenido actual (planes, médicos, guía médica, ciudades,
--      especialidades, convenios, usuario admin) desde el dump de datos:
--         mysql -u samap_v2 -p web_samap_v2 < backup_samap.sql
--      (backup_samap.sql incluye estructura + datos de las 12 tablas base;
--       las tablas nuevas quedan vacías, que es lo esperado.)
--
--   B) Empezar 100% vacío y cargar todo desde el panel. En ese caso hay que
--      crear al menos un usuario admin manualmente, por ejemplo:
--         INSERT INTO tbl_user (nombre, userName, userPass, rol, activo)
--         VALUES ('Administrador', 'admin', '<hash_bcrypt>', 'admin', 1);
-- ============================================================================
