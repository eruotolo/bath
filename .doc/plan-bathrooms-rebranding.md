# Plan — Baños rebranding (Inventario de Baños) · ejecución multi-IA

**Origen (template de diseño):** `rebranding/src/components/BanosView.tsx` (React + Tailwind, 491 líneas).
**Patrón de referencia en producción:** `app/public/dash-customers.php` (Tailwind puro, drawer server-side por query param, flash con SweetAlert2). **Este es el patrón canónico a replicar**, NO el `plan-customers-rebranding.md` (etapa anterior de scoping teal→indigo, ya superada por la reescritura Tailwind).
**Objetivo:** reescribir `app/public/dash-bathrooms.php` para que converja visual y funcionalmente con `BanosView.tsx` — grid/tabla con toggle, filtros por pills y drawer unificado de alta/edición/asignación — reutilizando la capa DDD de `Bathroom` ya migrada.
**Estado:** 🟡 implementado y con QA funcional completo — inspección visual manual pendiente por permisos de Accesibilidad de Orca.

**Modelo de ejecución:** 3 IA disponibles (**Sonnet 5**, **MiniMax-M3**, **GLM-5.2**). El trabajo se reparte por **capa / propiedad de archivos** (no por “trozos del mismo archivo”), con un contrato de datos congelado en la Fase 0 que todas consumen. Esto evita que dos modelos reescriban el mismo archivo con estilos distintos.

---

## 0. Contrato de datos y convenciones (CONGELAR ANTES DE PARALELIZAR)

> **Regla de oro:** ninguna fase de markup/JS arranca hasta que esta sección esté cerrada. Todas las IA leen esto primero y lo tratan como fuente de verdad. Si algo acá es ambiguo, se resuelve acá — no en el código de cada quien.

### 0.1 Modelo de datos real (tabla `bathrooms`)

| Columna | Tipo | Significado |
|---|---|---|
| `id_Bath` | int PK | ID |
| `codigo_Bath` | varchar | Código único (ej. `AT096`) |
| `fechaCompra_Bath` | date | Fecha de compra/ingreso |
| `observacion_Bath` | text | Observaciones técnicas |
| `estado_Bath` | int | `1` = Activo, `2` = Mantención, `0` = Inactivo (fuera de servicio) |
| `asignado_Bath` | int | `1` = asignado a obra, `0` = disponible |

Relación baño↔obra: tabla puente `contrato_bathroom (id_Relacion, id_Contrato, id_Bath)`.
Obra y cliente: `contratos.obra_Contrato`, `contratos.id_Cliente → clientes.nombre_Cliente`.
**Contrato activo:** `contratos.estado_Contrato = 2` (convención confirmada en `.doc/Viejos/checklist-fase2-historial-reglas-negocio.md`).

### 0.2 Mapeo de estados (mockup → datos)

La decisión vigente `[[2026-07-14-estado-bath-tres-valores]]` amplía el mockup a 4 estados visuales, en este orden de prioridad:

| Estado visual | Color | Condición sobre datos |
|---|---|---|
| **Inactivo** | slate | `estado_Bath = 0` (fuera de servicio) |
| **Mantención** | amber | `estado_Bath = 2` (en reparación temporal) |
| **Asignado** | blue | `estado_Bath = 1` **y** `asignado_Bath = 1` |
| **Disponible** (“En Bodega”) | emerald | `estado_Bath = 1` **y** `asignado_Bath = 0` |

Pills de filtro: `Todos` · `Disponibles` · `Asignados` · `Mantención` · `Inactivos`.

### 0.3 Contrato del listado (lo que Fase 1 debe entregar y Fase 2 consumir)

Nuevo método en el repositorio: **`listAllWithAssignment(): array`**. Cada item es un array asociativo con **exactamente** estas claves:

```
id_Bath, codigo_Bath, fechaCompra_Bath, observacion_Bath,
estado_Bath, asignado_Bath,
obra_Contrato,      // string|null — obra activa asignada (null si disponible/mantención)
nombre_Cliente      // string|null — cliente de esa obra (null si no aplica)
```

SQL de referencia (el backend AI ajusta bindings/edge-cases):

```sql
SELECT BT.id_Bath, BT.codigo_Bath, BT.fechaCompra_Bath, BT.observacion_Bath,
       BT.estado_Bath, BT.asignado_Bath,
       CT.obra_Contrato, CL.nombre_Cliente
FROM bathrooms BT
LEFT JOIN contrato_bathroom CB ON CB.id_Bath = BT.id_Bath
LEFT JOIN contratos CT ON CT.id_Contrato = CB.id_Contrato AND CT.estado_Contrato = 2
LEFT JOIN clientes CL ON CL.id_Cliente = CT.id_Cliente
WHERE BT.estado_Bath IN (0, 1, 2)
ORDER BY BT.fechaCompra_Bath DESC
```

> **Riesgo de datos a resolver en Fase 1:** un baño puede tener varias filas históricas en `contrato_bathroom`. El JOIN condicionado a `estado_Contrato = 2` reduce el ruido, pero si un baño quedara ligado a >1 contrato activo aparecería duplicado. El backend AI debe **garantizar 1 fila por baño** (ej. subconsulta que tome la relación más reciente, o `GROUP BY BT.id_Bath`). Documentar la decisión tomada.

### 0.4 Rutas, parámetros y nombres (congelados)

**Todo por query param sobre `dash-bathrooms.php`, patrón idéntico a customers:**

| Acción | URL |
|---|---|
| Abrir drawer alta | `?action=new` |
| Abrir drawer edición | `?action=edit&id=<id_Bath>` |
| Gestionar asignación | `?action=edit&id=<id_Bath>` (integrado en el drawer) |
| Filtro pill | `?filter=todos\|disponible\|asignado\|inactivo` |
| Paginación | `?page=<n>` |
| Cerrar overlay | vuelve a la URL sin `action`/`id`/`err` (helper `base_query_string`) |
| Flash toast | `?flash=success\|error&msg=<texto>` |

**Controllers nuevos (en `app/public/controller/`, patrón `customer-*-drawer.php`: validar → use case → `header('Location: …')` + `exit`):**

| Controller | Use case DDD que envuelve | Submit desde |
|---|---|---|
| `bath-create-drawer.php` | `App\Application\Bathroom\CreateBathroom` | drawer alta (`submit_new_bath`) |
| `bath-edit-drawer.php` | `App\Application\Bathroom\UpdateBathroom` | drawer edición (`submit_edit_bath`) |
| `bath-assign-drawer.php` | `App\Application\Bathroom\AssignBathroomToContract` | sección de asignación del drawer (`submit_assign_bath`) |
| `bath-unassign-drawer.php` | `App\Application\Bathroom\UnassignBathroomFromContract` | acción “Retirar” (con confirm) |

> Los controllers viejos (`bath-new.php`, `bath-update.php`, `bath-notassign.php`) se dejan intactos. `dash-bathrooms-add.php` / `dash-bathrooms-edit.php` también se conservan porque todavía tienen referencias desde controllers, layouts y `dash-bathrooms-contracts.php`; se migrarán o retirarán en una tarea separada.

**IDs / clases DOM que el JS (Fase 4) va a enganchar** (congelados para que markup y JS no se desincronicen):

```
#banos-local-search          input de búsqueda
#new-bath-btn                botón “Registrar Baño”
[data-view-toggle="grid"]    botón toggle grid
[data-view-toggle="table"]   botón toggle tabla
#banos-grid                  contenedor grid de cards
#banos-table                 contenedor tabla
.bano-card / .bano-row       unidad filtrable (data-* para search client-side)
#bath-drawer                 panel del drawer (slide-in JS)
```

### 0.5 Convenciones no negociables (de `CLAUDE.md`/`AGENTS.md`)

- **Escapar TODO output** con `htmlspecialchars($v, ENT_QUOTES, 'UTF-8')`. IDs enteros con `(int)`.
- **Prepared statements siempre** en los controllers nuevos y en el repo. Nunca interpolación.
- **Nada de namespaces/clases en `app/public/`** — solo en `app/src/`. Las vistas/controllers son procedurales y reciben las clases DDD ya migradas.
- SQL en MAYÚSCULAS, columnas `{campo}_{Tabla}`, variables/funciones `snake_case`.
- Validar `$_POST`/`$_GET` en el boundary (controllers).
- Tras cambios PHP: `docker-compose restart php` + smoke manual. Tras cambios en `app/src/`: `docker-compose exec php composer dump-autoload`. **No commitear sin pedido explícito.**
- Tailwind: el CSS ya está compilado; usar utilities existentes. Si hiciera falta un token/clase nueva, va en `app/public/assets/css/tw/` + `pnpm tw:build` (no debería ser necesario para esta vista).

---

## 1. Reparto por modelo

| Fase | Responsable | Archivos (propiedad exclusiva) | Depende de |
|---|---|---|---|
| **0 — Contrato de datos** | **Sonnet 5** (lead) | *(esta sección del doc)* | — |
| **1 — Backend / DDD** | **Sonnet 5** | `app/src/**` (repo + use case), `controller/bath-*-drawer.php` (nuevos) | Fase 0 |
| **2 — Vista principal (toolbar + pills + grid/tabla + paginación)** | **GLM-5.2** | `app/public/dash-bathrooms.php` | Fase 0 (contrato 0.3) |
| **3 — Drawer unificado alta/edición/asignación** | **GLM-5.2** | `app/public/dash-bathrooms.php` (mismo archivo, continúa) | Fase 2 |
| **4 — JS de interactividad** | **MiniMax-M3** | `app/public/assets/js/components/bathrooms.js` (nuevo) + `<script>` mínimo inline | Fase 2/3 (IDs de 0.4) |
| **5 — QA, seguridad y consistencia** | **MiniMax-M3** (+ review Sonnet) | *(read-only + fixes puntuales)* | Todas |

**Por qué este reparto:** cada archivo tiene **un solo dueño** → cero conflictos de merge y un único “acento” por archivo. `dash-bathrooms.php` lo hace un solo modelo (Fases 2+3, es el mismo archivo). El backend (archivos distintos) y el JS (archivo nuevo separado) corren en paralelo real una vez congelada la Fase 0.

**Paralelismo posible:** Fase 1 (Sonnet, backend) y Fase 2 (GLM, markup contra el contrato 0.3) pueden ir **a la vez** — se integran al final. Fase 3 sigue a Fase 2 (mismo archivo). Fase 4 necesita los IDs ya presentes (Fase 2/3). Fase 5 cierra.

**Protocolo de handoff:** al terminar su fase, cada IA deja al final de este doc una línea en `## 9. Bitácora de ejecución` con: fase, archivos tocados, decisiones tomadas (sobre todo la del riesgo 0.3), y comando de verificación corrido. La siguiente IA lee la bitácora antes de arrancar.

### 1.1 Progreso de ejecución (checklist maestro)

> Vista panorámica de hitos. **El detalle por ítem está en los checklists de cada fase** (secciones 2-6); este maestro no los duplica, los resume. Se actualiza a medida que cada IA completa su trabajo.

Leyenda de responsables: **S5** = Sonnet 5 · **GLM** = GLM-5.2 · **M3** = MiniMax-M3.

**Fase 0 — Contrato de datos (S5)**
- [ ] 0.1-0.5 congelado y confirmado por el lead

**Fase 1 — Backend / DDD (S5)**
- [ ] `listAllWithAssignment()` en repo + interfaz (1 fila por baño)
- [ ] Use case `ListBathroomsWithAssignment` (forma `['total','items']`)
- [ ] 4 controllers `bath-{create,edit,assign,unassign}-drawer.php`
- [ ] `composer dump-autoload` OK + `php -l` OK en controllers

**Fase 2 — Vista principal (GLM)** · archivo: `dash-bathrooms.php`
- [x] Cabecera PHP (helper `base_query_string`, query params, filtro pill 0.2, paginación server-side) — 2026-07-14 GLM
- [x] Toolbar (search `#banos-local-search` + pills + toggle grid/tabla + `#new-bath-btn`) — 2026-07-14 GLM
- [x] Grid de cards `#banos-grid` (color por estado 0.2, obra+cliente en asignados) — 2026-07-14 GLM
- [x] Tabla `#banos-table` con `data-*` en `.bano-row` — 2026-07-14 GLM
- [x] Footer de paginación (respeta filtro activo) — 2026-07-14 GLM

**Fase 3 — Drawer unificado (GLM)** · archivo: `dash-bathrooms.php`
- [x] Drawer alta/edición `#bath-drawer` (`?action=new` / `?action=edit&id`, slide-in + Escape) — 2026-07-14 GLM
- [x] Asignación y retiro integrados en el drawer de edición — 2026-07-14 GLM
- [x] "Retirar" con confirm Swal → POST a `bath-unassign-drawer.php` — 2026-07-14 GLM/M3
- [x] Flash toast Swal para `?flash` — 2026-07-14 GLM

**Fase 4 — JS de interactividad (M3)** · archivo: `assets/js/components/bathrooms.js`
- [x] Toggle grid/tabla con persistencia en `localStorage` — 2026-07-14 M3
- [x] Búsqueda client-side sobre `.bano-card` / `.bano-row` — 2026-07-14 M3

**Fase 5 — QA, seguridad y consistencia (M3 + review S5)**
- [x] Smoke funcional de los 9 puntos mediante HTTP local + DOM simulado — 2026-07-14 M3
- [x] Auditoría de escape / prepared statements / naming — 2026-07-14 M3
- [x] Evaluación de `dash-bathrooms-add.php` / `dash-bathrooms-edit.php`: se conservan porque siguen referenciadas — 2026-07-14 M3
- [ ] Inspección visual manual en navegador — bloqueada por permisos de Accesibilidad de Orca

### 1.2 Protocolo de marcado

- Al completar un hito, cambiar `[ ]` → `[x]` y agregar sufijo `— YYYY-MM-DD <inicial>` (ej. `[x] — 2026-07-14 GLM`).
- Un ítem se marca **solo tras pasar su verificación** (la del checklist detallado de su fase), no al empezar.
- Cada hito marcado **debe tener su entrada en `## 9. Bitácora de ejecución`** (archivos tocados + decisión + comando de verificación).
- Cada IA marca **solo sus propios hitos**; la Fase 5 la marcan M3 (items) y S5 (review).
- Si un hito se revierte en QA, vuelve a `[ ]` y se anota el motivo en la bitácora.

---

## 2. Fase 1 — Backend / DDD (Sonnet 5)

**Archivos:**
- `app/src/Infrastructure/Persistence/MysqliBathroomRepository.php` — agregar `listAllWithAssignment()` (SQL de 0.3, garantizando 1 fila por baño).
- `app/src/Application/Bathroom/` — nuevo use case `ListBathroomsWithAssignment` (o extender `ListBathrooms` con un método `handleWithAssignment()`). Devuelve `['total' => int, 'items' => array]`, misma forma que hoy.
- `app/src/Domain/Bathroom/BathroomRepositoryInterface.php` — declarar el método nuevo.
- `controller/bath-create-drawer.php`, `bath-edit-drawer.php`, `bath-assign-drawer.php`, `bath-unassign-drawer.php` — nuevos, patrón `customer-create-drawer.php`:
  - `REQUEST_METHOD === 'POST'` (salvo unassign, que puede ser GET con confirm) + `isset($_POST[<flag>])`, si no → redirect con `err`.
  - Validar y `trim()` inputs; código de baño obligatorio; fecha válida; unicidad de código vía `codeExists()`.
  - Envolver el use case en `try/catch (\mysqli_sql_exception)`.
  - Éxito → `?flash=success&msg=…`; error de validación → `?action=…&err=…`.
- Regenerar autoload: `docker-compose exec php composer dump-autoload`.

**Checklist de aceptación Fase 1:**
- [x] `listAllWithAssignment()` devuelve exactamente las claves de 0.3, **una fila por baño**, con `obra_Contrato`/`nombre_Cliente` correctos para asignados y `null` para el resto. Verificado con los 100 baños reales de la DB local: 100 ids únicos, sin duplicados. (Excepción de datos preexistente documentada en la bitácora — no invalida la query: cuando no hay relación activa, devuelve `null` correctamente.)
- [x] Los 4 controllers usan prepared statements (vía use case → repository) y redirigen con flash/err.
- [x] `php -l` OK en los 7 archivos tocados (interface, repo, use case, 4 controllers); `composer dump-autoload -d /var/www` sin errores.
- [x] Alta, edición, asignar y retirar probados vía `curl` con un baño de prueba real (`TEST-QA-001`, id 117): incluyó también los casos negativos (código duplicado rechazado, reasignar un baño ya asignado rechazado, retirar un baño no asignado rechazado). Registro de prueba eliminado al terminar, DB sin residuos.
- [x] Decisión del riesgo 0.3 documentada en la bitácora (sección 9), junto con las otras 3 decisiones de esta fase (gap de `id_Relacion`/`id_Contrato` para "Retirar", reuso de `ListContracts` para el select de asignación, y el hallazgo de datos de los 10 baños con asignación huérfana).

---

## 3. Fase 2 — Vista principal (GLM-5.2)

**Archivo:** `app/public/dash-bathrooms.php` (reescritura completa del `<body>`; el `<?php … ?>` de cabecera se adapta como en customers).

Replicar de `dash-customers.php`:
- Cabecera PHP: `require autoload`, `use` del use case nuevo, `include config.php`, leer `?filter`, `?page`, `?action`, `?id`, `?err`, `?flash`. Helper `base_query_string()` y filtrado del listado según el pill activo (mapeo 0.2). Paginación server-side antes de renderizar.
- Layout: `layouts/session.php`, `head-main.php`, `head.php`, `head-style.php`, `body.php`, `menu.php`, `vendor-scripts.php`. Contenedor `main-content > page-content > container-fluid px-10 py-10 bg-slate-50/50`.

Bloques (según `BanosView.tsx`):
- **Toolbar** (`BanosView.tsx:135-201`): buscador con lupa (`#banos-local-search`), **pills** de filtro (`Todos/Disponibles/Asignados/Mantención/Inactivos`) como `<a href="?filter=…">` con estado activo resaltado, toggle **grid/tabla** (`[data-view-toggle]`), botón **“Registrar Baño”** (`#new-bath-btn`, `href="?action=new…"`, emerald como el mockup).
- **Grid de cards** (`#banos-grid`, `BanosView.tsx:204-279`): card `rounded-3xl` con acento superior por estado (0.2), código en `font-mono`, punto de estado, cuerpo (obra+cliente si asignado / observación si mantención o inactivo / “En Bodega” si disponible) y acceso al drawer unificado mediante “Editar baño”. Cada card `.bano-card` incluye `data-estado`/`data-asignado`/`data-code`/`data-obra`/`data-cliente` para el search JS.
- **Tabla** (`#banos-table`, oculta por defecto, `BanosView.tsx:280-341`): columnas Código · F. Adquisición · Ubicación Actual · Observaciones · Estado Técnico · Acción. Filas `.bano-row` con los mismos `data-*`.
- **Footer de paginación** idéntico a customers (`dash-customers.php:246-261`).
- **Escapar todo** (0.5). Fecha mostrada con el `slice` del mockup (`C: {fecha}`) es opcional; usar la fecha real escapada.

**Checklist Fase 2:**
- [ ] `php -l dash-bathrooms.php` OK.
- [ ] Grid renderiza cards con el color correcto por estado (0.2); asignados muestran obra+cliente reales.
- [ ] Pills cambian el listado vía `?filter=` (server-side, correcto sobre TODOS los registros, no solo la página).
- [ ] Paginación funciona y respeta el filtro activo (`base_query_string`).
- [ ] Todo output escapado; IDs con `(int)`.

---

## 4. Fase 3 — Drawer unificado alta/edición/asignación (GLM-5.2, mismo archivo)

El drawer `?action=new` / `?action=edit&id=` replica el patrón de customers y concentra edición y gestión de obra:

- **Alta/edición:** overlay + `#bath-drawer`, slide-in, foco inicial y cierre con Escape. Campos Código, Fecha de compra, Estado (`Activo/Mantención/Inactivo`) y Observaciones. Forms POST a `bath-create-drawer.php` / `bath-edit-drawer.php` con CSRF.
- **Asignación integrada:** al editar un baño Activo y disponible, el drawer lista contratos activos (`estado_Contrato = 2`) mediante `ListContracts->handle(2)` y envía a `bath-assign-drawer.php`.
- **Retiro integrado:** al editar un baño asignado, el drawer muestra obra/cliente y un POST con CSRF a `bath-unassign-drawer.php`, previa confirmación SweetAlert2.
- **Flash toast:** el bloque Swal procesa `?flash=success|error&msg=...`.

**Checklist Fase 3:**
- [ ] Drawer abre/cierra por query param + overlay + Escape; alta y edición envían al controller correcto y vuelven con flash.
- [ ] La sección de asignación lista solo contratos activos y asigna correctamente (`asignado_Bath = 1`).
- [ ] “Retirar” pide confirmación Swal, envía POST con CSRF y desasigna.
- [ ] Valores en edición precargados y escapados; sin XSS en `err`/`msg`.

---

## 5. Fase 4 — JS de interactividad (MiniMax-M3)

**Archivo nuevo:** `app/public/assets/js/components/bathrooms.js`, cargado al final de `dash-bathrooms.php` (después de `vendor-scripts.php`). Sin dependencias nuevas (jQuery ya está disponible; preferir vanilla).

- **Toggle grid/tabla** (`[data-view-toggle]`): mostrar/ocultar `#banos-grid`/`#banos-table`, marcar el botón activo (estilo del mockup: `bg-white shadow-sm`), **persistir en `localStorage`** y restaurar al cargar.
- **Búsqueda client-side** (`#banos-local-search`): filtra `.bano-card`/`.bano-row` por `data-code`/obra/cliente (mismo criterio que el mockup, `BanosView.tsx:118-129`). Nota: filtra la **página actual** (misma limitación conocida que el search de customers) — los pills siguen siendo server-side para correctitud global.
- **Estado activo de pills**: si se resuelven 100% server-side (recomendado), el JS no toca esto; solo asegurar que el `<a>` activo tenga la clase resaltada (lo pinta PHP).
- No introducir modales JS que disparen `alert/confirm` nativos que bloqueen el runtime (usar Swal, ya presente).

**Checklist Fase 4:**
- [x] Toggle persiste entre recargas; la preferencia se aplica en `<head>` antes del primer render.
- [x] Search filtra cards y filas en vivo; vaciar el input restaura todo.
- [x] `bathrooms.js` pasa `node --check`; Lucide no requiere re-inicialización porque el markup es server-rendered.

---

## 6. Fase 5 — QA, seguridad y consistencia (MiniMax-M3 + review Sonnet)

**Matriz de smoke test manual** (`http://localhost`, `docker-compose restart php` antes):

1. Listado carga; contadores y estados correctos; asignados muestran obra+cliente reales.
2. Pills: cada filtro devuelve el subconjunto correcto sobre todos los registros; combina con paginación.
3. Grid↔Tabla: toggle + persistencia.
4. Búsqueda: filtra por código/obra/cliente.
5. Alta: validaciones (código obligatorio, duplicado bloqueado), éxito con flash, aparece en el listado.
6. Edición: precarga, guarda, flash.
7. Asignar: solo contratos activos, asigna, baño pasa a “Asignado” con obra correcta.
8. Retirar: confirm, vuelve a “Disponible”.
9. Activar/Mantención/Inactivar/Eliminar (acciones existentes): siguen funcionando.

**Auditoría de seguridad/consistencia:**
- [x] `grep` de output sin escapar en `dash-bathrooms.php` (todo `echo` de datos con `htmlspecialchars`/`(int)`).
- [x] Controllers nuevos: sesión obligatoria, CSRF, prepared statements, validación en boundary y `exit` tras `header`.
- [x] Sin namespaces/clases en `app/public/`; naming y SQL según 0.5.
- [x] `php -l` en todos los archivos tocados; `docker-compose logs php` sin warnings.
- [ ] Fidelidad visual final vs `BanosView.tsx`: pendiente inspección manual por permisos de Accesibilidad de Orca.
- [x] `dash-bathrooms-add.php` / `dash-bathrooms-edit.php` se conservan: siguen referenciadas desde controllers, layouts y `dash-bathrooms-contracts.php`. Los controllers viejos quedan intactos.

---

## 7. Fuera de alcance

- Migrar otras vistas de lista a este patrón (Servicios, Contratos, Facturas, Certificados, Usuarios): reutilizable cuando se pida, no ahora.
- `controller/servicio-pdf.php` (SQL injection + bug de columna conocido) — es otra tarea, ver `AGENTS.md`.
- Popper.js para dropdowns con flip: el shim actual alcanza.
- Corrección de los 41 baños duplicados en producción (`.doc/Viejos/informe-produccion-banos-duplicados.md`) — problema de datos, independiente de esta UI.

---

## 8. Referencias

- **Template de diseño:** `rebranding/src/components/BanosView.tsx`.
- **Patrón canónico a replicar:** `app/public/dash-customers.php` + `controller/customer-create-drawer.php`.
- **Vista a reescribir:** `app/public/dash-bathrooms.php`.
- **Capa DDD existente:** `app/src/Domain/Bathroom/`, `app/src/Application/Bathroom/` (13 use cases, incluye `AssignBathroomToContract`, `UnassignBathroomFromContract`, `CreateBathroom`, `UpdateBathroom`), `app/src/Infrastructure/Persistence/MysqliBathroomRepository.php`.
- **Reglas del proyecto:** `AGENTS.md` (SQL, seguridad, gotchas de Docker/composer).

---

## 9. Bitácora de ejecución

> Cada IA agrega su línea al terminar su fase (fase · archivos · decisiones · verificación).

- **Fase 1 (Sonnet 5, backend/DDD) — 2026-07-14.**
  **Archivos:** `app/src/Domain/Bathroom/BathroomRepositoryInterface.php` (2 métodos nuevos), `app/src/Infrastructure/Persistence/MysqliBathroomRepository.php` (implementación), `app/src/Application/Bathroom/ListBathroomsWithAssignment.php` (nuevo), `app/public/controller/bath-create-drawer.php`, `bath-edit-drawer.php`, `bath-assign-drawer.php`, `bath-unassign-drawer.php` (nuevos).

  **Decisiones tomadas:**
  1. **Riesgo 0.3 (duplicados por relación histórica):** resuelto con `GROUP BY BT.id_Bath, ...` + `MAX(CT.obra_Contrato)`/`MAX(CL.nombre_Cliente)` sobre el LEFT JOIN condicionado a `estado_Contrato = 2`. Colapsa a 1 fila por baño incluso en el caso patológico de >1 relación activa simultánea (no debería pasar por regla de negocio, pero el JOIN por sí solo no lo garantiza). Verificado con 100 baños reales: 100 ids únicos en `listAllWithAssignment()`.
  2. **Gap del contrato 0.3 para "Retirar":** `UnassignBathroomFromContract::handle()` exige `id_Relacion` + `id_Contrato`, pero las claves congeladas en 0.3 (que consume la vista) no los incluyen — solo `id_Bath`. En vez de ensanchar el contrato 0.3 (rompería lo ya congelado para GLM), agregué `BathroomRepositoryInterface::findActiveAssignment(int $idBath): ?array` que resuelve `id_Relacion`/`id_Contrato` a partir de `id_Bath` (mismo criterio `estado_Contrato = 2`, `ORDER BY id_Relacion DESC LIMIT 1` como tie-break). **Fase 3 (GLM) debe construir el link "Retirar" como `controller/bath-unassign-drawer.php?id_Bath=<id_Bath>`** (GET, sin flag de submit — mismo patrón que el viejo `bath-notassign.php`), no como `?id_Relacion=...&id_Contrato=...`.
  3. **`ListActiveContracts` (necesario para el `<select>` del modal de asignación, Fase 3):** no hace falta un use case nuevo. El dominio `Contract` (ya migrado) expone `ListContracts::handle(2)` → `['total'=>int,'items'=>array]` con `obra_Contrato`/`nombre_Cliente`/`id_Contrato` ya joineados vía `listWithCustomerName(2)`. GLM debe reusar esto tal cual.
  4. **`bath-assign-drawer.php`** valida que el baño esté `estado_Bath = 1` (Activo) y `asignado_Bath = 0` (disponible) antes de asignar, y que el contrato exista con `estado_Contrato = 2`, usando `FindBathroom`/`FindContract` (ambos dominios ya migrados). Rechaza reasignar un baño ya asignado.

  **⚠️ Hallazgo de datos (no es bug de esta fase, hay que anotarlo para Fase 2/3/5):** 10 baños en la DB local (`donbano`) tienen `asignado_Bath = 1` pero su única relación en `contrato_bathroom` apunta a un contrato con `estado_Contrato = 1` (cerrado), no `2`. `listAllWithAssignment()` los devuelve correctamente como `asignado_Bath=1` con `obra_Contrato`/`nombre_Cliente` en `null` (no encuentra relación activa) — es fiel al dato real, no un bug de la query. Visualmente en el mockup esto se vería como card "Asignado" (azul) sin obra/cliente. **Fase 2/3 (GLM) debería mostrar un fallback tipo "Obra no disponible" en ese caso en vez de dejarlo en blanco.** IDs afectados en este dump: 114, 32, 17, 34, 45, 53, 80, 47, 44, 38. Posible relación con `.doc/Viejos/informe-produccion-banos-duplicados.md` (fuera de alcance arreglar los datos, sección 7).

  **Verificación corrida:**
  - `php -l` OK en los 7 archivos (interface, repo, use case, 4 controllers).
  - `docker-compose exec php composer dump-autoload -d /var/www` OK.
  - Smoke test funcional vía `curl` sobre los 4 controllers con un baño de prueba (`TEST-QA-001`, id 117): alta OK, alta duplicada rechazada OK, edición OK, asignación a contrato activo OK, reasignación de un baño ya asignado rechazada OK, `listAllWithAssignment()` devuelve obra/cliente correctos para el asignado OK, retiro OK (contrato no se cierra si le quedan otros baños), retiro de un baño no asignado rechazado OK. Baño de prueba eliminado al final, DB sin residuos.

- **Fase 2+3 — GLM-5.2 (2026-07-14).**
  **Archivos:** `app/public/dash-bathrooms.php` (rewrite completo: 179 → ~510 líneas). Sin tocar `app/src/` ni controllers (propiedad de Sonnet, intactos).

  **Decisiones tomadas:**
  1. **Consumo del contrato 0.3 tal cual:** leí `ListBathroomsWithAssignment` + `listAllWithAssignment()` y usé las 8 claves exactas (`id_Bath, codigo_Bath, fechaCompra_Bath, observacion_Bath, estado_Bath, asignado_Bath, obra_Contrato, nombre_Cliente`). Confirmé la decisión 0.3 de Sonnet (`GROUP BY BT.id_Bath`) — 1 fila por baño OK.
  2. **Filtro pill 100% server-side** sobre `$bañosAll` (todos los registros) ANTES de paginar. `totalBaños = count($filtrado)` (no `count()` del repo, que cuenta sin filtrar). Paginación preserva `filter` vía `baseQueryString(['page'])`. Pills: `todos/disponible/asignado/inactivo` con mapeo 0.2 exacto.
  3. **Modal asignar — use case existente:** usé `ListContracts->handle(2)` (decisión 0.4/3 ya tomada por Sonnet en su punto 3 de la bitácora). NO se creó `ListActiveContracts`. Select muestra `obra_Contrato (nombre_Cliente)`; si no hay contratos activos, el botón queda disabled con aviso amber.
  4. **"Retirar" como GET** a `bath-unassign-drawer.php?id_Bath=<id>` (decisión de Sonnet, punto 2 de su bitácora) con `data-confirm-delete` (feedback.js → Swal confirm → `window.location = href`). Sin flag de submit.
  5. **Acceso a edición:** código del baño clicable → `?action=edit&id=` (mismo affordance que `dash-customers.php`), tanto en grid (sobre ícono+código) como en tabla (código) + entrada "Editar" en el dropdown.
  6. **Acciones administrativas (activar/inactivar/eliminar):** conservadas en el dropdown de la **tabla** (checklist Fase 5 las requiere). La **card** del grid queda fiel al mockup (footer con acción contextual únicamente, sin dropdown). Es un desvío menor del mockup (que no las muestra) pero necesario para no perder funciones existentes.
  7. **Fallback "Obra no disponible"** (hallazgo de Sonnet, sus 10 baños con `asignado_Bath=1` pero `obra_Contrato=null`): en tone `blue` con `$obra === ''`, muestro "Obra no disponible" (italic slate-400) en grid y tabla, en vez de strings vacíos. No arreglé los datos (fuera de alcance, sección 7 / informe duplicados).
  8. **Sin datatables/app.js** (tabla nativa como customers y el mockup). Swal/jQuery/Lucide/bs-shim vía `vendor-scripts.php`. Quité los 12 `<script>` de datatables y el bloque `$(document).ready` del dash-bathrooms viejo.

  **Para MiniMax (Fase 4):** NO cargué `assets/js/components/bathrooms.js` (es tu archivo). Dejé `#banos-grid` visible, `#banos-table` con `hidden`, y los botones `[data-view-toggle="grid|table"]` presentes (grid marcado activo por defecto). Al crear `bathrooms.js`, agregá el `<script src>` (o inline) al final de `dash-bathrooms.php` — esa carga es parte de tu Fase 4. IDs/search congelados de 0.4 todos presentes: `#banos-local-search`, `#new-bath-btn`, `#banos-grid`, `#banos-table`, `.bano-card`/`.bano-row` con `data-estado`/`data-asignado`/`data-code`/`data-obra`/`data-cliente`, `#bath-drawer`.

  **Verificación corrida:**
  - `php -l /var/www/html/dash-bathrooms.php` OK (2 pasadas: corregí warning `Undefined array key "filter"` línea 24 con patrón `$rawFilter = $_GET['filter'] ?? 'todos'`).
  - `docker-compose restart php` OK.
  - GET `/dash-bathrooms.php` → 302 sin sesión (redirect a login, esperado; no valida render completo). Logs php sin warnings tras el fix.
  - Smoke visual en navegador pendiente — lo hace Fase 5 (MiniMax + review S5).

- **Fase 4 — MiniMax-M3 (2026-07-14).**
  **Archivos:** `app/public/assets/js/components/bathrooms.js` (nuevo) y `app/public/dash-bathrooms.php` (carga del script + restauración crítica de vista antes del render).

  **Decisiones tomadas:**
  1. Toggle grid/tabla en vanilla JS, con estado activo accesible (`aria-pressed`), persistencia bajo `localStorage['bathrooms-view']` y CSS crítico en `<head>` para evitar mostrar primero la vista incorrecta.
  2. Búsqueda local normalizada sin tildes sobre código, obra y cliente; filtra cards y filas de la página actual, como define el alcance.
  3. La confirmación de retiro usa SweetAlert2 y envía un formulario POST; no usa `confirm()` nativo.

  **Verificación corrida:** `node --check` OK y prueba DOM simulada OK para restauración desde `localStorage`, cambio de vista, búsqueda y limpieza del filtro.

- **Fase 5 — MiniMax-M3 (2026-07-14).**
  **Archivos:** `app/public/dash-bathrooms.php`, `app/public/layouts/helpers.php`, los 4 controllers `bath-*-drawer.php`, `app/src/Infrastructure/Persistence/MysqliBathroomRepository.php`, `app/public/assets/css/tailwind.css` y este plan.

  **Decisiones y correcciones:**
  1. Los controllers nuevos ahora exigen sesión autenticada y token CSRF. Retirar cambió de GET a POST con confirmación Swal. Los errores sin overlay vuelven como flash visible.
  2. `base_query_string()` usa una whitelist de parámetros escalares y `http_build_query()`, cerrando la inyección mediante nombres de query params.
  3. La resolución de asignación activa por `GROUP BY` + `MAX(obra)`/`MAX(cliente)` de Fase 1 quedó superada: podía combinar obra y cliente de contratos distintos. El repo ahora toma la relación activa más reciente por `MAX(id_Relacion)`, manteniendo una fila y un par obra/cliente real.
  4. Asignar y retirar son transaccionales, bloquean el baño con `FOR UPDATE` y rechazan asignaciones activas duplicadas. Al retirar, `asignado_Bath` permanece en `1` si todavía existe otra relación activa.
  5. `dash-bathrooms-add.php` y `dash-bathrooms-edit.php` no se eliminaron porque siguen referenciadas desde controllers, layouts y `dash-bathrooms-contracts.php`.
  6. Se integró la decisión vigente de tres estados técnicos (`0` Inactivo, `1` Activo, `2` Mantención). La asignación y el retiro quedaron dentro del drawer de edición; los errores de asignación reabren ese drawer.

  **Verificación corrida:** autoload regenerado; `php -l` OK en los archivos PHP tocados; `node --check` OK; `pnpm tw:build` OK; 100 baños con IDs únicos, 8 claves exactas y pares obra/cliente válidos; auth y CSRF bloquean los 4 endpoints; smoke temporal alta/duplicado/edición/asignación/retiro OK con limpieza; estados `0/1/2` y acciones activar/mantención/inactivar/eliminar OK con limpieza; los cinco filtros, drawer unificado y payload XSS renderizan correctamente vía HTTP autenticado; logs PHP sin warnings. Revisión read-only final sin bloqueantes. La inspección visual manual queda pendiente porque Orca no tiene permiso de Accesibilidad.
