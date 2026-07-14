# Plan — Migración Bootstrap 5.3 → TailwindCSS 4.3 (`app/public`)

**Origen:** prototipo `rebranding/` (React + Vite + Tailwind 4.x, paleta indigo) — referencia visual y funcional oficial del proyecto según `AGENTS.md`.
**Objetivo:** reemplazar Bootstrap 5.3.2 + tema "Crow Advance" (`app.css`) + `icons.css` por TailwindCSS 4.3 como único sistema de estilos de `app/public`, de forma **incremental, vista por vista**, sin romper funcionalidad existente (DataTables, SweetAlert2, Choices, Flatpickr, Dropzone, GLightbox, Pristine) y convergiendo con el diseño del prototipo.
**Alcance:** solo `app/public` (vistas activas + layouts + auth). **NO** incluye `app/public/archive/` (77 templates legacy), `app/public/template/`, TCPDF ni los PDFs generados por backend.
**Depende de:** rebranding ya implementado de sidebar, header, dashboard (parcial) y Clientes (`plan-sidebar-rebranding.md`, `plan-header-rebranding.md`, `plan-dashboard-rebranding.md`, `plan-customers-rebranding.md`).
**Estado:** 📋 planificado — ninguna fase iniciada.

---

## 1. Estado actual (inventario auditado 2026-07-13)

### 1.1 CSS cargado hoy por todas las vistas (`layouts/head-style.php`)

| Archivo | Peso | Rol | Destino |
|---|---|---|---|
| `assets/css/preloader.css` | 4 K | Spinner de carga del tema | Migrar a Tailwind o eliminar (evaluar en Fase 9) |
| `assets/css/bootstrap.css` | 356 K | Bootstrap 5.3.2 completo | **Eliminar** (Fase 9) |
| `assets/css/icons.css` | 520 K | MDI + FontAwesome + otros del tema | **Eliminar** → Lucide (ya cargado) |
| `assets/css/app.css` | 116 K | Tema admin "Crow Advance" | **Eliminar** (Fase 9) |
| `assets/css/style.css` | — | CSS custom del proyecto (tabla homologada, tokens `--color-*`) | **Absorber** en Tailwind (`@theme` + utilities) |
| `assets/css/sidebar.css` | — | Rebranding sidebar (vanilla, scoped) | **Traducir** a Tailwind (Fase 2) |
| `assets/css/header.css` | 4 K | Rebranding header | **Traducir** a Tailwind (Fase 2) |
| `assets/css/dashboard.css` | 12 K | Rebranding dashboard | **Traducir** a Tailwind (Fase 4) |
| `assets/css/customers.css` | 12 K | Rebranding Clientes (scope `.cl-clients`) | **Traducir** a Tailwind (Fase 3) |
| CSS de libs (sweetalert2, choices, flatpickr, dropzone, glightbox) | — | Funcionales | **Mantener** — solo re-skin con tokens |

Payload CSS actual: **~1 MB sin comprimir** solo entre bootstrap + icons + app. El target post-migración es un único `tailwind.css` de **~40–80 K minificado** (solo las utilities usadas) + CSS de libs. Es la mejora de performance más grande disponible en el frontend hoy.

### 1.2 JS acoplado a Bootstrap (`layouts/vendor-scripts.php`)

| Lib | Uso real | Destino |
|---|---|---|
| `bootstrap.bundle.min.js` (incluye Popper) | Modales, dropdowns, tabs | **Eliminar** (Fase 8) — reemplazo vanilla |
| `jquery.min.js` | Requerido por DataTables | **Mantener** (DataTables no se reescribe en este plan) |
| `metisMenu.min.js` | Sidebar viejo del tema | **Eliminar** — el sidebar rebrandeado ya no lo usa (auditar en Fase 2) |
| `node-waves` | Efecto ripple del tema | **Eliminar** (Fase 9) |
| `pace-js` | Barra de progreso de carga | Evaluar: eliminar o mantener re-skineado |
| `feather-icons` | Iconos del markup viejo | **Eliminar** → todo a Lucide |
| `simplebar` | Scroll custom del sidebar | Evaluar en Fase 2 (el sidebar nuevo puede usar scrollbar nativo estilizado) |

### 1.3 Uso real de Bootstrap en vistas activas

Medido con grep excluyendo `archive/`, `template/` y `vendor/`:

- **36 archivos** incluyen `layouts/head-style.php` (todas las vistas + auth).
- **~507 instancias** de clases estructurales Bootstrap (`row`, `col-*`, `btn btn-*`, `card`, `table table-*`).
- Componentes **JS** de Bootstrap realmente usados: **9** `data-bs-toggle="modal"`, **9** `data-bs-toggle="dropdown"`, **2** `data-bs-toggle="tab"`. *(Los tooltips, collapse, offcanvas y popovers que aparecen en greps globales están solo en `archive/`/`template/` — fuera de alcance.)*
- **6 modales compartidos** en `layouts/`: `modal-edit-customer.php`, `modal-editar-contacto.php`, `modal-new-password.php`, `modal-nuevo-assign-bath.php`, `modal-nuevo-contacto.php`, `modal-ver-contacto.php`.
- DataTables usa el skin **`datatables.net-bs4`** (clases Bootstrap) en 10 vistas de lista.

### 1.4 Vistas a migrar (35 con UI + logout sin UI)

| Grupo | Vistas | Notas |
|---|---|---|
| Dashboard | `index.php` | CSS rebranding linkeado pero markup viejo (108 `class=`) |
| Auth | `auth-login`, `auth-register`, `auth-recoverpw`, `auth-lock-screen` | Layout propio, sin sidebar |
| Clientes | `dash-customers`, `-add`, `-item` | Lista ya rebrandeada (vanilla CSS) |
| Baños | `dash-bathrooms`, `-add`, `-edit`, `-contracts`, `-contracts-status` | 2 listas DataTables |
| Contratos | `dash-contracts`, `-add`, `-edit`, `-item` | Lista DataTables |
| Servicios | `dash-services`, `-add`, `-edit`, `-print`, `-bath` | `-print` es vista de impresión — cuidado con estilos print |
| Certificados | `dash-certificates`, `-add`, `-item` | Lista DataTables |
| Facturas | `dash-invoices-list`, `-add`, `-edit`, `-detail`, `-upload`, `-upload-preview`, `-upload-result` | Grupo más grande; upload usa Dropzone |
| Usuarios | `dash-users-list`, `-add`, `-edit`, `-profile` | `-add`/`-profile` usan Dropzone |
| Layouts | `sidebar.php`, `header.php`, `right-sidebar.php`, 6 modales, `head-style.php`, `vendor-scripts.php`, `body.php` | Compartidos por todo |

---

## 2. Decisiones clave

### 2.1 Build tooling: pnpm + `@tailwindcss/cli` en la raíz del repo

El proyecto no tiene tooling JS y **no se va a introducir bundler** (Vite/webpack). Tailwind 4 solo necesita su CLI:

- `package.json` **en la raíz del repo** (junto a `composer.json`) — no interfiere con Docker: no está bind-mounteado al contenedor.
- `pnpm add -D tailwindcss@^4.3 @tailwindcss/cli@^4.3` (pnpm, nunca npm/yarn — convención global).
- Scripts:
  ```json
  {
    "scripts": {
      "tw:watch": "tailwindcss -i ./app/public/assets/css/tw/main.css -o ./app/public/assets/css/tailwind.css --watch",
      "tw:build": "tailwindcss -i ./app/public/assets/css/tw/main.css -o ./app/public/assets/css/tailwind.css --minify"
    }
  }
  ```
- **El output `tailwind.css` se commitea.** Deploy es cPanel por copia de archivos: no hay pipeline de build en el servidor, el CSS compilado debe viajar en el repo. `node_modules/` va a `.gitignore`.
- Regla de trabajo: cualquier cambio de markup en una vista migrada exige correr `pnpm tw:build` antes de dar por cerrada la tarea (el watch cubre el desarrollo; el build minificado es lo que se commitea).

**Alternativa descartada:** binario standalone de Tailwind (sin node). Funciona, pero el ecosistema pnpm ya es el estándar de los demás proyectos y facilita fijar la versión en `package.json`.

### 2.2 Estrategia de convivencia: Tailwind sin Preflight + flag `important`

Durante la migración conviven Bootstrap y Tailwind en las mismas páginas. Dos problemas técnicos concretos y sus soluciones:

**a) Preflight vs. Bootstrap Reboot.** El reset de Tailwind (Preflight) pisaría los defaults que Bootstrap asume (márgenes de headings, estilos de botones, etc.) y rompería las vistas no migradas. Solución: durante la convivencia se importa Tailwind **sin Preflight**:

```css
/* app/public/assets/css/tw/main.css — FASE DE CONVIVENCIA */
@layer theme, base, components, utilities;
@import "tailwindcss/theme.css" layer(theme);
@import "tailwindcss/utilities.css" layer(utilities) important;
```

Preflight se activa recién en Fase 9, cuando `bootstrap.css` y `app.css` salen del `head-style.php`:

```css
/* FASE FINAL */
@import "tailwindcss" important; /* evaluar quitar important al final */
```

**b) Cascade layers: Bootstrap le gana a Tailwind por defecto.** Tailwind 4 emite sus utilities dentro de `@layer utilities`; Bootstrap es CSS *sin* layer, y en CSS **los estilos sin layer siempre ganan a los estilos en layers** a igual especificidad, sin importar el orden de carga. Sin mitigación, un `p-4` de Tailwind perdería contra un `.card-body` de Bootstrap sobre el mismo nodo. Mitigación: el flag **`important`** del import (arriba) hace que todas las utilities lleven `!important` durante la convivencia. Es la técnica documentada por Tailwind para convivir con CSS legacy. En Fase 9, ya sin Bootstrap, se evalúa quitar el flag (requiere re-QA de las vistas que dependan de él para pisar CSS de libs).

**c) Colisiones de nombres de clase.** Bootstrap y Tailwind comparten nombres con significados distintos. Con `important` activo, Tailwind gana — por eso en vistas **no migradas** está prohibido que aparezcan estas clases sueltas, y en vistas migradas hay que eliminar la clase Bootstrap homónima del nodo:

| Clase | Bootstrap 5 | Tailwind 4 | Riesgo |
|---|---|---|---|
| `collapse` | `display: none` (plegado) | `visibility: collapse` | **Alto** — rompe collapses BS |
| `table` | Estilos completos de tabla | `display: table` | **Alto** — DataTables usa `.table` |
| `container` | Ancho fijo por breakpoint + auto-margin | `container queries` / ancho distinto | Alto |
| `rounded`, `border` | radios/bordes con otros valores | radios/bordes propios | Medio |
| `visible` / `invisible` | visibility | visibility (igual) | Bajo |
| `text-center`, `flex-row`, `flex-wrap`, `text-start/end` | mismos efectos | mismos efectos | Bajo (redundantes) |

Regla operativa: **una vista o es Bootstrap o es Tailwind, nunca mezcla dentro del mismo bloque de markup**. La unidad mínima de migración es la vista completa (o el layout completo).

**Alternativa evaluada y descartada — prefijo `tw:`**: `@import "tailwindcss" prefix(tw)` elimina colisiones de raíz, pero obliga a reescribir todas las clases al copiar del prototipo `rebranding/` (que usa clases sin prefijo) y ensucia el markup de forma permanente. No compensa: las colisiones reales son pocas y auditables.

**Alternativa evaluada y descartada — switch de CSS por vista** (`$tw_view = true` antes de `head-style.php` para no cargar bootstrap.css en vistas migradas): elimina la convivencia dentro de la página, pero los layouts compartidos (sidebar/header/modales) renderizan en ambos regímenes y tendrían que soportar los dos a la vez — más frágil que la convivencia con `important`. Se reconsiderará solo si la convivencia da problemas en la Fase 3 (piloto).

### 2.3 Tokens de diseño: `@theme` desde el prototipo

Los tokens ya existen dos veces: en `rebranding/` (Tailwind) y en `style.css` (`--color-primary` teal + overrides indigo por scope). La migración los unifica en el `@theme` del CSS de entrada:

```css
@theme {
  --color-primary: #4f46e5;            /* indigo-600 — paleta rebrand */
  --color-primary-50: #eef2ff;
  --color-primary-200: #c7d2fe;
  /* … escala completa extraída de rebranding/ */

  --font-sans: "Inter", ui-sans-serif, system-ui, sans-serif;
  --font-mono: "JetBrains Mono", ui-monospace, monospace;

  --radius-card: 24px;                 /* border-radius de cards del rebrand */
}
```

Esto genera utilities (`bg-primary`, `text-primary-50`, `font-mono`, `rounded-card`) **y** custom properties CSS (`var(--color-primary)`) — lo que permite que el CSS de las libs (DataTables, Choices, Flatpickr, SweetAlert2) siga re-skineándose vía `var()` exactamente como hoy lo hace `style.css`.

⚠️ Decisión de paleta a confirmar con Edgardo antes de Fase 1: el rebrand indigo hoy convive con el teal `#2D5C6C` (vistas no rebrandeadas). El `@theme` debe nacer con **indigo como primario** (dirección declarada del rebranding); el teal sobrevive solo como token legacy mientras existan vistas sin migrar.

### 2.4 Detección de contenido: `@source` explícito

Tailwind 4 auto-detecta contenido pero hay que excluir el legacy para no inflar el CSS con clases de `archive/` (77 templates) ni de `vendor/`:

```css
@source "../../../**/*.php";
@source "../../js/**/*.js";
@source not "../../../archive";
@source not "../../../template";
@source not "../../../vendor";
@source not "../../../assets/tcpdf";
@source not "../../../assets/libs";
```

*(Rutas relativas al archivo `main.css`; ajustar al implementar. Verificar con `@source not` que el output no contenga utilities que solo usa `archive/`.)*

### 2.5 DataTables: se mantiene, se cambia el skin

DataTables + jQuery **no se tocan funcionalmente** (paginación, sort, export CSV/Excel/PDF, buscador — todo Sprint 2 operativo). Cambia solo la capa visual:

- Reemplazar `datatables.net-bs4` (skin Bootstrap) por **`datatables.net-dt`** (skin neutro) y re-estilizar con el sistema `table-card`/`table-toolbar` ya homologado, traducido a Tailwind + `var()` tokens.
- El trabajo ya hecho en `customers.css` (paginación pills, botones export `cl-icon-btn`, footer JetBrains Mono) **es el diseño destino** para todas las listas: se generaliza quitando el scope `.cl-clients` y pasa a ser el estilo global de tabla.
- Los botones/DOM que DataTables genera dinámicamente (`dom:`, `buttons:`) llevan clases configurables — se cambian de `btn btn-outline-secondary` a las clases nuevas en `assets/js/components/datatable.js` (un solo punto de cambio para las 10 listas).

### 2.6 Reemplazo del JS de Bootstrap (Fase 8)

Solo hay 3 comportamientos que reponer, todos chicos:

| Componente | Instancias | Reemplazo |
|---|---|---|
| Modales | 9 toggles + 6 modales compartidos en `layouts/` | `<dialog>` nativo + `assets/js/components/modal.js` propio (~40 líneas: open/close, backdrop, ESC, focus). Auditar `modal-editor.js` (Sprint 4), que hoy invoca la API `bootstrap.Modal` — es el punto de acople JS más grande |
| Dropdowns | 9 toggles (header usuario, columna Acción de listas) | Popover API nativa (`popovertarget`) o JS propio (~30 líneas). Ojo con los dropdowns dentro de DataTables que usan `data-bs-display="static"` |
| Tabs | 2 toggles | JS propio (~20 líneas) o CSS-only con `:checked` |

Sin Alpine, sin framework — consistente con la regla del proyecto de no introducir frameworks y con el patrón `assets/js/components/` existente.

### 2.7 Iconos: todo a Lucide

`icons.css` (520 K) existe para MDI/FontAwesome del tema. El rebranding ya introdujo Lucide. Durante cada fase de vista, los `<i class="mdi mdi-*">` / `<i class="fas fa-*">` del markup migrado se cambian a `<i data-lucide="*">`. `icons.css` y `feather-icons` se eliminan en Fase 9 cuando ninguna vista activa los referencie.

### 2.8 Soporte de navegadores — riesgo a validar ANTES de empezar

Tailwind 4 requiere **Safari 16.4+, Chrome 111+, Firefox 128+** (usa `@property`, `color-mix()`, cascade layers). En navegadores más viejos el CSS directamente no renderiza bien. Antes de la Fase 0: confirmar con Edgardo/cliente qué navegadores usan los usuarios reales del sistema (es un dashboard interno — si hay una sola máquina con un Chrome viejo en la oficina del cliente, hay que saberlo ahora, no en producción).

---

## 3. Arquitectura destino

```
package.json                          ← nuevo, raíz del repo (pnpm)
app/public/assets/css/
├── tw/
│   ├── main.css                      ← entrada: @import tailwindcss + @theme + @source
│   ├── theme.css                     ← tokens @theme (colores, fonts, radios)
│   ├── components.css                ← @layer components: table-card, badges, btn — lo que hoy es style.css
│   └── libs.css                      ← re-skin de DataTables/Choices/Flatpickr/SweetAlert2/Dropzone vía var()
├── tailwind.css                      ← OUTPUT compilado y commiteado (único CSS propio en producción)
└── (bootstrap.css, app.css, icons.css, style.css, sidebar.css,
     header.css, dashboard.css, customers.css → se eliminan al final)
```

Convenciones de escritura:

- **Utilities en el markup** para layout y estilo por vista (como el prototipo `rebranding/`).
- **`@layer components`** solo para patrones repetidos ≥3 veces (`.table-card`, `.badge-status`, `.btn-icon`) — sin `@apply` masivo ni componentes especulativos (KISS/YAGNI).
- **`var(--color-*)`** para re-skin de libs de terceros (no se puede meter utilities en DOM generado por DataTables/Choices sin hooks).
- PHP escapa outputs igual que siempre — la migración de estilos **no toca lógica**, pero cada vista tocada es oportunidad de auditar `htmlspecialchars` faltantes (anotar, no mezclar en el mismo diff salvo pedido).

---

## 4. Fases

> Cada fase termina con: `pnpm tw:build` → `docker-compose restart php` → smoke test manual en `http://localhost` (checklist §6) → captura de estado en este documento. Sin commit salvo pedido explícito.

### Fase 0 — Infraestructura de build (0.5 sesión)

- [ ] Confirmar soporte de navegadores del cliente (§2.8) — **bloqueante**.
- [ ] `package.json` en raíz + `pnpm add -D tailwindcss@^4.3 @tailwindcss/cli@^4.3` (pinear la 4.3 exacta disponible).
- [ ] `node_modules/` a `.gitignore` (⚠️ recordar: `.env` NO está gitignored — no aprovechar para `git add .`).
- [ ] `assets/css/tw/main.css` con imports sin Preflight + `important` (§2.2) + `@source` (§2.4).
- [ ] `@theme` inicial con tokens extraídos de `rebranding/` y `style.css` (§2.3).
- [ ] `pnpm tw:build` genera `tailwind.css`; agregarlo a `head-style.php` **después** de bootstrap/app/style (orden irrelevante por layers, pero documenta la intención).
- [ ] Smoke test: ninguna vista cambia visualmente (Tailwind cargado pero sin clases usadas aún).

### Fase 1 — Prueba de convivencia controlada (0.5 sesión)

- [ ] En una vista de bajo riesgo (`auth-lock-screen.php` o similar) aplicar un bloque con utilities Tailwind y verificar: gana a Bootstrap (`important` OK), no se rompe nada alrededor, el build detecta las clases del PHP.
- [ ] Probar las colisiones de §2.2.c a propósito (`.table`, `.collapse`) para confirmar el comportamiento y documentarlo.
- [ ] Verificar peso del output (~<20 K a esta altura) y que no arrastra clases de `archive/`.

### Fase 2 — Layouts compartidos: sidebar + header + body (1–2 sesiones)

Los layouts renderizan en TODAS las vistas: es la migración más sensible y la primera real.

- [ ] `layouts/sidebar.php`: traducir `sidebar.css` (vanilla) a utilities Tailwind. El diseño ya es el del prototipo — es traducción 1:1, no rediseño. Auditar si metisMenu/simplebar siguen siendo necesarios; si el sidebar nuevo no los usa, quitarlos de `vendor-scripts.php` ya.
- [ ] `layouts/header.php`: traducir `header.css`. El dropdown de usuario usa `data-bs-toggle="dropdown"` — **por ahora se deja con Bootstrap JS** (se reemplaza en Fase 8); solo migran los estilos.
- [ ] `layouts/body.php` + estructura `page-content`/`main-content` del tema: reemplazar por contenedor Tailwind propio. ⚠️ Estas clases (`#layout-wrapper`, `.main-content`) las estila `app.css`; hay que reproducir el comportamiento (margen del sidebar, responsive) en Tailwind antes de poder soltar `app.css`.
- [ ] `layouts/right-sidebar.php`: auditar si se usa realmente (settings del tema) — candidato a eliminación directa con confirmación de Edgardo.
- [ ] QA en las 36 vistas (recorrido rápido: sidebar/header idénticos en todas).
- [ ] Al cierre: eliminar `sidebar.css` y `header.css` del `head-style.php`.

### Fase 3 — Piloto: grupo Clientes (1 sesión)

`dash-customers.php` ya replica el prototipo vía `customers.css` — es la traducción más barata y valida el patrón completo para listas:

- [ ] Traducir `customers.css` → utilities en el markup + `@layer components` para `table-card`/`table-toolbar` (generalizables).
- [ ] Cambiar skin DataTables de esta vista a neutro + tokens (§2.5), ajustando `datatable.js` de forma retrocompatible (las demás listas siguen en Bootstrap hasta su fase).
- [ ] `dash-customers-add.php` y `dash-customers-item.php`: primera migración de formulario y de vista de detalle — establecen el patrón de forms (labels, inputs, selects Choices re-skineados, validación Pristine con estados de error en Tailwind) y de detail.
- [ ] Modales compartidos de clientes (`modal-edit-customer`, `modal-*-contacto`, `modal-nuevo-assign-bath`): migrar estilos, mantener Bootstrap JS hasta Fase 8.
- [ ] Eliminar `customers.css`.
- [ ] **Retrospectiva escrita en este doc**: qué costó, ajustar estimaciones de Fases 4–7.

### Fase 4 — Dashboard + vistas de lista restantes (2–3 sesiones)

- [ ] `index.php` (dashboard): el markup sigue siendo el viejo del tema — acá sí es **rediseño** contra `rebranding/` (cards de stats, actividad), no solo traducción. Absorber/eliminar `dashboard.css`.
- [ ] Listas restantes, una por sesión de trabajo, reutilizando los components de Fase 3: `dash-bathrooms`, `dash-bathrooms-contracts` + `-status`, `dash-contracts`, `dash-services`, `dash-certificates`, `dash-invoices-list`, `dash-users-list`.
- [ ] Al migrar la última lista: retirar `datatables.net-bs4` de `head-style.php`/`vendor-scripts.php`.

### Fase 5 — Formularios add/edit (2–3 sesiones)

Con el patrón de forms de Fase 3: `dash-bathrooms-add/-edit`, `dash-contracts-add/-edit`, `dash-services-add/-edit`, `dash-certificates-add`, `dash-invoices-add/-edit`, `dash-users-add/-edit`.

- [ ] Re-skin definitivo de Choices, Flatpickr, Dropzone y Pristine en `tw/libs.css` vía tokens (una vez, vale para todos).
- [ ] `dash-invoices-upload` + `-preview` + `-result`: flujo Dropzone completo (Fase 3 de la cotización cliente — regresión acá es visible para el cliente, QA extra).

### Fase 6 — Vistas item/detail + especiales (1–2 sesiones)

- [ ] `dash-contracts-item`, `dash-certificates-item`, `dash-invoices-detail`, `dash-users-profile`, `dash-services-bath`.
- [ ] Los 2 `data-bs-toggle="tab"`: migrar estilos; JS en Fase 8.
- [ ] `dash-services-print`: vista de impresión — migrar con `print:` variants de Tailwind y QA de impresión real (Cmd+P) contra el resultado actual.

### Fase 7 — Auth (0.5 sesión)

- [ ] `auth-login`, `auth-register`, `auth-recoverpw`, `auth-lock-screen`: layout centrado simple, sin sidebar. Rediseño contra prototipo si existe pantalla equivalente; si no, traducción sobria con tokens.

### Fase 8 — Retirada del JS de Bootstrap (1–2 sesiones)

Precondición: todas las vistas migradas visualmente.

- [ ] `components/modal.js` propio con `<dialog>` (§2.6); migrar los 6 modales de `layouts/` y los 9 toggles. Reescribir la integración de `modal-editor.js` (hoy usa API `bootstrap.Modal`).
- [ ] `components/dropdown.js` (o Popover API): header usuario + dropdowns de columna Acción en listas (respetar el comportamiento `data-bs-display="static"` dentro de `table-card` con `overflow: hidden`).
- [ ] Tabs: JS mínimo propio.
- [ ] Quitar `bootstrap.bundle.min.js` de `vendor-scripts.php`. QA exhaustivo de TODOS los modales/dropdowns/tabs (checklist §6).

### Fase 9 — Limpieza final (1 sesión)

- [ ] Quitar de `head-style.php`: `bootstrap.css`, `app.css`, `icons.css`, `style.css` (ya absorbido), `preloader.css` (o versión Tailwind).
- [ ] Quitar de `vendor-scripts.php`: `metismenu`, `node-waves`, `feather-icons`, `pace-js` (según decisión), `simplebar` (según Fase 2).
- [ ] Activar Preflight (`@import "tailwindcss"`) + evaluar quitar `important` → re-QA completo (el reset cambia defaults: márgenes, `box-sizing` ya estaba, estilos de listas, botones).
- [ ] Purga de `assets/libs/`: borrar carpetas bootstrap, metismenu, node-waves, feather-icons, twitter-bootstrap-wizard, datatables-bs4, y auditar el resto (apexcharts, echarts, leaflet, gmaps, fullcalendar… — la mayoría solo las usa `archive/`). ⚠️ Verificar referencias desde `archive/` antes de borrar cualquier lib (regla del proyecto: `archive/` puede referenciarlas; si `archive/` las necesita, se quedan aunque las vistas activas no las usen).
- [ ] Borrar los `.css` del tema y sus `.map` (incluyendo variantes `-rtl`).
- [ ] Medir payload final vs. el ~1 MB inicial y anotarlo acá (dato para el informe de cliente).

### Fase 10 — QA integral + cierre (1 sesión)

- [ ] Checklist §6 completo sobre las 35 vistas, en Chrome + Safari + Firefox y en viewport móvil (el sistema se usa en terreno).
- [ ] Flujos de negocio end-to-end: alta cliente → contrato → asignar baño → servicio → certificado PDF → factura (upload masivo incluido).
- [ ] Impresión: `dash-services-print` + PDFs TCPDF (no deberían verse afectados — TCPDF no usa el CSS del sitio, verificar igual).
- [ ] Actualizar `AGENTS.md` (stack, comandos `pnpm tw:*`, gotchas), nota del vault SitesDoc y decisión en `~/SitesDoc/decisiones/`.
- [ ] Actualizar informe de avance para cliente en lenguaje no técnico (velocidad de carga, look moderno).
- [ ] Mover este plan a `.doc/Viejos/` con estado final.

---

## 5. Estimación

| Fase | Sesiones |
|---|---|
| 0 — Infraestructura | 0.5 |
| 1 — Convivencia | 0.5 |
| 2 — Layouts | 1–2 |
| 3 — Piloto Clientes | 1 |
| 4 — Dashboard + listas | 2–3 |
| 5 — Formularios | 2–3 |
| 6 — Item/detail | 1–2 |
| 7 — Auth | 0.5 |
| 8 — JS Bootstrap out | 1–2 |
| 9 — Limpieza | 1 |
| 10 — QA integral | 1 |
| **Total** | **12–17 sesiones** |

La Fase 3 (piloto) recalibra esto: si la traducción de Clientes toma más de 1 sesión, ajustar el total antes de comprometer plazos con el cliente.

---

## 6. Checklist de QA por vista (repetir en cada fase)

- [ ] Render idéntico o mejor que el estado previo (comparar con screenshot "antes").
- [ ] Responsive: 375 px (móvil), 768 px (tablet), 1440 px (desktop).
- [ ] DataTables (si aplica): sort, paginación, buscador, export CSV/Excel/PDF, selector de columnas.
- [ ] Formularios (si aplica): validación Pristine visible, Choices busca y selecciona, Flatpickr abre en español, RUT mask, submit real crea/edita el registro.
- [ ] Modales: abren, cierran (X, backdrop, ESC), submit funciona.
- [ ] SweetAlert2 (feedback de acciones) sigue estilado coherente.
- [ ] Sin errores en consola del navegador ni warnings en `docker-compose logs php`.
- [ ] Sin fugas de estilo a otras vistas (muestrear 2 vistas no tocadas).
- [ ] `pnpm tw:build` corrido y `tailwind.css` actualizado.

---

## 7. Riesgos y mitigaciones

| Riesgo | Prob. | Impacto | Mitigación |
|---|---|---|---|
| Navegadores viejos en el cliente sin soporte TW4 | ? | Crítico | Validar en Fase 0 **antes** de escribir una línea (§2.8). Plan B: posponer o Tailwind 3.4 |
| `important` global pisa CSS de libs (SweetAlert2, Choices) de forma inesperada | Media | Medio | Piloto Fase 3 lo detecta temprano; los re-skins de libs usan selectores propios, no utilities |
| Colisión `.table`/`.collapse` en vista a medio migrar | Media | Alto | Regla "vista completa o nada" (§2.2.c) + prueba dirigida en Fase 1 |
| `modal-editor.js` acoplado a `bootstrap.Modal` más profundo de lo visto | Media | Medio | Auditarlo al inicio de Fase 8, no al final |
| Olvidar `pnpm tw:build` antes de probar/commitear → producción con CSS viejo | Alta | Medio | Regla en AGENTS.md + checklist §6; evaluar hook de pre-commit más adelante |
| Borrar una lib que `archive/` referencia | Media | Bajo | Grep de referencias en `archive/` antes de cada borrado (Fase 9) |
| Regresión en flujo de facturas (entregable de cotización cliente) | Baja | Alto | QA end-to-end dedicado en Fase 5 |
| Preflight (Fase 9) rompe detalles en vistas ya aprobadas | Alta | Medio | Presupuestado: la Fase 9 incluye re-QA completo, no es "solo borrar archivos" |
| Scope creep: convertir migración visual en rediseño funcional | Media | Alto | Las desviaciones funcionales se anotan y se cotizan aparte (mismo criterio que `plan-customers-rebranding.md` §0.2) |

---

## 8. Rollback

- **Por vista:** git revert del diff de la vista; `tailwind.css` es aditivo (clases extra no usadas no rompen nada) — regenerar con `pnpm tw:build` igualmente.
- **Global (hasta Fase 8):** Bootstrap sigue cargado; quitar `tailwind.css` de `head-style.php` + revertir vistas migradas devuelve el estado actual.
- **Post Fase 9:** el rollback ya no es trivial (Bootstrap eliminado). Antes de ejecutar la Fase 9: branch/tag de respaldo del estado "convivencia estable", y en producción el backup estándar `cp -r /bath /bath-backup-$(date +%F)`.
- Nada de este plan toca base de datos ni controllers — el riesgo es 100% de capa de presentación.

---

## 9. Fuera de alcance (explícito)

- `app/public/archive/` y `app/public/template/` — no se migran ni se borran (regla del proyecto).
- Reescritura de DataTables/jQuery — se mantienen; su eventual reemplazo es otro plan.
- Backend, controllers, DDD, PDFs TCPDF, emails PHPMailer.
- Portar el prototipo React de `rebranding/` como app — el prototipo es referencia visual; la app sigue siendo PHP server-rendered.
- Dark mode — el prototipo no lo define; si se quiere, es un plan posterior (Tailwind lo facilita vía `dark:`).
- Despliegue: este plan cubre solo el entorno local; la replicación a otros entornos se coordina aparte.
