-- ============================================================================
-- Migracion 003 — Crear tbl_user_token (recuperacion de contrasena + verificacion de email).
-- ============================================================================
-- Origen: el unico usuario admin (admin / admin123) no tenia forma de recuperar
-- la contrasena. Esta migration agrega una tabla generica de tokens de un solo
-- uso, expirables, vinculados a tbl_user.
--
-- Casos de uso previstos:
--   * tipo = 'reset_password'  -> enlace que llega por email al usuario para
--                                  restablecer la contrasena del panel admin.
--   * tipo = 'verify_email'    -> (futuro) confirmacion de email al registrarse.
--
-- Esquema:
--   * id          PK auto-increment.
--   * user_id     FK conceptual a tbl_user.id (no formal para no romper dumps legacy).
--   * token       hex de 64 chars (32 bytes randomicos). UNIQUE -> no colisionan.
--   * tipo        ENUM acotado para que no entren tokens basura.
--   * expires_at  TIMESTAMP. 1h para reset, 24h para verify.
--   * used_at     NULL hasta que se canjea. ONCE: el WHERE del canje filtra used_at IS NULL.
--   * created_at  cuando se genero.
--   * ip          IP de quien lo solicito (auditoria).
--
-- Indices:
--   * uniq_token      -> busqueda por token (UNIQUE garantiza 1 sola fila).
--   * idx_user_tipo   -> limpieza / listado por usuario y tipo.
--   * idx_expires     -> job que purga tokens vencidos.
-- ============================================================================

SET NAMES utf8;

CREATE TABLE IF NOT EXISTS `tbl_user_token` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`    INT UNSIGNED NOT NULL,
  `token`      VARCHAR(128) NOT NULL,
  `tipo`       ENUM('reset_password', 'verify_email') NOT NULL DEFAULT 'reset_password',
  `expires_at` TIMESTAMP    NOT NULL,
  `used_at`    TIMESTAMP    NULL DEFAULT NULL,
  `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip`         VARCHAR(45)  DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_token`     (`token`),
  KEY        `idx_user_tipo`  (`user_id`, `tipo`),
  KEY        `idx_expires`    (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;
