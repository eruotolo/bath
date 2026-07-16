# Auditoría — Web Interface Guidelines (`app/public/`)

**Fecha:** 16-07-2026
**Skill usada:** `web-design-guidelines` (guías fetcheadas de [vercel-labs/web-interface-guidelines](https://github.com/vercel-labs/web-interface-guidelines))
**Alcance:** 34 vistas (`dash-*.php`, `auth-*.php`) + 10 layouts/modales compartidos relevantes en `app/public/` — 43 archivos en total.
**Fuera de alcance (decisión deliberada):** `app/public/archive/` (77 templates legacy, no tocar sin confirmar), `app/public/vendor/`, `assets/libs/` (código de terceros vendorizado), `app/public/controller/*.php` (backend puro, sin HTML), `app/src/` (dominio/aplicación, sin UI).

**Nota de contexto para leer este informe:** el proyecto es PHP server-rendered con TailwindCSS + jQuery/vanilla JS (no React/Next.js). Reglas de las guías originales que asumen React/JSX/hidratación/`nuqs` no aplican literalmente a este stack y fueron adaptadas o descartadas por los agentes auditores. El proyecto tampoco tiene modo oscuro, así que esa sección de las guías no aplica.

**⚠️ Archivos huérfanos incluidos en este informe:** varios de los archivos auditados abajo resultaron estar sin ningún link de navegación real en la app viva (páginas reemplazadas por drawers, o nunca conectadas). El registro y seguimiento de esos casos vive en `.doc/orfanatos-pendientes.md` — este informe NO repite ese análisis, solo marca con la nota "🗄️ Archivo huérfano" cada sección afectada para que se lea con ese contexto (los hallazgos de accesibilidad/UX siguen siendo válidos si el archivo se reconecta o se usa como base de otra vista, pero no son urgentes mientras el archivo no tenga entrada real).

---

## 🔴 Hallazgos críticos (priorizar)

1. **Inyección SQL real** — `auth-recoverpw.php:17-18`. Usa `mysqli_real_escape_string` + interpolación directa en el `WHERE` (`useremail = '$useremail'`) en vez de prepared statement. Es la única vista de todo el lote con esta falla activa.
2. **Password reflejado en el HTML** — `auth-register.php:122,130`. Tras un error de validación, los campos `password`/`confirm_password` quedan con `value=` seteado al valor ingresado, exponiéndolo en el markup de la respuesta.
3. **XSS reflejado potencial** — `auth-recoverpw.php:85`. Usa `htmlentities($_SERVER["PHP_SELF"])` sin `ENT_QUOTES`.
4. **Acciones destructivas/mutación sin confirmación** (inconsistente con el resto del proyecto, que sí usa `data-confirm-delete` + SweetAlert2 en la mayoría de los deletes):
   - `dash-contracts-item.php:129` — desasignar baño, sin confirmación (contrasta con `dash-contracts.php:519` que sí confirma).
   - `dash-customers-item.php:144` — eliminar contacto, sin confirmación.
   - `dash-invoices-list.php:104-110` — Marcar Pagado / Anulado, `<a href>` GET sin confirmación.
   - `dash-invoices-detail.php:165` — eliminar servicio de factura, sin confirmación.
   - `dash-users-list.php:80-81` — Password Default / Set Admin-Usuario, mutan sin confirmación (solo Inactivar confirma).

*Nota: `auth-recoverpw.php`, `auth-register.php` y `dash-contracts-item.php` (hallazgos 1, 2, 3 y parte del 4) son archivos huérfanos — ver `.doc/orfanatos-pendientes.md`. Esto no reduce la urgencia de los bugs de seguridad, solo aclara que hoy no son alcanzables por navegación normal.*

---

## Hallazgos por archivo

### Módulo Baños Químicos

#### `app/public/dash-bathrooms.php`
- :246 — input `#banos-local-search` sin `<label>` ni `aria-label` (solo placeholder)
- :248 — placeholder "Código o Faena..." → "…"
- :246 — input search sin `autocomplete="off"` ni `spellcheck="false"`
- :268,277 — toggles grid/tabla solo-ícono con `title` pero sin `aria-label`; falta `aria-pressed` para estado activo
- :439 — dropdown-toggle solo-ícono sin `aria-label` (solo `title="Más acciones"`)
- :245,275,284,293,324,440,445+ — íconos lucide decorativos sin `aria-hidden="true"`
- :576,584,598,664 — `focus:outline-none` sin ring de reemplazo (fecha/estado/observación/select del drawer)
- :486 — separador de paginación "..." → "…"
- :509 — overlay backdrop `<div>` con `onclick` para cerrar (no semántico; Esc sí está cubierto en :711)
- :552,571,581,593 — `<label>` del drawer sin `for=` ni envolviendo el control (no clickeables, no asociados)
- :609 — submit del drawer sin estado disabled/spinner (doble submit posible)
- :306,408 — `date('d-m-Y')` formato de fecha hardcodeado
- :215 — transiciones sin guard `@media prefers-reduced-motion`

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
- :52 — `SELECT *` en JOIN de 4 tablas (perf); consulta legacy `mysqli_query` directa
- :94,100,120,126 — íconos lucide decorativos sin `aria-hidden="true"`
- :86 — `date('d-m-Y')` fecha hardcodeada
- :28 — headers de orden como `<a href>` ✓ (navegación real, soporta Cmd+click)

#### `app/public/dash-bathrooms-contracts-status.php`
- :52,58 — botones de tab sin `role="tab"` ni `aria-selected` (el `<ul>` tiene `role=tablist` pero el patrón ARIA está incompleto)
- :66,131 — tabpanels sin `aria-labelledby` ni `tabindex`
- :97,103,109,115,159,165 — íconos decorativos sin `aria-hidden="true"`
- :89,153 — `date('d-m-Y')` hardcodeada
- :52 — tab activo no reflejado en la URL (al recargar vuelve al primer tab)

### Módulo Contratos

#### `app/public/dash-contracts.php`
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
- **:129 — ⚠️ acción destructiva "No Asignar" (desasignar baño) es `<a>` directo SIN `data-confirm-delete`: elimina la relación sin confirmación (inconsistente con `dash-contracts.php:519`, que sí confirma)**
- :129 — enlace solo-ícono (trash) sin `aria-label` (solo `title`)
- :71,74 — fechas crudas de DB (`Y-m-d`) sin formato `d-m-Y` consistente con el resto

### Módulo Servicios

#### `app/public/dash-services.php`
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
- **:144 — eliminar contacto `<a href="controller/contact-remove.php?...">` SIN confirmación** (destructiva inmediata; el resto del proyecto usa `data-confirm-delete`)
- :60,100,128,136 — acciones (abrir modal) vía `<a href="javascript:void(0)"/"#">` → deberían ser `<button>`
- :128,136,144 — acciones solo-ícono con `title` pero sin `aria-label` (menor)

### Módulo Certificados

#### `app/public/dash-certificates.php`
- :67 — dropdown-toggle solo-ícono sin `aria-label` (tiene `aria-expanded`)
- (DataTables pagina la lista; delete con `data-confirm-delete` OK)

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
- :41 — botón solo-ícono (calendario `#date1`) sin `aria-label`
- :40 — input de rango de fechas sin label ni `aria-label`
- :86 — botón solo-ícono editar fecha de pago usa `title` pero sin `aria-label`
- :87,94,98 — íconos decorativos sin `aria-hidden="true"` (patrón en todo el archivo)
- **:104-110 — acciones que mutan estado (Marcar Pagado/Anulado) son `<a href>` GET sin confirmación**
- :56,69 — columna Monto sin `tabular-nums`

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
- **:165 — eliminar servicio es `<a href>` GET destructiva sin confirmación**
- :165 — botón solo-ícono eliminar usa `title` sin `aria-label`
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
- :60 — `<img>` avatar con `alt=""` pero sin width/height explícitos ni `loading="lazy"`
- :75 — botón dropdown solo-ícono sin `aria-label`
- **:80,81 — Password Default y Set Admin/Usuario son `<a href>` GET que mutan sin confirmación (solo Inactivar confirma)**
- :85-87 — items deshabilitados sin explicación (tooltip) del porqué
- :72,76,80 — íconos decorativos sin `aria-hidden`

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

### Autenticación (legacy, no migrada a Tailwind)

#### `app/public/auth-login.php`
- :114 — input username sin `autocomplete="username"` ni `spellcheck="false"`
- :129 — input password sin `autocomplete="current-password"`
- :135-142 — botón toggle-password ("eye") solo-ícono sin `aria-label`
- :146 — submit sin disabled/spinner durante request
- :99 — alerta de error sin `aria-live="polite"`
- :149,85 — `active:scale-98` / `hover:scale-105` sin honrar `prefers-reduced-motion`
- :110,125 — íconos decorativos en inputs sin `aria-hidden`
- :60 — `echo "Oops! Something went wrong..."` en inglés y fuera del layout

#### `app/public/auth-register.php`
🗄️ *Archivo huérfano (sin link desde `auth-login.php` ni de ningún otro lado) — ver `.doc/orfanatos-pendientes.md`. A diferencia de los clusters de arriba, este no es un resto de migración: parece que nunca se conectó.*
- :106 — input email sin `autocomplete="email"`
- :114 — input username sin `autocomplete="username"` ni `spellcheck=false`
- **:122 — password sin `autocomplete="new-password"`; además `value=` refleja el password en el HTML tras error (fuga en el markup)**
- **:130 — `confirm_password` sin `autocomplete="new-password"`; también reflejado en `value=`**
- :140 — submit "Registrar" sin disabled durante request
- :9-52 — mensajes de error en inglés con UI en español
- :158 — animación float infinita sin `prefers-reduced-motion`
- :94,161 — `<img logo-sm>` sin `width`
- :137 — links "Términos de Uso" con `href="#"` (placeholder)

#### `app/public/auth-recoverpw.php`
🗄️ *Archivo huérfano (sin link desde `auth-login.php` ni de ningún otro lado) — ver `.doc/orfanatos-pendientes.md`. Sigue siendo accesible por URL directa, lo cual no reduce la urgencia de la inyección SQL de abajo.*
- **:17-18 — 🔴 SQL injection: `mysqli_real_escape_string` + interpolación directa (`WHERE useremail = '$useremail'`), no prepared statement**
- :88 — input email es `type="text"` (debería `type="email"`) sin `autocomplete` ni `inputmode`
- :87 — `<label>` sin `for=` asociado al input `#email`
- :94 — submit sin disabled durante request
- :26,43,48 — mensajes en inglés con UI en español
- **:85 — form usa `htmlentities($_SERVER["PHP_SELF"])` sin `ENT_QUOTES` (XSS reflejado potencial)**
- :112 — animación float infinita sin `prefers-reduced-motion`
- :79-83 — mensaje de éxito sin `aria-live`

#### `app/public/auth-lock-screen.php`
- :87 — input password sin `autocomplete="current-password"`
- :88 — botón toggle-password solo-ícono sin `aria-label`
- :97 — submit sin disabled durante request
- :42 — `echo "¡Ups!..."` fuera del layout (rompe la UI)
- :77 — `<img>` avatar sin width/height explícitos
- :115 — animación float infinita sin `prefers-reduced-motion`
- :89 — ícono decorativo sin `aria-hidden`

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
7. **Acciones destructivas/de mutación de estado sin `data-confirm-delete`**, rompiendo la consistencia con el resto del proyecto (ver sección de hallazgos críticos).
8. **`alert()` JS crudo para errores de carga**, sin mensaje con next-step, en varias vistas `-edit.php`.
9. **`prefers-reduced-motion` ignorado** en animaciones de drawers (translateX) y en las vistas `auth-*` (animación float infinita, `animate-pulse`, `scale`).
10. **Mensajes de error en inglés** en las 3 vistas `auth-*` legacy, con el resto de la UI en español.
11. **`type="number"` usado para teléfonos** en varios modales de contacto — rompe `+`, ceros a la izquierda y espacios; debería ser `type="tel"`.
12. **IDs de modal duplicados** (`exampleModalLabel`, `myLargeModalLabel`) entre varios modales — potencial colisión de `aria-labelledby` si dos modales coexisten en el DOM de la misma página.

---

## Nota metodológica

Este informe se generó delegando la lectura y el chequeo de reglas a 3 agentes en paralelo (uno por bloque de módulos), cada uno con el checklist completo de las guías y el mismo contexto de stack para evitar falsos positivos por asumir React/JSX donde hay PHP/vanilla JS. No se aplicó ningún fix — es un informe de solo lectura. Las correcciones quedan a criterio de Edgardo, priorizando los 3 hallazgos 🔴 de la sección de arriba antes que la deuda de accesibilidad/UX general.
