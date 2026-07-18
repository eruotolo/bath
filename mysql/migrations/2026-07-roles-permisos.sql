-- ============================================================================
-- Migracion: 2026-07-roles-permisos.sql
-- Proyecto: php-bathroom
-- Plan:     .doc/plan-roles-permisos.md, seccion 3
-- Fecha:    2026-07-18
--
-- Objetivo:
--   Agregar la columna `nivel_category` a la tabla `category` y poblarla
--   con la jerarquia de los 3 roles:
--     - Usuario           (id=1) -> nivel 1
--     - Administrador     (id=2) -> nivel 2
--     - SuperAdministrador(id=3) -> nivel 3
--   Luego promover al usuario inicial (Edgardo, users.id=1) a SuperAdmin.
--
-- Notas:
--   - La fila (3, 'SuperAdministrador') YA EXISTE (fue insertada por la
--     migracion previa `2026-07-superadmin-category.sql`). Este script NO la
--     vuelve a crear; solo asegura que su `nivel_category` quede en 3.
--   - El `id_category` no refleja la jerarquia (Administrador=1 es menor que
--     SuperAdmin=3). Toda la logica de permisos compara `nivel_category`,
--     nunca el id. Ver .doc/plan-roles-permisos.md seccion 1.
--   - ALTER TABLE ADD COLUMN IF NOT EXISTS esta soportado en MariaDB 11.2
--     (motor de este proyecto). Las sentencias UPDATE son idempotentes por
--     si mismas. Es seguro re-ejecutar este script completo.
-- ============================================================================


-- 1) Nivel jerarquico en category (columna nueva, default 1 para no romper filas)
ALTER TABLE `category`
  ADD COLUMN IF NOT EXISTS `nivel_category` INT(11) NOT NULL DEFAULT 1
  AFTER `name_category`;


-- 2) Asignar niveles a los 3 roles
UPDATE `category` SET `nivel_category` = 2 WHERE `id_category` = 1; -- Administrador
UPDATE `category` SET `nivel_category` = 1 WHERE `id_category` = 2; -- Usuario
UPDATE `category` SET `nivel_category` = 3 WHERE `id_category` = 3; -- SuperAdministrador


-- 3) Promover al SuperAdmin inicial (Edgardo, users.id = 1)
--    Idempotente: si users.id=1 ya tiene category=3, el UPDATE no cambia nada.
UPDATE `users` SET `category` = 3 WHERE `id` = 1;


-- ============================================================================
-- Verificacion (correr despues de aplicar):
--   SELECT id_category, name_category, nivel_category FROM category;
--     -> debe devolver:
--        1 | Administrador     | 2
--        2 | Usuario           | 1
--        3 | SuperAdministrador| 3
--
--   SELECT id, category FROM users WHERE id = 1;
--     -> debe devolver:
--        1 | 3
-- ============================================================================
