# Checklist — Plan DDD / Entidad: Contracts

**Origen:** `.doc/plan-migracion-ddd-drycode.md` (sección 6, entidad #5 — requiere Customers y Bathrooms ya migradas)
**Estado:** ✅ implementado y verificado (2026-07-09)

---

## ⚠️ Bugs reales encontrados y corregidos (autónomo — sesión "termina todo el DDD")

Esta migración se hizo en modo autónomo (pedido explícito: "termina con todo el tema de la migración a DDD... de forma autónoma"), sin pausar a confirmar cada hallazgo salvo que fuera de seguridad. Se encontraron y corrigieron 2 bugs reales, ninguno de seguridad, documentados acá para que quede registro:

### 1. `contract-new.php` guardaba `fechaInicio_Contrato` con el valor de `fechaFin_Contrato`

El INSERT original tenía la variable `$fechaFin_Contrato` **duplicada** en la lista de VALUES (typo, se copió mal de `$fechaInicio_Contrato`):
```php
// ANTES (bug):
$sql = "INSERT INTO contratos (..., fechaInicio_Contrato, fechaFin_Contrato, ...)
VALUE (..., '$fechaFin_Contrato', '$fechaFin_Contrato', ...)";
```
Resultado: **todo contrato creado por la app tenía la fecha de inicio igual a la fecha de fin**, silenciosamente, desde que existe ese formulario. Se confirma en los datos reales — el listado de contratos (`dash-contracts.php`) muestra fecha de inicio = fecha de fin en la gran mayoría de los registros existentes.

**Corregido** en `CreateContract::handle()`, que ahora mapea `fechaInicio_Contrato` → `startDate` y `fechaFin_Contrato` → `endDate` correctamente. **No se tocaron los datos históricos** — es una decisión de alcance (backfill de datos requiere criterio de negocio, no algo para decidir en una migración de arquitectura).

### 2. Typo en el string de tipos de `bind_param()` en `MysqliContractRepository::insert()` y `::update()`

Al escribir el Repository nuevo (no en el código original — bug introducido en esta misma migración), el string de tipos tenía una `i` donde correspondía `s` en la posición de `fechaFin_Contrato`, hacía que mysqli tratara la fecha como entero. Se detectó de inmediato al probar la creación de un contrato (`Incorrect date value: '2026' for column fechaFin_Contrato`) — el string `2026-06-30` se truncaba a `2026` por el cast implícito a integer. Corregido: `'issisiiis'` → `'ississiis'` (insert) y `'issisiiisi'` → `'ississiisi'` (update). Verificado con un script aislado antes de reintentar en el navegador.

## Alcance

**Controllers (5 nuevos + 3 ya migrados en Bathrooms):**

| Archivo | Operación | Estado antes |
|---|---|---|
| `controller/contract-new.php` | INSERT | SQL injection + bug de fechas (arriba) |
| `controller/contract-update.php` | UPDATE | SQL injection |
| `controller/contract-active.php` | UPDATE estado=2 | SQL injection |
| `controller/contract-inactive.php` | UPDATE estado=1 + desasigna todos los baños | SQL injection |
| `controller/contract-down.php` | UPDATE estado=0 | SQL injection — botón "Eliminar" está comentado en `dash-contracts.php`, controller huérfano pero accesible por URL directa (mismo patrón que `bath-get.php`/`contract-bath-assign.php` en Bathrooms) |

`contract-bath-assign.php`, `contract-bath-new-assign.php`, `contract-bath-notassign.php` ya se habían migrado durante Bathrooms (usan `BathroomRepositoryInterface`).

**Vistas:**

- `dash-contracts.php` — reemplaza 3 queries (COUNT sin filtro, listado con filtro por estado vía prepared statement parcial, listado sin filtro con SQL plano) por `ListContracts`; agrega `htmlspecialchars()`
- `dash-contracts-edit.php` — SQL injection vía `$_GET['id_Contrato']`, sin escapar; reescrito con `FindContract`
- `dash-contracts-item.php` — SQL injection en 2 queries (contrato+cliente, baños del contrato); reescrito con `FindContractWithCustomer` + `ListBathroomsByContract` (nuevo método en `BathroomRepositoryInterface`, no en Contract — la tabla `contrato_bathroom` ya es del dominio Bathroom desde la migración anterior)

`dash-contracts-add.php` no se tocó — su único SQL (`SELECT * FROM clientes` sin filtro, para el selector) pertenece al dominio Customer y no es inyectable.

## Bug encontrado en un modal incluido (mismo patrón que en Customers)

`layouts/modal-nuevo-assign-bath.php` (incluido desde `dash-contracts-item.php`) tenía un campo oculto `id_Contrato` que leía `$row['id_Contrato']` — variable del código viejo que dejó de existir al reemplazar por `$contrato` (array). Se corrigió a `$contrato['id_Contrato']` antes de probar, aplicando la lección ya documentada en `checklist-ddd-customers.md`.

## Archivos nuevos

```
app/src/
├── Domain/Contract/
│   ├── Contract.php                               — Entity (id, customerId, obra, address, state, startDate, endDate, monthlyValue, totalValue, observation)
│   └── ContractRepositoryInterface.php
├── Application/Contract/
│   ├── CreateContract.php                         — fix bug #1 (fechas)
│   ├── UpdateContract.php
│   ├── SetContractState.php                       — usado por contract-active.php (2) y contract-down.php (0)
│   ├── DeactivateContract.php                     — usado por contract-inactive.php; cruza a BathroomRepositoryInterface para desasignar todos los baños
│   ├── FindContract.php
│   ├── FindContractWithCustomer.php
│   └── ListContracts.php
└── Infrastructure/Persistence/
    └── MysqliContractRepository.php               — fix bug #2 (bind_param)
```

**Extensión a `BathroomRepositoryInterface`** (no archivo nuevo, se agregó a la interfaz y su implementación existentes):
- `listByContract(int $idContrato): array` — para "Baños de la Obra" en dash-contracts-item.php
- `unassignAllFromContract(int $idContrato): void` — usado por `DeactivateContract`
- Nuevo Use Case `App\Application\Bathroom\ListBathroomsByContract`

## Verificación en navegador real

- [x] `php -l` sobre los 5 controllers + 3 vistas + 1 modal tocados + 9 archivos nuevos + 2 archivos extendidos — sin errores
- [x] Listado (`dash-contracts.php`): 194 contratos, mismo formato
- [x] Filtros "Contratos Activos" (`?estado=2`, 195) y "Contratos Terminados" (`?estado=1`, 195) — ambos correctos
- [x] Crear contrato de prueba — primer intento falló por el bug #2 (bind_param), diagnosticado con un script aislado (mismo patrón que la verificación del IDOR en Users, para no depender de la UI inestable con selects "enhanced"), corregido, reverificado con `curl` directo al controller — encabezados y redirect correctos
- [x] Confirmado en DB que `fechaInicio_Contrato` y `fechaFin_Contrato` quedaron con valores distintos y correctos (bug #1 corregido)
- [x] Ver detalle (`dash-contracts-item.php`) — datos correctos, tabla de baños vacía inicialmente
- [x] Asignar un baño (modal ya corregido) — aparece en la tabla como "Asignado"
- [x] Editar contrato (`dash-contracts-edit.php` + `contract-update.php`) — cambio de observación persiste
- [x] Inactivar contrato (`contract-inactive.php`) — el baño se desasigna automáticamente (`unassignAllFromContract`), confirmado que vuelve a "Disponible" en `dash-bathrooms.php`
- [x] Activar contrato (`contract-active.php`) — vuelve a aparecer en el filtro "Activos"
- [x] `contract-down.php` (huérfano) — verificado por `curl`, funciona, usado para limpiar el contrato de prueba (queda en estado 0, fuera de todos los listados — no hay hard-delete)
- [x] `docker-compose logs php` — sin errores/warnings nuevos en toda la sesión

## Pendiente / fuera de alcance

- Services, Invoices — siguen en el orden del plan (en curso, misma sesión autónoma).
- Backfill de `fechaInicio_Contrato` en contratos históricos afectados por el bug #1 — requiere decisión de negocio (¿se puede reconstruir la fecha real de algún otro lado, o se deja como está?), no se tocó.
- No hay hard-delete de contratos, solo `contract-down.php` (estado 0, sin botón visible en la UI).

---

## Reglas respetadas (de `CLAUDE.md`)

- SQL 100% prepared statements — elimina 5 SQL injections activas
- `htmlspecialchars()` agregado en las vistas reescritas
- Bug de datos (fechas) corregido y documentado explícitamente, sin tocar históricos — mismo criterio que el bug de `bath-notassign.php` en Bathrooms y el de reactivación en Customers
- Commit atómico por entidad, igual que las anteriores — el pedido explícito de completar toda la migración de forma autónoma cubre los commits de esta tarea, pero se mantiene un commit por entidad para preservar el historial granular y revertible
