# Checklist — Plan DDD / Entidad: Bathrooms

**Origen:** `.doc/Viejos/plan-migracion-ddd-drycode.md` (sección 6, entidad #2 tras el piloto Certificates)
**Estado:** ✅ implementado y verificado en navegador (2026-07-09)

---

## Alcance

Migrar la entidad Bathrooms del patrón procedural (SQL inline, varios controllers con SQL injection activa) al patrón DDD liviano: Entity + Repository + Use Case, reusando la infraestructura de Composer/autoload ya montada para Certificates.

El relevamiento encontró **más superficie que la estimada por el plan** ("5 archivos, relación simple con contratos"): 10 controllers y 4 vistas tocan directamente la tabla `bathrooms` o su relación con `contratos` (`contrato_bathroom`).

## Archivos en alcance

**Controllers (10):**

| Archivo | Operación | Estado SQL antes |
|---|---|---|
| `controller/bath-new.php` | INSERT | ya tenía prepared statements (sesión previa) |
| `controller/bath-update.php` | UPDATE | ya tenía prepared statements (sesión previa) |
| `controller/bath-delete.php` | DELETE | SQL injection (`$_GET['id_Bath']`) |
| `controller/bath-active.php` | UPDATE estado=1 | SQL injection |
| `controller/bath-inactive.php` | UPDATE estado=0 | SQL injection |
| `controller/bath-notassign.php` | UPDATE asignado=0 | SQL injection |
| `controller/bath-get.php` | SELECT (JSON) | SQL injection — **huérfano, sin link en el frontend, pero accesible por URL directa** |
| `controller/contract-bath-assign.php` | UPDATE asignado=1 | SQL injection — **huérfano, sin link en el frontend** |
| `controller/contract-bath-new-assign.php` | INSERT + UPDATE | SQL injection |
| `controller/contract-bath-notassign.php` | DELETE + UPDATE + UPDATE condicional en `contratos` | ya tenía prepared statements (sesión previa) |

Los dos controllers huérfanos (`bath-get.php`, `contract-bath-assign.php`) se migraron igual — quedaban con SQL injection activa y expuestos por URL directa aunque el frontend no los enlace. Queda pendiente decidir si se eliminan (ver nota en memoria `project_ddd_migration_state`).

**Vistas (3 de 6 tocadas):**

- `dash-bathrooms.php` — reemplaza `SELECT COUNT(*)` + `SELECT *` inline por `ListBathrooms`
- `dash-bathrooms-edit.php` — tenía SQL injection vía `$_GET['id_Bath']` **y** XSS (los 3 campos del form se imprimían sin `htmlspecialchars()`); se corrigieron ambos
- `dash-bathrooms-contracts.php` — sin input externo (no inyectable), se agregó `htmlspecialchars()` a 4 columnas que no lo tenían

**Fuera de alcance (documentado, no tocado):**

- `dash-bathrooms-contracts-status.php` — sin input externo, ya tenía `htmlspecialchars()`. Su SQL cruza fuerte con `contratos`/`clientes`; se revisita cuando se migre Contracts.
- `dash-bathrooms-add.php` — sin SQL propio, solo el formulario.
- `dash-services-bath.php`, `controller/service-bath-add.php`, `controller/service-bath-remove.php` — pertenecen al dominio **Services** (tocan `servicios_bathrooms`), no migrado todavía. **Siguen con SQL injection activa** (`service-bath-add.php` vía `$_POST` en loop, `service-bath-remove.php` vía `$_GET`) — quedan para cuando se migre Services, según el orden del plan.

## Archivos nuevos

```
app/src/
├── Domain/Bathroom/
│   ├── Bathroom.php                              — Entity (id, codigoBath, fechaCompraBath, observacionBath, estadoBath, asignadoBath)
│   └── BathroomRepositoryInterface.php
├── Application/Bathroom/
│   ├── CreateBathroom.php                        — valida código único, inserta
│   ├── UpdateBathroom.php                        — valida código único (excluyendo el propio id), actualiza
│   ├── DeleteBathroom.php
│   ├── SetBathroomEstado.php                     — usado por bath-active.php (1) y bath-inactive.php (0)
│   ├── SetBathroomAssigned.php                   — usado por bath-notassign.php (0) y contract-bath-assign.php (1)
│   ├── FindBathroom.php                          — usado por bath-get.php y dash-bathrooms-edit.php
│   ├── ListBathrooms.php                         — usado por dash-bathrooms.php
│   ├── AssignBathroomToContract.php               — INSERT contrato_bathroom + marca asignado
│   └── UnassignBathroomFromContract.php           — DELETE contrato_bathroom + marca disponible + cierra el contrato si queda sin baños
└── Infrastructure/Persistence/
    └── MysqliBathroomRepository.php               — único archivo con SQL de bathrooms, 100% prepared statements
```

`SetBathroomEstado` y `SetBathroomAssigned` unifican 4 controllers casi idénticos (mismo query, distinto literal) en 2 Use Cases parametrizados — mejora DRY sobre el código original.

## Decisiones de diseño

1. **`BathroomRepositoryInterface` cruza al dominio Contract** con `closeContract(int $idContrato)` — replica exactamente la lógica ya existente en `contract-bath-notassign.php` (si el contrato se queda sin baños asignados, pasa a estado "Terminado"). Mismo criterio que el piloto Certificates (que ya leía `clientes`/`contratos` desde su Repository): no se crea un `ContractRepository` completo solo para esta escritura puntual, se revisita cuando se migre Contracts.
2. **Bug de datos preexistente, documentado y NO corregido** (preserva comportamiento): `bath-notassign.php` (botón "Deshacer la asignación" desde el listado de Baños) solo pone `asignado_Bath = 0`, sin borrar la fila de `contrato_bathroom` — a diferencia de `contract-bath-notassign.php` (botón desde el detalle del contrato) que sí borra la relación. Si se usa el primero, el baño queda visualmente "Disponible" pero sigue technically vinculado a un contrato en la tabla de relación. Es un bug del código original, no introducido por esta migración.
3. **`contract-bath-assign.php` es código huérfano** — no lo enlaza ninguna vista (verificado con grep sobre todo `app/public`). Solo hace `asignado_Bath = 1` sin tocar `contrato_bathroom`, a diferencia de `contract-bath-new-assign.php` que sí es el flujo real usado por el modal de asignación. Se migró por seguridad (SQL injection expuesta por URL directa) pero no se investigó su propósito original.

## Bugs encontrados durante la implementación

Ninguno nuevo esta vez — los 3 gotchas de infraestructura ya documentados en `checklist-ddd-piloto-certificates.md` (autoload PSR-4, `bind_param` con propiedades `readonly`, bind mount de `composer.json`) no volvieron a aparecer porque no hizo falta tocar `composer.json` ni Docker en esta pasada.

## Verificación en navegador real

- [x] `php -l` sobre los 10 controllers + 3 vistas tocadas + 11 archivos nuevos — sin errores
- [x] Listado (`dash-bathrooms.php`): 100 baños, filtros de estado/asignación intactos
- [x] Crear baño nuevo (`TEST-DDD-001`) — contador sube a 101, aparece "Disponible"
- [x] Editar el baño (observación) — el cambio se refleja en el listado
- [x] Inactivar / Activar — estado cambia correctamente en ambas direcciones
- [x] Asignar a contrato (modal desde `dash-contracts-item.php`, contrato #155 APIA SPA) — aparece en la tabla de baños del contrato como "Asignado" (id_Relacion 414)
- [x] Desasignar desde el contrato — desaparece de la tabla del contrato, vuelve a "Disponible" en el listado general
- [x] Eliminar el baño de prueba — contador vuelve a 100
- [x] `dash-bathrooms-contracts.php` (Histórico) y `dash-bathrooms-contracts-status.php` (Baños & Contratos) cargan sin errores, datos consistentes
- [x] `docker-compose logs php` sin errores/warnings nuevos durante toda la sesión de pruebas

**No verificado en navegador** (mismo método de Repository ya probado por otro camino, ver Decisiones de diseño): el botón "Deshacer la asignación" (`bath-notassign.php`) desde el listado — usa `SetBathroomAssigned` con el mismo `setAsignado()` ya ejercitado por `assignToContract()`. El cierre automático de contrato al quedarse sin baños (`closeContract`) tampoco se disparó en las pruebas porque el contrato usado seguía teniendo otro baño asignado (AT026).

## Pendiente / fuera de alcance de esta migración

- Users, Customers, Contracts, Services, Invoices — siguen en el orden del plan.
- `service-bath-add.php` / `service-bath-remove.php` — SQL injection activa, pertenece a Services (dominio no migrado).
- Decidir si se eliminan `bath-get.php` y `contract-bath-assign.php` (huérfanos) o se dejan migrados por si acaso.
- El bug de `bath-notassign.php` (no limpia `contrato_bathroom`) — documentado, no corregido, requiere decisión del usuario si se quiere alinear con `contract-bath-notassign.php`.

---

## Reglas respetadas (de `CLAUDE.md`)

- SQL 100% prepared statements en el Repository nuevo — elimina 8 SQL injections activas (todos los controllers salvo los 2 que ya venían corregidos de sesiones previas)
- `htmlspecialchars()` agregado en las vistas reescritas que lo necesitaban
- Sin framework, sin abstracciones DDD de más — mismo criterio que el piloto
- No se commiteó nada sin pedido explícito
