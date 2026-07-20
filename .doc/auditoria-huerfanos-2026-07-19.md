**Estado:** ✅ EJECUTADO — 28 archivos movidos a `app/huerfanos/` el 2026-07-19. Ver §6.

# Auditoría completa de archivos huérfanos — 2026-07-19

**Reemplaza a `.doc/orfanatos-pendientes.md`** (confirmado desactualizado por Edgardo — contiene al menos una afirmación de migración que nunca se hizo en el código real, ver §3). Ese archivo se deja como histórico, no se borra.

**Método:** para cada archivo de `app/public/` (raíz, `controller/`, `layouts/`, `cron/`) y cada clase de `app/src/`, se grepeó todo el árbol de `public/` + `src/` (excluyendo `archive/`, `template/`, `vendor/`) buscando toda referencia real: includes, `header('Location: ...')`, `<a href>`, `<form action>`, llamadas JS, `use App\...`/instanciación de clases. No se confió en el doc anterior — se re-verificó cada claim contra el código actual.

**Convención de estados** (igual que el doc anterior): 🔴 Huérfano (sin ningún referenciador) · 🟡 Semi-huérfano (solo lo usan otros huérfanos) · 🟢 Vivo / pendiente de decisión (no tocar).

---

## 1. Correcciones sobre el doc anterior (ya no son válidas tal como estaban escritas)

### 🔴→🟢 `dash-services-print.php` — el doc viejo decía "reemplazada por lightbox", es falso

`git log -p` sobre `dash-services.php` confirma que el `href` de los 2 botones "Imprimir" (línea ~309 vista tarjetas, ~396 dropdown de tabla) **siempre** apuntó a `dash-services-print.php?id_Servicio=X` — nunca se cambió a `controller/service-pdf.php`. **`dash-services-print.php` sigue activo, no es huérfano.**

### 🟢→🟡 `controller/service-pdf.php` — construido pero nunca conectado

Es la contracara del punto anterior: el controller moderno (usa `FindServiceForPrint` + `MysqliServiceRepository`, TCPDF con `setPrintHeader(false)`, igual patrón que `certificate-pdf.php`) existe completo y funcional, pero **ningún botón de la UI lo llama**. Solo es alcanzable por:
- URL directa, o
- el shim `servicio-pdf.php` (que ya redirige ahí por diseño, ver `.doc/orfanatos-pendientes.md` cluster "servicio-pdf.php").

**No es código muerto en el sentido de "nadie lo necesita"** — es trabajo terminado que quedó sin cablear a los 2 botones de `dash-services.php`. Requiere una decisión tuya, no un movimiento automático (ver §4).

---

## 2. Huérfanos ya conocidos, re-confirmados sin cambios

Estos siguen exactamente como los describía `orfanatos-pendientes.md` — re-verificados, sin novedad:

| Archivo | Motivo |
|---|---|
| 🔴 `dash-contracts-add.php`, `dash-contracts-edit.php`, `dash-contracts-item.php` | Reemplazadas por drawers en `dash-contracts.php` |
| 🟡 `layouts/modal-nuevo-assign-bath.php` | Cascada de `dash-contracts-item.php` |
| 🔴 `dash-bathrooms-add.php`, `dash-bathrooms-edit.php` | Reemplazadas por drawers en `dash-bathrooms.php` |
| 🔴 `controller/bath-new.php`, `controller/bath-update.php` | Solo los llaman las 2 páginas huérfanas de arriba (el drawer real usa `bath-create-drawer.php`/`bath-edit-drawer.php`) |
| 🔴 `dash-services-add.php`, `dash-services-edit.php` | Reemplazadas por drawer en `dash-services.php` |
| 🔴 `dash-certificates-add.php`, `dash-certificates-item.php` | Reemplazadas por drawer + lightbox en `dash-certificates.php` (esta migración sí se hizo de verdad, confirmado) |
| 🔴 `dash-users-add.php`, `dash-users-edit.php` | Reemplazadas por drawer en `dash-users-list.php` |
| 🔴 `controller/user-setadmin.php` | Sin llamador desde la UI. Ya parchado el bypass de seguridad (2026-07-18) — no se toca su lógica, solo candidato a mover |
| 🔴 `dash-invoices-detail.php` | Reemplazada por sección "Servicios de la Factura" en el drawer editar de `dash-invoices-list.php` |
| 🔴 `auth-recoverpw.php`, `auth-register.php` | Nunca tuvieron link desde `auth-login.php`. **Decisión previa de Edgardo (2026-07-16): no tocar.** Sigo respetándola — no incluir en el lote de esta limpieza salvo que confirmes lo contrario |
| 🟢 `dash-customers-item.php` + sus 4 modales + `controller/customer-update.php`, `customer-get.php`, `contact-get.php`, `contact-new.php`, `contact-update.php`, `contact-remove.php` | Sigue siendo la única UI para CRUD de contactos. **No tocar** |
| 🟢 `controller/servicio-pdf.php` | Shim de seguridad intencional (27 líneas), no error — dejar como está |

---

## 3. Huérfanos nuevos — no estaban en ningún documento anterior

### Cluster "Carga de Facturas" (legacy de 3 páginas completas)

`dash-invoices-list.php` ya tiene un drawer inline (`?action=upload`) que postea a `controller/invoice-upload-parse.php` y muestra el preview con un modal que se auto-abre leyendo `$_SESSION['carga_facturas']` (línea 70). El flujo viejo de 3 páginas quedó completamente desconectado:

- 🔴 `dash-invoices-upload.php` — formulario de carga viejo
- 🔴 `dash-invoices-upload-preview.php` — preview viejo
- 🔴 `dash-invoices-upload-result.php` — resultado viejo

**Bug de redirect muerto (mismo patrón que `service-new.php:21`, ya documentado y no corregido a pedido de Edgardo en ese caso):** `controller/invoice-upload-confirm.php:18` redirige a `dash-invoices-upload.php` si `$_SESSION['carga_facturas']` no existe (sesión vencida o acceso directo a la URL de confirmación). Si se mueve el archivo, ese caso da 404 en vez de mostrar el formulario. **No corregido, dejado para tu decisión igual que el caso análogo de servicios.**

### Cluster "Factura individual" (páginas nunca usadas por el flujo actual)

- 🔴 `dash-invoices-add.php` — sin ningún link real, solo referenciada en `header.php`/`sidebar.php` (arrays de config, no navegación)
- 🔴 `dash-invoices-edit.php` — misma situación

### `dash-customers-add.php` — mismo patrón que Servicios/Baños, nunca documentado

- 🔴 `dash-customers-add.php` — reemplazada por el drawer de `dash-customers.php` (`customer-create-drawer.php`). **Bug de redirect muerto:** `controller/customer-new.php:35,39` sigue redirigiendo ahí en los 2 casos de error (código duplicado / fallo genérico).

### `controller/contract-down.php` — acción de "tercer estado" nunca cableada

`dash-contracts.php` solo usa 2 acciones de estado en su dropdown: "Activar" (`contract-active.php`, estado 2) y "Terminar" (`contract-inactive.php`). `contract-down.php` (estado 0) no tiene ningún botón que lo llame en ningún lado — 🔴 huérfano. La clase `SetContractState` que usa **no** es huérfana (la comparte con `contract-active.php`, que sí está vivo).

### `controller/customer-inactive.php` — no existe botón de inactivar cliente

`dash-customers.php` no tiene dropdown de acciones ni botón de inactivar/eliminar cliente en absoluto (a diferencia de baños, contratos, servicios, facturas, usuarios). 🔴 huérfano.
- 🟡 `src/Application/Customer/DeactivateCustomer.php` — semi-huérfano, cascada de `customer-inactive.php` (único llamador).

### Clases de `app/src/Application/` sin ningún consumidor

- 🔴 `src/Application/Bathroom/ListBathrooms.php` — ningún controller/vista lo instancia (los 3 listados de baños usan `ListBathroomsWithAssignment`, `ListAvailableBathrooms` o `ListBathroomsByContract`)
- 🔴 `src/Application/Contract/ListContractsExpiringSoon.php` — funcionalidad "contratos por vencer" que nunca se conectó a ninguna vista
- 🔴 `src/Application/Service/FindService.php` — ningún controller/vista lo instancia (usan `FindServiceForPrint` o `FindServiceWithContractAndCustomer`)

---

## 4. No son huérfanos — no mover, requieren decisión o son configuración pendiente

- **`controller/service-pdf.php`** (ver §1): trabajo terminado, sin cablear. Antes de decidir si se mueve, definir: ¿conectamos los 2 botones "Imprimir" de `dash-services.php` al lightbox nuevo (como se creía ya hecho), o se descarta este controller y se queda con la página completa vieja? Necesito tu decisión — no lo voy a mover a ciegas porque representa funcionalidad real, no deuda.
- **`app/public/cron/purge-logs-actividad.php`** — **no es huérfano de código**, es un script vivo y funcional (política de retención de `logs_actividad`, 12 meses) que nunca fue agendado en ningún cron real — ni en `docker-compose.yml`, ni en el crontab del contenedor (verificado, solo tiene las entradas default de Alpine). Por diseño propio del script (comentario en el archivo) se pensó para agendarlo en el cron externo del hosting (cPanel), no en Docker. Es una tarea de configuración pendiente, no candidato a `app/huerfanos`.

---

## 5. Resumen — listo para mover (pendiente tu confirmación)

### Páginas / archivos completos (`app/public/`)
```
dash-contracts-add.php
dash-contracts-edit.php
dash-contracts-item.php
layouts/modal-nuevo-assign-bath.php
dash-bathrooms-add.php
dash-bathrooms-edit.php
controller/bath-new.php
controller/bath-update.php
dash-services-add.php
dash-services-edit.php
dash-certificates-add.php
dash-certificates-item.php
dash-users-add.php
dash-users-edit.php
controller/user-setadmin.php
dash-invoices-detail.php
dash-invoices-upload.php
dash-invoices-upload-preview.php
dash-invoices-upload-result.php
dash-invoices-add.php
dash-invoices-edit.php
dash-customers-add.php
controller/contract-down.php
controller/customer-inactive.php
```

### Clases (`app/src/`)
```
src/Application/Bathroom/ListBathrooms.php
src/Application/Contract/ListContractsExpiringSoon.php
src/Application/Service/FindService.php
src/Application/Customer/DeactivateCustomer.php   (semi-huérfano, cascada de customer-inactive.php)
```

**Excluidos de este lote a propósito** (respetando decisiones tuyas previas o pendientes de una decisión nueva): `auth-recoverpw.php`, `auth-register.php`, `dash-services-print.php` / `controller/service-pdf.php`.

## 6. Ejecución (2026-07-19)

A pedido explícito de Edgardo ("mover todo lo que no se usa a huerfanos, ya"), se ejecutó el lote completo de §5 más `public/template/template.php` (carpeta `template/` completa, único archivo, 0 referencias). Se excluyeron a propósito `auth-recoverpw.php`, `auth-register.php` (decisión previa de Edgardo de no tocarlas) y el par `dash-services-print.php` / `controller/service-pdf.php` (§4 — `service-pdf.php` tiene un llamador real vía el shim `servicio-pdf.php`, no calza con "no se usa").

**Hallazgo adicional durante la ejecución:** `src/Application/User/ToggleUserAdmin.php` es semi-huérfano en cascada de `controller/user-setadmin.php` (único llamador) — no estaba en la lista de §5, se agregó al lote.

**28 archivos movidos** con `git mv` a `app/huerfanos/` (misma estructura relativa: `public/`, `public/controller/`, `public/layouts/`, `public/template/`, `src/Application/...`).

**3 redirects corregidos** (decisión tomada sin volver a preguntar, dado el pedido de ejecutar ya — dejar estos redirects rotos habría convertido un caso de error manejado en un 404):
- `controller/service-new.php:37` → ahora `dash-services.php?action=new&err=...` (antes apuntaba a `dash-services-add.php`, movida)
- `controller/customer-new.php:35,39` → ahora `dash-customers.php?action=new&err=...` (antes `dash-customers-add.php`, movida)
- `controller/invoice-upload-confirm.php:18` → ahora `dash-invoices-list.php?action=upload&err=sesion_expirada` (antes `dash-invoices-upload.php`, movida; se agregó el código `sesion_expirada` al mapa `$erroresCarga` de `dash-invoices-list.php`)

**`sidebar.php`/`header.php` limpiados:** se quitaron las entradas de los arrays `match` (sidebar) y las líneas de breadcrumb (header) correspondientes a los 18 archivos de página movidos. Se dejaron intactas las entradas de `dash-services-print.php` y `dash-customers-item.php` (no huérfanas).

**Post-move:** `composer dump-autoload` + `docker-compose restart php`. QA en navegador (DevTools) de las 7 vistas con archivos movidos + los 3 drawers con el redirect corregido — todo sin errores nuevos (el único error de consola presente, `metisMenu is not a function`, es preexistente y no relacionado, confirmado reproduciéndolo en una vista no tocada). `php -l` limpio en los 6 archivos editados. Grep final confirma cero referencias colgantes a los 28 archivos movidos.

## 7. Decisión resuelta: `service-pdf.php` vs `dash-services-print.php` (2026-07-19)

Edgardo decidió: usar el lightbox nuevo. Se cablearon los 2 botones "Imprimir" de `dash-services.php` (vista tarjetas y dropdown de tabla) a `controller/service-pdf.php` con `data-glightbox-preview` (mismo patrón que certificados). `dash-services-print.php` quedó huérfana de verdad y se movió a `app/huerfanos/` (+ limpieza de `sidebar.php`/`header.php`).

Verificado en navegador: el lightbox abre el PDF con logo y firma renderizados correctamente. Un 404 al probar un segundo servicio (`id_Servicio=2809`) es comportamiento esperado y preexistente — `FindServiceForPrint::handle()` devuelve `null` (por diseño, ver docblock) cuando el servicio no tiene ningún baño asignado en `servicios_bathrooms`; la página vieja usaba el mismo use case y tenía la misma restricción, no es una regresión.

**Hallazgo sin resolver, fuera de alcance de esta tarea:** `controller/service-pdf.php` (y también `certificate-pdf.php`, mismo patrón) no tiene ningún chequeo de `require_permission`/`require_login` — solo `session_start()`. No se corrigió porque no fue lo pedido, queda para una revisión de seguridad aparte.
