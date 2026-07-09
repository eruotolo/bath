# Checklist — Roadmap interno / Mejoras frontend / Sprint 3: Selects con buscador

**Origen:** `.doc/plan-mejoras-frontend.md` (sección 11, Sprint 3) — roadmap interno, no cotizado al cliente
**Estado:** ✅ implementado y verificado en navegador (2026-07-08)

---

## Alcance de este sprint

1. `assets/js/components/select-enhanced.js` (Choices.js)
2. Migrar los selects reales de catálogo a `data-enhanced-select`
3. Selects en cascada (cliente → contrato)

## Relevamiento real vs. plan original

El plan estimaba "15+ selects" en 9 vistas, incluyendo `user_Rol`/`user_Estado` en `dash-users-add.php`/`dash-users-edit.php` y `estado_Bath` en `dash-bathrooms-edit.php`. Se relevó el código real (`grep` de `<select` en todo `app/public/`, excluyendo `archive/`) y esos selects **no existen** — el alta de usuario tiene rol/estado hardcodeados en el controller (`$category = 2; $state = 1;`, sin input del usuario) y `dash-bathrooms-edit.php` no tiene ningún `<select>`. El inventario real, más chico y preciso, quedó en **13 selects reales en 8 vistas**, todos migrados:

| Vista | Select(es) | Tipo |
|---|---|---|
| `dash-contracts-add.php` | `id_Cliente`, `estado_Contrato` | simple |
| `dash-contracts-edit.php` | `id_Cliente`, `estado_Contrato` | simple |
| `dash-certificates-add.php` | `id_Cliente` → `id_Contrato` | **cascada** |
| `dash-services-add.php` | `id_Cliente` → `id_Contrato` | **cascada** |
| `dash-invoices-add.php` | `id_Cliente` → `id_Contrato` | **cascada** |
| `dash-invoices-edit.php` | `id_Cliente` → `id_Contrato` (con precarga) | **cascada + preselect** |
| `dash-invoices-detail.php` | `id_Servicio` | simple |
| `dash-bathrooms-add.php` | `estado_Bath` | simple |
| `dash-customers-add.php` | `region_Cliente` | simple |
| `layouts/modal-nuevo-assign-bath.php` | `id_Bath` | simple |

## El riesgo técnico real de este sprint: cascada + Choices.js

Las 4 vistas con cascada cliente→contrato **ya tenían** un mecanismo funcionando antes de este sprint: jQuery escuchaba el `change` del select de cliente y hacía `$('#id_Contrato').html(respuestaAjax)` contra `controller/obtener_contratos.php` (que ya existía, ya usaba prepared statements — el plan decía que necesitaba ese refactor, pero ya estaba hecho).

El problema: Choices.js reemplaza el `<select>` nativo por su propio DOM (oculta el original, arma su propia lista). Si se sigue hacienda `.html(...)` sobre el `<select>` oculto, Choices.js nunca se entera del cambio y el usuario sigue viendo la lista vieja (o vacía). Por eso `select-enhanced.js` expone `SelectEnhanced.cascade()` y `SelectEnhanced.setOptionsFromHtml(selectId, html, selectedValue)`, que detectan si el select de destino tiene una instancia de Choices activa y, si la tiene, actualizan sus opciones con `instance.setChoices(...)` en vez de tocar el DOM directamente. Se verificó en navegador que:
- El cliente se busca y selecciona con Choices.js.
- Al seleccionarlo, el select de contrato (también con Choices.js) se repuebla solo con los contratos de ese cliente.
- En la pantalla de edición de factura, el contrato guardado queda preseleccionado correctamente dentro del Choices.js ya potenciado (no solo en el `<select>` nativo escondido).

## 2 bugs preexistentes encontrados y corregidos al tocar las cascadas

No eran parte del pedido, pero estaban en las 3 líneas que había que tocar para migrar la cascada, así que se corrigieron ahí mismo:

1. **`dash-certificates-add.php` y `dash-invoices-add.php` tenían `<<script>` (doble `<`) en la etiqueta que abre el bloque de la cascada.** Es HTML inválido — el navegador no lo interpreta como un `<script>` real, así que ese bloque de JS nunca se ejecutaba. La cascada cliente→contrato estaba rota en esas 2 pantallas antes de este sprint (no se notaba porque ambos selects siguen siendo usables a mano, solo no se autocompletaba el segundo).
2. **`dash-invoices-add.php` cargaba una segunda copia de jQuery desde un CDN externo** (`https://code.jquery.com/jquery-3.6.4.min.js`), duplicando la que ya viene vendorizada localmente en `vendor-scripts.php`. Además de ser una dependencia de red innecesaria (y contraria a la convención del proyecto de vendorizar todo a mano), una segunda instancia de jQuery puede pisar la primera y romper cualquier plugin que dependa de `$` (DataTables, Choices, SweetAlert2, etc.). Se eliminó la línea.
3. **`controller/obtener_contratos.php` no escapaba `obra_Contrato`** al armar las `<option>` — se agregó `htmlspecialchars()`, ya que ahora es un endpoint central de las 4 cascadas.

## Decisiones de alcance

- **No se creó `layouts/select_options.php`** (el helper PHP centralizado que proponía el plan, sección 4.2). El propio plan lo marca como "cambios opcionales" y aclara en la sección de compatibilidad que ese helper "se beneficia del Repository `CustomerRepositoryInterface` cuando exista" — es decir, tiene más sentido después del plan DDD, no antes. Migrar ~10 queries de selects distintos (algunas con filtro de estado, otras sin filtro, distintos JOIN) a una función compartida ahora es un refactor de backend con riesgo de cambiar sutilmente el comportamiento de cada vista, fuera del límite que el propio plan se puso ("JS/CSS puros, cero cambios de backend" salvo lo estrictamente necesario para que la cascada siga funcionando).
- Este sprint quedó, en la práctica, como **frontend puro** salvo por 2 líneas en `obtener_contratos.php` (escape de output) — ninguna consulta SQL nueva ni cambio de esquema.

## Archivos nuevos

- `app/public/assets/js/components/select-enhanced.js` — `SelectEnhanced.init()`, `.cascade()`, `.setOptionsFromHtml()`

## Archivos tocados

- `app/public/layouts/vendor-scripts.php` — agrega `choices.min.js` + `select-enhanced.js`
- `app/public/layouts/head-style.php` — agrega `choices.min.css`
- `app/public/controller/obtener_contratos.php` — `htmlspecialchars()` en el nombre de obra, `(int)` en el id
- `app/public/dash-contracts-add.php`, `dash-contracts-edit.php` — `data-enhanced-select` en `id_Cliente` y `estado_Contrato`
- `app/public/dash-certificates-add.php` — `data-enhanced-select` en ambos selects + cascada reescrita + fix del `<<script>`
- `app/public/dash-services-add.php` — `data-enhanced-select` en ambos selects + cascada reescrita
- `app/public/dash-invoices-add.php` — `data-enhanced-select` en ambos selects + cascada reescrita + fix del `<<script>` + jQuery duplicado eliminado
- `app/public/dash-invoices-edit.php` — `data-enhanced-select` en ambos selects + `cargarContratos()` adaptada para precargar la selección
- `app/public/dash-invoices-detail.php` — `data-enhanced-select` en `id_Servicio`
- `app/public/dash-bathrooms-add.php` — `data-enhanced-select` en `estado_Bath`
- `app/public/dash-customers-add.php` — `data-enhanced-select` en `region_Cliente`
- `app/public/layouts/modal-nuevo-assign-bath.php` — `data-enhanced-select` en `id_Bath`

## Checklist

- [x] Choices.js inicializado en los 13 selects reales de catálogo (relevamiento propio, no el estimado del plan)
- [x] Selects en cascada funcionan en las 4 vistas que los tienen (`dash-invoices-add.php`, `dash-invoices-edit.php`, `dash-services-add.php`, `dash-certificates-add.php`) — 2 de las 4 estaban rotas antes de este sprint por el bug del `<<script>`
- [x] **Verificado en navegador real:** búsqueda funcional en el select de cliente (90 clientes → 1 filtrando "SALMONES"), cascada completa cliente→contrato en `dash-invoices-add.php` (selecciona cliente, aparecen sus 2 contratos reales), precarga + preselección correcta en `dash-invoices-edit.php` (abre la factura #1896, muestra "CONSTRUCTORA SIERRA NEVADA S.A." → "OBRA CAMINO A CHONCHI- QUELLON" ya seleccionados), Choices.js aplicado correctamente en un select hardcodeado corto (`estado_Contrato`)
- [x] Sin cambios de SQL nuevos, sin Composer, sin librerías nuevas más allá de Choices.js (ya vendorizada)

## Pendiente / fuera de alcance de este sprint

- `layouts/select_options.php` (helper PHP centralizado) — descoping documentado arriba, se retoma naturalmente durante el plan DDD.
- Sprint 4 (datepickers, modal de edición unificado, Glightbox, Dropzone) sigue pendiente — es el último del plan de mejoras frontend.

---

## Reglas a respetar durante la implementación (de `CLAUDE.md`)

- SQL nuevo → prepared statements (ya lo estaba en `obtener_contratos.php`, sin cambios de fondo) — cumplido
- Output sin escapar → corregido en `obtener_contratos.php` al tocarlo — cumplido
- Sin Composer, sin npm, sin librerías nuevas — cumplido (Choices.js ya vendorizada)
- Estilo procedural en el PHP tocado — cumplido
- No se commiteó nada sin pedido explícito
