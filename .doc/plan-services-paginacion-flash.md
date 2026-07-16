# Plan — Servicios: paginación en vista lista + fix del flash de vista al cambiar filtro

**Origen:** feedback de Edgardo (2026-07-16) sobre `dash-services.php`. Dos bugs de comportamiento en la vista de lista (tabla):
1. La tabla no tiene paginador (las otras páginas, p. ej. `dash-contracts.php`, paginan cada 9 items).
2. Al cambiar de pestaña (Todos / Facturados / No Facturados) estando en vista lista, aparece un "preview" fugaz de la vista cards antes de quedar como lista.

**Patrones canónicos de referencia:**
- Paginación client-side: `app/public/layouts/native-table.php` (`table_native_open()` / `table_native_close()`) + `app/public/assets/js/components/native-table.js`. Es el patrón que usa `dash-contracts.php:172` con `'per_page' => 9`.
- Toggle de vista grid/tabla con localStorage + anti-flash: `app/public/dash-services.php` (`<style>` y `<script>` del `<head>` líneas 117-127) + `app/public/assets/js/components/services.js`.

**Estado:** 🟡 implementado (Fases 1-3 + paginación de cards), falta QA en vivo (Fase 4).

---

## 0. Diagnóstico

### Bug #1 — Sin paginador en vista lista

- `dash-services.php:20` carga **todos** los servicios vía `ListServices` y luego los filtra en PHP por `facturado` (líneas 28-41). No hay paginación server-side.
- La tabla (`#servicios-table`, líneas 284-352) se construye **a mano** (no usa `table_native_open()`), por eso no hereda el footer de paginación ni el atributo `data-per-page` que lee `native-table.js`.
- `native-table.js` pagina client-side: filtra `<tr data-search="...">` y pagina según `data-per-page` del `<table>`, renderizando el footer dentro del ancestro `[data-table-native-wrap]` (`summary` + `pages`). Hoy ningún elemento de la tabla de services tiene esos atributos → el script no la toca.

### Bug #2 — Flash de vista cards al cambiar filtro (vista lista)

- Los filtros son `<a href="?filter=...">` (líneas 154-161) → **recarga completa de página** en cada cambio de pestaña.
- Existe un script anti-flash en el `<head>` (líneas 121-127) que, si `localStorage['services-view'] === 'table'`, setea `document.documentElement.dataset.servicesView = 'table'` antes del paint. El CSS inline (líneas 118-119) oculta `#servicios-grid` y muestra `#servicios-table` cuando ese dataset está activo.
- Por análisis estático, el bloque debería prevenir el flash (el `<script>` síncrono del head corre antes de parsear el body). Aun así se observa el "preview" fugaz de cards → **hay una race condition real que no se confirma por inspección de código**. Hay que reproducirla en vivo antes de fixear a ciegas.

### Conflicto conocido (afecta al diseño del fix de #1)

- `services.js` (`filterItems`, líneas 65-78) hoy filtra **ambas** vistas a la vez: selector `.servicio-card, .servicio-row`, toggling `.hidden` por match contra `data-search-cliente/obra/nro/tipos`.
- `native-table.js` también filtra y pagina agregando `.hidden` a los `<tr>` que no entran en la página visible.
- Si ambos scripts operan sobre los `<tr>` de la tabla, compiten por la clase `.hidden` → comportamiento indeterminado. Por eso el paso 6 (Fase 3) desacopla la búsqueda: grid para `services.js`, tabla para `native-table.js`.

---

## 1. Contrato congelado

### 1.1 UX

- La pestaña activa (Todos / Facturados / No Facturados) no cambia: sigue siendo navegación server-side vía `?filter=` (recarga). Sin AJAX.
- La vista guardada (grid/tabla) se preserva en `localStorage['services-view']`, igual que hoy.
- El input de búsqueda `#servicios-local-search` sigue siendo **único** y aplica a la vista activa (grid o tabla, la que esté visible).
- Tabla: 9 items por página (igual que `dash-contracts.php`), con footer "Mostrando X-Y de Z Servicios" + botones Anterior / números / Siguiente (calco de `table_native_close()`).
- El flash de cards al cambiar filtro debe desaparecer: la vista activa al cargar debe ser la correcta desde el primer paint, sin parpadeo.

### 1.2 Selectores / atributos DOM

```
#servicios-grid                 ya existe (vista cards) — sin cambios
#servicios-table                ya existe (vista tabla) — se le agregan atributos
#servicios-local-search         ya existe (input de búsqueda) — se le agrega data-table-search-input
[data-table-native-wrap]        wrapper NUEVO que envuelve a #servicios-table
[data-table-native-summary]     texto "Mostrando X-Y de Z" (footer) — NUEVO
[data-table-native-pages]       contenedor de botones de página (footer) — NUEVO
data-per-page="9"               en el <table id="servicios-table">
data-item-label="Servicios"     en el <table id="servicios-table">
data-search="<texto concatenado>"  en cada <tr class="servicio-row"> (native-table.js filtra por este)
```

### 1.3 Backend

- Sin cambios. `ListServices` sigue devolviendo todos los servicios; la paginación es 100% client-side (`native-table.js`), igual que en `dash-contracts.php`. El filtro `?filter=` se sigue aplicando server-side en PHP (líneas 28-41).

---

## Fase 1 — Diagnosticar y fixear el flash de vista (Bug #2)

**Objetivo:** que al cambiar de pestaña `?filter=` estando en vista tabla, no aparezca ni un frame de la vista cards.

1. **Reproducir y diagnosticar en vivo** (vía `computer-use` sobre la pestaña `4909d0e5-5693-4788-ba92-eed754ce701a` de Chrome, `http://localhost/dash-services.php`):
   - Asegurar `localStorage['services-view'] = 'table'`.
   - Navegar a `?filter=no-facturados` y capturar el `dataset.servicesView` del `<html>` y el `display` computado de `#servicios-grid` / `#servicios-table` **en el momento de carga** (no post-load).
   - Confirmar si el `<script>` anti-flash (`dash-services.php:121-127`) está seteando el dataset antes del primer paint o si llega tarde.
2. **Aplicar el fix** según lo encontrado. Candidatos (elegir tras diagnóstico):
   - **(a) Robustecer el anti-flash:** garantizar que el dataset se setee síncronamente lo antes posible (ya está en el head; verificar que no haya un `<link>` render-blocking de `head-style.php` que retrase el paint y deje un frame sin aplicar). Si el problema es orden de carga, mover/asegurar el `<style>` inline antes del `<link>` de tailwind.
   - **(b) Anti-flash por defecto defensivo:** que el HTML server-side renderice `#servicios-grid` ya oculto cuando corresponda (no se puede leer `localStorage` server-side, pero se puede preservar la vista vía query param `?view=table` seteado por el toggle, además del localStorage).
   - **(c) Skeleton:** ocultar ambas vistas por CSS hasta que el JS defina cuál mostrar (agrega complejidad, preferir (a) o (b)).
3. Verificar en vivo que el flash desaparece al cambiar entre los 3 filtros en vista tabla (y que la vista grid sigue funcionando sin regresión).

**Notas:**
- No tocar `services.js` en esta fase (su lógica de toggle se mantiene). El fix es sobre el markup del head / el script anti-flash.
- Si el diagnóstico revela que el anti-flash ya funciona y el flash tiene otra causa (p. ej. FOUC por carga tardía de `tailwind.css` afectando al `.hidden`/`.grid`), documentar y replantear el fix.

---

## Fase 2 — Agregar markup de paginación a la tabla de services (Bug #1, parte 1)

**Objetivo:** que `native-table.js` reconozca `#servicios-table` como una tabla paginable y renderice el footer.

1. En el `<table id="servicios-table">` (`dash-services.php:284`), agregar atributos:
   - `data-per-page="9"`
   - `data-item-label="Servicios"`
2. Envolver la tabla (junto con su `overflow-x-auto`) en un contenedor con `data-table-native-wrap`, calco de `native-table.php:85`. Es decir, el `div#servicios-table` actual pasa a ser (o a contener) el `[data-table-native-wrap]`.
3. Agregar el footer de paginación después de `</table>`, dentro del wrap, calco de `table_native_close()` (`native-table.php:116-119`):
   ```html
   <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-50 flex items-center justify-between" data-table-native-pagination>
       <span class="font-mono text-[10px] text-slate-400 font-bold uppercase" data-table-native-summary></span>
       <div class="flex items-center space-x-1" data-table-native-pages></div>
   </div>
   ```
4. En cada `<tr class="servicio-row">` (línea ~306), agregar `data-search="<?php echo htmlspecialchars($cliente . ' ' . $obra . ' #' . $nro . ' ' . $searchTipos, ENT_QUOTES, 'UTF-8'); ?>"` — texto concatenado buscable (native-table.js filtra por `data-search`, no por `data-search-cliente`). Reutilizar las variables ya calculadas (`$cliente`, `$obra`, `$nro`, `$searchTipos`).
5. Al input `#servicios-local-search` (línea 148), agregar `data-table-search-input="#servicios-table"` para que `native-table.js` lo asocie a la tabla.
6. Cargar el script: agregar `<script src="assets/js/components/native-table.js"></script>` después de `services.js` (`dash-services.php:566`).
7. **NO correr todavía la búsqueda de la tabla** desde `services.js` — se desacopla en Fase 3. Si se testingue antes de Fase 3, los dos scripts competirán por `.hidden` en los `<tr>` (ver §0 Conflicto conocido).

**Notas:**
- Los anchos de columna fijados con `table-fixed` + `w-[X%]` (agregados en la sesión del 2026-07-16) se mantienen — son compatibles con la paginación (esta solo oculta/muestra filas).
- El footer de paginación hereda el estilo del de contracts; si hace falta, ajustar padding/border para que encaje con el `rounded-3xl` del card de la tabla.

---

## Fase 3 — Desacoplar búsqueda grid/tabla en `services.js` (Bug #1, parte 2)

**Objetivo:** evitar el conflicto de `.hidden` entre `services.js` y `native-table.js` sobre los `<tr>`.

1. En `services.js`, cambiar `filterItems` (líneas 65-78) para que filtre **solo el grid**:
   - Selector `.servicio-card` (quitar `.servicio-row` del selector). La tabla la filtra+paginea `native-table.js`.
2. Verificar que el input `#servicios-local-search` dispara ambos:
   - `services.js` ya tiene el listener `input` (líneas 88-92) → filtrará el grid.
   - `native-table.js` bindea el mismo input vía `data-table-search-input` (línea 181) → filtrará+pagineará la tabla.
   - Como solo una vista está visible a la vez (`display:none !important` en la otra), el usuario solo percibe la activa.
3. Confirmar que `applyView` (líneas 43-55) y el toggle (líneas 82-86) siguen sin cambios — solo se modifica la búsqueda.
4. Edge case: cuando se busca y se cambia de vista, el estado de búsqueda debe ser consistente. `native-table.js` mantiene su propio `_nativeTableState` por tabla; `services.js` no mantiene estado de query (lee del input en cada `input` event). Confirmar que al cambiar de vista con un query activo, ambas vistas lo respetan.

**Notas:**
- `native-table.js` resetea a página 1 al escribir (`state.page = 1`, línea 174) — comportamiento deseado.
- Si el grid también necesitara paginación (hoy no la tiene), queda fuera de scope; este plan solo pagina la tabla.

---

## Fase 4 — QA manual

1. Vista tabla: con >9 servicios, aparece el footer "Mostrando 1-9 de N Servicios" + botones. Navegar páginas cambia las filas visibles.
2. Cambiar de pestaña (Todos / Facturados / No Facturados) en vista tabla: **sin flash de cards**, la tabla carga directa. Mismo check en vista grid (sin flash de tabla).
3. Buscar texto en el input: la vista activa (tabla o grid) filtra. En tabla, además repagina sobre el resultado filtrado y resetea a página 1.
4. Cambiar de vista con un query activo: ambas vistas respetan el filtro.
5. Toggle grid/tabla persiste al recargar y al cambiar filtro (localStorage).
6. Responsive del footer de paginación en viewport angosto (que los botones no desborden).
7. Confirmar que `dash-contracts.php` no sufrió regresión (no se tocó, pero `native-table.js` es compartido — verificar que la tabla de contracts sigue paginando igual).

---

## Out of scope

- ~~Paginación del grid de cards (hoy muestra todos los filtrados; no se pidió).~~ → **Reincorporado (2026-07-16):** Edgardo pidió paginar también las cards. Implementado con paginador propio en `services.js` (`renderCards`), calco visual del footer de `native-table.js`, `native-table.js` intacto para evitar regresiones en `dash-contracts`/`dash-bathrooms`. 9 cards/página (3×3), footer `#servicios-cards-pagination` hermano del grid, oculto salvo en vista grid vía el CSS anti-flash del `<head>`.
- Migrar la tabla de services a usar `table_native_open()`/`close()` (refactor mayor; se descarta por ahora — se integra `native-table.js` sobre el markup manual existente).
- AJAX en los filtros `?filter=` (sigue siendo recarga server-side).
