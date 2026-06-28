-- ============================================================================
-- Migracion 010 (file: 004) — Crear tbl_audit_log (auditoria del panel admin).
-- ============================================================================
-- Origen: el panel admin no tiene trazabilidad. No se sabe quien edito un
-- plan, quien borro un slider, quien cambio el estado de un lead, quien
-- entro/salio, etc. Esta migration agrega una tabla append-only que registra
-- todas las acciones criticas (CRUD sobre contenido + auth + cambios de
-- estado de leads).
--
-- Decisiones de diseno:
--   * Append-only: nunca UPDATE/DELETE del lado de la app. La limpieza es
--     responsabilidad de un job externo (samap_audit_purge).
--   * `usuario`  -> userName de la sesion actual, o 'anonymous' si todavia
--                   no hay sesion (ej. intento de login), o 'system' para
--                   operaciones automaticas.
--   * `rol`      -> copia de samap_rol() al momento de la accion (admin/editor/
--                   comercial/vacio).
--   * `accion`   -> 'login' | 'login_fail' | 'logout' | 'insert' | 'update' |
--                   'delete' | 'restore' | 'hard_delete' | 'view' | 'export'
--   * `entidad`  -> nombre de la tabla afectada (ej. 'tbl_planes') o vacio
--                   para acciones globales (login/logout).
--   * `entidad_id` -> PK del registro afectado, 0 si no aplica.
--   * `descripcion` -> texto humano corto (max 500 chars).
--   * `datos_anteriores` / `datos_nuevos` -> JSON snapshots para comparar
--                   cambios en updates. NULL en inserts (nuevo) o deletes
--                   (viejo) segun corresponda.
--   * `ip` / `user_agent` -> del request HTTP, para forensia.
--
-- Indices:
--   * idx_usuario   -> filtrar por usuario (ej. "que hizo Pepito esta semana")
--   * idx_accion    -> filtrar por tipo de operacion
--   * idx_entidad   -> buscar historial de un registro especifico
--   * idx_created   -> orden/paginacion por fecha (el default es DESC)
--
-- Las inserciones son baratas (~30 bytes por row sin los JSON) y la limpieza
-- la hace samap_audit_purge() que borra lo de mas de N dias.
-- ============================================================================

SET NAMES utf8;

CREATE TABLE IF NOT EXISTS `tbl_audit_log` (
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
