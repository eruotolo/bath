# Checklist — Plan DDD / Entidad: Customers (+ Contacts)

**Origen:** `.doc/Viejos/plan-migracion-ddd-drycode.md` (sección 4, ejemplo completo de referencia; sección 6, entidad #4)
**Estado:** ✅ implementado y verificado (2026-07-09)

---

## Alcance

A diferencia de Certificates/Bathrooms/Users, el plan ya trae un ejemplo de código casi completo para `Customer` (Entity, Repository interface, `MysqliCustomerRepository`, `CreateCustomer`, y los controllers). Se siguió esa plantilla, verificando contra el schema real (coincide exactamente) y ajustando 2 detalles que el ejemplo simplificaba de más frente al comportamiento real:

1. El ejemplo del plan no incluye `estado_Cliente` en el UPDATE. El código real sí lo hacía (siempre fuerza `estado_Cliente = 1`) — y es **el único mecanismo existente en la app para reactivar un cliente inactivo** (no hay `customer-active.php`). Se preservó ese comportamiento.
2. El ejemplo del plan no cubre el listado (`dash-customers.php`) ni el detalle (`dash-customers-item.php`), que sí tenían SQL injection y XSS reales — se migraron también.

**Hallazgo de alcance no anticipado por el plan**: `dash-customers-item.php` embebe un CRUD completo de **Contactos** (tabla `contactos`, FK a `clientes`), con 4 controllers propios (`contact-new.php`, `contact-update.php`, `contact-remove.php`, `contact-get.php`), todos con SQL injection. El plan no lista "Contacts" como entidad separada — es un sub-dominio de Customer (no tiene vista propia, vive enteramente dentro de la página de detalle del cliente) y se migró en la misma pasada con su propio Domain/Application/Infrastructure, mismo criterio que se usó con la relación baño↔contrato en Bathrooms.

**Controllers Customer (4):**

| Archivo | Operación | Estado antes |
|---|---|---|
| `controller/customer-new.php` | INSERT | SQL injection |
| `controller/customer-update.php` | UPDATE (reactiva siempre) | SQL injection |
| `controller/customer-inactive.php` | UPDATE estado=0 | SQL injection |
| `controller/customer-get.php` | SELECT (JSON, usado por `ModalEditor`) | SQL injection |

**Controllers Contact (4):**

| Archivo | Operación | Estado antes |
|---|---|---|
| `controller/contact-new.php` | INSERT | SQL injection |
| `controller/contact-update.php` | UPDATE | SQL injection |
| `controller/contact-remove.php` | DELETE | SQL injection |
| `controller/contact-get.php` | SELECT (JSON) | SQL injection |

**Vistas:**

- `dash-customers.php` — reemplaza `SELECT COUNT(*)` (sin filtro) + `SELECT * WHERE estado_Cliente=1` por `ListCustomers`; agrega `htmlspecialchars()` en 4 columnas
- `dash-customers-item.php` — tenía SQL injection vía `$_GET['id_Cliente']` en **dos** queries (cliente y contactos); se reescribió con `FindCustomer` + `ListContactsByCustomer`, se agregó `htmlspecialchars()` en 11 campos
- `dash-customers-add.php` — sin SQL, no se tocó

## Bug encontrado durante la implementación (no en el plan)

**`layouts/modal-edit-customer.php`... no, `layouts/modal-nuevo-contacto.php` quedó roto tras el primer refactor de `dash-customers-item.php`.** Ese modal (incluido desde dentro de `dash-customers-item.php`) tenía un campo oculto `value="<?php echo $row['id_Cliente'] ?>"` que dependía de la variable `$row` del código viejo (el array del `while` original). Al reemplazar `$row` por el objeto `$customer`, el campo oculto quedó vacío/con `undefined variable` — el formulario de "Agregar Nuevo Contacto" mandaba `id_Cliente` vacío, y el INSERT fallaba por la FK a `clientes`. Se corrigió cambiando la referencia a `$customer->id`. Se revisaron los otros 3 modales incluidos en la misma vista (`modal-edit-customer.php`, `modal-editar-contacto.php`, `modal-ver-contacto.php`) — ninguno tenía la misma dependencia rota.

**Lección para las próximas entidades**: al reemplazar una variable de vista (`$row` → objeto tipado) hay que grepear los `layouts/modal-*.php` incluidos desde esa vista, no solo el archivo principal — pueden depender de la misma variable sin que se note en una lectura rápida del archivo padre.

## Archivos nuevos

```
app/src/
├── Domain/Customer/
│   ├── Customer.php                               — Entity (id, rut, name, phone, email, address, region, city, commune, active)
│   └── CustomerRepositoryInterface.php
├── Domain/Contact/
│   ├── Contact.php                                — Entity (id, customerId, name, lastname, rut, phone, address, observation, state)
│   └── ContactRepositoryInterface.php
├── Application/Customer/
│   ├── CreateCustomer.php
│   ├── UpdateCustomer.php                         — fuerza active=true (preserva el mecanismo de reactivación)
│   ├── DeactivateCustomer.php
│   ├── FindCustomer.php
│   └── ListCustomers.php
├── Application/Contact/
│   ├── CreateContact.php
│   ├── UpdateContact.php
│   ├── DeleteContact.php
│   ├── FindContact.php
│   └── ListContactsByCustomer.php
└── Infrastructure/Persistence/
    ├── MysqliCustomerRepository.php                — único archivo con SQL de clientes
    └── MysqliContactRepository.php                 — único archivo con SQL de contactos
```

## Verificación en navegador real

- [x] `php -l` sobre los 8 controllers + 2 vistas tocadas + 14 archivos nuevos — sin errores
- [x] Listado (`dash-customers.php`): 89→90 tras crear, filtros/buscador intactos
- [x] Crear cliente de prueba, ver detalle (`dash-customers-item.php`) — datos correctos
- [x] Crear contacto — falló la primera vez por el bug del modal descrito arriba; corregido y reverificado, funciona (probado también end-to-end vía `curl` directo al controller para descartar problemas de automatización del navegador)
- [x] Editar contacto (agregar observación vía modal `ModalEditor` + `contact-get.php`) — confirmado que persiste
- [x] Eliminar los 3 contactos de prueba — uno por uno, la tabla se actualiza correctamente
- [x] Editar cliente (`customer-get.php` precarga + `customer-update.php` guarda) — confirmado
- [x] Inactivar cliente de prueba — desaparece del listado activo (contador de cabecera no baja, preserva el comportamiento original de contar sin filtro de estado)
- [x] `docker-compose logs php` — sin errores nuevos después del fix del modal

## Pendiente / fuera de alcance

- Contracts, Services, Invoices — siguen en el orden del plan.
- No hay forma de reactivar un cliente salvo editarlo (mismo comportamiento que antes, documentado, no es un bug introducido).

---

## Reglas respetadas (de `CLAUDE.md`)

- SQL 100% prepared statements — elimina 8 SQL injections activas (4 Customer + 4 Contact)
- `htmlspecialchars()` agregado en las vistas reescritas
- Se siguió la plantilla del propio plan para Customer, ajustando solo lo que divergía del comportamiento real verificado
- No se commiteó nada sin pedido explícito
