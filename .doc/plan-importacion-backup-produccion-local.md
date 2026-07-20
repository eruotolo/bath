# Plan — Importación de backup de producción a local + aplicación de migraciones pendientes

**Estado:** ✅ EJECUTADO Y CERRADO — 2026-07-19
**Fecha:** 2026-07-19
**Objetivo:** reemplazar la base de datos local (`donbano`, contenedor `mariadb-bathroom`) por una copia fresca y real de los datos de producción (droplet `138.197.32.40`, contenedor `mariadb-bathroom` remoto), y sobre esa copia aplicar las 4 migraciones pendientes que hoy solo existen en local — sin tocar ni modificar la base de datos de producción en ningún momento.

## Resultado de la ejecución (2026-07-19)

Ejecutado directamente por Sonnet 5, secuencial, sin incidentes. Se reutilizó el backup de producción ya descargado (`backup/19-07-2026/donbano-2026-07-19.sql`) en vez de descargar de nuevo — Fase 1 del plan original se dio por cubierta.

- **Fase 0:** backup de resguardo del estado local previo → `backup/19-07-2026/donbano-local-pre-import-2026-07-19.sql` (482K).
- **Fase 2:** verificación en DB temporal (`donbano_check`, luego eliminada) contra datos reales: **0** `codigo_Bath` duplicados; **8** contratos activos sin baño a cerrar (no 62 — ese número correspondía a datos de prueba locales viejos, no a producción real). `contactos` confirmada real y genuinamente vacía en producción.
- **Fase 3:** import del dump de producción sobre `donbano` local — reemplazo completo, 14 tablas.
- **Fase 4:** las 4 migraciones aplicadas sin errores (`fase1-fase2`, `superadmin-category`, `roles-permisos`, `logs-actividad`). Cerró los 8 contratos reales identificados en la Fase 2.
- **Fase 5 (QA):** todas las columnas nuevas verificadas presentes (`nivel_category`, `codigo_Bath` UNIQUE, `created_at` x4 tablas, `fecha_Pago`, `valor_Servicio`, `retiro_Tipo`, `logs_actividad` completa); conteo de filas idéntico entre Fase 2 y post-migración en las 8 tablas críticas — **cero pérdida de datos reales**; login HTTP 200, sin errores fatales en logs de PHP.
- **Hallazgo no previsto:** `logs_actividad` sobrevivió al import (el dump de producción no tiene esa tabla, así que el `DROP TABLE IF EXISTS` no aplicó) con entradas de test de QA locales viejas. A pedido de Edgardo, se vació con `TRUNCATE TABLE logs_actividad` tras confirmar que no eran datos reales.

**Estado final de `donbano` local:** datos reales de producción + las 4 migraciones aplicadas + `logs_actividad` limpia. Sigue siendo un entorno **solo local** — producción no fue tocada en ningún momento.

**Alcance explícito:** este plan es **solo local**. No incluye desplegar nada contra el servidor de producción — eso queda para una decisión posterior, fuera de este plan.

---

## 0. Contexto (hallazgos de la comparación previa)

Comparando el backup de producción descargado el 2026-07-19 (`backup/19-07-2026/donbano-2026-07-19.sql`) contra la base de datos local en ejecución:

| Tabla | Diferencia | Origen |
|---|---|---|
| `logs_actividad` | No existe en producción | `mysql/migrations/2026-07-logs-actividad.sql` |
| `category` | Falta columna `nivel_category`; falta fila `(3, 'SuperAdministrador')` | `2026-07-superadmin-category.sql` + `2026-07-roles-permisos.sql` |
| `bathrooms` | `codigo_Bath` sin `UNIQUE KEY` | `2026-07-fase1-fase2.sql` sección 3 |
| `certificados`, `facturas`, `servicios`, `contratos` | Falta `created_at` | `2026-07-fase1-fase2.sql` sección 1 |
| `facturas` | Falta `fecha_Pago` | `2026-07-fase1-fase2.sql` sección 5 |
| `servicios` | Falta `valor_Servicio` | `2026-07-fase1-fase2.sql` sección 6 |
| `tipo_servicio` | Falta `retiro_Tipo` | `2026-07-fase1-fase2.sql` sección 2 |
| `contratos` | 62 contratos "Activos" sin baño asignado no cerrados (sección 4) | `2026-07-fase1-fase2.sql` sección 4 — este número corresponde a los datos de prueba locales viejos, **hay que recalcularlo contra los datos reales de producción** |

**Verificación preliminar (parseo del dump, a confirmar con SQL real tras importar):** 0 códigos `codigo_Bath` duplicados en el backup de producción → la sección 3 (UNIQUE KEY) debería aplicar sin conflicto. Esto es un problema **distinto** al ya documentado de 41 baños con múltiples contratos "activos" simultáneos en `contrato_bathroom` (`.doc/Viejos/informe-produccion-banos-duplicados.md`), que sigue sin resolver y queda **fuera de alcance** de este plan.

---

## 1. Riesgos y salvaguardas

- **Los datos reales de producción no se tocan:** todas las operaciones de este plan corren contra `mariadb-bathroom` **local**. La única interacción con el droplet es un `mariadb-dump` de solo lectura.
- **Los datos actuales en local se pierden al importar** (el dump trae `DROP TABLE IF EXISTS` por cada tabla). Por eso T0 hace un backup de resguardo del estado local actual antes de tocar nada, para poder revertir si algo sale mal.
- **Las secciones 3 y 4 de `fase1-fase2.sql` tocan datos reales** (constraint UNIQUE + cierre automático de contratos). Aunque ahora corren sobre una copia local, siguen siendo datos de negocio reales — se corren con revisión manual antes de confirmar, no en automático ciego.
- Ninguna operación de este plan usa `TRUNCATE` ni `DELETE` sin `WHERE` — la sustitución de datos la hace el propio dump (`DROP TABLE` + `CREATE TABLE` + `INSERT`), que es el mecanismo estándar y controlado de un restore.

---

## 2. Procedimiento — Fases

### Fase 0 — Backup de resguardo de la DB local actual
Antes de tocar nada, volcar el estado actual de `donbano` local a `backup/<fecha>/donbano-local-pre-import-<fecha>.sql`, por si hace falta revertir.

```bash
mkdir -p backup/$(date +%d-%m-%Y)
docker exec mariadb-bathroom sh -c 'mariadb-dump -u root -p"$MYSQL_ROOT_PASSWORD" donbano' \
  > backup/$(date +%d-%m-%Y)/donbano-local-pre-import-$(date +%F).sql
```

### Fase 1 — Backup fresco de producción
Descarga directa por pipe SSH (sin dejar ningún archivo en el droplet, a diferencia del intento anterior):

```bash
ssh php-bathroom-droplet 'docker exec mariadb-bathroom sh -c "mariadb-dump -u root -p\"\$MYSQL_ROOT_PASSWORD\" donbano"' \
  > backup/$(date +%d-%m-%Y)/donbano-prod-$(date +%F).sql
```

Verificar integridad: conteo de `CREATE TABLE` (debe dar 14) y tamaño razonable (~400K, similar al backup anterior).

### Fase 2 — Verificación previa sobre los datos de producción (antes de importar)
Importar el dump de producción a una base **temporal** (no la `donbano` local real) para correr ahí, de solo lectura, la Sección 0 de `mysql/migrations/2026-07-fase1-fase2.sql`:

- 0.1: `codigo_Bath` duplicados (confirmar el 0 preliminar con SQL real).
- 0.2: contratos "Activos" (`estado_Contrato = 2`) sin ningún baño asignado — recalcular el número real de contratos que la sección 4 va a cerrar.

Documentar el resultado. Si 0.1 devuelve filas (duplicados reales), **parar** y decidir con Edgardo cómo resolverlos antes de continuar — la sección 3 del script fallará si hay duplicados.

### Fase 3 — Importación del backup de producción a la DB local real
Con la Fase 2 aprobada, importar el dump de producción sobre `donbano` local (reemplaza completamente los datos locales actuales por los reales de producción):

```bash
docker exec -i mariadb-bathroom sh -c 'mariadb -u root -p"$MYSQL_ROOT_PASSWORD" donbano' \
  < backup/<fecha>/donbano-prod-<fecha>.sql
```

Verificar: `SHOW TABLES` (14 tablas, sin `logs_actividad` todavía) y conteo de filas por tabla contra lo esperado del dump.

### Fase 4 — Aplicar las 4 migraciones pendientes sobre los datos ya importados
En orden, sobre la `donbano` local que ahora tiene datos reales de producción:

1. `mysql/migrations/2026-07-fase1-fase2.sql` — **pausar antes de la Sección 3** (UNIQUE KEY) y la **Sección 4** (cierre de contratos) para revisión manual de los resultados de la Fase 2 antes de confirmar.
2. `mysql/migrations/2026-07-superadmin-category.sql`
3. `mysql/migrations/2026-07-roles-permisos.sql`
4. `mysql/migrations/2026-07-logs-actividad.sql`

Todas son idempotentes (`IF NOT EXISTS` / `ON DUPLICATE KEY UPDATE`), seguras de re-ejecutar si algo se corta a mitad de camino.

### Fase 5 — QA final
- `DESCRIBE` de cada tabla modificada: confirmar que las columnas nuevas están presentes.
- Queries de verificación indicadas en cada script de migración (ya documentadas en cada `.sql`).
- Conteo total de filas antes (Fase 3) / después (Fase 4) por tabla, para confirmar que ningún dato real de producción se perdió en el proceso.
- Smoke test manual de la app local (`http://localhost`) contra los datos reales: login, listados de las 7 entidades, que no haya errores de columna faltante.

---

## 3. Tabla de fases (Orca)

Trabajo operativo secuencial sobre datos reales — no aplica paralelización entre modelos (cada fase depende del resultado verificado de la anterior, y el riesgo de negocio de las Secciones 3/4 exige revisión directa, no delegación). Todas las tareas las ejecuta **Sonnet 5 directamente**, con checkpoint de revisión manual de Edgardo antes de Fase 3 y antes de confirmar Secciones 3/4 de Fase 4.

| Tarea | Ola | Modelo (Orca) | Depende de | Entregable | Riesgo |
|---|---|---|---|---|---|
| T0 | 0 | Sonnet 5 (ejecución directa) | — | Backup de resguardo de la DB local actual | Bajo |
| T1 | 0 | Sonnet 5 (ejecución directa) | — | Backup fresco de producción descargado a local (sin dejar copia en el droplet) | Bajo (solo lectura en prod) |
| T2 | 1 | Sonnet 5 (ejecución directa) | T1 | Verificación previa (Sección 0) contra los datos reales de producción, en DB temporal | Bajo |
| T3 | 2 | Sonnet 5 (ejecución directa) | T0, T2 (aprobada) | Importación del dump de producción sobre la DB local real | **Alto** (reemplaza datos locales; mitigado por T0) |
| T4 | 3 | Sonnet 5 (ejecución directa) | T3 | Las 4 migraciones aplicadas sobre los datos importados | Medio (Secciones 3/4 tocan datos reales — checkpoint manual) |
| T5 | 4 | Sonnet 5 (ejecución directa) | T4 | QA de esquema + conteo de filas + smoke test en navegador | Bajo |

---

## 4. Fuera de alcance de este plan

- Aplicar estas migraciones contra el servidor de producción real.
- Resolver el problema de los 41 baños con múltiples contratos "activos" simultáneos (`.doc/Viejos/informe-produccion-banos-duplicados.md`) — pendiente de decisión de Edgardo, no relacionado con el UNIQUE KEY de `codigo_Bath`.
- Verificar la tabla `contactos` mostró 0 filas en el parseo preliminar del dump — confirmar en Fase 2/5 si es un dato real (tabla vacía en producción) o un artefacto del parseo.
