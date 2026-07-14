# Plan de mejoras: componentes UI reutilizables

**Proyecto:** php-bathroom
**Fecha del análisis:** 2026-07-06
**Relacionado:** [`plan-migracion-php-8.5.md`](./plan-migracion-php-8.5.md), [`plan-migracion-ddd-drycode.md`](./plan-migracion-ddd-drycode.md)

---

## 1. Resumen ejecutivo

Recorrí las 27 vistas `dash-*.php`, los 19 controllers que disparan feedback, los `layouts/modal-*.php` y los `assets/js/`. Hay **tres hallazgos estructurales** que motivan todo el plan:

1. **El sistema YA TIENE todas las librerías frontend necesarias** en `app/public/assets/libs/` (Choices.js, Flatpickr, SweetAlert2, IMask, PristineJS, Dropzone, DataTables con StateRestore, Feather Icons, Glightbox). Pero **no se usan** — el código reimplementa a mano lo que cada librería ya resuelve.
2. **El script de inicialización de DataTables está duplicado inline en 4 vistas** (`dash-bathrooms.php`, `dash-bathrooms-contracts.php`, `dash-services.php`, `dash-invoices-list.php`), con pequeñas variaciones. Existe un init compartido en `assets/js/pages/datatables.init.js` pero no se incluye en ninguna vista — solo se usa el de la librería base `datatable-pages.init.js` (otro archivo distinto con menos config).
3. **Hay 15+ `<select>` de catálogo en formularios** (clientes, contratos, servicios, regiones) que se llenan con `mysqli_fetch_all` + `foreach` y no tienen buscador ni orden alfabético. Choices.js está literalmente en la carpeta `libs/` y nunca se importa.

El plan introduce **4 componentes UI reutilizables**, más un sistema de feedback y validación, priorizando el uso de las librerías ya instaladas (cero npm install, cero downloads, cero Composer).

**Importante — qué NO se introduce:** ningún framework frontend (React/Vue/Svelte), ningún build pipeline, ningún bundler. Se mantiene jQuery + Bootstrap + assets estáticos, que es el stack actual. Esto respeta la decisión ya tomada en el plan DDD de no sobredimensionar la arquitectura.

---

## 2. Inventario de librerías disponibles vs usadas

| Librería | En `assets/libs/` | Usada actualmente | Oportunidad |
|---|---|---|---|
| **DataTables core + Buttons + Responsive** | ✓ | Parcial (script init duplicado en 4 vistas) | Consolidar init compartido + features avanzadas |
| **DataTables Select** | ✓ | No | Selección múltiple de filas + acciones en lote |
| **DataTables StateRestore** | ✓ | No | Persistir filtros/orden/página entre recargas |
| **Choices.js** | ✓ | **No** | Selects con buscador + orden alfabético |
| **Flatpickr** (con `l10n/es.js`) | ✓ | **No** | Date picker consistente cross-browser, en español |
| **SweetAlert2** | ✓ | **No** | Reemplazar 19 `alert()` nativos + confirms de borrado (¡no hay!) |
| **IMask** | ✓ | **No** | Formato RUT chileno `12.345.678-9` con validación de DV |
| **PristineJS** | ✓ | **No** | Validación de formularios en español (hoy solo `required` HTML5) |
| **Dropzone** | ✓ | **No** | Drag & drop con preview para fotos de usuario |
| **Feather Icons** | ✓ | Parcial | Estandarizar iconos (hoy mezcla FA + otros) |
| **Glightbox** | ✓ | No | Preview de PDFs e imágenes en modales |
| **AlertifyJS** | ✓ | No | (alternativa a SweetAlert2 — descartada, usamos SW2) |
| **ApexCharts / Chart.js / ECharts** | ✓ | Solo en `dashboard.init.js` | (sin cambios, ya funciona) |

**Conclusión:** 12 librerías disponibles, 9 subutilizadas o sin usar. No hace falta instalar nada nuevo para resolver los problemas planteados.

---

## 3. Componente 1: `DataTable` potenciado (wrapper compartido)

### 3.1 — Diagnóstico

Hoy hay **dos archivos** intentando ser "el init de DataTables":

| Archivo | Usado en | Configuración |
|---|---|---|
| `assets/js/pages/datatable-pages.init.js` | `template/template.php` y vistas simples | lengthMenu `[50, 100, "All"]`, botones `copy/excel/pdf/colvis`, idioma español |
| `assets/js/pages/datatables.init.js` | **Nadie lo incluye** — huérfano | Similar, sin colvis |
| Script inline en `dash-bathrooms.php:206`, `dash-bathrooms-contracts.php:193`, `dash-services.php:197`, `dash-invoices-list.php:217` | Esas 4 vistas | Variantes con `order`, `columnDefs`, `responsive: true` |

Cada script inline tiene ~45 líneas de configuración duplicada + idioma español hardcodeado (¡4 veces!).

### 3.2 — Componente objetivo

Un único módulo **`assets/js/components/datatable.js`** que centralice toda la config y permita inicializar cualquier tabla con una sola línea + opciones específicas:

```javascript
// Antes (45 líneas inline por vista)
$('#datatable-buttons').DataTable({ lengthMenu: [...], buttons: [...], language: {...} });

// Después (1 línea)
DataTable.init('#datatable-buttons', {
  order: [[2, 'desc']],
  columnDefs: [{ targets: 2, type: 'date' }],
});
```

### 3.3 — Features nuevas que se habilitan

Configurables vía `data-*` attributes en el HTML (sin tocar JS por cada vista):

| Feature | Cómo se activa | Beneficio |
|---|---|---|
| **Selección múltiple** | `data-dt-select="true"` | Checkboxes por fila + botón "Acciones en lote" (ej: inactivar varios) |
| **Persistencia de estado** | `data-dt-state="true"` | Filtros/orden/página sobreviven al refresh (DataTables StateRestore) |
| **Filtros por columna** | `data-dt-column-filters="true"` | Inputs de búsqueda arriba de cada columna (mejor UX que la búsqueda global) |
| **Exportar con logo y header** | `data-dt-export-brand="true"` | PDFs de exportación con logo Donbano + fecha |
| **Refresh manual** | `data-dt-refresh="true"` | Botón "Actualizar" para tablas con datos que cambian |
| **Sticky header** | `data-dt-sticky="true"` | Header fijo al scrollear (útil en invoices con cientos de filas) |
| **Botón eliminar en lote** | `data-dt-batch-delete="controller-x.php"` | Confirm con SweetAlert2, POST por AJAX |
| **Row details** | `data-dt-row-details="true"` | Click en fila expande detalles (formato child row de DataTables) |

### 3.4 — Estructura del módulo

```
app/public/assets/js/
├── components/
│   ├── datatable.js          ← init único, opciones por data-attrs
│   ├── select-enhanced.js    ← Choices.js wrapper (sección 4)
│   ├── datepicker.js         ← Flatpickr wrapper (sección 5)
│   ├── rut-mask.js           ← IMask para RUT chileno (sección 6)
│   ├── feedback.js           ← SweetAlert2 + toasts (sección 7)
│   ├── form-validator.js     ← PristineJS wrapper (sección 8)
│   └── modal-editor.js       ← patrón unificado de modal de edición (sección 9)
└── pages/
    └── datatable-pages.init.js   ← deprecado, redirect a components/datatable.js
```

### 3.5 — Migración de las 4 vistas con script inline

Por cada vista, reemplazar el `<script>` inline de 45 líneas por:

```html
<table id="datatable-buttons"
       class="table align-middle datatable dt-responsive table-check nowrap w-100"
       data-dt-select="true"
       data-dt-state="true"
       data-dt-column-filters="true"
       data-dt-export-brand="true">
  <!-- ... -->
</table>
```

Al final del archivo:
```html
<script src="assets/js/components/datatable.js"></script>
<script>
  DataTable.init('#datatable-buttons', {
    order: [[2, 'desc']],
    columnDefs: [{ targets: 2, type: 'date' }],
  });
</script>
```

Las 4 vistas: `dash-bathrooms.php`, `dash-bathrooms-contracts.php`, `dash-services.php`, `dash-invoices-list.php`. Commit atómico por vista.

---

## 4. Componente 2: `Select` con buscador y orden alfabético (Choices.js)

### 4.1 — Diagnóstico

Hoy los selects se ven así (ejemplo real de `dash-contracts-add.php:63`):

```html
<select name="id_Cliente" id="id_Cliente" class="form-select">
    <option value="" selected>Seleccionar un Cliente</option>
    <?php
        $sql = "SELECT * FROM clientes";
        $result = mysqli_query($link, $sql);
        $clientes = mysqli_fetch_all($result, MYSQLI_ASSOC);
        foreach ($clientes as $cliente) { ?>
            <option value="<?= $cliente['id_Cliente']; ?>">
                <?= $cliente['nombre_Cliente'] ?>
            </option>
    <?php } ?>
</select>
```

**Problemas:**
1. Sin buscador (con 100+ clientes se vuelve unusable).
2. Sin orden alfabético — el orden es el de inserción en DB.
3. Carga todos los clientes siempre (sin filtro por estado).
4. SQL inline en la vista (se podría centralizar).
5. Expuesto a XSS si `nombre_Cliente` contiene `<script>`.

**Selects identificados** (15+ ocurrencias en 7 entidades):

| Vista | Select | Fuente de datos |
|---|---|---|
| `dash-contracts-add.php`, `dash-contracts-edit.php` | `id_Cliente`, `estado_Contrato` | clientes (sin filtro), hardcoded |
| `dash-certificates-add.php` | `id_Cliente`, `id_Contrato` | clientes + contratos (sin filtro) |
| `dash-services-add.php` | `id_Cliente`, `id_Contrato` | clientes + contratos (sin filtro) |
| `dash-invoices-add.php` | `id_Cliente`, `id_Contrato` | clientes + contratos (sin filtro) |
| `dash-invoices-detail.php` | `id_Servicio` | servicios (sin filtro) |
| `dash-bathrooms-add.php`, `dash-bathrooms-edit.php` | `estado_Bath` | hardcoded |
| `dash-customers-add.php` | `region_Cliente` | hardcoded (debería ser dinámico) |
| `dash-users-add.php`, `dash-users-edit.php` | `user_Rol`, `user_Estado` | hardcoded |
| `layouts/modal-*` | varios | varios |

### 4.2 — Componente objetivo

**Frontend:** `assets/js/components/select-enhanced.js` que inicializa Choices.js sobre cualquier `<select>` con `data-enhanced-select`:

```javascript
SelectEnhanced.init('[data-enhanced-select]', {
  searchEnabled: true,       // buscador integrado
  shouldSort: true,          // orden alfabético por label
  searchPlaceholderValue: 'Buscar...',
  noResultsText: 'Sin resultados',
  itemSelectText: 'Click para seleccionar',
});
```

**Uso en HTML (cero cambios al PHP):**

```html
<select name="id_Cliente" id="id_Cliente" class="form-select"
        data-enhanced-select
        data-search-placeholder="Buscar cliente por nombre o RUT...">
    <!-- options existentes -->
</select>
```

**Backend (cambios opcionales):**

Crear un helper PHP `app/public/layouts/select_options.php` que centralice el render:

```php
<?php
// select_options.php — incluye layouts/config.php antes
function render_select_clientes(string $name, string $id, ?int $selected = null): string {
    $sql = "SELECT id_Cliente, nombre_Cliente, rut_Cliente FROM clientes WHERE estado_Cliente = 1 ORDER BY nombre_Cliente ASC";
    $result = mysqli_query($GLOBALS['link'], $sql);
    $options = '<option value="">Seleccionar un Cliente</option>';
    while ($row = mysqli_fetch_assoc($result)) {
        $sel = ((int)$row['id_Cliente'] === $selected) ? ' selected' : '';
        $label = htmlspecialchars($row['nombre_Cliente'] . ' (' . $row['rut_Cliente'] . ')', ENT_QUOTES, 'UTF-8');
        $options .= "<option value=\"{$row['id_Cliente']}\"{$sel}>{$label}</option>";
    }
    return $options;
}

function render_select_contratos(string $name, string $id, int $id_cliente, ?int $selected = null): string { /* ... */ }
function render_select_servicios(string $name, string $id, int $id_contrato, ?int $selected = null): string { /* ... */ }
function render_select_regiones(string $name, string $id, ?string $selected = null): string { /* ... */ }
```

Y en la vista:

```php
<?php
require_once __DIR__ . '/../layouts/select_options.php';
?>
<select name="id_Cliente" id="id_Cliente" class="form-select"
        data-enhanced-select>
    <?= render_select_clientes('id_Cliente', 'id_Cliente') ?>
</select>
```

### 4.3 — Beneficios concretos

| Antes | Después |
|---|---|
| 100 clientes en dropdown plano, sin orden | Lista alfabética con buscador en español |
| SQL `SELECT *` sin filtro (incluye clientes inactivos) | Solo clientes activos, con JOIN opcional al RUT |
| `<option><?= $cliente['nombre_Cliente'] ?></option>` (XSS si el nombre tiene `<script>`) | `htmlspecialchars` automático |
| 4-5 vistas duplicando el mismo `foreach` | Helper único, una sola fuente de verdad |
| Sin filtro por contrato del cliente seleccionado | Selects en cascada (cliente → contratos de ese cliente → servicios de ese contrato) |

### 4.4 — Selects en cascada (mejora extra)

Hoy los formularios como `dash-invoices-add.php` tienen selects independientes de cliente y contrato — el usuario puede asignar un contrato que no pertenece al cliente seleccionado. Solución:

```javascript
SelectEnhanced.cascade('[data-enhanced-select]', {
  parent: 'id_Cliente',
  child: 'id_Contrato',
  endpoint: 'controller/obtener_contratos.php',
  paramName: 'id_Cliente',
});
```

`obtener_contratos.php` ya existe — solo necesita refactor (prepared statements, parte del plan DDD).

---

## 5. Componente 3: Date picker consistente (Flatpickr)

### 5.1 — Diagnóstico

12+ inputs `type="date"` con UX inconsistente (Chrome muestra un picker nativo decente; Safari muestra un spinner horrible; Firefox muestra otro distinto). Sin localización (los meses salen en inglés en algunos navegadores). Sin restricciones de fechas (un servicio puede tener fecha del futuro lejano).

### 5.2 — Componente objetivo

**Frontend:** `assets/js/components/datepicker.js`:

```javascript
Datepicker.init('[data-datepicker]', {
  locale: 'es',
  dateFormat: 'Y-m-d',
  allowInput: true,
});
```

**Casos de uso especiales** (configurables por data-attrs):

| Caso | Atributo | Configuración |
|---|---|---|
| Fecha de servicio (no en el pasado) | `data-datepicker-min="today"` | `minDate: 'today'` |
| Fecha de fin de contrato (después del inicio) | `data-datepicker-min-from="#fechaInicio_Contrato"` | `minDate: { from: selector }` |
| Rango de fechas (ej: filtro "entre X e Y") | `data-datepicker-range="true"` | `mode: 'range'` |
| Fecha con hora (visitas, mantenimientos) | `data-datepicker-time="true"` | `enableTime: true` |
| Solo fechas pasadas (ej: fecha de compra de baño) | `data-datepicker-max="today"` | `maxDate: 'today'` |

**Uso en HTML:**

```html
<input type="date" id="fechaInicio_Contrato" name="fechaInicio_Contrato"
       class="form-control" data-datepicker required>
```

Flatpickr detecta el `type="date"` y lo reemplaza visualmente, pero **el name y el formato enviado al backend (`Y-m-d`) son idénticos** → cero cambios en controllers ni en la base de datos.

### 5.3 — Archivos a modificar

12 inputs identificados: `dash-contracts-{add,edit}.php` (4), `dash-bathrooms-{add,edit}.php` (2), `dash-services-{add,edit}.php` (2), `dash-invoices-add.php` (1), `dash-certificates-add.php` (1), `layouts/modal-edit-bath.php` (1).

---

## 6. Componente 4: Input de RUT chileno con máscara y validación (IMask)

### 6.1 — Diagnóstico

6 inputs de RUT en el sistema, todos con `<input type="number">` — esto es **incorrecto**:
- El dígito verificador puede ser `K` (no es número).
- No se valida formato ni DV en tiempo real.
- El usuario escribe `12345678` y queda así en DB; en otros lados se muestra sin formato.

```html
<!-- Patrón actual (incorrecto) -->
<input type="number" class="form-control" id="rut_Cliente" name="rut_Cliente" required>
```

Encontrados en: `dash-customers-add.php:61`, `dash-customers-item.php`, `layouts/modal-edit-customer.php:21`, `layouts/modal-editar-contacto.php:33`, `layouts/modal-ver-contacto.php:32`, `layouts/modal-nuevo-contacto.php:33`.

### 6.2 — Componente objetivo

**Frontend:** `assets/js/components/rut-mask.js`:

```javascript
RutMask.init('[data-rut-mask]', {
  validateOnBlur: true,        // valida DV al perder foco
  formatOnType: true,          // aplica `12.345.678-9` mientras tipea
  showFeedback: true,          // muestra ✓/✗ al lado del input
});
```

**Uso en HTML:**

```html
<input type="text" class="form-control" id="rut_Cliente" name="rut_Cliente"
       placeholder="12.345.678-9" data-rut-mask required>
```

### 6.3 — Validación de DV en backend (pendiente del plan DDD)

El plan DDD ya menciona `App\Shared\Rut.php` como Value Object. El frontend es la mitad del trabajo — la otra mitad es validar en el servidor. Esto se hace cuando se migre la entidad `clientes` al patrón Repository (sección 4 del plan DDD).

### 6.4 — Compatibilidad

IMask es **independiente** del refactor DDD: se puede implementar hoy sin tocar el backend. Cuando llegue el Value Object `Rut`, se elimina la validación duplicada del frontend para que solo el backend sea autoridad.

---

## 7. Sistema de feedback (SweetAlert2)

### 7.1 — Diagnóstico

**19 controllers** terminan con `echo '<script>alert("No se pudo ...")</script>'`. Sin feedback de éxito, sin confirmaciones de borrado, sin toasts.

**Búsqueda:**
```bash
grep -rn 'alert(\|confirm(' app/public/controller/  # 19 matches, 0 confirms
```

**No hay un solo `confirm()`** — los usuarios pueden eliminar registros haciendo click sin confirmación. Riesgo operacional alto.

### 7.2 — Sistema objetivo

**Frontend:** `assets/js/components/feedback.js`:

```javascript
// Reemplazo de alert()
Feedback.error('No se pudo crear el cliente');
Feedback.success('Cliente creado correctamente');
Feedback.toast('Contrato actualizado');  // no bloqueante

// Confirmación antes de eliminar
Feedback.confirm({
  title: '¿Eliminar baño?',
  text: 'Esta acción no se puede deshacer.',
  confirmText: 'Sí, eliminar',
  cancelText: 'Cancelar',
  onConfirm: () => { /* fetch POST a controller/delete */ },
});
```

### 7.3 — Cambios en controllers

Reemplazar el patrón actual:

```php
// ANTES
echo '<script>alert("No se pudo crear el cliente")</script>';
header('Location: ../dash-customers.php');

// DESPUÉS
header('Location: ../dash-customers.php?status=error&msg=' . urlencode('No se pudo crear el cliente'));
```

Y la vista `dash-customers.php` lee el query string al cargar:

```javascript
// Al final de cada vista con tabla
document.addEventListener('DOMContentLoaded', () => {
  const params = new URLSearchParams(window.location.search);
  if (params.get('status') === 'success') {
    Feedback.toast(params.get('msg') || 'Operación exitosa');
  } else if (params.get('status') === 'error') {
    Feedback.error(params.get('msg') || 'Error en la operación');
  }
});
```

### 7.4 — Confirmaciones en eliminación

Reemplazar los botones `<a href="controller/x-delete.php?id=...">` por botones que disparan `Feedback.confirm`:

```html
<!-- ANTES -->
<a href="controller/bath-delete.php?id_Bath=5" class="btn btn-danger">Eliminar</a>

<!-- DESPUÉS -->
<button class="btn btn-danger" data-confirm-delete
        data-endpoint="controller/bath-delete.php"
        data-id="5" data-id-field="id_Bath"
        data-name="Baño #BA-001">Eliminar</button>
```

---

## 8. Validación de formularios (PristineJS)

### 8.1 — Diagnóstico

Los formularios usan `class="needs-validation"` (Bootstrap 5) **pero la inicialización no está incluida en ninguna vista** → el patrón no funciona nunca. Solo se valida con `required` HTML5 nativo (mensaje genérico del navegador, en inglés).

### 8.2 — Componente objetivo

**Frontend:** `assets/js/components/form-validator.js`:

```javascript
FormValidator.init('form[data-validate]', {
  locale: 'es',
  classTo: 'mb-4',             // clase Bootstrap para el contenedor del mensaje
  errorClass: 'is-invalid',
  successClass: 'is-valid',
});
```

**Uso:**

```html
<form class="needs-validation" method="post" action="controller/x.php" data-validate>
  <input class="form-control" required minlength="3" data-pristine-required-message="Este campo es obligatorio">
  <input type="email" data-pristine-type-email-message="Email no válido">
</form>
```

### 8.3 — Validaciones custom soportadas

| Validador | Atributo | Mensaje default |
|---|---|---|
| RUT chileno | `data-pristine-rut` | "RUT inválido" |
| Teléfono (8-12 dígitos) | `data-pristine-phone` | "Teléfono inválido" |
| Email | `type="email"` | "Email no válido" |
| Confirmación de password | `data-pristine-equals="#password"` | "Las contraseñas no coinciden" |

---

## 9. Modal de edición unificado

### 9.1 — Diagnóstico

Hay **3 modales de edición** con el mismo patrón:

- `layouts/modal-edit-customer.php` (89 líneas)
- `layouts/modal-edit-bath.php`
- `layouts/modal-editar-contacto.php`

Y **3 scripts AJAX** que los alimentan (uno por entidad):

- `assets/js/customer-edit.js` (27 líneas, jQuery AJAX)
- `assets/js/bathEdit.js`
- `assets/js/contactoEdit.js`

Cada script repite el patrón: click → fetch → `JSON.parse` → `$('#campo').val(resp.campo)`. **DRY roto en frontend.**

### 9.2 — Componente objetivo

**Frontend:** `assets/js/components/modal-editor.js`:

```javascript
ModalEditor.init({
  modalId: 'editarCliente',
  triggerSelector: '.editarCliente',
  endpoint: 'controller/customer-get.php',
  fieldMap: {
    idCliente: 'id_Cliente',
    rutCliente: 'rut_Cliente',
    nombreCliente: 'nombre_Cliente',
    // ...
  },
  onLoaded: (data) => { /* hook opcional */ },
});
```

**Uso en HTML:**

```html
<a href="#" class="editarCliente btn btn-primary"
   data-id="<?= $row['id_Cliente'] ?>">Editar</a>

<?php include 'layouts/modal-edit-customer.php'; ?>
```

Una sola inicialización por entidad en el `<script>` final. Elimina 3 archivos JS y reduce ~80 líneas de código.

---

## 10. Otras oportunidades detectadas

Recorriendo el sistema encontré mejoras adicionales, de menor prioridad pero de alto impacto visual:

### 10.1 — Iconografía inconsistente

Mezcla de FontAwesome y otros sets. `feather-icons` ya está instalado. Sugerencia: estandarizar en Feather en todo el panel (es el set del theme Skote que el template usa).

### 10.2 — Glightbox para PDFs e imágenes

Cuando un usuario hace click en un PDF de factura o un certificado, hoy se abre en una pestaña nueva. Glightbox permite preview inline con download incluido. Aplicable en: `dash-invoices-list.php`, `dash-certificates.php`.

### 10.3 — Drag & drop para foto de usuario

`dash-users-add.php:108` y `dash-users-edit.php:76` usan `<input type="file">` nativo. Dropzone permite preview + drag & drop + validación. Aplicable a ambos.

### 10.4 — Loading states en botones de submit

Cuando un form se envía, el botón no muestra estado de carga. Riesgo: doble submit. Solución: `FormValidator.onSubmit` deshabilita el botón y muestra spinner.

### 10.5 — Helpers JS globales

Crear `assets/js/app-helpers.js` con:
- `formatCurrency(number)` — formato chileno `$1.234.567`
- `formatRut(string)` — `12345678` → `12.345.678-9`
- `parseDate(string, format)` — wrapper sobre Flatpickr.parseDate

---

## 11. Plan de ejecución por sprints

### Sprint 1 — Quick wins (1-2 días, sin riesgo)
1. `assets/js/components/feedback.js` (SweetAlert2) → reemplazar los 19 `alert()` en controllers
2. `assets/js/components/form-validator.js` (PristineJS) → agregar `data-validate` a los 8 forms principales
3. `assets/js/components/rut-mask.js` (IMask) → los 6 inputs de RUT

**Impacto:** feedback visual profesional, sin cambios en backend.

### Sprint 2 — Tabla potenciada (2-3 días)
1. Crear `assets/js/components/datatable.js` con todas las opciones (select, state, filters, brand export)
2. Migrar las 4 vistas con script inline (`dash-bathrooms`, `dash-bathrooms-contracts`, `dash-services`, `dash-invoices-list`)
3. Marcar `datatable-pages.init.js` como deprecado

**Impacto:** tablas con filtros por columna, selección múltiple, persistencia, export branded.

### Sprint 3 — Selects con buscador (2-3 días)
1. Crear `assets/js/components/select-enhanced.js` (Choices.js)
2. Crear `layouts/select_options.php` con helpers PHP
3. Migrar los 15+ selects a `data-enhanced-select`
4. Implementar selects en cascada (cliente → contratos → servicios)

**Impacto:** formularios usables con catálogos grandes, sin XSS.

### Sprint 4 — Date pickers + extras (1-2 días)
1. Crear `assets/js/components/datepicker.js` (Flatpickr)
2. Migrar los 12 inputs `type="date"`
3. Modal editor unificado (`modal-editor.js`) → elimina 3 scripts duplicados
4. Glightbox en invoices y certificates
5. Dropzone en upload de usuarios

**Impacto:** UX consistente, código más mantenible.

---

## 12. Definition of Done por sprint

### Sprint 1
- [ ] Cero `alert(` en código propio (controllers)
- [ ] Cada form principal tiene `data-validate` y muestra errores en español
- [ ] Inputs de RUT muestran formato `12.345.678-9` automáticamente

### Sprint 2
- [ ] `datatable.js` cubre el 100% de las features necesarias
- [ ] Las 4 vistas con script inline ya no lo tienen
- [ ] Selección múltiple funciona y elimina en lote (con confirm)
- [ ] Estado de filtros persiste al refresh

### Sprint 3
- [ ] Choices.js inicializado en todos los selects de catálogo
- [ ] Helper `select_options.php` con orden alfabético + filtro de activos
- [ ] Selects en cascada funcionan en al menos `dash-invoices-add.php`

### Sprint 4
- [ ] Todos los date inputs tienen Flatpickr en español
- [ ] `customer-edit.js`, `bathEdit.js`, `contactoEdit.js` eliminados
- [ ] Preview de PDFs en `dash-invoices-list.php`

---

## 13. Compatibilidad con los otros planes

| Plan | Interacción |
|---|---|
| **Plan PHP 8.5** | Ninguna. Estos componentes son JS/CSS puros, no requieren cambio de versión PHP. |
| **Plan DDD** | Complementario. Los componentes frontend **reducen el trabajo del backend**: el helper `select_options.php` se beneficia del Repository `CustomerRepositoryInterface` cuando exista. Los selects en cascada usan `controller/obtener_contratos.php` (que el plan DDD migra a Repository). La validación RUT en frontend es complementaria al Value Object `Rut` del plan DDD. |

**Orden sugerido de ejecución:** este plan primero (mejoras visibles, sin riesgo), luego plan DDD (refactor arquitectónico), luego plan PHP 8.5 (upgrade de runtime).

---

## 14. Lo que NO entra en este plan

- **No** se introduce framework frontend (React, Vue, Svelte, Alpine).
- **No** se introduce build pipeline (Vite, Webpack, esbuild).
- **No** se cambian las convenciones PHP (se mantiene procedural).
- **No** se reemplazan las 12 librerías disponibles — se usan.
- **No** se cambia la UI de los menús, layout ni tema visual (sigue siendo Skote).
- **No** se agrega Composer ni npm — todo es vendorización manual de assets.

La filosofía es: **las herramientas ya están, solo hay que usarlas**.