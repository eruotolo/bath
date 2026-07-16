# Orfanatos pendientes de limpieza

Registro vivo de archivos que quedan huérfanos o semi-huérfanos a medida que avanza el rediseño de la app. La idea es acumular acá y hacer una limpieza por lotes cuando el resto de las páginas involucradas estén migradas al nuevo diseño.

**Convención de estados:**
- 🔴 **Huérfano** — ningún archivo del proyecto lo referencia/usa. Seguro de borrar.
- 🟡 **Semi-huérfano** — referenciado solo por otros archivos que también son candidatos a borrar (cascada).
- 🟢 **Pendiente de decisión** — se usa hoy pero se sabe que va a salir del diseño nuevo.

---

## Cambios aplicados

### 2026-07-14 — Eliminado botón "Ver ficha cliente" (ojo) en `dash-customers.php`

- **Hecho:** se eliminó el `<a>` con ícono `eye` en la columna Acciones del listado de clientes (`app/public/dash-customers.php`, era líneas 224-230).
- **Motivo:** el nuevo diseño ya no usa el botón de visualización en el listado.
- **Quedó:** solo el botón de Editar (`square-pen`) en esa columna.
- Los enlaces del **RUT** (línea 182) y del **Nombre** (línea 187) siguen apuntando a `dash-customers-item.php` — **no se tocaron** porque la ficha sigue existiendo por ahora.

---

## Cluster "ficha de cliente" (candidato a limpieza futura)

Origen: la página `dash-customers-item.php` (ficha de detalle del cliente) va a desaparecer del nuevo diseño. Hoy es la **única UI** para el CRUD de **contactos** del cliente y para la edición de cliente vía modal. Cuando el nuevo diseño cubra ese flujo, todo este cluster queda eliminable.

**No borrar todavía** — esperar a que las páginas nuevas involucren/reemplacen este flujo.

### Página principal
- 🟢 `app/public/dash-customers-item.php` — la ficha. Referenciada desde `dash-customers.php` (RUT, Nombre), `layouts/sidebar.php:36` (match menú), `layouts/header.php:9` (breadcrumb) y 4 controllers que redirigen a ella.

### Modales (solo los incluye `dash-customers-item.php`)
- 🟡 `app/public/layouts/modal-edit-customer.php` → POST a `controller/customer-update.php`
- 🟡 `app/public/layouts/modal-nuevo-contacto.php` → POST a `controller/contact-new.php`
- 🟡 `app/public/layouts/modal-ver-contacto.php`
- 🟡 `app/public/layouts/modal-editar-contacto.php` → POST a `controller/contact-update.php`

### Controllers que redirigen a la ficha (al borrarla, hay que re-apuntarlos o borrarlos)
- 🟡 `app/public/controller/customer-update.php` — editar cliente (nadie más lo llama fuera del modal)
- 🟡 `app/public/controller/customer-get.php` — endpoint AJAX, solo lo usa la ficha
- 🟡 `app/public/controller/contact-get.php` — endpoint AJAX, solo lo usa la ficha
- 🟡 `app/public/controller/contact-new.php` — crear contacto
- 🟡 `app/public/controller/contact-update.php` — editar contacto
- 🟡 `app/public/controller/contact-remove.php` — eliminar contacto

### Otros puntos a tocar al limpiar
- `app/public/layouts/sidebar.php:36` — quitar `'dash-customers-item.php'` del array `match`.
- `app/public/layouts/header.php:9` — quitar entrada del breadcrumb.
- `app/public/dash-customers.php:182` y `:187` — los enlaces de RUT/Nombre (convertir a texto plano o re-apuntar).

---

## Cluster "Obras & Contratos" (huérfano, 2026-07-15)

Origen: `dash-contracts.php` migró sus flujos de crear/editar contrato y gestionar baños a drawers laterales embebidos en la misma página (`?action=new`, `?action=edit&id_Contrato=X`, `?action=manage&id_Contrato=X`). Las 3 páginas legacy de página completa que cumplían esos roles quedaron sin ningún link de navegación que apunte a ellas.

**Seguras de borrar** — a diferencia del cluster de clientes, acá el reemplazo (los drawers) ya está construido, probado y en uso. No hay flujo pendiente que dependa de estas páginas.

### Páginas huérfanas
- 🔴 `app/public/dash-contracts-add.php` — página completa de "Nuevo Contrato" (posteaba a `controller/contract-new.php`, mismo endpoint que ahora usa el drawer `?action=new`). Ya estaba huérfana antes de esta sesión — nadie la enlazaba.
- 🔴 `app/public/dash-contracts-edit.php` — página completa de "Editar Contrato" (posteaba a `controller/contract-update.php`). Reemplazada por el drawer `?action=edit&id_Contrato=X`. Antes la enlazaba el botón lápiz de la tabla.
- 🔴 `app/public/dash-contracts-item.php` — ficha de detalle del contrato con lista de baños asignados + modal para asignar uno nuevo. Reemplazada por el drawer `?action=manage&id_Contrato=X`. Antes la enlazaba "Agregar Baños Químicos" del dropdown de acciones.

### Semi-huérfano (cascada de `dash-contracts-item.php`)
- 🟡 `app/public/layouts/modal-nuevo-assign-bath.php` — modal "Asignar Nuevo Baño al Contrato", solo lo incluye `dash-contracts-item.php`. Postea a `controller/contract-bath-new-assign.php` (**ese controller NO es huérfano** — el drawer de gestión de baños lo sigue usando activamente con un formulario distinto).

### Controllers — no huérfanos, no tocar
`controller/contract-new.php`, `controller/contract-update.php`, `controller/contract-bath-new-assign.php` y `controller/contract-bath-notassign.php` siguen todos en uso activo por los drawers nuevos. Solo las 3 páginas de arriba y el modal quedaron sin uso.

### Otros puntos a tocar al limpiar
- `app/public/layouts/sidebar.php:62` — quitar `'dash-contracts-add.php'`, `'dash-contracts-edit.php'` y `'dash-contracts-item.php'` del array `match`.
- `app/public/layouts/header.php:16-18` — quitar las 3 entradas del breadcrumb (`dash-contracts-add.php`, `dash-contracts-edit.php`, `dash-contracts-item.php`).

---

## Cluster "Baños Químicos" (huérfano, 2026-07-16)

Origen: `dash-bathrooms.php` ya usaba el patrón de drawer (`?action=new`, `?action=edit&id=X`) desde antes de esta sesión — nunca se limpió. Detectado al verificar huérfanos disparado por la migración de Servicios.

**Es la isla más completa de las tres** — a diferencia de Contratos, acá el drawer real postea a controllers *distintos* (`bath-create-drawer.php` / `bath-edit-drawer.php`), así que los controllers viejos también quedaron sin ningún uso. Seguras de borrar (páginas + controllers): el reemplazo ya está construido y en uso.

### Páginas huérfanas
- 🔴 `app/public/dash-bathrooms-add.php` — página completa de "Registrar Baño" (posteaba a `controller/bath-new.php`). Reemplazada por el drawer `?action=new`.
- 🔴 `app/public/dash-bathrooms-edit.php` — página completa de "Editar Baño" (posteaba a `controller/bath-update.php`). Reemplazada por el drawer `?action=edit&id=X`.

### Controllers huérfanos (a diferencia de Contratos, acá SÍ hay que borrarlos)
- 🔴 `app/public/controller/bath-new.php` — solo lo llama `dash-bathrooms-add.php` (huérfana). El drawer real usa `bath-create-drawer.php`.
- 🔴 `app/public/controller/bath-update.php` — solo lo llama `dash-bathrooms-edit.php` (huérfana). El drawer real usa `bath-edit-drawer.php`.

### Otros puntos a tocar al limpiar
- `app/public/layouts/sidebar.php:42` — quitar `'dash-bathrooms-add.php'` y `'dash-bathrooms-edit.php'` del array `match`.
- `app/public/layouts/header.php:11-12` — quitar las 2 entradas del breadcrumb.

---

## Cluster "Servicios" (huérfano, 2026-07-16)

Origen: migración de `dash-services.php` al drawer inline (`?action=new`, `?action=edit&id_Servicio=X`) hecha en esta misma sesión de trabajo. Mismo patrón que Baños/Contratos, detectado apenas terminada la migración.

**Seguro de borrar** — igual que Contratos, el controller (`service-new.php`/`service-update.php`) queda vivo porque lo comparte el drawer nuevo; solo las páginas de página completa quedaron sin uso.

### Páginas huérfanas
- 🔴 `app/public/dash-services-add.php` — página completa de "Nuevo Servicio" (posteaba a `controller/service-new.php`). Reemplazada por el drawer `?action=new`.
- 🔴 `app/public/dash-services-edit.php` — página completa de "Editar Servicio" (posteaba a `controller/service-update.php`). Reemplazada por el drawer `?action=edit&id_Servicio=X`.

### Controllers — no huérfanos, no tocar
`controller/service-new.php` y `controller/service-update.php` siguen en uso activo por el drawer nuevo.

### 🐛 Bug relacionado, no corregido (a pedido de Edgardo, 2026-07-16)
`app/public/controller/service-new.php:21` — si `CreateService` lanza `mysqli_sql_exception`, redirige a `dash-services-add.php?status=error&msg=...`. Como esa página quedó huérfana, un error real de creación manda al usuario a una página sin entrada en vez de devolverlo al drawer con el mensaje. El path de éxito (línea 16→ahora corregido a `dash-services.php?flash=...`) y el de `service-update.php` ya redirigen bien — falta parchear este caso. **Pendiente, no tocar todavía.**

### Otros puntos a tocar al limpiar
- `app/public/layouts/sidebar.php:68` — quitar `'dash-services-add.php'` y `'dash-services-edit.php'` del array `match` (dejar `'dash-services.php'` y `'dash-services-print.php'`, ese último sigue vivo).
- `app/public/layouts/header.php:20-21` — quitar las 2 entradas del breadcrumb.

---

## Huérfanas de otro tipo — nunca conectadas, no son restos de migración (2026-07-16)

A diferencia de los 3 clusters de arriba (páginas que SÍ tuvieron uso y quedaron atrás tras una migración), estas dos parecen no haber tenido nunca un link de entrada desde la UI:

- 🔴 `app/public/auth-recoverpw.php` — cero enlaces desde `auth-login.php` ni de ningún otro lado; no hay "¿Olvidaste tu contraseña?" en el login. Auto-contenida (maneja su propio `$_POST`, sin controller separado). Contiene una inyección SQL real (`:17-18`, interpolación directa sin prepared statement) y un XSS reflejado potencial (`:85`, `htmlentities` sin `ENT_QUOTES`) — ver `.doc/auditoria-web-interface-guidelines-julio-2026.md`. **Decisión de Edgardo (2026-07-16): no tocar por ahora** (ni el bug de seguridad ni conectarla).
- 🔴 `app/public/auth-register.php` — cero enlaces desde `auth-login.php`; no hay "Registrarse". Auto-contenida. Contiene un bug de password reflejado en `value=` tras error de validación (`:122,130`). **Decisión de Edgardo (2026-07-16): no tocar por ahora.**

Ambas siguen siendo accesibles tecleando la URL directo — el "huérfano" es solo de navegación UI, no de disponibilidad real.
