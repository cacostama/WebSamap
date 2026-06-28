-- ============================================================================
-- Migracion 002 — Crear tbl_leads (captura de formularios de contacto).
-- ============================================================================
-- Origen: enviar.php solo enviaba el formulario por SMTP pero NO persistia
-- el contenido, asi que el equipo de marketing perdia los mensajes si el
-- correo rebotaba. Esta migration agrega una tabla minima para guardar cada
-- submission y permitirle al panel administrarlos (estado, notas, spam).
--
-- Convenciones aplicadas (alineadas con tablas ya existentes en web_samap):
--   * id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY
--   * Columnas: origen | nombre | email | telefono | mensaje | ip | user_agent
--   * estado ENUM('nuevo','contactado','cerrado','spam') default 'nuevo'
--   * notas text (editables desde el panel)
--   * created_at / updated_at (timestamp con default NOW / on update)
--   * Indices por estado, origen y fecha (los filtros mas usados en el panel)
--   * InnoDB + utf8 / utf8_spanish_ci (la conexion de admin/funciones/db.php
--     fuerza utf8 con mysqli_set_charset)
-- ============================================================================

SET NAMES utf8;

CREATE TABLE IF NOT EXISTS `tbl_leads` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `origen`     VARCHAR(20)  NOT NULL DEFAULT 'contacto', -- 'contacto' | 'trabajo'
  `nombre`     VARCHAR(200) NOT NULL,
  `email`      VARCHAR(200) NOT NULL,
  `telefono`   VARCHAR(50)  DEFAULT NULL,
  `mensaje`    TEXT         NOT NULL,
  `ip`         VARCHAR(45)  DEFAULT NULL,  -- IPv4 o IPv6
  `user_agent` VARCHAR(255) DEFAULT NULL,
  `estado`     ENUM('nuevo','contactado','cerrado','spam') NOT NULL DEFAULT 'nuevo',
  `notas`      TEXT         DEFAULT NULL,  -- notas del administrador
  `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP    NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_leads_estado`   (`estado`),
  KEY `idx_leads_origen`   (`origen`),
  KEY `idx_leads_created`  (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;
