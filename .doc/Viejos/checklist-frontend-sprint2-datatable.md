# Checklist — Roadmap interno / Mejoras frontend / Sprint 2: Tabla potenciada

**Origen:** `.doc/Viejos/plan-mejoras-frontend.md` (sección 11, Sprint 2) — roadmap interno, no cotizado al cliente
**Estado:** ✅ implementado y verificado en navegador (2026-07-08)

---

## Alcance de este sprint

1. `assets/js/components/datatable.js` — init único compartido, reemplaza el script inline duplicado ~45 líneas x4
2. Migrar las 4 vistas con script inline: `dash-bathrooms.php`, `dash-bathrooms-contracts.php`, `dash-services.php`, `dash-invoices-list.php`
3. Marcar `datatable-pages.init.js` como deprecado

## Decisiones de alcance (descoping deliberado respecto del plan original)

El plan (sección 3.3) proponía 8 features opt-in vía `data-dt-*`. Se implementaron 2 (las de valor concreto e inmediato) y se dejaron 6 afuera por ser especulativas — sin un caso de uso real detectado en el sistema actual:

| Feature del plan | ¿Se hizo? | Motivo |
|---|---|---|
| **Persistencia de estado** (`data-dt-state`) | ✅ Sí | Filtros/orden/página sobreviven al refresh — valor inmediato en listas de 100-1600+ filas. Implementado con el `stateSave: true` nativo de DataTables (localStorage), **no** con la extensión "DataTables StateRestore" que el plan daba por instalada — se verificó que esa extensión específica no está vendorizada en `assets/libs/`, solo el core + Buttons + Responsive + Select. |
| **Filtros por columna** (`data-dt-column-filters`) | ✅ Sí | Input de búsqueda por columna, mejor UX que el buscador global en tablas anchas. Se excluyen del filtro las columnas de solo-botones (`Acción`, `Otros`, `Acciones`, `Fecha de Pago`) marcándolas con `class="no-filter"` en el `<th>`. |
| Selección múltiple + botón "eliminar en lote" | ❌ No | Requeriría controllers nuevos de borrado en lote (`bath-batch-delete.php`, etc.) — cambio de backend fuera del alcance declarado del plan ("JS/CSS puros, cero cambios de backend", sección 13). Sin un caso de uso pedido, es especulativo (YAGNI). La librería `datatables.net-select` sí está vendorizada por si se retoma esto más adelante. |
| Exportar con logo y header (brand export) | ❌ No | Cosmético — requiere embeber el logo en el PDF export. Bajo valor frente al esfuerzo. |
| Refresh manual | ❌ No | No hay datos en tiempo real ni polling en ninguna de estas 4 vistas; el botón no tendría más efecto que un F5. |
| Sticky header | ❌ No | Es una regla CSS (`position: sticky`), no justifica ser una "feature" del componente JS. Se puede agregar en 2 líneas de CSS el día que se pida. |
| Row details (expandir fila) | ❌ No | No hay datos ocultos por fila en ninguna de las 4 tablas que justifiquen un detalle expandible. |

## Hallazgo durante la implementación

`.doc/Viejos/plan-mejoras-frontend.md` (sección 2, tabla de inventario) afirma que "DataTables StateRestore" está instalado en `assets/libs/`. Se verificó (`find` en `assets/libs/`) que **no es así** — lo único vendorizado es `datatables.net`, `datatables.net-bs4`, `datatables.net-buttons(-bs4)`, `datatables.net-responsive(-bs4)` y `datatables.net-select(-bs4)`. La persistencia de estado se logró igual con la opción nativa `stateSave` del core de DataTables, sin necesitar la extensión.

## Lógica page-specific preservada sin cambios

`DataTable.init()` devuelve la instancia de DataTables (como especificaba el ejemplo del plan en sección 3.2), así que el código propio de cada vista sigue funcionando exactamente igual sobre esa instancia:
- `dash-bathrooms.php`: los filtros rápidos de Estado/Asignado (agregados en el commit `00cca0a`, previo a este sprint) — verificado que ambos siguen funcionando.
- `dash-invoices-list.php`: el listener del modal "Fecha de Pago" — sin cambios.
- `dash-services.php` / `dash-invoices-list.php`: `order: []` para preservar el orden `created_at DESC` que ya viene del SQL — preservado literal.

## Archivos nuevos

- `app/public/assets/js/components/datatable.js` — `DataTable.init(selector, options)`, con soporte opt-in de `data-dt-state` y `data-dt-column-filters`

## Archivos tocados

- `app/public/assets/js/pages/datatable-pages.init.js` — comentario de deprecación agregado (sigue en uso real en `dash-users-list.php`, `dash-customers.php`, `dash-contracts.php` — migrarlas queda pendiente, no era parte del alcance de este sprint)
- `app/public/assets/css/style.css` — estilos de la fila de filtros por columna
- `app/public/dash-bathrooms.php` — `data-dt-state`, script inline → `DataTable.init()` + filtros rápidos preservados
- `app/public/dash-bathrooms-contracts.php` — `data-dt-state`, script inline → `DataTable.init()`
- `app/public/dash-services.php` — `data-dt-state` + `data-dt-column-filters`, columna "Acción" marcada `no-filter`, script inline → `DataTable.init()`
- `app/public/dash-invoices-list.php` — `data-dt-state` + `data-dt-column-filters`, columnas "Fecha de Pago"/"Otros"/"Acciones" marcadas `no-filter`, script inline → `DataTable.init()` + listener del modal preservado

## Checklist

- [x] `datatable.js` cubre las features con valor real identificado (persistencia + filtros por columna) — no el 100% de lo que el plan enumeraba, ver descoping arriba
- [x] Las 4 vistas con script inline ya no lo tienen (verificado: 0 coincidencias de `$('#datatable-buttons').DataTable(` fuera de `datatable.js`)
- [x] Estado de filtros persiste al refresh (verificado en `dash-invoices-list.php`: filtro de columna "Cliente" con texto "SALMONES" sobrevive a un F5 completo)
- [x] **Verificado en navegador real:** filtro por columna funcional en `dash-invoices-list.php` (reduce de 100+ a 8 filas), filtros rápidos de Estado/Asignado siguen funcionando en `dash-bathrooms.php`, `dash-bathrooms-contracts.php` inicializa con 143 filas, `dash-services.php` inicializa con 1643 filas y `order: []` preservado
- [x] `localStorage` de DataTables limpiado después de las pruebas, para no dejar el listado de un usuario real con un filtro de prueba aplicado

## Pendiente / fuera de alcance de este sprint

- `dash-users-list.php`, `dash-customers.php`, `dash-contracts.php` siguen usando `datatable-pages.init.js` (ahora marcado deprecado) — no se migraron, no estaban en la lista de "4 vistas" del plan.
- Selección múltiple + borrado en lote, export con marca, refresh manual, sticky header, row details — descoping documentado arriba.
- Sprint 3 (Choices.js / selects con buscador) y Sprint 4 (datepickers, modal unificado, Glightbox, Dropzone) siguen pendientes.

---

## Reglas a respetar durante la implementación (de `CLAUDE.md`)

- Sin cambios de backend/SQL — cumplido (JS/CSS puro)
- Sin Composer, sin npm, sin librerías nuevas — cumplido (todo ya vendorizado en `assets/libs/`)
- Estilo procedural en el PHP tocado, sin introducir clases — cumplido
- No se commiteó nada sin pedido explícito
