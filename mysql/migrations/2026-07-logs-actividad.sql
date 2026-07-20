-- ============================================================================
-- Migracion: 2026-07-logs-actividad.sql
-- Proyecto:  php-bathroom
-- Plan:      .doc/plan-log-actividad.md, seccion 3 (Modelo de datos)
-- Fecha:     2026-07-18
--
-- Objetivo:
--   Crear la tabla `logs_actividad` para registrar las acciones del usuario
--   logueado sobre las 7 entidades del sistema + eventos de sesion
--   (LOGIN/LOGOUT) y salidas (EXPORT/PDF). Ver .doc/plan-log-actividad.md
--   seccion 1 (alcance) y seccion 8 (consideraciones criticas).
--
-- Decisiones de diseno clave (ver plan §3):
--   - Sin FK fisica dura sobre `users`: `id_Usuario` + snapshot `username_Log`
--     para que el log sobreviva aunque se borre el usuario.
--   - `datos_Log` como TEXT (JSON string), no columna JSON nativa, por
--     compatibilidad y simplicidad. El helper `layouts/activity_logger.php`
--     se encarga de sanear con blacklist de campos sensibles.
--   - 4 indices pensados para la vista de administracion (filtrar por
--     usuario, entidad+id, accion, fecha).
--   - `ip_Log` VARCHAR(45) para soportar IPv6.
--
-- Idempotencia:
--   CREATE TABLE IF NOT EXISTS permite re-ejecutar el script sin error.
-- ============================================================================


CREATE TABLE IF NOT EXISTS `logs_actividad` (
  `id_Log`          INT(11)      NOT NULL AUTO_INCREMENT,
  `id_Usuario`      INT(11)      DEFAULT NULL,          -- FK logica a users.id (nullable: login fallido / sin sesion)
  `username_Log`    VARCHAR(50)  DEFAULT NULL,          -- snapshot: sobrevive si se borra el usuario
  `accion_Log`      VARCHAR(20)  NOT NULL,              -- CREATE|UPDATE|DELETE|ACTIVATE|DEACTIVATE|LOGIN|LOGOUT|EXPORT|PDF|ERROR
  `entidad_Log`     VARCHAR(40)  DEFAULT NULL,          -- Bathroom|Customer|Contract|Service|Invoice|Certificate|User (NULL en LOGIN/LOGOUT)
  `entidad_id_Log`  INT(11)      DEFAULT NULL,          -- id del registro afectado
  `descripcion_Log` VARCHAR(255) DEFAULT NULL,          -- texto legible: "Creó baño código B-102"
  `pantalla_Log`    VARCHAR(120) DEFAULT NULL,          -- controller que ejecutó la acción: controller/bath-new.php
  `metodo_Log`      VARCHAR(8)   DEFAULT NULL,          -- GET|POST
  `datos_Log`       TEXT         DEFAULT NULL,          -- payload sanitizado (JSON), sin secretos
  `resultado_Log`   VARCHAR(10)  NOT NULL DEFAULT 'success', -- success|error
  `ip_Log`          VARCHAR(45)  DEFAULT NULL,          -- soporta IPv6
  `user_agent_Log`  VARCHAR(255) DEFAULT NULL,
  `fecha_Log`       DATETIME     NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_Log`),
  KEY `idx_log_usuario` (`id_Usuario`),
  KEY `idx_log_entidad` (`entidad_Log`, `entidad_id_Log`),
  KEY `idx_log_accion`  (`accion_Log`),
  KEY `idx_log_fecha`   (`fecha_Log`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ============================================================================
-- Verificacion (correr despues de aplicar):
--   DESCRIBE logs_actividad;
--     -> debe listar las 14 columnas definidas arriba (id_Log, id_Usuario,
--        username_Log, accion_Log, entidad_Log, entidad_id_Log, descripcion_Log,
--        pantalla_Log, metodo_Log, datos_Log, resultado_Log, ip_Log,
--        user_agent_Log, fecha_Log) con la PK en id_Log.
--
--   SHOW INDEX FROM logs_actividad;
--     -> debe listar 5 indices: PRIMARY + idx_log_usuario + idx_log_entidad
--        + idx_log_accion + idx_log_fecha.
-- ============================================================================
