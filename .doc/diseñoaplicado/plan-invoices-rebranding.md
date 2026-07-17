# Plan — Facturas rebranding (Facturación)

**Origen (template de diseño):** `rebranding/src/components/FacturasView.tsx` (React + Tailwind, 518 líneas).
**Patrón de referencia en producción:** `app/public/dash-services.php` + `app/public/assets/js/components/native-table.js` — **este es el patrón canónico a replicar**: toolbar con buscador client-side (`data-table-search-input`) + pills de filtro server-side (`?filter=`) + tabla Tailwind hecha a mano con paginación client-side genérica (`data-table-native-wrap`), sin DataTables. Servicios es la migración hermana más reciente (cerrada 2026-07-16) y ya prueba el patrón en producción.
**Objetivo:** reestilizar `app/public/dash-invoices-list.php` (hoy DataTables/Bootstrap) para que tenga buscador + pills de filtro + KPIs financieros + tabla Tailwind nativa, retirando DataTables de esta vista.
**Fuera de alcance:** `dash-invoices-add.php`, `dash-invoices-edit.php`, `dash-invoices-upload.php`, `dash-invoices-upload-preview.php`, `dash-invoices-upload-result.php`, `dash-invoices-detail.php` — ya migrados a Tailwind (`dt-card`, `dt-input`, `dt-select`, `dt-page-title`) y no forman parte de este plan. Los controllers (`invoice-new.php`, `invoice-update.php`, `invoice-estado.php`, `invoice-fecha-pago.php`, `invoice-upload-*.php`, `invoice-delete.php`, `invoice-pdf.php`, `invoice-service-*.php`) ya son 100% DDD y no requieren cambios.
**Estado:** 🔴 planificado — nada implementado todavía.

---

## 0. Contrato de datos y convenciones (CONGELAR ANTES DE IMPLEMENTAR)

### 0.1 Hallazgo crítico: el mockup reinventa dos features que YA EXISTEN, con datos reales

`FacturasView.tsx` incluye dos "slide drawers" que en este proyecto **no son mocks — ya están construidos como flujos reales, completos y en producción**:

| Feature del mockup | Implementación real ya existente | Decisión |
|---|---|---|
| Drawer "Importador de Planillas Excel" (drag&drop simulado, staging en memoria de React, botón "Cargar Archivo Excel de Prueba") | `dash-invoices-upload.php` → `controller/invoice-upload-parse.php` (parsea `.xlsx` real) → `dash-invoices-upload-preview.php` (previsualización real con datos parseados) → `controller/invoice-upload-confirm.php` (inserta en `facturas`) / `invoice-upload-cancel.php`. Página con plantilla descargable (`assets/templates/plantilla-facturas.xlsx`), manejo de errores (`sin_archivo`, `formato_invalido`, `sin_filas`), ya Tailwind (`dt-card`, `dt-input`, `dt-btn-add`) | **No se reimplementa como drawer.** El drawer del mockup es una simulación de UI sin backend; el flujo real ya resuelve el mismo objetivo de negocio con más garantías (validación de archivo real, preview real, confirmación transaccional). Se mantiene el flujo de 3 páginas, solo se linkea desde el nuevo toolbar |
| Drawer "Emitir Factura Manual" (formulario en drawer lateral) | `dash-invoices-add.php`, página completa ya Tailwind-migrada, con select de contrato (`data-enhanced-select`), validación (`form-validator.js`) | **No se reimplementa como drawer.** Mismo criterio que Clientes (`plan-customers-rebranding.md` §0.2): no reescribir un formulario que ya funciona. Se mantiene el link a la página |

Esto reduce drásticamente el alcance real de este plan frente al mockup: no hay backend nuevo que escribir, no hay JS de drag&drop que construir. El trabajo es **puramente de presentación** sobre `dash-invoices-list.php`.

### 0.2 Modelo de datos real

Tabla `facturas` (`app/src/Domain/Invoice/Invoice.php`, `MysqliInvoiceRepository`):

| Columna | Significado |
|---|---|
| `id_Factura` | PK |
| `id_Cliente`, `id_Contrato` | FKs |
| `numero_Factura` | string, se muestra con `#` delante |
| `fecha_Factura` | date de emisión |
| `valor_Factura` | int, monto neto (formateado con `format_clp()`, `layouts/helpers.php:33`) |
| `estado_Factura` | `1` = Pendiente, `2` = Pagado, `3` = Anulado |
| `fecha_Pago` | date nullable, editable vía modal existente |

`MysqliInvoiceRepository::listActiveWithDetails()` (líneas 106-118) ya trae `estado_Factura IN (1, 2)` — **excluye Anuladas del listado**, join con `clientes.nombre_Cliente` y `contratos.obra_Contrato`. Esto **coincide exactamente** con el modelo del mockup (`Invoice.status: 'Pagado' | 'Pendiente'`, sin tercer estado) — no hay divergencia que resolver. `ListInvoices->handle()` es un passthrough directo, no requiere cambios.

**No se toca el backend en este plan.** Las 3 pills de filtro y los 3 KPIs se calculan en la vista, en PHP, sobre el array `$facturas` ya cargado (mismo patrón que Servicios usa para KPIs simples, sin nueva query).

### 0.3 Pills de filtro (server-side, mismo patrón que Servicios)

`?filter=todas|pagadas|pendientes` — filtro **en memoria sobre `$facturas`** (no hace falta tocar SQL, ya viene todo cargado):

```php
$rawFilter = $_GET['filter'] ?? 'todas';
$filter = in_array($rawFilter, ['todas', 'pagadas', 'pendientes'], true) ? $rawFilter : 'todas';

$facturasFiltradas = array_values(array_filter($facturas, function ($row) use ($filter) {
    if ($filter === 'pagadas') return (int) $row['estado_Factura'] === 2;
    if ($filter === 'pendientes') return (int) $row['estado_Factura'] === 1;
    return true;
}));
```

Los **KPIs se calculan sobre `$facturas` completo** (no sobre `$facturasFiltradas`), igual que el mockup calcula sobre `invoices`, no sobre `filteredInvoices` (`FacturasView.tsx:146,157,168`).

### 0.4 IDs / clases DOM (congelados para que markup no se desincronice)

```
#facturas-local-search          input de búsqueda, data-table-search-input="#facturas-table"
#facturas-table                 wrapper de la tabla, data-table-native-wrap data-per-page="10" data-item-label="Facturas"
tr[data-search="..."]           numero_Factura + nombre_Cliente + obra_Contrato concatenados (mismo algoritmo que native-table.js espera)
```

No hace falta JS nuevo: `native-table.js` ya maneja búsqueda + paginación de forma genérica vía data-attributes (usado hoy por Servicios). **No se crea `invoices.js`.**

### 0.5 Paleta

`--color-primary` ya es indigo-600 en todo el sistema (`tw/theme.css`) — coincide con `FacturasView.tsx`. Sin override scopeado (igual que Servicios).

### 0.6 Desviaciones intencionales del prototipo

| Elemento | Prototipo | Este plan | Motivo |
|---|---|---|---|
| Drawer importar Excel | Sandbox simulado en memoria | Botón "Subir Excel" enlaza a `dash-invoices-upload.php` (flujo real de 3 páginas) | §0.1 — no reinventar lo que ya funciona con datos reales |
| Drawer emitir factura manual | Formulario en drawer | Botón "Emitir Factura" enlaza a `dash-invoices-add.php` (página ya Tailwind) | §0.1 — mismo criterio que Clientes |
| Búsqueda | State de React, filtra array en cliente | `native-table.js` (mismo resultado, ya es el patrón del proyecto) | Reutilizar componente existente en vez de JS nuevo |
| Filtro Todas/Pagadas/Pendientes | State local de React | Pills server-side vía `?filter=`, filtrado en PHP sobre el array ya cargado | Consistencia con el patrón recién validado en Servicios |
| Acción "Registrar Pago" | Botón inline con `confirm()` nativo del navegador | Botón inline reutilizando `data-confirm-delete`-style (SweetAlert2, `feedback.js`) apuntando a `controller/invoice-estado.php?estado_Factura=2`, visible solo si `estado_Factura == 1` | El controller y el toast de confirmación (`?status=success&msg=`) ya existen; solo se agrega el link visible fuera del dropdown |
| Columna "Acciones" completa (dropdown) | No existe en el mockup — solo Registrar Pago | Se conserva el dropdown actual (Editar, Imprimir PDF, Agregar Servicios, Marcar Pendiente/Pagado/Anulado, Anular) | Funcionalidad de negocio real que el mockup (2 estados, sin PDF ni servicios asociados) no contempla |
| Columna "Fecha de Pago" + modal edición | No existe en el mockup | Se conserva tal cual (`modalFechaPago`, ya funcional) | Dato real del negocio, ausente del modelo simplificado del mockup |
| Estado "Anulado" | No existe (`Invoice.status` solo tiene 2 valores) | El listado ya excluye Anuladas (`listActiveWithDetails()`), sin cambios | El backend ya resuelve esto de fábrica |
| KPI "Eficiencia de Cobro" | `pagadas / total * 100` | Igual, calculado en PHP sobre `$facturas` | Fidelidad 1:1, sin dato adicional necesario |

---

## 1. Anatomía objetivo

```
<div class="space-y-4">
  <!-- KPIs -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
    KPI 1: Recaudación Lograda (indigo tint)   -- suma valor_Factura donde estado=2
    KPI 2: Por Recaudar (amber tint)            -- suma valor_Factura donde estado=1
    KPI 3: Eficiencia de Cobro (white/dt-kpi-card) -- % pagadas/total
  </div>

  <!-- Toolbar -->
  <div class="flex ... justify-between">
    buscador (#facturas-local-search) + pills (Todas/Pagadas/Pendientes, ?filter=)
    date-range picker existente (se mantiene, ya funcional con flatpickr)
    botón "Subir Excel / CSV"  -> dash-invoices-upload.php
    botón "Agregar Nueva Factura" -> dash-invoices-add.php  (ya existe, solo reestilo)
  </div>

  <!-- Tabla nativa -->
  <div id="facturas-table" data-table-native-wrap data-per-page="10" data-item-label="Facturas" class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
    thead: Nro. Factura | Fecha | Cliente | Obra | Monto | Estado | Fecha de Pago | Acciones
    tbody: tr[data-search="..."] por fila, badge-status, botón Registrar Pago condicional, dt-cell-action + dropdown
  </div>

  <!-- Modal Fecha de Pago -->  (SIN CAMBIOS, ya funcional)
</div>
```

---

## 2. Fases de implementación

### Fase 1 — Toolbar: KPIs + búsqueda + pills

**Archivo:** `app/public/dash-invoices-list.php`

- Antes del `table-toolbar` actual, calcular en PHP (sobre `$facturas`, el array completo de `ListInvoices->handle()`):
  ```php
  $totalPagado = array_sum(array_map(fn($f) => (int) $f['valor_Factura'], array_filter($facturas, fn($f) => (int) $f['estado_Factura'] === 2)));
  $totalPendiente = array_sum(array_map(fn($f) => (int) $f['valor_Factura'], array_filter($facturas, fn($f) => (int) $f['estado_Factura'] === 1)));
  $countPagadas = count(array_filter($facturas, fn($f) => (int) $f['estado_Factura'] === 2));
  $eficiencia = count($facturas) > 0 ? round($countPagadas / count($facturas) * 100) : 0;
  ```
- Grid de 3 KPIs (`grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5`), calco visual de `FacturasView.tsx:141-174` (ver Fase 2 para el CSS de las 2 variantes tintadas que faltan).
- Reemplazar el `table-toolbar` actual por buscador + pills (calco de `dash-services.php:176-198`, con labels `Todas / Pagadas / Pendientes` y `?filter=`).
- Mantener el date-range picker existente (`.dt-range-picker`, flatpickr) — no está en el mockup pero es funcionalidad real ya operativa; se reubica dentro del nuevo toolbar sin cambios de comportamiento.
- Mantener los botones "Subir Excel / CSV" (nuevo, `href="dash-invoices-upload.php"`) y "Agregar Nueva Factura" (ya existe, solo reestilo a `bg-indigo-500 hover:bg-indigo-600 text-white rounded-xl` como en Servicios).

**Checklist de salida:**
- [ ] Los 3 números de KPI coinciden con una suma manual verificada en `mariadb` sobre `facturas` filtrando `estado_Factura`
- [ ] Las pills navegan con `?filter=` y el contador del `table-toolbar-title` refleja `count($facturasFiltradas)`

### Fase 2 — CSS: variantes tintadas de KPI card

**Archivo:** `app/public/assets/css/tw/components.css` (extender el bloque `.dt-kpi-card` existente, ~línea 332, definido en Fase 4 del plan Tailwind pero nunca consumido por ninguna vista hasta ahora).

- Agregar 2 variantes nuevas junto a `.dt-kpi-card` (que se mantiene como la variante blanca/neutra ya usada para "Eficiencia de Cobro"):
  ```css
  .dt-kpi-card.is-indigo-tint { background: rgba(99, 102, 241, .1); border-color: rgba(99, 102, 241, .2); }
  .dt-kpi-card.is-amber-tint  { background: rgba(245, 158, 11, .1); border-color: rgba(245, 158, 11, .2); }
  ```
  (Sin `::before` decorativo en las variantes tintadas — el degradé de esquina del `.dt-kpi-card` base no aporta sobre fondo ya coloreado; se anula con `.dt-kpi-card.is-indigo-tint::before, .dt-kpi-card.is-amber-tint::before { display: none; }`.)
- `pnpm tw:build` tras el cambio.

**Checklist de salida:**
- [ ] `pnpm tw:build` sin errores, `tailwind.css` regenerado
- [ ] Ninguna vista que ya use `.dt-kpi-card` (ninguna hoy, pero el Dashboard lo hará eventualmente) se ve afectada por las clases nuevas — son aditivas

### Fase 3 — Tabla nativa: retirar DataTables

**Archivo:** `app/public/dash-invoices-list.php`

- Quitar `<link>` de `datatables.net-bs4` / `responsive-bs4` (líneas 21-22).
- Envolver la tabla en `<div id="facturas-table" data-table-native-wrap data-per-page="10" data-item-label="Facturas" class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">` (reemplaza `table-card`).
- Convertir `<table class="table align-middle datatable dt-responsive nowrap w-100" data-dt-state="true" data-dt-column-filters="true">` a tabla Tailwind hecha a mano (calco de `#servicios-table`, `dash-services.php:334-343`): `<table class="w-full table-fixed text-left border-collapse">`, `<thead>` con `font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase`.
- Cada `<tr>` recibe `data-search="<?php echo htmlspecialchars($row['numero_Factura'] . ' ' . $row['nombre_Cliente'] . ' ' . ($row['obra_Contrato'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"`.
- Columna Monto: mantener `format_clp()` (no `Intl.NumberFormat`, es el helper del proyecto — ver §0.6).
- Columna Estado: mantener `badge-status is-warn/is-success` (ya coincide con el sistema de diseño; **no** se toca — Anulado no aparece en este listado, §0.2).
- Columna Acciones: **agregar** botón "Registrar Pago" visible solo si `estado_Factura == 1`, antes o junto al ícono de editar:
  ```php
  <?php if ((int) $row['estado_Factura'] === 1): ?>
      <a href="controller/invoice-estado.php?id_Factura=<?php echo (int) $row['id_Factura']; ?>&estado_Factura=2"
         class="px-3 py-1 bg-primary-50 text-primary-600 hover:bg-primary-100 transition-colors rounded-lg font-sans text-xs font-bold"
         data-confirm-delete
         data-confirm-title="¿Registrar el pago de esta factura?"
         data-confirm-text="La factura #<?php echo htmlspecialchars($row['numero_Factura'], ENT_QUOTES, 'UTF-8'); ?> se marcará como Pagada."
         data-confirm-confirm-text="Sí, registrar pago">
          Registrar Pago
      </a>
  <?php endif; ?>
  ```
  (Reusa `data-confirm-delete`/`feedback.js` — genérico pese al nombre, ya toma `title`/`text`/`confirmText` por atributo; el controller ya redirige con `?status=success&msg=...` que `feedback.js` ya sabe mostrar como toast.)
- Mantener el resto del dropdown de acciones (Editar, Imprimir, Agregar Servicios, Marcar Pendiente/Pagado/Anulado, Anular) **sin cambios** — es funcionalidad real que el mockup no contempla (§0.6).
- Mantener el botón de editar Fecha de Pago (`data-bs-toggle="modal" data-bs-target="#modalFechaPago"`) sin cambios.

**Checklist de salida:**
- [ ] `php -l dash-invoices-list.php` sin errores
- [ ] La tabla muestra todas las facturas activas (Pendiente + Pagado), buscador filtra por número/cliente/obra, paginación funciona
- [ ] Botón "Registrar Pago" solo aparece en filas Pendiente, pide confirmación SweetAlert2, y tras confirmar la fila pasa a Pagado con toast de éxito
- [ ] Dropdown de acciones (6 opciones) sigue funcionando igual que antes
- [ ] Modal de Fecha de Pago sigue abriendo/guardando igual que antes

### Fase 4 — Retirar assets de DataTables

**Archivo:** `app/public/dash-invoices-list.php`

- Quitar los `<script>` de DataTables/JSZip/pdfmake (líneas 161-172) y el bloque `DataTable.init(...)`.
- Mantener el `<script>` de `show.bs.modal` para `#modalFechaPago` (usa jQuery, que sigue disponible vía `vendor-scripts.php`, independiente de DataTables).
- Confirmar que el dropdown de acciones (bs-shim) y el buscador (native-table.js, ya cargado global si Servicios lo dejó en `app.js`/`vendor-scripts.php`, o se agrega su `<script>` puntual si no está global) siguen funcionando sin DataTables.

**Checklist de salida:**
- [ ] Sin requests a assets de DataTables en Network tab
- [ ] Sin errores en consola del navegador ni en `docker-compose logs php`

### Fase 5 — QA manual (sin tests automatizados en este proyecto)

En `http://localhost/dash-invoices-list.php`:

1. Los 3 KPIs muestran valores correctos (verificados contra suma manual en `mariadb`).
2. Buscar por número de factura, cliente y obra → filtra correctamente.
3. Cambiar pills Todas/Pagadas/Pendientes → recarga con el filtro correcto, contador correcto.
4. Botón "Registrar Pago" en una factura Pendiente → confirma, pasa a Pagado, toast de éxito, botón desaparece de esa fila.
5. Dropdown: Imprimir (glightbox preview del PDF), Agregar Servicios, Marcar Pendiente/Pagado/Anulado, Anular (con confirm) — sin regresiones.
6. Modal Fecha de Pago: abrir, editar, guardar — persiste correctamente.
7. Botón "Subir Excel / CSV" → navega a `dash-invoices-upload.php`, flujo completo de carga (parse → preview → confirm) sin tocar, debe seguir funcionando igual.
8. Botón "Agregar Nueva Factura" → navega a `dash-invoices-add.php`, sin cambios.
9. Date-range picker (flatpickr) sigue funcionando si tenía lógica activa; si no, confirmar que al menos no rompe visualmente.
10. Responsive: mobile, scroll horizontal de tabla, KPIs se apilan a 1 columna.
11. Confirmar que no quedan requests a assets de DataTables (Network tab).

---

## 3. Resumen ejecutivo (para retomar sesión)

1. **Sin backend nuevo:** `ListInvoices`/`MysqliInvoiceRepository` no se tocan — KPIs y pills se calculan en PHP sobre el array ya devuelto por `listActiveWithDetails()`.
2. **Sin JS nuevo:** se reutiliza `native-table.js` (búsqueda + paginación) y `feedback.js` (`data-confirm-delete` para "Registrar Pago"), ambos ya en producción vía Servicios.
3. **1 archivo a reescribir:** `dash-invoices-list.php` (KPIs + toolbar + tabla nativa, retirar DataTables).
4. **1 archivo CSS a extender:** `tw/components.css` (2 variantes tintadas de `.dt-kpi-card`, ya definido pero sin uso hasta ahora).
5. **Los 2 drawers del mockup (Excel, manual) NO se implementan** — ya existen como flujos reales de página completa, más robustos que el mockup; solo se linkean desde el nuevo toolbar.
6. Después de cada fase de código PHP: `docker-compose restart php` + smoke test manual (regla del proyecto, no hay CI). Después de tocar `tw/components.css`: `pnpm tw:build`.

---

## 4. Referencias

- **Prototipo fuente:** `rebranding/src/components/FacturasView.tsx` (518 líneas, React + Tailwind).
- **Vista a reestilizar:** `app/public/dash-invoices-list.php`.
- **Patrón canónico (tabla nativa sin DataTables):** `app/public/dash-services.php`, `app/public/assets/js/components/native-table.js`.
- **Confirmación genérica + toasts:** `app/public/assets/js/components/feedback.js`.
- **Entidad/repositorio:** `app/src/Domain/Invoice/Invoice.php`, `app/src/Infrastructure/Persistence/MysqliInvoiceRepository.php` (`listActiveWithDetails()` ya trae todo lo necesario).
- **Flujos reales fuera de alcance (no reinventar):** `dash-invoices-add.php`, `dash-invoices-upload.php` + `dash-invoices-upload-preview.php` + `dash-invoices-upload-result.php` + `controller/invoice-upload-parse.php` / `invoice-upload-confirm.php` / `invoice-upload-cancel.php`.
- **Planes hermanos:** `plan-services-rebranding.md` (patrón de tabla nativa + pills), `plan-customers-rebranding.md` (precedente de "no convertir páginas funcionales en drawers").
