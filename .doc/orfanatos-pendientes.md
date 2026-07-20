> **⚠️ SUPERADO (2026-07-19):** este documento quedó desactualizado — contiene al menos una afirmación de migración (cluster "Impresión de Servicios") que nunca se implementó en el código real. Ver `.doc/auditoria-huerfanos-2026-07-19.md` para el estado verificado y corregido. Se deja este archivo como histórico, no se borra.

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
- `app/public/layouts/sidebar.php:68` — quitar `'dash-services-add.php'` y `'dash-services-edit.php'` del array `match` (dejar `'dash-services.php'`; `'dash-services-print.php'` dejó de estar vivo, ver cluster de abajo).
- `app/public/layouts/header.php:20-21` — quitar las 2 entradas del breadcrumb.

---

## Cluster "Impresión de Servicios" (huérfano, 2026-07-16)

Origen: los 2 botones "Imprimir" de `dash-services.php` (vista tarjetas línea ~266 y dropdown de la tabla línea ~338) apuntaban a `dash-services-print.php?id_Servicio=X`, una página completa de dashboard con un botón que disparaba `window.print()`. Edgardo reportó dos problemas: el print del navegador capturaba su propio header/footer (fecha, URL, página), y tras imprimir/guardar PDF la pestaña quedaba parada en esa página del dashboard, que no debería ser navegable públicamente.

**Reemplazo:** se creó `app/public/controller/service-pdf.php` (mismo patrón que `certificate-pdf.php`/`invoice-pdf.php`: usa el use case ya migrado `FindServiceForPrint` + `MysqliServiceRepository`, genera el PDF con TCPDF con `setPrintHeader(false)`/`setPrintFooter(false)`, `Output(..., 'I')` inline). Los 2 botones ahora apuntan ahí con `data-glightbox-preview data-type="external" data-width="900px" data-height="90vh"` — el PDF se abre en un lightbox sobre `dash-services.php`, sin navegar nunca a otra página. Resuelve ambos problemas: TCPDF controla el layout del PDF (sin header/footer del navegador) y no queda ninguna URL de dashboard cargada.

**Bug de TCPDF encontrado en el camino (no soy yo quien lo introdujo, es preexistente):** `writeHTML()` con `<img src="{ruta absoluta filesystem}">` no renderiza la imagen cuando el script corre vía PHP-FPM (sí funciona por CLI) — probado que **también afecta a `certificate-pdf.php`** (logo y firma vienen en blanco ahí también). En `service-pdf.php` se evitó insertando el logo y la firma con `$pdf->Image()` nativo en vez de embeberlos en el HTML. **`certificate-pdf.php` e `invoice-pdf.php` NO se tocaron** — quedan con el mismo bug latente, fuera del alcance de este cambio.

### Página huérfana
- 🔴 `app/public/dash-services-print.php` — ya no la enlaza ningún botón. Sigue siendo accesible tecleando la URL directo (requiere sesión activa via `layouts/session.php`, igual exposición que siempre tuvo). **Decisión de Edgardo (2026-07-16): no borrar todavía, dejar documentado acá para limpieza por lotes.**

### Otros puntos a tocar al limpiar (cuando se borre)
- `app/public/layouts/sidebar.php:68` — quitar `'dash-services-print.php'` del array `match`.
- `app/public/layouts/header.php:22` — quitar la entrada `'dash-services-print.php' => 'Servicios en Terreno & Ruta'`.

---

## Huérfanas de otro tipo — nunca conectadas, no son restos de migración (2026-07-16)

A diferencia de los 3 clusters de arriba (páginas que SÍ tuvieron uso y quedaron atrás tras una migración), estas dos parecen no haber tenido nunca un link de entrada desde la UI:

- 🔴 `app/public/auth-recoverpw.php` — cero enlaces desde `auth-login.php` ni de ningún otro lado; no hay "¿Olvidaste tu contraseña?" en el login. Auto-contenida (maneja su propio `$_POST`, sin controller separado). Contiene una inyección SQL real (`:17-18`, interpolación directa sin prepared statement) y un XSS reflejado potencial (`:85`, `htmlentities` sin `ENT_QUOTES`) — ver `.doc/auditoria-web-interface-guidelines-julio-2026.md`. **Decisión de Edgardo (2026-07-16): no tocar por ahora** (ni el bug de seguridad ni conectarla).
- 🔴 `app/public/auth-register.php` — cero enlaces desde `auth-login.php`; no hay "Registrarse". Auto-contenida. Contiene un bug de password reflejado en `value=` tras error de validación (`:122,130`). **Decisión de Edgardo (2026-07-16): no tocar por ahora.**

Ambas siguen siendo accesibles tecleando la URL directo — el "huérfano" es solo de navegación UI, no de disponibilidad real.

---

## Cluster "Detalle de Factura" (huérfano, 2026-07-17)

Origen: `dash-invoices-list.php` migró la gestión de servicios asociados a una factura al drawer lateral **"Editar Factura" ya existente** (`?action=edit&id_Factura=X`), agregándole una sección "Servicios de la Factura" (agregar/quitar), en vez de crear una acción `manage` separada. La página legacy full-page `dash-invoices-detail.php` (que mostraba el detalle de factura con layout de "factura impresa" y un modal para asignar servicios) quedó sin ningún link de navegación que apunte a ella tras quitar el item "Agregar Servicios a la Factura" del dropdown de acciones (a pedido de Edgardo, ronda de feedback de diseño del 2026-07-17).

**Nota de corrección:** una versión anterior de esta entrada mencionaba un drawer `?action=manage` — eso no llegó a implementarse así; el drawer real reutiliza `?action=edit`. Se corrige acá para que quede consistente con el código.

**Seguro de borrar** — el reemplazo (sección de servicios dentro del drawer Editar) ya está implementado, probado en navegador (agregar/quitar servicio, orden de la sección) y en uso. Igual que el resto de los clusters, se deja para limpieza por lotes.

### Páginas huérfanas
- 🔴 `app/public/dash-invoices-detail.php` — página completa de detalle de factura con modal "Asignar Servicio". Reemplazada por la sección "Servicios de la Factura" del drawer `?action=edit&id_Factura=X`. Antes la enlazaba el botón "Agregar Servicios a la Factura" del dropdown de acciones de la fila (quitado en `dash-invoices-list.php`).

### Controllers — re-apuntados, no huérfanos
`controller/invoice-new.php` redirige ahora a `dash-invoices-list.php` (plano) tras crear la factura, en vez de a la página detail. `controller/invoice-service-add.php` e `invoice-service-remove.php` siguen aceptando ambos orígenes: si vienen con `origen=edit-factura` (el caso real hoy, desde el drawer) redirigen a `dash-invoices-list.php?action=edit&id_Factura=X`; si no, siguen con su comportamiento original de redirigir a `dash-invoices-detail.php` (rama que quedó sin ningún llamador real tras este cambio, pero no se borró — no se tocó su lógica, solo se le sumó la rama nueva). No borrar estos 3 controllers.

### Otros puntos a tocar al limpiar (todavía NO tocados, a diferencia de lo que decía la versión anterior de esta entrada)
- `app/public/layouts/sidebar.php:76` — sigue con `'dash-invoices-detail.php'` en el array `match` del menú activo. Falta quitarlo.
- `app/public/layouts/header.php:29` — sigue con la entrada del breadcrumb `'dash-invoices-detail.php' => 'Control de Facturación'`. Falta quitarla.
- La rama "no edit-factura" de `invoice-service-add.php`/`invoice-service-remove.php` (redirige a la página huérfana) queda muerta — al borrar la página, evaluar si conviene simplificar esos controllers para que siempre redirijan al drawer.

---

## Cluster "Certificados" (huérfano, 2026-07-18)

Origen: la migración de `dash-certificates.php` al lineamiento de las vistas ya rebrandeadas reemplazó dos páginas de página completa. La creación pasó a un drawer lateral embebido (`?action=new`, mismo endpoint `controller/certificate-new.php`) y la visualización imprimible pasó a un lightbox sobre `controller/certificate-pdf.php` (mismo patrón que `dash-services.php` → `service-pdf.php`).

**Seguro de borrar** — el reemplazo (drawer + lightbox) ya está implementado, probado (curl autenticado + inspección visual del PDF generado) y en uso. Se deja para limpieza por lotes, misma política que los demás clusters.

### Páginas huérfanas
- 🔴 `app/public/dash-certificates-add.php` — página completa de "Nuevo Certificado" (posteaba a `controller/certificate-new.php`, mismo endpoint que ahora usa el drawer `?action=new`).
- 🔴 `app/public/dash-certificates-item.php` — página completa de preview imprimible (layout full-page con CSS print, sin POST propio). Reemplazada por el lightbox sobre `controller/certificate-pdf.php`.

### Controllers — no huérfanos, no tocar
`controller/certificate-new.php` sigue en uso activo por el drawer `?action=new` (mismo endpoint que ya usaba la página legacy). No hay controller análogo para `dash-certificates-item.php` porque la versión full-page solo hacía preview — no tenía POST propio.

### Otros puntos a tocar al limpiar
- `app/public/layouts/sidebar.php:82` — quitar `'dash-certificates-add.php'` y `'dash-certificates-item.php'` del array `match` (dejar solo `'dash-certificates.php'`).
- `app/public/layouts/header.php:31-32` — quitar las 2 entradas del breadcrumb (`dash-certificates-add.php`, `dash-certificates-item.php`).

---

## Cluster "Personal y Roles" (huérfano, 2026-07-18)

Origen: la migración de `dash-users-list.php` al lineamiento de las vistas ya rebrandeadas agregó un drawer lateral (`#user-drawer`, modos `new` + `edit`) que reemplaza las 2 páginas de página completa para crear/editar usuario. Además se retiró la opción "Set Admin/User" del dropdown de acciones de la fila — el rol ahora se asigna desde el selector de `category` dentro del mismo drawer (incluyendo el nuevo `SuperAdministrador`, ver T0 del mismo plan).

**Seguro de borrar** — el reemplazo (drawer) ya está implementado y probado; se deja para limpieza por lotes, igual que los demás clusters.

### Páginas huérfanas
- 🔴 `app/public/dash-users-add.php` — página completa de "Agregar Nuevo Usuario" (posteaba a `controller/user-new.php`, mismo endpoint que ahora usa el drawer `?action=new`).
- 🔴 `app/public/dash-users-edit.php` — página completa de "Editar Usuario" (posteaba a `controller/user-update.php`, mismo endpoint que ahora usa el drawer `?action=edit&id_User=X`).

### Controllers
- No huérfanos — `controller/user-new.php` y `controller/user-update.php` siguen en uso activo por el drawer `?action=new` / `?action=edit&id_User=X` (mismos endpoints que ya usaban las páginas legacy).
- 🔴 `app/public/controller/user-setadmin.php` junto con el use case `App\Application\User\ToggleUserAdmin` (`app/src/Application/User/ToggleUserAdmin.php`) — quedaron sin ningún llamador: la opción "Set Admin/User" se retiró del dropdown de `dash-users-list.php` y el rol ahora se edita completo desde el selector `category` del drawer. **⚠️ Actualización 2026-07-18 (QA de `.doc/plan-roles-permisos.md`):** aunque sin llamador desde la UI, el endpoint seguía siendo un bypass real y explotable por URL directa — un Admin (nivel 2) podía degradar a un SuperAdministrador existente vía `GET user-setadmin.php?id_User=X&category=1`, saltándose la protección "solo SuperAdmin otorga/edita SuperAdmin" que sí tiene `user-update.php`. **Parchado** con el mismo guard `grant_superadmin` (no se eliminó el archivo). Sigue siendo candidato a limpieza por lotes junto con el resto del cluster, pero ya no es explotable mientras tanto.

### Otros puntos a tocar al limpiar
- `app/public/layouts/sidebar.php:90` — el array `match` de la entrada "Personal & Roles" incluye hoy `'dash-users-add.php'` y `'dash-users-edit.php'` además de `'dash-users-list.php'` y `'dash-users-profile.php'`. Al limpiar, quitar las 2 entradas huérfanas y dejar solo `list.php` y `profile.php` (`profile.php` NO es huérfana, se queda).
- `app/public/layouts/header.php:34-35` — el array de breadcrumbs tiene una entrada por cada una de las 4 vistas de usuarios (`dash-users-add.php` => 'Personal de Operaciones', `dash-users-edit.php` => 'Personal de Operaciones', junto a `list` y `profile` en líneas 33 y 36). Al limpiar, quitar las 2 entradas de las páginas huérfanas.

## Cluster "servicio-pdf.php" (huérfano neutralizado, 2026-07-18)

Origen: el QA final de `.doc/plan-roles-permisos.md` (caso 10) encontró que `app/public/controller/servicio-pdf.php` — un controller legacy con **SQL injection activa** (interpolación directa de `$_GET['id_Servicio']` en el SELECT) y el bug conocido de columna inexistente `SR.id_Bath` (ya documentado en `AGENTS.md § SQL — reglas críticas`) — no tenía ningún llamador en el codebase (`grep -rn 'servicio-pdf'` sin resultados fuera del propio archivo). Ya existe su reemplazo moderno `app/public/controller/service-pdf.php` (nombre en inglés), 100% migrado a DDD (`FindServiceForPrint` + `MysqliServiceRepository`), sin el bug de columna y con outputs escapados.

**No es "seguro de borrar y listo"** como los demás clusters — era una vulnerabilidad activa, no solo deuda de navegación. Se neutralizó de inmediato (no se dejó para limpieza por lotes): el archivo se reemplazó completo por un redirect seguro a `service-pdf.php` (valida `id_Servicio` con `ctype_digit`, sin tocar la DB). Verificado con curl: id válido → 302 a `service-pdf.php`, payload de SQL injection → 400 antes de llegar a ejecutarse.

### Archivo
- 🟢 `app/public/controller/servicio-pdf.php` — ya no es la página completa original; ahora es un shim de 27 líneas. Candidato a **borrado directo** (no solo neutralización) en la próxima limpieza por lotes, ya que `service-pdf.php` cubre toda su funcionalidad y no hay bookmarks/integraciones externas conocidas que dependan de la URL vieja.
