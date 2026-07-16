# Plan — Drawer Nuevo/Editar Servicio con checklist de baños

**Origen:** corrección al plan anterior (`plan-services-rebranding.md`), que dejó fuera el action de los botones "Agregar Nuevo Servicio" y "Editar". Referencia de diseño: `rebranding/src/components/ServiciosView.tsx`, bloque `{/* Slide Drawer: LOG ROUTE VISIT (Checklist based) */}`.
**Patrón de referencia en producción:** `app/public/dash-contracts.php` (`$drawerMode`, `.app-drawer`, `closeDrawerUrl`, `baseQueryString()`, animación translateX, toast flash) — **patrón canónico a replicar** para el drawer de Servicios.
**Decisión confirmada con Edgardo:** la funcionalidad de `dash-services-bath.php` ("Asignar Baños a Servicios") se absorbe dentro del mismo drawer de Nuevo/Editar Servicio. La página separada, su entrada en el dropdown y los controllers asociados se retiran.
**Estado:** 🔴 planificado — nada implementado todavía.

---

## 0. Contrato congelado

### 0.1 UX

- "Agregar Nuevo Servicio" (toolbar de `dash-services.php`) deja de enlazar a `dash-services-add.php` y pasa a `?action=new` (drawer inline, mismo patrón que Contratos).
- "Editar" (los 2 links de la card + el ícono de la tabla) deja de enlazar a `dash-services-edit.php?id_Servicio=X` y pasa a `?action=edit&id_Servicio=X`.
- El drawer (`#service-drawer`) sirve ambos modos con un flag `$isEdit`, igual que `#contract-drawer` en `dash-contracts.php`.
- Campos del drawer: Cliente (select) → Contrato (select, cascade) → **checklist único de baños del contrato** (`#service-bath-checklist`, checkbox `name="id_Bath[]"` + checkbox "TODOS LOS BAÑOS") → Tipo de Servicios (9 checkboxes existentes) → Fecha → Observaciones.
- **Modo Nuevo:** Contrato se elige en el form (cascade `id_Cliente → id_Contrato` ya existente vía `SelectEnhanced.cascade` + `controller/obtener_contratos.php`). El checklist de baños arranca vacío ("Seleccione un contrato...") y se puebla vía fetch a un endpoint nuevo `controller/obtener_banos_contrato.php` cuando cambia `id_Contrato`.
- **Modo Editar:** Contrato es fijo y de solo lectura (igual que hoy). El checklist se renderiza server-side directo (sin AJAX) vía `ListBathroomsByContract`, con `checked` en los baños que ya están en `ListAssignedBathrooms`.
- Al enviar (Nuevo o Editar), el backend hace **sync** del set de baños (reemplaza completo: DELETE + INSERT), no solo INSERT aditivo — evita duplicados si se reenvía un baño ya asignado.
- `controller/service-new.php` y `controller/service-update.php` dejan de redirigir a `dash-services-bath.php` y redirigen a `dash-services.php?flash=success&msg=...` (mismo patrón de toast que `dash-contracts.php:601-625`).

### 0.2 Retiro de `dash-services-bath.php`

Una vez el drawer cubra el caso:
- Quitar el `<li>` "Asignar Baños a Servicios" del dropdown de `#servicios-table` (línea ~286 de `dash-services.php`).
- Grep de referencias a `dash-services-bath.php`, `service-bath-add.php`, `service-bath-remove.php`, `AssignBathroomsToService`, `RemoveAssignedBathroom` en todo `app/` (excluyendo `archive/`) antes de borrar nada.
- Si no quedan referencias: borrar `dash-services-bath.php`, `controller/service-bath-add.php`, `controller/service-bath-remove.php`, y los Use Cases `AssignBathroomsToService`/`RemoveAssignedBathroom` si quedan huérfanos (mantener `ListAssignedBathrooms` — se sigue usando para prellenar el checklist en modo Editar).

### 0.3 Backend nuevo/modificado

- `ServiceRepositoryInterface`: agregar `syncBathrooms(int $idServicio, array $bathIds): void`.
- `MysqliServiceRepository::syncBathrooms()`: transacción — `DELETE FROM servicios_bathrooms WHERE id_Servicio = ?` + INSERT de cada `bathId` (dedupe con `array_unique` + cast `(int)`).
- Use Case nuevo `App\Application\Service\SyncBathroomsToService` (análogo a `AssignBathroomsToService`).
- Endpoint nuevo `controller/obtener_banos_contrato.php`: recibe POST `id_Contrato`, devuelve el fragmento HTML de checkboxes (mismo estilo que `obtener_contratos.php`, pero usando `ListBathroomsByContract` + `MysqliBathroomRepository` — código nuevo, no SQL raw).

### 0.4 IDs/clases DOM

```
#service-drawer                   drawer que sirve modo new y edit
#service-bath-checklist           contenedor del checklist de baños (se repuebla vía fetch en modo new)
id_Cliente_service / id_Contrato_service   selects del drawer (evitar colisión de id con otras vistas)
```

---

## Fase 1 (Sonnet 5) — Backend

- `app/src/Domain/Service/ServiceRepositoryInterface.php`: agregar `syncBathrooms(int $idServicio, array $bathIds): void`.
- `app/src/Infrastructure/Persistence/MysqliServiceRepository.php`: implementar `syncBathrooms()` (transacción DELETE+INSERT, ver §0.3).
- Crear `app/src/Application/Service/SyncBathroomsToService.php` (análogo a `AssignBathroomsToService.php`).
- Crear `app/public/controller/obtener_banos_contrato.php` (ver §0.3, endpoint POST `id_Contrato` → HTML de checkboxes vía `ListBathroomsByContract`).
- Extender `app/public/controller/service-new.php`: tras `CreateService`, llamar `SyncBathroomsToService` con `$_POST['id_Bath'] ?? []`; cambiar redirect a `../dash-services.php?flash=success&msg=...` (dejar de usar `dash-services-bath.php`).
- Extender `app/public/controller/service-update.php`: igual, sync + redirect a `../dash-services.php?flash=success&msg=...`.
- `docker-compose exec php composer dump-autoload` (clase nueva) + `docker-compose restart php`.

## Fase 2 (GLM-5.2) — Drawer "Nuevo Servicio"

- En `app/public/dash-services.php`: agregar manejo de `$_GET['action']` (`new`/`edit`) + `$closeDrawerUrl` + `baseQueryString()` (calco de `dash-contracts.php:49-93`, adaptado a `id_Servicio`).
- Construir `#service-drawer` en modo `new`: Cliente (`ListCustomers`) → Contrato (select vacío + cascade JS existente a `obtener_contratos.php`, calco de `dash-services-add.php:114-122`) → `#service-bath-checklist` (vacío, poblado por fetch a `obtener_banos_contrato.php` al cambiar contrato, con checkbox "TODOS LOS BAÑOS" calco de `dash-services-bath.php:127-146`) → Tipo de Servicios (9 checkboxes, calco de `dash-services-add.php:64-86`) → Fecha → Observaciones → submit a `controller/service-new.php`.
- Cambiar el botón "Agregar Nuevo Servicio" (línea ~141) de `href="dash-services-add.php"` a `href="?action=new"`.
- Agregar el script de animación de drawer (translateX, backdrop click, Escape) calco de `dash-contracts.php:575-593`.

## Fase 3 (MiniMax-M3) — Drawer "Editar Servicio"

- Extender `#service-drawer` con el modo `edit` (flag `$isEdit`): cargar servicio (`FindServiceWithContractAndCustomer`), baños del contrato (`ListBathroomsByContract`) y asignados (`ListAssignedBathrooms`) para marcar `checked` en el checklist — sin AJAX, contrato fijo.
- Cliente/Contrato solo lectura (calco de `dash-services-edit.php:63-71`).
- Tipo de Servicios / Fecha / Observaciones prefilled (calco de `dash-services-edit.php:74-104`).
- Submit a `controller/service-update.php`.
- Cambiar los 3 links "Editar" existentes (línea ~183, ~262, ~278 de `dash-services.php`) de `dash-services-edit.php?id_Servicio=X` a `?action=edit&id_Servicio=X`.

## Fase 4 (Sonnet 5) — Retirar `dash-services-bath.php`

- Quitar el `<li>` "Asignar Baños a Servicios" del dropdown (línea ~286).
- Grep de referencias (ver §0.2) antes de borrar.
- Borrar `dash-services-bath.php`, `controller/service-bath-add.php`, `controller/service-bath-remove.php`, y Use Cases huérfanos (`AssignBathroomsToService`, `RemoveAssignedBathroom` si nadie más los usa) — mantener `ListAssignedBathrooms`.
- `composer dump-autoload` tras borrar clases + `docker-compose restart php`.

## Fase 5 (GLM-5.2) — QA manual

1. "Agregar Nuevo Servicio" abre drawer (no navega de página).
2. Elegir cliente → carga contratos; elegir contrato → carga checklist real de baños de ESE contrato.
3. Tildar 2-3 baños + completar el resto → crear → el servicio queda creado con esos baños asignados (verificar reabriendo el edit).
4. "Editar" de un servicio existente abre drawer con datos prefilled y baños ya asignados tildados.
5. Destildar uno + tildar otro nuevo → guardar → el sync reemplaza el set (el destildado ya no aparece, el nuevo sí).
6. Confirmar que no queda ninguna referencia visible a "Asignar Baños a Servicios" ni acceso a `dash-services-bath.php`.
7. Cerrar el drawer (X, backdrop, Escape) sin efectos secundarios.
8. Responsive del drawer en mobile.
