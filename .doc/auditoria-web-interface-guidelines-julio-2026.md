# Auditoría — Web Interface Guidelines (`app/public/`)

**Fecha creación:** 16-07-2026
**Fecha actualización:** 18-07-2026 — re-auditoría contra el código actual tras los planes de Roles y Permisos, Homologación de Toolbars/Exportaciones y Log de Actividad (ejecutados el mismo día). Ver `§Changelog 18-07-2026` antes de la tabla de fases.
**Skill usada:** `web-design-guidelines` (guías re-fetcheadas de [vercel-labs/web-interface-guidelines](https://github.com/vercel-labs/web-interface-guidelines) en esta actualización).
**Alcance:** el mismo de la versión original — vistas `dash-*.php` / `auth-*.php` + layouts/modales compartidos relevantes en `app/public/`. Se sumó `dash-activity-log.php` (vista nueva, no existía el 16-07) — total 35 vistas + 10 layouts/modales = 45 archivos.
**Fuera de alcance (decisión deliberada, sin cambios):** `app/public/archive/` (77 templates legacy, no tocar sin confirmar), `app/public/vendor/`, `assets/libs/` (código de terceros vendorizado), `app/public/controller/*.php` (backend puro, sin HTML), `app/src/` (dominio/aplicación, sin UI).

**Nota de contexto para leer este informe:** el proyecto es PHP server-rendered con TailwindCSS + jQuery/vanilla JS (no React/Next.js). Reglas de las guías originales que asumen React/JSX/hidratación/`nuqs` no aplican literalmente a este stack y fueron adaptadas o descartadas. El proyecto tampoco tiene modo oscuro, así que esa sección de las guías no aplica.

## Changelog 18-07-2026

Esta actualización es de **solo lectura sobre el informe** (no se tocó código durante el re-análisis, salvo los 4 hallazgos 🔴 críticos de abajo, corregidos a pedido explícito de Edgardo antes de continuar con el resto en modo lectura). Cambios respecto de la versión del 16-07:

1. **Los 4 hallazgos 🔴 críticos originales fueron corregidos** (SQLi, XSS reflejado, password reflejado, 5 acciones destructivas sin confirmación) — ver detalle marcado ✅ RESUELTO en la sección de abajo.
2. **8 vistas fueron modificadas** por los planes de Roles/Permisos, Toolbars/Exportaciones y Log de Actividad desde el 16-07: `auth-login.php`, `auth-lock-screen.php`, `dash-bathrooms.php`, `dash-certificates.php`, `dash-contracts.php`, `dash-invoices-list.php`, `dash-services.php`, `dash-users-list.php`. Sus secciones fueron re-auditadas contra el contenido actual.
3. **1 vista nueva:** `dash-activity-log.php` (bitácora de actividad, acceso SuperAdmin). Sección agregada al final del módulo correspondiente.
4. **2 vistas fueron refactorizadas** a `table_native_open()/close()` (helper compartido) sin cambiar su alcance funcional: `dash-bathrooms-contracts.php`, `dash-bathrooms-contracts-status.php`. Números de línea de la versión anterior quedaron obsoletos — actualizados.
5. **Patrón transversal nuevo detectado:** los botones de exportación Excel/PDF (`table_native_export_buttons()`, usado ahora en 8 vistas) repiten íconos decorativos sin `aria-hidden` y `transition-all`. Ver patrón #13.
6. **Regresión detectada:** el botón `dt-cell-action dropdown-toggle dropdown-toggle-split` (usado en Baños, Certificados, Usuarios) **perdió el `title="Más acciones"` que tenía en la versión anterior** — antes tenía `title` sin `aria-label` (hallazgo menor), ahora no tiene ninguno de los dos (hallazgo peor). Ver patrón #14.

**⚠️ Archivos huérfanos incluidos en este informe:** varios de los archivos auditados abajo resultaron estar sin ningún link de navegación real en la app viva (páginas reemplazadas por drawers, o nunca conectadas). El registro y seguimiento de esos casos vive en `.doc/orfanatos-pendientes.md` — este informe NO repite ese análisis, solo marca con la nota "🗄️ Archivo huérfano" cada sección afectada para que se lea con ese contexto (los hallazgos de accesibilidad/UX siguen siendo válidos si el archivo se reconecta o se usa como base de otra vista, pero no son urgentes mientras el archivo no tenga entrada real).

---

## 🔴 Hallazgos críticos (priorizar) — ✅ TODOS RESUELTOS el 18-07-2026

1. ✅ **RESUELTO** — **Inyección SQL real** — `auth-recoverpw.php:17-18`. Usaba `mysqli_real_escape_string` + interpolación directa en el `WHERE` (`useremail = '$useremail'`). Convertido a `mysqli_prepare` + `bind_param('s', ...)`.
2. ✅ **RESUELTO** — **Password reflejado en el HTML** — `auth-register.php:122,130`. Se quitó el `value=` de los inputs `password`/`confirm_password` (además se agregó `autocomplete="new-password"` a ambos).
3. ✅ **RESUELTO** — **XSS reflejado potencial** — `auth-recoverpw.php:85`. `htmlentities($_SERVER["PHP_SELF"])` ahora usa `ENT_QUOTES, 'UTF-8'`.
4. ✅ **RESUELTO** — **Acciones destructivas/mutación sin confirmación** — se agregó `data-confirm-delete` (+ título/texto contextual) a las 5 acciones:
   - `dash-contracts-item.php:129` — desasignar baño. También se agregó `aria-label` y `aria-hidden` al ícono.
   - `dash-customers-item.php:144` — eliminar contacto. Ídem `aria-label`/`aria-hidden`.
   - `dash-invoices-list.php:283,286` — Pagado / Anular Pago (el "Anular factura" de `:290` ya tenía `data-confirm-delete` desde antes — el hallazgo original citaba líneas de una versión anterior del archivo).
   - `dash-invoices-detail.php:165` — eliminar servicio de factura. Ídem `aria-label`/`aria-hidden`.
   - `dash-users-list.php:129,153` — Password Default (en ambas ramas: vista Admin y vista con step-up de elevación). **"Set Admin/Usuario" ya no existe como acción separada** — el rework de Roles y Permisos (18-07) lo reemplazó por el flujo de edición con elevación (`data-requires-elevation`), así que ese sub-ítem del hallazgo original quedó obsoleto por diseño, no por bug.

*Nota histórica: `auth-recoverpw.php`, `auth-register.php` y `dash-contracts-item.php` siguen siendo archivos huérfanos sin link de navegación — ver `.doc/orfanatos-pendientes.md`. Eso no le restó urgencia a corregir los bugs (accesibles por URL directa), solo aclaraba que no eran alcanzables por navegación normal.*

---

## Hallazgos por archivo

### Módulo Baños Químicos

#### `app/public/dash-bathrooms.php`
*Modificada 18-07-2026 (rebranding indigo + toolbar de exportación + reemplazo de link "Editar" por ícono). Re-auditada.*
- :246 — input `#banos-local-search` sin `<label>` ni `aria-label` (solo placeholder)
- :248 — placeholder "Código o Faena..." → "…"
- :246 — input search sin `autocomplete="off"` ni `spellcheck="false"`
- :268,277 — toggles grid/tabla solo-ícono con `title` pero sin `aria-label`; falta `aria-pressed` para estado activo
- **:435 — NUEVO: link "Editar" antes tenía texto visible, ahora es ícono-solo (`dt-cell-action`) con `title="Editar"` pero sin `aria-label`** (regresión de accesibilidad introducida al mismo tiempo que el toolbar de exportación)
- **:442 — REGRESIÓN: el dropdown-toggle "Más acciones" perdió el `title` que tenía en la versión anterior — ahora no tiene ni `title` ni `aria-label`** (ver patrón transversal #14)
- :258-263 — NUEVO: botones export Excel/PDF (`table_native_export_buttons()`) — íconos decorativos sin `aria-hidden`, `transition-all` (ver patrón transversal #13)
- :245,275,284,293,324,440,445+ — íconos lucide decorativos sin `aria-hidden="true"`
- :576,584,598,664 — `focus:outline-none` sin ring de reemplazo (fecha/estado/observación/select del drawer)
- :486 — separador de paginación "..." → "…"
- :509 — overlay backdrop `<div>` con `onclick` para cerrar (no semántico; Esc sí está cubierto en :711)
- :552,571,581,593 — `<label>` del drawer sin `for=` ni envolviendo el control (no clickeables, no asociados)
- :609 — submit del drawer sin estado disabled/spinner (doble submit posible)
- :306,408 — `date('d-m-Y')` formato de fecha hardcodeado
- :215 — transiciones sin guard `@media prefers-reduced-motion`
- ✓ pass: el bug de "Bodega Central" mostrado como estado en vez de Mantención/Inactivo (encontrado en una revisión previa fuera de esta auditoría) está corregido — la tabla ahora distingue correctamente Mantención/Fuera de Servicio/En Bodega.

#### `app/public/dash-bathrooms-add.php`
🗄️ *Archivo huérfano — ver `.doc/orfanatos-pendientes.md` (cluster Baños Químicos).*
- :45,50,64 — inputs sin `autocomplete`
- :45 — `codigo_Bath` sin `spellcheck="false"`
- :37 — ícono decorativo sin `aria-hidden="true"`
- :69 — submit "Crear" sin disabled/spinner; copy poco específico ("Crear Baño")

#### `app/public/dash-bathrooms-edit.php`
🗄️ *Archivo huérfano — ver `.doc/orfanatos-pendientes.md` (cluster Baños Químicos).*
- :96 — error con `alert()` JS crudo, sin next-step ("Problema al cargar el Baño")
- :64,69,74 — inputs sin `autocomplete`
- :64 — `codigo_Bath` sin `spellcheck="false"`
- :53 — ícono decorativo sin `aria-hidden="true"`
- :79 — submit "Actualizar" sin disabled/spinner

#### `app/public/dash-bathrooms-contracts.php`
*Refactorizada 18-07-2026 a `table_native_open()/close()` + toolbar de exportación. Números de línea de la versión anterior obsoletos — actualizados.*
- :52-56 — `SELECT *` en JOIN de 4 tablas (perf); consulta legacy `mysqli_query` directa (sin cambios)
- :68-72 — NUEVO: botones export Excel/PDF sin `aria-hidden` en íconos, `transition-all` (patrón #13)
- :94,100,120,126 — íconos lucide decorativos sin `aria-hidden="true"` (sin cambios, solo se movieron de línea)
- :86 — `date('d-m-Y')` fecha hardcodeada (sin cambios)
- ✓ pass: la migración a `table_native_open()` no introdujo regresiones de accesibilidad nuevas más allá de los botones de export (patrón transversal, no específico de este archivo).

#### `app/public/dash-bathrooms-contracts-status.php`
*Refactorizada 18-07-2026 a `table_native_open()/close()` (dos tabpanels) + toolbar de exportación por tab. Números de línea actualizados.*
- :50,58 — botones de tab sin `role="tab"` ni `aria-selected` (el `<ul>` tiene `role=tablist` pero el patrón ARIA está incompleto) — sin cambios
- :66,131 — tabpanels sin `aria-labelledby` ni `tabindex` — sin cambios
- :73-77,138-142 — NUEVO: botones export Excel/PDF por tab (contratos-activos / banos-disponibles) sin `aria-hidden` en íconos, `transition-all` (patrón #13)
- :97,103,109,115,159,165 — íconos decorativos sin `aria-hidden="true"` (renumerados, mismo hallazgo)
- :89,153 — `date('d-m-Y')` hardcodeada — sin cambios
- :52 — tab activo no reflejado en la URL (al recargar vuelve al primer tab) — sin cambios

### Módulo Contratos

#### `app/public/dash-contracts.php`
*Modificada 18-07-2026: se agregó toolbar de exportación (CSV/PDF) respetando filtro de estado y orden actual.*
- :191-195 — NUEVO: botones export Excel/PDF (`table_native_export_buttons()`) sin `aria-hidden` en íconos, `transition-all` (patrón #13)
- :244 — dropdown-toggle solo-ícono sin `aria-label`
- :235,239 — enlaces acción solo-ícono (editar/ver) con `title` pero sin `aria-label`
- :197,203,216,222,236,240,245,248-251 — íconos decorativos sin `aria-hidden="true"`
- :227,230 — `number_format` con separadores hardcodeados (CLP) en vez de `Intl.NumberFormat`
- :190,191 — `date('d-m-Y')` hardcodeadas
- :287,479 — overlay backdrop `<div>` con `onclick` (Esc sí cubierto en :586)
- :317,343,356,366,379,392,407,420,439 — `<label>` del drawer sin `for=` ni envolviendo control
- :344,408 — inputs texto (obra, dirección) sin `autocomplete`
- :464 — submit del drawer sin disabled/spinner (doble submit)
- :329,349,361,371,386,399,413,444 — `focus:outline-none`; reponen border pero sin ring `focus-visible` (foco débil)
- :382,395 — valores money `type="text"` + `inputmode="numeric"` + `data-money-mask` ✓
- :423,544 — checkbox+label comparten hit target ✓

#### `app/public/dash-contracts-add.php`
🗄️ *Archivo huérfano — ver `.doc/orfanatos-pendientes.md` (cluster Obras & Contratos).*
- :59,64 — inputs texto (obra, dirección) sin `autocomplete`
- :37 — ícono decorativo sin `aria-hidden="true"`
- :48 — `SELECT * FROM clientes` legacy directa (usar select de campos)
- :103 — submit "Crear" sin disabled/spinner; copy poco específico ("Crear Contrato")
- :83 — `fechaFin` con `data-datepicker-min-from` ✓ (validación de rango)

#### `app/public/dash-contracts-edit.php`
🗄️ *Archivo huérfano — ver `.doc/orfanatos-pendientes.md` (cluster Obras & Contratos).*
- :66 — `SELECT * FROM clientes` legacy directa
- :77,82 — inputs texto sin `autocomplete`
- :54 — ícono decorativo sin `aria-hidden="true"`
- :88 — opción de estado `value="0"` ("Eliminado") expuesta al usuario en el select (permite setear un estado inválido a mano)
- :121 — submit "Actualizar" sin disabled/spinner
- :140 — error con `alert()` JS crudo sin next-step

#### `app/public/dash-contracts-item.php`
🗄️ *Archivo huérfano — ver `.doc/orfanatos-pendientes.md` (cluster Obras & Contratos).*
- :59-83 — datos de solo-lectura como `<input readonly>`/`<textarea readonly>` en vez de texto semántico (lectores de pantalla los anuncian como campos editables)
- :95 — "Agregar Nuevo Baño" es `<a href="#">` con `data-bs-toggle="modal"`; debería ser `<button>` (abre modal, no navega)
- :96,130 — íconos decorativos sin `aria-hidden="true"`
- ✅ **RESUELTO** — :129 — acción destructiva "No Asignar" (desasignar baño) ahora tiene `data-confirm-delete` + `aria-label` + `aria-hidden` en el ícono.
- :71,74 — fechas crudas de DB (`Y-m-d`) sin formato `d-m-Y` consistente con el resto

### Módulo Servicios

#### `app/public/dash-services.php`
*Modificada 18-07-2026: se agregó toolbar de exportación (CSV/PDF) respetando el filtro activo.*
- :202-206 — NUEVO: botones export Excel/PDF (`table_native_export_buttons()`) sin `aria-hidden` en íconos, `transition-all` (patrón #13)
- :148 — input search sin `<label>` ni `aria-label`
- :147 — placeholder "Cliente, obra o número..." → "…"
- :157 — pill `<a>` con `transition-all` (anti-patrón)
- :172,181 — view-toggle buttons `transition-all`; solo `title`, falta `aria-label`
- :189 — botón "Agregar" `transition-all`
- :216 — article card `transition-all duration-300` (anti-patrón; usar `transform`/`opacity`)
- :198,282 — grid y tabla renderizan TODOS los servicios sin paginación ni virtualización (lista potencialmente >50)
- :334 — dropdown-toggle solo-ícono sin `aria-label` (tiene `aria-expanded`)
- :372 — backdrop `<div onclick="window.location=...">` (div con click handler, sin rol/teclado)
- :468 — checklist `overflow-y-auto` sin `overscroll-behavior: contain`
- :539 — placeholder "Bitácora de la visita..." → "…"
- :576 — animación del drawer (`translateX`) sin honrar `prefers-reduced-motion`
- :205,302 — `date('d-m-Y')` formato de fecha hardcodeado

#### `app/public/dash-services-add.php`
🗄️ *Archivo huérfano — ver `.doc/orfanatos-pendientes.md` (cluster Servicios). Además tiene un bug relacionado documentado ahí: `controller/service-new.php:21` todavía redirige acá en el path de error.*
- :90 — date input sin `autocomplete` (menor)
- :100 — submit "Crear" sin disabled/spinner durante request (menor)
- (sin hallazgos mayores — labels, types y focus correctos)

#### `app/public/dash-services-edit.php`
🗄️ *Archivo huérfano — ver `.doc/orfanatos-pendientes.md` (cluster Servicios).*
- :127 — error handling vía `alert()` inyectado; copy sin next-step
- (resto limpio)

#### `app/public/dash-services-print.php`
- :52 — `<img>` logo con `height` pero sin `width`; `alt=""` en logo informativo
- :123 — `<img src="firma_01.png">` sin width/height explícitos
- :58,61 — íconos decorativos (mail/phone) sin `aria-hidden="true"`
- :143 — `<a href="javascript:window.print()">` — la acción debería ser `<button>`
- :85 — `date('d/m/Y')` formato hardcodeado

### Módulo Clientes

#### `app/public/dash-customers.php`
- :120 — input search sin `<label>` ni `aria-label`
- :122 — placeholder "Buscar por RUT, Nombre, Comuna..." → "…"
- :226 — link edit solo-ícono con `title` pero sin `aria-label` (menor)
- :272 — backdrop `<div onclick="window.location=...">` (div con click handler)
- :443 — animación del drawer sin `prefers-reduced-motion`
- :449 — autofocus vía `setTimeout`; en móvil dispara zoom/teclado (justificar desktop-only)
- :322,357 — inputs RUT/email sin `autocomplete` significativo (menor)

#### `app/public/dash-customers-add.php`
- :58 — `type="number"` para teléfono → usar `type="tel"` (number rompe `+`, ceros, espacios)
- :44-102 — inputs sin `autocomplete` (menor)
- (labels/for correctos)

#### `app/public/dash-customers-item.php`
- ✅ **RESUELTO** — :144 — eliminar contacto ahora tiene `data-confirm-delete` + `aria-label` + `aria-hidden` en el ícono.
- :60,100,128,136 — acciones (abrir modal) vía `<a href="javascript:void(0)"/"#">` → deberían ser `<button>`
- :128,136,144 — acciones solo-ícono con `title` pero sin `aria-label` (menor)

### Módulo Certificados

#### `app/public/dash-certificates.php`
*Modificada 18-07-2026: refactorizada de DataTables a `table_native_open()/close()` + toolbar de exportación. Números de línea actualizados.*
- :46-49 — NUEVO: botones export Excel/PDF sin `aria-hidden` en íconos, `transition-all` (patrón #13)
- **:85 — REGRESIÓN: el dropdown-toggle "Más acciones" perdió el `title` que tenía en la versión con DataTables — ahora no tiene ni `title` ni `aria-label`** (mismo patrón que `dash-bathrooms.php:442`, ver patrón transversal #14)
- (paginación ahora vía `table_native.js` en vez de DataTables — mismo comportamiento de UX, sin regresión funcional; delete con `data-confirm-delete` sigue OK en :92)

#### `app/public/dash-certificates-add.php`
- :73 — campo fecha con `type="text"` (debería `type="date"` o datepicker); placeholder con typo "Ingrese las Fecha"
- :68 — mts `type=number` OK

#### `app/public/dash-certificates-item.php`
- :54,59 — `<img>` logos con `height` sin `width`; `alt=""` en logo informativo (:54)
- :87 — `<img src="firma.jpeg">` sin width/height explícitos (solo clase `h-24`)
- :108 — `<a href="javascript:window.print()">` — debería ser `<button>`
- :66,75 — `date('d/m/Y')` formato hardcodeado

### Módulo Facturas

#### `app/public/dash-invoices-list.php`
*Modificada 18-07-2026 (toolbar de exportación) y en esta misma sesión (confirmaciones agregadas).*
- :41 — botón solo-ícono (calendario `#date1`) sin `aria-label`
- :40 — input de rango de fechas sin label ni `aria-label`
- :86 — botón solo-ícono editar fecha de pago usa `title` pero sin `aria-label`
- :87,94,98 — íconos decorativos sin `aria-hidden="true"` (patrón en todo el archivo, salvo los ya corregidos abajo)
- ✅ **RESUELTO** — :283,286 — Pagado / Anular Pago ahora tienen `data-confirm-delete` + `aria-hidden` en sus íconos. La acción "Anular factura" (:290) ya tenía confirmación desde antes.
- :56,69 — columna Monto sin `tabular-nums`
- NUEVO (no auditado en la versión anterior): toolbar de exportación Excel/PDF — sin `aria-hidden` en íconos, `transition-all` (patrón #13)

#### `app/public/dash-invoices-add.php`
- :43 — input `numero_Factura` sin `autocomplete`
- :74 — input `valor_Factura` `type=number` sin `inputmode="numeric"`
- :79 — submit "Crear" sin disabled/spinner
- :35 — ícono decorativo sin `aria-hidden`
- :43,48,74 — placeholders no terminan en "…"

#### `app/public/dash-invoices-edit.php`
- :107 — submit sin disabled durante request
- :69 — `numero_Factura` sin `autocomplete`
- :102 — `valor_Factura` sin `inputmode`
- :59 — ícono decorativo sin `aria-hidden`
- :63 — form sin warning ante navegación con cambios sin guardar

#### `app/public/dash-invoices-detail.php`
- :56 — `<img logo_zl>` sin `width` (solo `height`)
- :61 — `<img logo_rc>` sin `width`; `alt="Rata"` poco descriptivo
- :110 — "Agregar Servicios" es `<a href="#">` con `onClick` de modal, debería ser `<button>`
- ✅ **RESUELTO** — :165 — eliminar servicio ahora tiene `data-confirm-delete` + `aria-label` + `aria-hidden` en el ícono.
- :69,70,111 — íconos decorativos sin `aria-hidden`
- :200 — error de carga vía `alert()` JS en vez de estado en UI

#### `app/public/dash-invoices-upload.php`
- :66 — `<input type="file">` sin `aria-describedby` al texto de ayuda (:55-59)
- :71 — botón "Continuar" sin disabled/spinner durante upload
- :53 — ícono decorativo sin `aria-hidden`

#### `app/public/dash-invoices-upload-preview.php`
- :94 — `<select select-obra>` sin label ni `aria-label`
- :63 — tabla potencialmente >50 filas sin virtualización ni `content-visibility`
- :114 — botón confirmar sin spinner al enviar
- :1 — `session_start()` + include `session.php` (posible doble `session_start`/warning)

#### `app/public/dash-invoices-upload-result.php`
- :64 — columna Número de Factura sin `tabular-nums` (resto pass)

### Módulo Usuarios

#### `app/public/dash-users-list.php`
*Modificada 18-07-2026 por el plan de Roles y Permisos: agrega la rama con step-up de elevación (`data-requires-elevation`) para usuarios sin nivel Admin, y reemplaza "Set Admin/Usuario" por el flujo de edición con elevación. Re-auditada + confirmaciones agregadas en esta sesión.*
- :109 — `<img>` avatar con `alt=""` pero sin width/height explícitos ni `loading="lazy"`
- :125,149 — botón dropdown solo-ícono sin `aria-label` (una instancia por rama Admin/step-up)
- ✅ **RESUELTO** — :129,153 — Password Default ahora tiene `data-confirm-delete` en ambas ramas (vista Admin y vista con elevación). "Set Admin/Usuario" ya no existe como acción separada: el rework de Roles y Permisos lo reemplazó por edición con `data-requires-elevation`, que ya exige autorización de un Admin antes de ejecutar la acción — el riesgo original (mutación sin fricción) quedó cubierto por el gate de elevación, y ahora además pide confirmación explícita.
- :106-165 — íconos decorativos (`key`, `lock`, `square-pen`, `more-horizontal`) sin `aria-hidden` en la mayoría de las instancias

#### `app/public/dash-users-add.php`
- :49 — input email sin `autocomplete="email"`
- :55 — input username sin `autocomplete="username"` ni `spellcheck="false"`
- :70 — input password sin `autocomplete="new-password"`
- :81 — submit "Registrar" sin disabled/spinner
- :74 — label "Imagen de Perfil" sin `for=`; input `#file` hidden (label no clickable al control)
- :39 — ícono decorativo sin `aria-hidden`

#### `app/public/dash-users-edit.php`
- :72 — input email sin `autocomplete="email"`
- :77 — input username sin `autocomplete="username"` ni `spellcheck="false"`
- :61 — `<img>` avatar sin width/height explícitos
- :92 — submit "Actualizar" sin disabled durante request
- :114 — error vía `alert()` JS; copy incorrecto ("Problema al cargar el Servicio", debería decir Usuario)

#### `app/public/dash-users-profile.php`
- :32 — `<img>` avatar sin width/height explícitos ni `loading`
- :49 — trigger "Cambiar Password" es `<a href="javascript:void(0)">` en vez de `<button>`
- :50 — ícono decorativo sin `aria-hidden`

### Módulo Bitácora de Actividad

#### `app/public/dash-activity-log.php`
🆕 *Vista nueva (plan de log de actividad, ejecutado 18-07-2026). No existía en la versión anterior de este informe. Acceso restringido a `NIVEL_SUPERADMIN` vía `require_min_nivel()` — control correcto, no es un hallazgo.*
- :126-176 — los 6 controles de filtro (`usuario`, `entidad`, `accion`, `resultado`, `desde`, `hasta`) tienen `<label>` visible pero **sin `for=`** y el input/select correspondiente **sin `id`** — no están asociados programáticamente (el label no es clickeable y un lector de pantalla no los vincula)
- :159 — botón "Filtrar" con `transition-all` (anti-patrón; listar propiedades explícitas) y `active:scale-95` sin guard `prefers-reduced-motion`
- :221-226 — botón "Ver detalle" (`js-log-detail-btn`) es ícono-solo con `title="Ver detalle"` pero sin `aria-label`
- :283 — botón cerrar drawer (`#log-detail-close`) es ícono-solo sin `title` ni `aria-label`
- :276 — ícono decorativo (`file-clock`) sin `aria-hidden`; :225,284 ídem (`eye`, `x`)
- :271 — backdrop `<div id="log-detail-backdrop">` con `addEventListener('click', closeDrawer)` — sin manejo de tecla Esc (el resto de drawers del proyecto sí cubre Esc; acá no se ve un listener de teclado en el script inline)
- :272 — animación del drawer (`transition-transform duration-300`) sin guard `prefers-reduced-motion`
- :210 — celda "Descripción" usa `truncate` + `title=` con el texto completo (✓ patrón correcto de content handling)
- :198 — `<?php echo htmlspecialchars(format_fecha($row['fecha_Log']), ...)` usa el helper centralizado de fecha (✓ mejor que el `date('d-m-Y')` hardcodeado repetido en el resto del proyecto — ver patrón transversal #5, este archivo no lo repite)
- :250,256 — paginación server-side con enlaces `<a href>` reales (✓ soporta Cmd/Ctrl+click, a diferencia de los backdrops con `onclick` de otras vistas)
- ✓ pass general: SQL 100% parametrizado (`execute_query` con placeholders `?` en todos los filtros), sin SQL injection.

### Autenticación (legacy, no migrada a Tailwind)

#### `app/public/auth-login.php`
*Modificada 18-07-2026: se agregó `activity_logger.php` (registra LOGIN/ERROR en `logs_actividad`) y se mejoró parcialmente el markup (ver ✅ abajo). Re-auditada — la mayoría de los hallazgos de accesibilidad de la versión anterior siguen presentes.*
- :156-162 — input username sin `autocomplete="username"` ni `spellcheck="false"` (sin cambios)
- :170-178 — input password sin `autocomplete="current-password"` (sin cambios)
- ✅ mejora parcial: el input password ahora tiene `aria-label="Password"` y `aria-describedby="password-addon"` (no estaba en la versión anterior), pero el botón toggle (`#password-addon`, :179-186) sigue sin `aria-label` propio — solo tiene `tabindex="-1"`
- :190-197 — submit sin disabled/spinner durante request (sin cambios)
- :143 — alerta de error (`#login-error-alert`) sin `aria-live="polite"` (sin cambios)
- :193 — `active:scale-98`; :129 — `hover:scale-105` — sin honrar `prefers-reduced-motion` (sin cambios)
- :130,144,154,169,185,195 — íconos decorativos sin `aria-hidden` (sin cambios)
- :104 — `echo "Oops! Something went wrong..."` sigue en inglés y fuera del layout (se movió de línea, mismo bug)

#### `app/public/auth-register.php`
🗄️ *Archivo huérfano (sin link desde `auth-login.php` ni de ningún otro lado) — ver `.doc/orfanatos-pendientes.md`. A diferencia de los clusters de arriba, este no es un resto de migración: parece que nunca se conectó.*
- :106 — input email sin `autocomplete="email"`
- :114 — input username sin `autocomplete="username"` ni `spellcheck=false`
- ✅ **RESUELTO** — :122 — password ahora tiene `autocomplete="new-password"` y ya no refleja el valor en `value=`.
- ✅ **RESUELTO** — :130 — `confirm_password` ídem: `autocomplete="new-password"` agregado, `value=` quitado.
- :140 — submit "Registrar" sin disabled durante request
- :9-52 — mensajes de error en inglés con UI en español
- :158 — animación float infinita sin `prefers-reduced-motion`
- :94,161 — `<img logo-sm>` sin `width`
- :137 — links "Términos de Uso" con `href="#"` (placeholder)

#### `app/public/auth-recoverpw.php`
🗄️ *Archivo huérfano (sin link desde `auth-login.php` ni de ningún otro lado) — ver `.doc/orfanatos-pendientes.md`. Sigue siendo accesible por URL directa, lo cual no reduce la urgencia de la inyección SQL de abajo.*
- ✅ **RESUELTO** — :17-19 — SQL injection: convertido a `mysqli_prepare` + `bind_param('s', ...)`, ya no interpola `$useremail` en el SQL.
- :88 — input email es `type="text"` (debería `type="email"`) sin `autocomplete` ni `inputmode`
- :87 — `<label>` sin `for=` asociado al input `#email`
- :94 — submit sin disabled durante request
- :26,43,48 — mensajes en inglés con UI en español
- ✅ **RESUELTO** — :85 — `htmlentities($_SERVER["PHP_SELF"], ENT_QUOTES, 'UTF-8')` ya no es vulnerable a XSS reflejado.
- :112 — animación float infinita sin `prefers-reduced-motion`
- :79-83 — mensaje de éxito sin `aria-live`

#### `app/public/auth-lock-screen.php`
*Modificada 18-07-2026: se agregó `activity_logger.php` (LOGIN/ERROR) y se corrigió el idioma del mensaje de error genérico (ver nota abajo). Re-auditada.*
- :110 — input password sin `autocomplete="current-password"` (tiene `aria-label="Password"`/`aria-describedby`, mejora ya presente desde antes de esta ronda)
- :111-113 — botón toggle-password solo-ícono sin `aria-label`
- :120 — submit sin disabled durante request
- **Corregido en esta ronda:** :65 — el `echo` de error genérico ya no está en inglés (era `"Oops!..."`, ahora `"¡Ups! Algo salió mal. Por favor, inténtelo de nuevo más tarde."`) — sigue fuera del layout (rompe la UI en ese branch), solo se resolvió el idioma.
- :100 — `<img>` avatar sin width/height explícitos
- :138 — animación float infinita sin `prefers-reduced-motion`
- :112 — ícono decorativo sin `aria-hidden`

### Layouts y modales compartidos

#### `app/public/layouts/menu.php`
- ✓ pass

#### `app/public/layouts/header.php`
- :68 — botón trigger dropdown de usuario sin `aria-label` ni texto accesible en móvil (nombre oculto `sm:block`)
- :75 — `<img>` avatar sin width/height explícitos ni `loading`
- :55,78,79,81 — íconos decorativos sin `aria-hidden`
- :58 — dos `<h1>` por página (header + page-title de cada vista): jerarquía de headings duplicada
- :52 — sin skip-link a contenido principal

#### `app/public/layouts/sidebar.php`
- :148 — `animate-pulse` en indicador activo sin `prefers-reduced-motion`
- :145 — `group-hover:scale-110` en íconos sin `prefers-reduced-motion`
- :111,119,145 — íconos decorativos sin `aria-hidden`
- :124 — `<nav>` sin `aria-label`; aside scrollable sin `overscroll-behavior: contain`

#### `app/public/layouts/native-table.php`
- :75 — input de búsqueda sin label ni `aria-label` (solo placeholder)
- :44,52 — íconos export decorativos sin `aria-hidden`
- :85 — `overflow-x-auto` presente (pass en scroll horizontal)

#### `app/public/layouts/modal-edit-customer.php`
- :9 — `action="../controller/..."` con `../` inconsistente con el resto (`controller/...`); posible 404 según la vista que lo incluya
- :23 — teléfono `type="text"` sin `type="tel"`/`inputmode`
- :1 — `aria-labelledby` con id `myLargeModalLabel` duplicado entre modales
- :27 — email sin `autocomplete`
- :43 — campo Región `readonly` sin indicación de por qué

#### `app/public/layouts/modal-editar-contacto.php`
- :27 — teléfono `type="number"` (incorrecto para teléfonos, usar `type="tel"`)
- :1 — id `myLargeModalLabel` duplicado
- :15,19,23,31 — inputs sin `autocomplete`

#### `app/public/layouts/modal-new-password.php`
- :14 — input nuevo password sin `autocomplete="new-password"`
- :1 — id `exampleModalLabel` duplicado
- :18 — submit "Actualizar" sin disabled durante request

#### `app/public/layouts/modal-nuevo-assign-bath.php`
- :16 — query SQL inline dentro del modal (acoplamiento vista/DB)
- :1 — id `exampleModalLabel` duplicado
- :27 — submit "Asignar" sin disabled durante request

#### `app/public/layouts/modal-nuevo-contacto.php`
- :27 — teléfono `type="number"` en vez de `type="tel"`
- :1 — id `exampleModalLabel` duplicado
- :15,19,23,31 — inputs sin `autocomplete`
- :37 — submit "Registrar" sin disabled durante request

#### `app/public/layouts/modal-ver-contacto.php`
- :25 — teléfono `type="number"` readonly (menor)
- :1 — id `myLargeModalLabel` duplicado en `aria-labelledby`
- :13-33 — campos readonly correctos (pass general)

---

## Patrones transversales (deuda repetida en todo el proyecto)

1. **Íconos solo-ícono sin `aria-label`** — dropdowns de tabla, toggles grid/tabla, botones "más acciones", toggle de password en auth. Es el hallazgo más repetido de toda la auditoría.
2. **Íconos decorativos (`<i data-lucide="...">`) sin `aria-hidden="true"`** — presente en prácticamente todas las vistas.
3. **`<label>` de drawers (Baños, Contratos) sin `for=` ni envolviendo el control** — no clickeables ni asociados al input.
4. **Submit buttons sin estado disabled/spinner durante el request** — presente en casi todos los forms de creación/edición; riesgo de doble-submit.
5. **`date('d-m-Y')` / `date('d/m/Y')` hardcodeado** en vez de `Intl.DateTimeFormat` (o al menos una función helper centralizada) — repetido en Baños, Contratos, Servicios, Certificados.
6. **Acciones que abren modales usando `<a href="#">` en vez de `<button>`** — Contratos, Clientes, Facturas, perfil de Usuario.
7. ✅ **RESUELTO 18-07-2026** — ~~Acciones destructivas/de mutación de estado sin `data-confirm-delete`~~ — las 5 instancias detectadas en la auditoría original (Contratos, Clientes, Facturas x2, Usuarios) ya tienen confirmación. Si aparecen acciones destructivas nuevas en vistas futuras, seguir el mismo patrón (`data-confirm-delete` + `data-confirm-title` + `data-confirm-text`, ver `assets/js/components/feedback.js:116`).
8. **`alert()` JS crudo para errores de carga**, sin mensaje con next-step, en varias vistas `-edit.php`.
9. **`prefers-reduced-motion` ignorado** en animaciones de drawers (translateX) y en las vistas `auth-*` (animación float infinita, `animate-pulse`, `scale`). También presente ahora en `dash-activity-log.php` (drawer de detalle + botón Filtrar).
10. **Mensajes de error en inglés** — parcialmente corregido: `auth-lock-screen.php` ya está en español. Sigue pendiente en `auth-login.php:104` (1 string residual) y en `auth-recoverpw.php` / `auth-register.php` completos (huérfanos, pero accesibles por URL directa).
11. **`type="number"` usado para teléfonos** en varios modales de contacto — rompe `+`, ceros a la izquierda y espacios; debería ser `type="tel"`.
12. **IDs de modal duplicados** (`exampleModalLabel`, `myLargeModalLabel`) entre varios modales — potencial colisión de `aria-labelledby` si dos modales coexisten en el DOM de la misma página.
13. **NUEVO (18-07-2026) — Botones de exportación Excel/PDF (`table_native_export_buttons()`) sin `aria-hidden` en sus íconos decorativos + `transition-all`** — el helper se usa ahora en 8 vistas (`dash-bathrooms.php`, `dash-bathrooms-contracts.php`, `dash-bathrooms-contracts-status.php`, `dash-certificates.php`, `dash-contracts.php`, `dash-invoices-list.php`, `dash-services.php`, `dash-users-list.php`). Como es un helper único en `layouts/native-table.php:34-56`, corregirlo ahí resuelve las 8 instancias de una sola vez — no requiere tocar cada vista.
14. **NUEVO (18-07-2026) — Regresión: el botón `dt-cell-action dropdown-toggle dropdown-toggle-split` ("más acciones") perdió el `title="Más acciones"` que tenía antes de los refactors de toolbar** — confirmado en las 6 vistas que comparten este fragmento de markup (no es un helper compartido, es HTML repetido por copia): `dash-bathrooms.php:442`, `dash-certificates.php:85`, `dash-contracts.php:266`, `dash-services.php:392`, `dash-invoices-list.php:272`, `dash-users-list.php:125,149` (7 instancias en total, 2 en users-list). Antes tenía `title` sin `aria-label` (hallazgo menor); ahora no tiene ninguno de los dos. Prioridad más alta que el resto de patrón #1 porque es una regresión, no deuda preexistente.

---

## Fases y tareas de remediación (orquestación Orca)

Los 4 hallazgos 🔴 críticos ya están resueltos (ver changelog). Lo que queda es deuda de accesibilidad/UX de severidad media-baja — se organiza en 4 fases, ejecutables por olas según la convención de orquestación del proyecto (`CLAUDE.md §Orquestación de planes`). Dentro de una ola, las tareas son paralelizables salvo que compartan archivo (columna "Depende de" marca precedencia real; el resto es orden por archivos compartidos).

| Fase | Tarea | Ola | Modelo (Orca) | Depende de | Entregable | Riesgo |
|---|---|---|---|---|---|---|
| 1 | **T1** — Patrón #13: agregar `aria-hidden="true"` a los íconos de `table_native_export_buttons()` y reemplazar `transition-all` por propiedades explícitas en `layouts/native-table.php:34-56` | 1 | MiniMax-M3 | — | 1 archivo, resuelve las 8 vistas que usan el helper | Bajo — cambio cosmético en un solo punto |
| 1 | **T2** — Patrón #14: agregar `title="Más acciones"` + `aria-label="Más acciones"` a las 7 instancias de `dropdown-toggle-split` (`dash-bathrooms.php`, `dash-certificates.php`, `dash-contracts.php`, `dash-services.php`, `dash-invoices-list.php`, `dash-users-list.php` x2) | 1 | MiniMax-M3 | — | Restaura + mejora el `title` perdido en el refactor de toolbars | Bajo — atributo estático, sin lógica |
| 1 | **T3** — `dash-activity-log.php`: asociar los 6 `<label>` de filtros con `for=`/`id=`, agregar `aria-hidden` a íconos (`file-clock`, `eye`, `x`), `aria-label` al botón "Ver detalle" y al botón cerrar drawer | 1 | MiniMax-M3 | — | Vista nueva queda al mismo nivel que el resto del proyecto | Bajo |
| 2 | **T4** — Íconos solo-ícono sin `aria-label` (patrón #1, resto de instancias no cubiertas en T2): toggles grid/tabla, toggle-password en `auth-login.php`/`auth-lock-screen.php`, edit/view/print en Clientes/Contratos/Certificados | 2 | MiniMax-M3 | T1, T2 (no tocar los mismos botones dos veces) | Cobertura completa del patrón #1 | Bajo — atributos estáticos |
| 2 | **T5** — Íconos decorativos sin `aria-hidden` (patrón #2, resto del proyecto no cubierto en T1/T3) | 2 | MiniMax-M3 | T1, T3 | Cobertura completa del patrón #2 | Bajo |
| 2 | **T6** — `prefers-reduced-motion`: guardar animaciones de drawers (`translateX`), `animate-pulse` del sidebar, floats de `auth-*`, y las nuevas del drawer de `dash-activity-log.php` | 2 | GLM-5.2 | — | Bloque `@media (prefers-reduced-motion: reduce)` en `tw/layout.css` + ajuste de clases afectadas | Bajo-medio — tocar `tw/layout.css` requiere `pnpm tw:build` y QA visual de los drawers |
| 3 | **T7** — Formularios no-auth: `autocomplete`, `type` correcto (`tel`/`email`/`date`), `spellcheck="false"`, submit disabled/spinner durante request — Baños, Contratos, Servicios, Clientes, Certificados, Facturas, Usuarios (no auth) | 3 | GLM-5.2 | T6 (evitar tocar los mismos submits que agregan guard de animación) | Forms alineados a las guías de forms | Medio — el disabled/spinner toca JS de submit, requiere QA de que el form siga funcionando |
| 3 | **T8** — Formularios `auth-*` (login, lock-screen, recoverpw, register): `autocomplete="username"/"current-password"/"new-password"`, `aria-label` en toggles de password, submit disabled/spinner, `aria-live="polite"` en alertas de error | 3 | **Codex-terra (high)** | — | Los 4 forms de autenticación quedan homologados | **Alto** — toca campos de password y el flujo de login/lock-screen; requiere QA adversarial (que el toggle no rompa el submit, que el disabled no bloquee un login legítimo) |
| 4 | **T9** — `date('d-m-Y')` hardcodeado: reemplazar por el helper `format_fecha()` ya existente (`layouts/helpers.php:39`, usado como referencia en `dash-activity-log.php:198`) en las vistas que todavía lo hardcodean (Baños, Contratos, Servicios, Certificados) | 4 | MiniMax-M3 | T7 (mismos archivos, evitar conflicto) | Formato de fecha centralizado en un solo helper | Bajo — mismo output visual, solo cambia la fuente |
| 4 | **T10** — Modales compartidos (`modal-edit-customer.php`, `modal-editar-contacto.php`, `modal-nuevo-contacto.php`, etc.): IDs de `aria-labelledby` duplicados, `type="tel"` en teléfonos, `autocomplete` faltante | 4 | MiniMax-M3 | — | Modales sin colisión de IDs, teléfonos con el `type` correcto | Bajo-medio — un modal roto afecta a todas las vistas que lo incluyen, QA manual obligatorio en cada una |
| 4 | **T11** — QA manual de cierre: abrir cada vista tocada en `http://localhost`, verificar que ningún `data-confirm-delete` quedó duplicado, que los dropdowns con `title` nuevo no rompieron el posicionamiento (sin Popper, ver `§Bootstrap JS shim`), y smoke test de los 4 forms `auth-*` | 4 | Sonnet 5 (orquestador) | T1–T10 | Cierre de ciclo, sin hallazgos nuevos abiertos | — |

**Reglas de despacho** (idénticas a los planes anteriores del proyecto):
- Ola 1 no tiene dependencias entre sí — T1/T2/T3 son paralelizables porque no comparten archivo.
- Ola 2 depende de que Ola 1 haya tocado los mismos botones/íconos primero, para no pisarse.
- T8 va a Codex-terra (high) aunque es un cambio mayormente cosmético, porque toca campos de contraseña de los 4 flujos de autenticación del sistema — la política del proyecto hace que la criticidad de seguridad mande sobre la complejidad pura.
- No se incluye una fase para reconectar `auth-recoverpw.php`/`auth-register.php` a la navegación — esa es una decisión de producto trackeada aparte en `.doc/orfanatos-pendientes.md`, fuera del alcance de esta auditoría.

---

## Nota metodológica

**Versión original (16-07-2026):** este informe se generó delegando la lectura y el chequeo de reglas a 3 agentes en paralelo (uno por bloque de módulos), cada uno con el checklist completo de las guías y el mismo contexto de stack para evitar falsos positivos por asumir React/JSX donde hay PHP/vanilla JS.

**Actualización (18-07-2026):** re-auditoría manual (sin subagentes) de los 8 archivos modificados desde la versión original + 1 archivo nuevo, comparando contra `git diff` para ubicar exactamente qué cambió. Se corrigieron en el momento los 4 hallazgos 🔴 críticos (a pedido explícito de Edgardo, antes de continuar el resto en modo solo-lectura) y se agregó la tabla de fases de remediación de arriba para el resto de la deuda, sin aplicar ningún otro fix. Las correcciones de las fases 1-4 quedan a criterio de Edgardo.
