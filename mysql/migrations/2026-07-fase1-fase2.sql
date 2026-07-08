-- ============================================================================
-- Migracion: Fase 1 + Fase 2 (mejoras julio 2026)
-- Proyecto: php-bathroom
-- Generado: 2026-07-08
--
-- Uso: correr manualmente contra produccion cuando se despliegue este trabajo.
-- Este archivo se va a seguir ampliando con cada cambio de esquema/datos que
-- se haga durante el resto de las fases (no se reescribe, se agrega abajo).
--
-- IMPORTANTE - antes de correr esto en produccion:
--   1. Backup completo:
--      mariadb-dump -u root -p$MYSQL_ROOT_PASSWORD $USER_DB_NAME > backup-pre-fase1-fase2-$(date +%F).sql
--   2. Correr primero la SECCION 0 (solo lectura) y revisar los resultados
--      antes de seguir con el resto del script.
--   3. Todas las ALTER TABLE usan IF NOT EXISTS - es seguro re-ejecutar este
--      script completo mas de una vez sin romper nada si ya se aplico parcial
--      o totalmente antes (probado en la base de testing, ver seccion final).
-- ============================================================================


-- ----------------------------------------------------------------------------
-- SECCION 0 - VERIFICACIONES PREVIAS (solo lectura, correr y revisar antes de seguir)
-- ----------------------------------------------------------------------------

-- 0.1 - ¿Hay codigos de baño duplicados? Si esta query devuelve filas, PARAR
--       y resolver los duplicados a mano (la SECCION 3 va a fallar si hay
--       duplicados, porque agrega un UNIQUE KEY sobre codigo_Bath).
SELECT codigo_Bath, COUNT(*) AS cantidad
FROM bathrooms
GROUP BY codigo_Bath
HAVING cantidad > 1;

-- 0.2 - Vista previa de los contratos que la SECCION 4 va a marcar "Terminado"
--       (contratos Activos que hoy no tienen ningun baño asignado)
SELECT id_Contrato, obra_Contrato, estado_Contrato, fechaInicio_Contrato, fechaFin_Contrato
FROM contratos
WHERE estado_Contrato = 2
  AND id_Contrato NOT IN (SELECT DISTINCT id_Contrato FROM contrato_bathroom);


-- ----------------------------------------------------------------------------
-- SECCION 1 - Columnas created_at (Fase 1 items 1-3, Fase 2 item 7b)
-- ----------------------------------------------------------------------------

ALTER TABLE certificados ADD COLUMN IF NOT EXISTS created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE facturas     ADD COLUMN IF NOT EXISTS created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE servicios    ADD COLUMN IF NOT EXISTS created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE contratos    ADD COLUMN IF NOT EXISTS created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

-- Nota: en los registros ya existentes, created_at queda con el momento en
-- que se corre esta migracion (no hay forma de reconstruir la fecha real de
-- creacion historica). El codigo de la app ya contempla esto con un fallback
-- de orden secundario por ID (ver dash-certificates.php, dash-invoices-list.php,
-- dash-services.php, dash-contracts.php).


-- ----------------------------------------------------------------------------
-- SECCION 2 - Servicios: tipo "Retiro de Baños" (Fase 1 item 5)
-- ----------------------------------------------------------------------------

ALTER TABLE tipo_servicio ADD COLUMN IF NOT EXISTS retiro_Tipo TINYINT(1) DEFAULT 0;


-- ----------------------------------------------------------------------------
-- SECCION 3 - Baños: codigo unico (Fase 1 item 6)
-- ----------------------------------------------------------------------------

-- Si la verificacion 0.1 mostro duplicados, resolverlos ANTES de correr esto
-- (por ejemplo, renombrando o dando de baja el registro duplicado mas viejo).
ALTER TABLE bathrooms ADD UNIQUE KEY IF NOT EXISTS codigo_Bath_unique (codigo_Bath);


-- ----------------------------------------------------------------------------
-- SECCION 4 - Cierre retroactivo de contratos sin baños asignados (Fase 2 item 9)
-- ----------------------------------------------------------------------------

-- Revisar el resultado de la verificacion 0.2 antes de correr este UPDATE.
-- Es seguro re-ejecutar: un contrato que ya paso a estado_Contrato = 1 deja
-- de matchear el WHERE, no se vuelve a tocar en una segunda corrida.
UPDATE contratos
SET estado_Contrato = 1
WHERE estado_Contrato = 2
  AND id_Contrato NOT IN (SELECT DISTINCT id_Contrato FROM contrato_bathroom);


-- ----------------------------------------------------------------------------
-- PROBLEMA CONOCIDO, NO RESUELTO POR ESTE SCRIPT:
--
-- Baños asignados a mas de un contrato "activo" al mismo tiempo (ver caso real
-- AT060 documentado en .doc/checklist-fase2-historial-reglas-negocio.md,
-- item 8). Pasa porque muchos contratos de un solo dia nunca se marcaron
-- "Terminado" a mano, y su relacion en contrato_bathroom nunca se borro aunque
-- el baño se haya reutilizado despues en otro contrato. La SECCION 4 de arriba
-- SOLO cierra contratos que quedaron con CERO baños - no resuelve contratos
-- con relaciones "fantasma" que siguen teniendo (incorrectamente) un baño
-- asociado. Corregir esto requiere decidir, contrato por contrato, cual es la
-- relacion vigente real - no es seguro automatizarlo sin criterio de negocio.
-- Pendiente de decision de Edgardo (ver checklist Fase 2, item 9).
-- ----------------------------------------------------------------------------


-- ============================================================================
-- Verificado y APLICADO contra la base de testing (donbano) el 2026-07-08:
--   - Secciones 1-3: columnas/constraint ya presentes, IF NOT EXISTS confirmado
--     idempotente (no rompe si se corre de nuevo).
--   - Seccion 4: ya corrida en testing -> cerro 62 contratos que estaban
--     "Activos" sin ningun baño asignado. Verificado despues: 0 contratos
--     activos sin baños restantes. Segura de re-correr en produccion (el
--     WHERE no vuelve a tocar los que ya quedaron en estado_Contrato = 1).
-- ============================================================================
