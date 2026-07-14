# Checklist — Plan DDD / Entidad: Services

**Origen:** `.doc/Viejos/plan-migracion-ddd-drycode.md` (sección 6, entidad #6)
**Estado:** ✅ implementado y verificado (2026-07-09)

---

## Alcance

**Controllers (5):**

| Archivo | Operación | Estado antes |
|---|---|---|
| `controller/service-new.php` | INSERT en `servicios` + `tipo_servicio` | SQL injection en ambas |
| `controller/service-update.php` | UPDATE en `servicios` + `tipo_servicio` | SQL injection en ambas |
| `controller/service-inactive.php` | UPDATE estado=0 | SQL injection |
| `controller/service-bath-add.php` | INSERT en `servicios_bathrooms` (loop) | SQL injection — **pendiente desde la migración de Bathrooms**, documentado ahí |
| `controller/service-bath-remove.php` | DELETE de `servicios_bathrooms` | SQL injection — mismo pendiente |

`controller/invoice-service-add.php` e `invoice-service-remove.php` tocan `factura_servicio` (relación Factura↔Servicio) — **no se tocaron**, pertenecen al dominio Invoices, siguiente y última entidad del plan.

`controller/obtener_contratos.php` (usado por el select cascada cliente→contrato en `dash-services-add.php`) ya tenía prepared statements y `htmlspecialchars()` de una sesión anterior — no se tocó.

**Vistas:**

- `dash-services.php` — reemplaza el listado + el cálculo N+1 de "Facturado" (una query `SELECT * FROM factura_servicio` por fila) por una sola query con `EXISTS(...)` correlacionado; agrega `htmlspecialchars()`
- `dash-services-edit.php` — SQL injection vía `$_GET['id_Servicio']` en 2 queries (servicio+contrato+cliente, y `tipo_servicio` aparte); reescrito con `FindServiceWithContractAndCustomer` (un solo query con los 4 joins, incluido `tipo_servicio`)
- `dash-services-bath.php` — la vista más compleja: 3 queries SQL injectables (servicio+contrato+cliente+tipo, baños del contrato para el checkbox de asignación, baños ya asignados al servicio) reescritas con `FindServiceWithContractAndCustomer` + `ListBathroomsByContract` (reutilizado de Bathrooms/Contracts) + `ListAssignedBathrooms`
- `dash-services-print.php` — SQL injection vía `$_GET['id_Servicio']`; reescrito con `FindServiceForPrint`, que preserva el comportamiento original de fallar si el servicio no tiene ningún baño asignado (el `INNER JOIN` original con `servicios_bathrooms` producía cero filas en ese caso)

`dash-services-add.php` no se tocó — su único SQL (`SELECT * FROM clientes ORDER BY nombre_Cliente`) pertenece al dominio Customer y no es inyectable.

## Decisiones de diseño

1. **`Service` (Entity) incluye los 9 campos de `tipo_servicio`** aunque viven en una tabla separada, unida por `nro_Servicio` (no por FK a `id_Servicio` — así es el schema real, no se tocó). Se decidió así porque en la práctica **todas** las vistas necesitan ambas tablas juntas siempre — separarlas en dos Entities hubiera obligado a instanciar y sincronizar dos objetos en cada Use Case sin beneficio real.
2. **`MysqliServiceRepository::insert()` y `::update()` NO usan transacción** — el código original tampoco la usaba (dos `INSERT`/`UPDATE` sueltos, sin rollback si el segundo falla). Se preserva el comportamiento tal cual; agregar una transacción sería una mejora real pero fuera del alcance de esta migración (cambia el comportamiento ante fallos, no solo la forma del SQL).
3. **`listActiveWithDetails()` calcula "Facturado" con un `EXISTS` correlacionado** en vez de una query aparte por fila (que era lo que hacía el código original, un N+1 real sobre potencialmente miles de servicios). Se considera una mejora de implementación dentro del mismo alcance (mismo resultado, mismo dato), no una funcionalidad nueva — mismo criterio que la corrección del JOIN en Certificates.
4. **`AssignBathroomsToService` deja de continuar insertando si una de las filas falla** (el original hacía `echo` del error y seguía con el resto del array). Con prepared statements y validación de tipos esto casi nunca pasa; se documenta como una diferencia de comportamiento menor y aceptable ante errores (falla rápido en vez de insertar parcialmente).

## Verificación

- [x] `php -l` sobre los 5 controllers + 4 vistas tocadas + 22 archivos nuevos — sin errores
- [x] **Toda la lógica de negocio se verificó primero con scripts aislados** (mismo método que en Contracts, para no depender de la UI inestable con selects "enhanced"): crear servicio con tipos correctos, asignar/listar/quitar baños, actualizar, buscar para imprimir (con y sin baños asignados, confirmando que preserva el fallo original sin baños), inactivar — todo funcionó a la primera, sin bugs de `bind_param` esta vez (se revisaron los tipos char por char antes de ejecutar)
- [x] Crear servicio de prueba vía `curl` directo al controller — redirect correcto
- [x] `dash-services-bath.php`: información del servicio correcta, modal "Agregar Nuevo Baño" lista los 4 baños del contrato correctamente (`ListBathroomsByContract` reutilizado de Contracts), asignación funciona desde la UI
- [x] `dash-services-print.php`: comprobante completo, incluye "Baños Tratados" con el código correcto
- [x] `dash-services.php`: 1644 servicios, columna "Facturado"/"No Facturado" calculada correctamente sin N+1
- [x] `docker-compose logs php` — sin errores/warnings nuevos en toda la sesión

## Pendiente / fuera de alcance

- **Invoices** — última entidad del plan, en curso en la misma sesión autónoma.
- `controller/invoice-service-add.php` / `invoice-service-remove.php` — SQL injection activa, se migran junto con Invoices.

---

## Reglas respetadas (de `CLAUDE.md`)

- SQL 100% prepared statements — elimina 5 SQL injections activas
- `htmlspecialchars()` agregado en las vistas reescritas
- N+1 corregido dentro del mismo alcance de la migración (mismo dato, mejor implementación)
- No se tocó el dominio Invoices (`factura_servicio`) — queda para la entidad siguiente
- Commit atómico por entidad
