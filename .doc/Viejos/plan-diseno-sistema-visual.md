# Plan — Sistema de diseño visual del panel (Blanco Servicios)

**Origen:** propuesta de diseño consensuada con Edgardo (2026-07-10), a partir de diagnóstico en código + captura en vivo del panel + research de convenciones actuales de dashboards B2B (Linear, Vercel, Retool).
**Estado:** 📋 plan — nada de esto está implementado todavía.
**Audiencia de este documento:** cualquier IA o desarrollador que vaya a implementar una fase, sin contexto previo de la sesión de diseño.

---

## 0. Cómo usar este documento

Cada fase es independiente y auto-contenida: lista los archivos exactos a tocar, el código exacto (antes/después), y un checklist de aceptación. **No implementar dos fases a la vez** — el proyecto no tiene tests automatizados, cada fase se verifica manualmente en `http://localhost` antes de pasar a la siguiente (ver `CLAUDE.md` del proyecto, sección Workflow).

Reglas del proyecto que aplican a todo lo de abajo (de `CLAUDE.md`, no se repiten en cada fase):
- `snake_case` para variables/funciones PHP nuevas, SQL en mayúsculas.
- No mezclar refactors grandes con features — cada fase de este plan es un cambio acotado.
- Después de cualquier cambio de código PHP: `docker-compose up -d --force-recreate php` (o `restart php` si no se tocó `composer.json`/`src/`) + smoke test manual.
- No se introduce build tooling (Sass, PostCSS, bundlers) — todo el CSS se edita directo en los `.css` existentes, sin preprocesado. Es una limitación real del proyecto (legacy, sin Composer para frontend), no una preferencia estética.
- No tocar `app/public/archive/` ni `app/public/assets/libs/` (librerías vendorizadas) salvo que una fase lo diga explícitamente.

---

## 1. Tokens de diseño

### 1.1 Paleta — mapeo completo antes → después

El panel tiene **tres sistemas de color superpuestos que nunca se unificaron**: las variables raíz de Bootstrap (tema "Skote" original, violeta), los overrides puntuales en `style.css` (teal, aplicados a mano en 2015... es un decir, en algún commit viejo), y valores hardcodeados sueltos en vistas y JS. La tabla siguiente es la fuente de verdad única — cualquier color fuera de esta tabla que aparezca en el código es un bug a corregir.

| Rol | Valor final | Dónde vive hoy (y su problema) |
|---|---|---|
| **Primario (teal, marca)** | `#2D5C6C` | `bootstrap.css:32` `--bs-primary` — ✅ ya correcto, no tocar |
| **Primario RGB (para transparencias)** | `45, 92, 108` | `bootstrap.css:40` `--bs-primary-rgb: 81, 86, 190` — ❌ **es el RGB del violeta original, nunca se actualizó cuando se cambió el hex de arriba.** Esta es la causa raíz de que el violeta "destelle" en focus rings, hovers y fondos sutiles en toda la app (`rgba(var(--bs-primary-rgb), ...)` se usa en ~15 selectores de `bootstrap.css`/`app.css`). |
| **Primario texto-énfasis** | `#16232a` | `bootstrap.css:48` `--bs-primary-text-emphasis: #20224c` — violeta oscuro, cambiar |
| **Primario fondo sutil** | `#dde8ea` | `bootstrap.css:56` `--bs-primary-bg-subtle: #b9bbe5` — violeta claro, cambiar |
| **Primario borde sutil** | `#b9d2d7` | `bootstrap.css:64` `--bs-primary-border-subtle: #b9bbe5` — mismo valor que bg-subtle (ya era un bug menor en el original), separar |
| **Éxito / Activo / Pagado** | `#2ab57d` | Ya coincide entre `bootstrap.css:34 --bs-success` y `style.css:42 .item-activo` — no tocar |
| **Peligro / acción destructiva** | `#d5453b` | `bootstrap.css:38 --bs-danger: #fd625e` (framework) y `style.css:48 .item-inactivo: #F52D00` (custom) son dos rojos distintos para el mismo rol semántico. Unificar ambos a `#d5453b`. |
| **Peligro — borde suave de validación** | `#f46a6a` | `style.css:164` (`.is-invalid .form-control`) y `feedback.js:45` (`confirmButtonColor`) — **no tocar**, es un matiz más claro reservado a bordes de error inline; no confundir con el rojo de botones/badges de arriba. Documentado para que quede claro que es intencional, no otro residuo. |
| **Info / Disponible** | `#3B82F6` | `bootstrap.css:35 --bs-info: #4ba6ef` (framework) vs `style.css:53 .item-disponible: #3B82F6` (custom) — dos azules distintos, unificar a `#3B82F6` (ya es el que el usuario ve hoy en los badges). |
| **Atención / Pendiente** | `#b8790a` | No existe hoy como rol propio — se introduce en Fase 3 para "Asignado" (que hoy reutiliza el verde de "Activo", ver hallazgo 2.1). Bootstrap `--bs-warning: #ffbf53` queda intacto, es un ámbar distinto de menor prioridad. |
| **Residuo violeta suelto en vistas** | — | `dash-users-list.php:135` tiene `style="color: #5156be"` inline en el link "Set Admin/User". Es el único caso de violeta hardcodeado *fuera* de los CSS — corregir en Fase 2. |

### 1.2 Tipografía

Se mantiene **Open Sans** (`style.css:1`, ya cargada vía `@import` de Google Fonts, pesos 300–800 disponibles). No hay razón de negocio para migrar de fuente — es legible y ya está integrada; el problema no es la tipografía en sí sino que no tiene escala. Hoy casi todo el texto corre en `font-size: 14px !important` (`style.css:44,47,52`).

Escala tipográfica a introducir (nombrar como clases utilitarias nuevas en `style.css`, prefijo `fs-` para no chocar con las utilidades `font-size-*` que ya trae el template):

| Token | Tamaño | Peso | Uso |
|---|---|---|---|
| `--fs-page-title` | 24px | 700 | `<h4 class="mb-sm-0 font-size-18">` de cada vista → subir a 24px/700 (hoy 18px, mismo peso que subtítulos) |
| `--fs-section-title` | 16px | 700 | `card-title` (ej. "Cantidad de Baños") |
| `--fs-body` | 14px | 400 | texto de tabla, párrafos — sin cambios, ya está bien |
| `--fs-label` | 13px | 600 | labels de formulario — hoy los labels no tienen peso definido, heredan 400 |
| `--fs-caption` | 12px | 600, uppercase, letter-spacing 0.04em | encabezados de columna de tabla, badges pequeños |

### 1.3 Spacing, radio, sombra

No se introduce una escala nueva — Bootstrap 5 ya trae `--bs-border-radius: 0.375rem` (6px) sin override en este proyecto, y se mantiene así por consistencia con el resto del template (botones, cards, inputs ya renderizan con ese radio). Los KPI cards y componentes nuevos de este plan usan el mismo `--bs-border-radius`, no un valor propio.

### 1.4 Bloque de tokens — CSS listo para aplicar

Este bloque va en `app/public/assets/css/style.css`, al principio del archivo, después del `@import` de la fuente (línea 1) y antes de la regla `body` (línea 3):

```css
:root {
    /* Paleta semántica unificada — ver tabla 1.1 para el porqué de cada valor */
    --color-primary: #2D5C6C;
    --color-primary-rgb: 45, 92, 108;
    --color-primary-text-emphasis: #16232a;
    --color-primary-bg-subtle: #dde8ea;
    --color-primary-border-subtle: #b9d2d7;

    --color-success: #2ab57d;
    --color-success-bg: rgba(42, 181, 125, .18);

    --color-danger: #d5453b;
    --color-danger-bg: rgba(213, 69, 59, .18);
    --color-danger-soft: #f46a6a; /* solo bordes de validación inline, no tocar su uso actual */

    --color-info: #3B82F6;
    --color-info-bg: rgba(59, 130, 246, .18);

    --color-warn: #b8790a;
    --color-warn-bg: rgba(184, 121, 10, .18);

    /* Tipografía */
    --fs-page-title: 24px;
    --fs-section-title: 16px;
    --fs-body: 14px;
    --fs-label: 13px;
    --fs-caption: 12px;
}
```

---

## 2. Especificación de componentes

### 2.1 Badges de estado

**Hallazgo:** hoy hay 3 clases (`style.css:41-55`): `.item-activo` (verde), `.item-inactivo` (rojo), `.item-disponible` (azul). La columna "Asignado a Obra" de `dash-bathrooms.php:157` reutiliza `.item-activo` (verde) para el texto "Asignado" — visualmente "Activo" (estado del baño) y "Asignado" (estado de asignación) son indistinguibles si aparecen en la misma fila, porque comparten el mismo verde. Se necesita una 4ª variante.

**Reemplazo completo de `style.css:41-55`:**

```css
.badge-status {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: var(--fs-caption);
    font-weight: 600;
    padding: 3px 10px;
    border-radius: 100px;
}
.badge-status::before {
    content: "";
    width: 6px;
    height: 6px;
    border-radius: 50%;
}
.badge-status.is-success { color: var(--color-success); background-color: var(--color-success-bg); }
.badge-status.is-success::before { background: var(--color-success); }
.badge-status.is-danger { color: var(--color-danger); background-color: var(--color-danger-bg); }
.badge-status.is-danger::before { background: var(--color-danger); }
.badge-status.is-info { color: var(--color-info); background-color: var(--color-info-bg); }
.badge-status.is-info::before { background: var(--color-info); }
.badge-status.is-warn { color: var(--color-warn); background-color: var(--color-warn-bg); }
.badge-status.is-warn::before { background: var(--color-warn); }

/* Compat: alias de las clases viejas mientras se migran las vistas (Fase 3) — borrar cuando no queden referencias */
.item-activo { color: var(--color-success) !important; background-color: var(--color-success-bg) !important; font-size: var(--fs-caption) !important; }
.item-inactivo { color: var(--color-danger) !important; background-color: var(--color-danger-bg) !important; font-size: var(--fs-caption) !important; }
.item-disponible { color: var(--color-info) !important; background-color: var(--color-info-bg) !important; font-size: var(--fs-caption) !important; }
```

**Markup nuevo** (reemplaza `<div class="badge item-...">` por `<span class="badge-status is-...">`):

```html
<span class="badge-status is-success">Activo</span>
<span class="badge-status is-danger">Inactivo</span>
<span class="badge-status is-info">Disponible</span>
<span class="badge-status is-warn">Asignado</span>
```

Nota: se cambia `<div class="badge ...">` por `<span class="badge-status ...">` porque un badge de estado no es un bloque — usar `div` para esto es semánticamente incorrecto y el template original ya lo hacía mal.

### 2.2 Botones y acciones de fila

**Hallazgo (inventario completo, las 7 entidades):**

| Vista | Botones hoy | Ícono | Confirmación antes de ejecutar |
|---|---|---|---|
| `dash-bathrooms.php` | Editar, Activar, Inactivar, Deshacer asignación, Eliminar (5 sueltos, todos `btn-outline-secondary`) | Font Awesome (`fa-pencil-alt`, `fa-lock-open`, `fa-lock`, `fa-level-down-alt`, `fa-trash-alt`) | Solo Eliminar, vía `data-confirm-delete` |
| `dash-customers.php` | Ver, Eliminar (`customer-inactive.php`) | `fa-eye`, `fa-trash-alt` | **Ninguna** |
| `dash-contracts.php` | Agregar Baños, Editar, Inactivar, Activar | `fa-toilet`, `fa-pencil-alt`, `fa-lock`, `fa-lock-open` | **Ninguna** |
| `dash-services.php` | Asignar Baños, Editar, Eliminar/Inactivar | `fa-toilet`, `fa-pencil-alt`, `fa-trash-alt` | **Ninguna** |
| `dash-invoices-list.php` | Imprimir, Agregar Servicios, Editar, Anular | `fa-print`, `fa-plus`, `fa-pencil-alt`, `fa-trash-alt` | Solo Anular, vía `data-confirm-delete` |
| `dash-certificates.php` | Ver, Imprimir, Eliminar | `fa-eye`, `fa-print`, `fa-trash-alt` | **Ninguna** |
| `dash-users-list.php` | dropdown: Editar, Inactivar, Password Default, Set Admin/User | texto plano, sin íconos | **Ninguna** |

**Este plan es de diseño visual, pero el hallazgo de arriba es un problema de seguridad de datos, no estético: 5 de 7 entidades permiten borrar/inactivar/anular registros de un solo click, sin ningún aviso.** El mecanismo para arreglarlo ya existe y funciona (`feedback.js`, wrapper de SweetAlert2, ver `2.2.2`) — falta solo agregar el atributo a los links que no lo tienen. Se incluye como Fase 1 de este plan por ser el cambio de menor riesgo y mayor impacto; no se toca la lógica de los controllers, solo se agrega confirmación antes de llamarlos.

#### 2.2.1 Patrón visual de botones (todas las entidades)

Reemplazar los botones sueltos por: la acción no-destructiva más usada visible (Editar), el resto agrupado en un menú `⋯` (dropdown de Bootstrap, mismo patrón que ya usa `dash-users-list.php`), y la acción destructiva SIEMPRE en rojo dentro del menú, separada del resto por un `<hr>` en el dropdown.

```html
<div class="d-flex align-items-center gap-1 justify-content-center">
    <a href="dash-bathrooms-edit.php?id_Bath=<?php echo $row['id_Bath'] ?>"
       class="btn btn-outline-secondary btn-sm" title="Editar">
        <i class="fas fa-pencil-alt"></i>
    </a>
    <div class="dropdown">
        <button class="btn btn-outline-secondary btn-sm dropdown-toggle dropdown-toggle-split"
                type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-ellipsis-h"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="controller/bath-active.php?id_Bath=<?php echo $row['id_Bath'] ?>"><i class="fas fa-lock-open me-2"></i>Activar</a></li>
            <li><a class="dropdown-item" href="controller/bath-inactive.php?id_Bath=<?php echo $row['id_Bath'] ?>"><i class="fas fa-lock me-2"></i>Inactivar</a></li>
            <li><a class="dropdown-item" href="controller/bath-notassign.php?id_Bath=<?php echo $row['id_Bath'] ?>"><i class="fas fa-level-down-alt me-2"></i>Deshacer asignación</a></li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <a class="dropdown-item text-danger" href="controller/bath-delete.php?id_Bath=<?php echo $row['id_Bath'] ?>"
                   data-confirm-delete
                   data-confirm-title="¿Eliminar este baño?"
                   data-confirm-text="Esta acción no se puede deshacer."
                   data-confirm-confirm-text="Sí, eliminar">
                    <i class="fas fa-trash-alt me-2"></i>Eliminar
                </a>
            </li>
        </ul>
    </div>
</div>
```

CSS de soporte (agregar a `style.css`):

```css
.dropdown-item.text-danger:hover,
.dropdown-item.text-danger:focus {
    background-color: var(--color-danger-bg);
    color: var(--color-danger) !important;
}
```

#### 2.2.2 Confirmaciones faltantes — atributos a agregar por vista (Fase 1)

`feedback.js` ya auto-engancha cualquier elemento con `[data-confirm-delete]` (ver `feedback.js:53-71`) — no requiere JS nuevo, solo agregar los atributos a los `<a>` existentes:

| Archivo | Link a modificar | `data-confirm-title` sugerido | `data-confirm-text` sugerido |
|---|---|---|---|
| `dash-customers.php` | `<a>` hacia `customer-inactive.php` | `¿Inactivar este cliente?` | `Sus contratos activos no se ven afectados, pero no podrá asignársele contratos nuevos.` |
| `dash-contracts.php` | `<a>` hacia inactivar/activar contrato | `¿Inactivar este contrato?` | `Los baños asignados quedarán disponibles para otros contratos.` |
| `dash-services.php` | `<a>` hacia eliminar/inactivar servicio | `¿Eliminar este servicio?` | `Esta acción no se puede deshacer.` |
| `dash-certificates.php` | `<a>` hacia `certificate-remove.php` | `¿Eliminar este certificado?` | `Esta acción no se puede deshacer.` |
| `dash-users-list.php` | `<a>` hacia `user-inactive.php` | `¿Inactivar este usuario?` | `No podrá iniciar sesión hasta que se reactive.` |

No se toca `user-setadmin.php` (cambiar rol no es destructivo en el mismo sentido) ni `user-default-pass.php` — quedan fuera de alcance de este plan, a criterio de Edgardo si se agregan después.

### 2.3 KPI cards del Tablero

Reemplazo del markup en `app/public/index.php` (las 4 tarjetas actuales, sin tendencia ni contexto):

```html
<div class="col-md-3">
    <div class="kpi-card">
        <div class="kpi-card-label">
            <i class="fas fa-toilet"></i> Baños
        </div>
        <div class="kpi-card-value"><?php echo $totalBaths; ?></div>
    </div>
</div>
```

CSS nuevo (agregar a `style.css`):

```css
.kpi-card {
    border: 1px solid var(--bs-border-color);
    border-radius: var(--bs-border-radius);
    background: #fff;
    padding: 16px 18px;
}
.kpi-card-label {
    font-size: var(--fs-caption);
    color: var(--bs-secondary-color);
    display: flex;
    align-items: center;
    gap: 6px;
}
.kpi-card-value {
    font-size: 24px;
    font-weight: 700;
    color: var(--bs-body-color);
    margin-top: 6px;
    font-variant-numeric: tabular-nums;
}
```

La fila de "Contratos por vencer" / "Actividad reciente" bajo los KPI (Fase 5) requiere backend nuevo — se especifica aparte en 3.5, no es solo CSS.

### 2.4 Tablas (Datatables)

No se cambia la librería (Datatables.net + Bootstrap 4 skin ya integrado, `datatable.js` con `spanishLanguage()`). Cambios puramente de CSS:

```css
/* Fila más compacta, alineación consistente */
.dataTable td, .dataTable th {
    padding-top: 10px;
    padding-bottom: 10px;
    vertical-align: middle;
}
.dataTable th {
    font-size: var(--fs-caption);
    text-transform: uppercase;
    letter-spacing: 0.04em;
    font-weight: 600;
    color: var(--bs-secondary-color);
}
.dataTable td.text-center, .dataTable th.text-center {
    text-align: center;
}
```

Aplicar `class="text-center"` a las columnas de Estado, Asignación y Acción en las `<th>`/`<td>` de las 7 tablas de listado (hoy alineadas a la izquierda por defecto).

### 2.5 Formularios

**Hallazgo (inventario completo, 12 formularios de alta/edición):** las 7 entidades no tienen ningún formulario en grid — 0 coincidencias de `col-md-*` en los 12 archivos. Todos son una columna con inputs de ancho fijo desproporcionado (`dash-bathrooms-add.php`: input de "Código del Baño" mide 851px para un valor tipo `AT055`).

Patrón de grid a aplicar (Bootstrap `row`/`col-md-6`, sin librería nueva):

```html
<div class="row">
    <div class="col-md-6">
        <div class="mb-4">
            <label for="codigo_Bath" class="form-label">Código del Baño</label>
            <input type="text" class="form-control" id="codigo_Bath" name="codigo_Bath" required>
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-4">
            <label for="fechaCompra_Bath" class="form-label">Fecha de compra</label>
            <input type="date" class="form-control" id="fechaCompra_Bath" name="fechaCompra_Bath" required>
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="col-12">
        <div class="mb-4">
            <label for="observacion_Bath" class="form-label">Observaciones</label>
            <input type="text" class="form-control" id="observacion_Bath" name="observacion_Bath">
        </div>
    </div>
</div>
```

**Regla de composición de columnas** (para decidir qué campo va en `col-md-6` vs `col-12`):
- `col-12` (ancho completo): `textarea`, campos de tipo `text` cuyo contenido esperado supera ~40 caracteres (direcciones, observaciones), y grupos de checkboxes.
- `col-md-6` (media columna): el resto — texto corto, fechas, selects, números, email.
- `col-md-4` / `col-md-3`: solo si un formulario tiene 3+ campos numéricos cortos en la misma fila lógica (ningún caso actual lo requiere, se deja documentado por si aparece).

**Importante — no romper la validación existente:** `form-validator.js:31` usa `classTo: 'mb-4'` — el wrapper de cada campo **debe seguir siendo `<div class="mb-4">`** (o el error/success de PristineJS deja de pintarse, ver `style.css:162-168`). Al mover a grid, el `.mb-4` va **dentro** del `.col-md-*`, nunca se reemplaza por él.

**Mapeo campo por campo, las 7 entidades** (columna = ancho a aplicar):

| Entidad | Archivo(s) | Campos → columna |
|---|---|---|
| Bathrooms | `dash-bathrooms-add.php`, `dash-bathrooms-edit.php` | `codigo_Bath`→6, `fechaCompra_Bath`→6, `estado_Bath`→6, `observacion_Bath`→12 |
| Certificates | `dash-certificates-add.php` (no tiene edit) | `id_Cliente`→6, `id_Contrato`→6, `mts_Certificado`→6, `fecha_Servicio`→6, `fechahoy_Certificado`→12 |
| Contracts | `dash-contracts-add.php`, `dash-contracts-edit.php` | `id_Cliente`→6, `obra_Contrato`→6, `direccion_Contrato`→12, `estado_Contrato`→6 (solo edit), `fechaInicio_Contrato`→6, `fechaFin_Contrato`→6, `valorMensual_Contrato`→6, `valorTotal_Contrato`→6, `observacion_Contrato`→12 (textarea) |
| Customers | `dash-customers-add.php` (edición es modal, ver 2.6) | `rut_Cliente`→6, `nombre_Cliente`→6, `telefono_Cliente`→6, `email_Cliente`→6, `direccion_Cliente`→12, `region_Cliente`→6, `ciudad_Cliente`→6, `comuna_Cliente`→6 |
| Invoices | `dash-invoices-add.php`, `dash-invoices-edit.php` | `numero_Factura`→6, `fecha_Factura`→6, `id_Cliente`→6, `id_Contrato`→6, `valor_Factura`→6 |
| Services | `dash-services-add.php`, `dash-services-edit.php` | `id_Cliente`→6, `id_Contrato`→6, 9 checkboxes de tipo servicio→12 (grupo, ver nota abajo), `fecha_Servicio`→6, `observaciones_Servicio`→12 (textarea) |
| Users | `dash-users-add.php`, `dash-users-edit.php` | `useremail`→6, `username`→6, `name`→6, `lastname`→6, `password`→6 (solo add), `file`→12 (Dropzone, ya ocupa ancho completo) |

Nota Services: los 9 checkboxes (`instalacion_Tipo` … `retiro_Tipo`) se agrupan en un único `col-12` con `d-flex flex-wrap gap-3` — no cada uno en su propia columna, son un solo grupo lógico de selección.

### 2.6 Modales

**Hallazgo:** `ModalEditor.js` (`modal-editor.js`) es agnóstico de estructura HTML — no impone clases fijas, solo requiere que los campos destino tengan `id` coincidente con el `fieldMap` de la config JS. Hoy solo se usa en `dash-customers-item.php` (edición de cliente + 2 modales de contacto). Este plan **no cambia el mecanismo JS**, solo el estilo visual del modal en sí (header, botones, spacing), que hereda del Bootstrap modal estándar del template.

CSS a agregar a `style.css` para header/footer de modal consistentes con la paleta nueva:

```css
.modal-header {
    border-bottom: 1px solid var(--bs-border-color);
    padding: 16px 24px;
}
.modal-header .modal-title {
    font-size: var(--fs-section-title);
    font-weight: 700;
}
.modal-footer {
    border-top: 1px solid var(--bs-border-color);
    padding: 14px 24px;
}
.modal-body {
    padding: 20px 24px;
}
```

Los formularios **dentro** de modales siguen la misma regla de grid de 2.5 (los 2 modales de contacto en `dash-customers-item.php` son candidatos directos, no están en la tabla de 2.5 porque no son vistas `dash-*-add/edit.php` — se listan aparte en Fase 4 si Edgardo confirma incluirlos).

### 2.7 Iconografía

**Hallazgo:** el panel mezcla 3 librerías de íconos sin un criterio: Font Awesome (`fa`/`fas`, la mayoría de botones de acción en tablas), Boxicons (`bx`, botones "Agregar Nuevo X" y el dropdown de usuarios), Material Design Icons (`mdi`, menú lateral y dropdown de perfil), Feather (`data-feather`, 3 usos sueltos en el header: `home`, `search`, `users`).

Este plan **no propone unificar a una sola librería** — es un cambio de alto esfuerzo (cientos de referencias) y bajo beneficio visible para el usuario final, comparado con el resto de las fases. La recomendación es más acotada: **dentro de cada categoría de uso, ser consistente**, que ya casi se cumple:

- Botones de acción en tablas de listado → Font Awesome (`fas fa-*`), ya es el estándar de facto en 6 de 7 entidades. Al tocar `dash-users-list.php` en Fase 3 (que hoy no usa íconos, solo texto en el dropdown), agregar íconos Font Awesome consistentes con el resto: `fa-pencil-alt` (Editar), `fa-lock` (Inactivar), `fa-key` (Password Default).
- Navegación lateral y header → se deja Material Design Icons + Feather como están, no se tocan en este plan (no generan la inconsistencia visible que sí generan los botones de acción).

---

## 3. Fases de implementación

### Fase 1 — Confirmaciones destructivas faltantes

**Por qué va primero:** no es un cambio visual, es corregir que 5 de 7 entidades permiten borrar/inactivar/anular sin ningún aviso. Cero riesgo de romper nada (solo agrega atributos HTML a links existentes), el mecanismo JS ya funciona en producción hoy mismo en 2 vistas.

**Archivos a tocar:** `dash-customers.php`, `dash-contracts.php`, `dash-services.php`, `dash-certificates.php`, `dash-users-list.php`. Ver tabla completa en 2.2.2.

**Qué hacer en cada uno:** agregar a los `<a>` de la tabla de acciones (Bash de referencia para ubicarlos: `grep -n "controller/.*inactive\|controller/.*delete\|controller/.*remove" app/public/dash-{customers,contracts,services,certificates,users-list}.php`):
```html
data-confirm-delete
data-confirm-title="..."
data-confirm-text="..."
data-confirm-confirm-text="..."
```

**No se toca:** ningún controller PHP, ningún archivo JS. `feedback.js` ya lee estos atributos automáticamente.

**Checklist de aceptación:**
- [ ] En cada una de las 5 vistas, hacer click en la acción destructiva dispara un `Swal.fire` de confirmación antes de navegar
- [ ] Cancelar la confirmación no ejecuta la acción (la URL no cambia)
- [ ] Confirmar sí ejecuta la acción (comportamiento idéntico al actual, solo con el paso previo)
- [ ] `dash-bathrooms.php` y `dash-invoices-list.php` (ya tenían confirmación) siguen funcionando igual — no se tocan en esta fase

---

### Fase 2 — Tokens base + limpieza de residuo violeta

**Archivos a tocar:** `app/public/assets/css/bootstrap.css` (líneas 40, 48, 56, 64, y las versiones dark-mode en 145/153/161 si se quiere cubrir por completitud, opcional/baja prioridad ya que no hay toggle de dark mode activo), `app/public/assets/css/style.css` (agregar bloque de tokens de 1.4), `app/public/dash-users-list.php` (línea 135, quitar `style="color: #5156be"` inline).

**Cambios exactos en `bootstrap.css`:**
```diff
- --bs-primary-rgb: 81, 86, 190;
+ --bs-primary-rgb: 45, 92, 108;
```
```diff
- --bs-primary-text-emphasis: #20224c;
+ --bs-primary-text-emphasis: #16232a;
```
```diff
- --bs-primary-bg-subtle: #b9bbe5;
+ --bs-primary-bg-subtle: #dde8ea;
```
```diff
- --bs-primary-border-subtle: #b9bbe5;
+ --bs-primary-border-subtle: #b9d2d7;
```

**Cambio en `dash-users-list.php:135`:**
```diff
- <a class="dropdown-item cat-admin" href="..." style="color: #5156be">Set Admin/User</a>
+ <a class="dropdown-item cat-admin" href="...">Set Admin/User</a>
```
(el color inline no aporta nada semántico — si se quiere destacar esta acción por ser sensible, usar `text-warning` de Bootstrap en vez de un hex suelto; a criterio de Edgardo, no bloqueante).

**Agregar el bloque completo de la sección 1.4 al principio de `style.css`.**

**Checklist de aceptación:**
- [ ] Ningún elemento de la UI destella violeta al hacer focus/hover (revisar: checkboxes, inputs, botones, sidebar) — comparar antes/después con foco de teclado (Tab) sobre inputs de un formulario
- [ ] El botón "Set Admin/User" en `dash-users-list.php` ya no tiene color inline
- [ ] Los 4 KPI del Tablero, el botón "Ingresar" del login y el sidebar siguen viéndose teal (no deberían cambiar, ya estaban correctos)
- [ ] `grep -rn "81, 86, 190\|5156be\|5b73e8" app/public/assets/css/ app/public/dash-*.php` devuelve 0 resultados fuera de comentarios

---

### Fase 3 — Badges, botones agrupados, iconografía

**Archivos a tocar:** `style.css` (agregar CSS de 2.1 y 2.2.1), y las 7 vistas de listado: `dash-bathrooms.php`, `dash-customers.php`, `dash-contracts.php`, `dash-services.php`, `dash-invoices-list.php`, `dash-certificates.php`, `dash-users-list.php`.

**Por vista:**
1. Reemplazar `<div class="badge item-...">` por `<span class="badge-status is-...">` (ver 2.1) — aplica a `dash-bathrooms.php` (Estado + Asignación, con la corrección de "Asignado" a `is-warn` en vez de reusar `is-success`), y cualquier otra vista con badges de estado similares (`dash-contracts.php`, `dash-services.php`, `dash-invoices-list.php`, `dash-certificates.php` — verificar cuáles tienen badges antes de tocar, no todas los usan).
2. Agrupar botones de acción según el patrón de 2.2.1: 1 botón visible (Editar u otra acción primaria de esa entidad) + dropdown `⋯` con el resto + la acción destructiva en rojo (`text-danger`) al final, separada con `<hr class="dropdown-divider">`.
3. `dash-users-list.php`: agregar íconos Font Awesome a los `dropdown-item` que hoy son texto plano (ver 2.7).

**Depende de Fase 1:** los `data-confirm-delete` agregados en Fase 1 se mantienen al mover el link dentro del dropdown — no se pierden, solo cambia el contenedor HTML alrededor del `<a>`.

**Checklist de aceptación:**
- [ ] En las 7 vistas de listado, cada fila muestra máximo 2 elementos de acción visibles (botón + dropdown), no 3+ botones sueltos
- [ ] La acción destructiva es visualmente roja dentro del dropdown en las 7 vistas
- [ ] Los badges de estado usan `.badge-status`, no las clases viejas `.item-*` (excepto donde se decida no migrar por bajo uso — documentar cuál si aplica)
- [ ] "Asignado" y "Activo" en `dash-bathrooms.php` ya no comparten el mismo color

---

### Fase 4 — Formularios a grid de 2 columnas

**Archivos a tocar:** los 12 formularios listados en la tabla de 2.5, más (si Edgardo confirma) los 2 modales de contacto dentro de `dash-customers-item.php`.

**Por archivo:** envolver los campos en `<div class="row">`, cada campo en `<div class="col-md-6">` o `<div class="col-12">` según la tabla de 2.5, preservando el wrapper `<div class="mb-4">` intacto dentro de cada columna (ver nota de PristineJS en 2.5 — es el punto que más fácil se rompe si no se respeta).

**Orden recomendado dentro de la fase** (de menor a mayor cantidad de campos, para validar el patrón en un caso simple antes de los formularios grandes): Bathrooms (4 campos) → Invoices (5) → Certificates (5) → Customers (8) → Contracts (9) → Users (6, pero con Dropzone que ya ocupa ancho completo) → Services (11-13, el más complejo por el grupo de checkboxes).

**Checklist de aceptación (repetir por cada uno de los 12 archivos):**
- [ ] El formulario se ve en 2 columnas en desktop (≥768px) y colapsa a 1 columna en mobile (Bootstrap `col-md-6` ya hace esto automático, verificar que no se rompió)
- [ ] Enviar el formulario vacío sigue mostrando los mensajes de error de PristineJS en rojo bajo cada campo (confirma que `.mb-4` no se movió fuera de la columna)
- [ ] Ancho de cada input es proporcional a su contenido esperado (código corto no mide 850px)
- [ ] El submit del formulario sigue guardando correctamente (no se tocó ningún `name` de input, solo el wrapper visual)

---

### Fase 5 — Tablero con contenido real

**Alcance mayor que las anteriores — requiere backend, no es solo CSS/HTML.** Se documenta la forma, la implementación de detalle queda para cuando se aborde esta fase específicamente.

**Objetivo:** agregar bajo los 4 KPI (ya migrados a `.kpi-card` en Fase 2/3) un panel de "Contratos por vencer" (contratos con `fechaFin_Contrato` dentro de los próximos 7 días) y opcionalmente "Actividad reciente".

**Archivos:**
- `app/src/Application/Contract/` — nuevo Use Case de lectura (ej. `ListContractsExpiringSoon`), siguiendo el patrón DDD ya establecido (ver `ListBathrooms` en `dash-bathrooms.php:8-16` como referencia de la forma: Use Case + Repository, sin lógica en la vista).
- `app/public/index.php` — invocar el nuevo Use Case y pintar el panel con el markup de `.mock-panel`/`.mock-list-item` (ver componente en la propuesta visual original, sección "Antes/Después" del artifact de diseño).

**Checklist de aceptación:**
- [ ] El panel de "Contratos por vencer" muestra datos reales de la tabla `contratos`, no datos de ejemplo
- [ ] Un contrato sin vencimiento próximo no aparece (query filtra correctamente por fecha)
- [ ] La consulta no genera N+1 (usar el mismo patrón de Repository ya validado en la migración DDD de Contract)

---

## 4. Fuera de alcance de este plan

- Migración de tipografía a Inter/Geist — evaluada y descartada por ahora (ver 1.2); Open Sans se mantiene.
- Unificación de las 4 librerías de íconos a una sola — alto esfuerzo, bajo beneficio visible, ver 2.7.
- Dark mode — el proyecto no tiene toggle activo, las variables `[data-bs-theme=dark]` de `bootstrap.css` no se tocan salvo mención opcional en Fase 2.
- Cualquier cambio a `app/public/archive/` (77 templates legacy, no se sabe con certeza qué está referenciado).
- Rotación de la password `Guns026772` reusada entre `DB_PASSWORD` de producción y el login de prueba usado para las capturas de este plan — **hallazgo de seguridad real, no relacionado a diseño**, señalado a Edgardo aparte, pendiente de que él decida cuándo rotarla.

---

## 5. Referencia — captura del estado "antes"

Las capturas usadas para el diagnóstico (login, tablero, listado de baños, formulario de alta) se tomaron en vivo el 2026-07-10 contra `http://localhost` con datos de test. No se adjuntan a este documento (viven en el artifact de la propuesta visual, fuera del control de versiones del repo). Si se necesita volver a compararlas contra el estado real en una fase futura, repetir la captura contra el ambiente local vigente en ese momento — los datos de ejemplo (cantidad de baños, contratos, etc.) van a haber cambiado.
