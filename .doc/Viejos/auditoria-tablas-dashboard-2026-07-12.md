# Auditoría de tablas/grillas del Dashboard (frontend)

**Origen:** relevamiento estático del código de las vistas del dashboard en `app/public/` (`dash-*.php`), con foco en las `<table>` y sus equivalentes DataTables. Auditoría manual, sin tests automatizados — smoke test pendiente para validar comportamiento real en `http://localhost`.
**Fecha:** 2026-07-12.
**Audiencia:** cualquier dev o IA que vaya a tocar una de las 13 vistas con grilla sin contexto previo.
**Estado:** 📋 informe — nada remediado todavía. Los sprints de remediación propuestos al final son la fase siguiente.

---

## 0. Cómo leer este documento

- Las **vistas con grilla** se agrupan en dos categorías: "listados principales" (DataTables + filtros + paginación) y "sub-grids" (tablas HTML planas en vistas de detalle, sin sort/search).
- Cada hallazgo está numerado y mapeado a su archivo + línea(s) exacta(s). Los códigos están en bloques `php` con resaltado de línea cuando ayuda.
- Los hallazgos están priorizados en 4 niveles: 🔴 críticos (seguridad/correctness), 🟠 altos (UX/consistencia), 🟡 medios (estructura/deuda), 🟢 bajos (nice-to-have).
- Al final hay un **plan de remediación en 3 sprints** con esfuerzo e impacto estimado — ejecutables independientemente.

Reglas del proyecto que aplican a cualquier remediación (de `AGENTS.md`, no se repiten en cada hallazgo):

- `snake_case` para variables/funciones nuevas, SQL en MAYÚSCULAS, columnas `{nombre}_{Tabla}`, tablas en plural.
- No mezclar refactors con features — cada sprint es un cambio acotado, verificable manualmente.
- Después de cualquier cambio de código PHP: `docker-compose up -d --force-recreate php` (o `restart php` si no se tocó `composer.json`/`src/`) + smoke test en `http://localhost`.
- Después de cambios en `app/src/`: `docker-compose exec php composer dump-autoload`.
- No tocar `app/public/archive/` ni `app/public/assets/libs/` (librerías vendorizadas) salvo que un sprint lo diga explícitamente.

---

## 1. Mapa de cobertura

| Categoría | Cantidad | Vistas | Stack de grilla |
|---|---|---|---|
| **Listados principales** | 7 | `dash-bathrooms.php`, `dash-certificates.php`, `dash-contracts.php`, `dash-customers.php`, `dash-invoices-list.php`, `dash-services.php`, `dash-users-list.php` | `DataTable.init('#datatable-buttons', {...})` (vía `assets/js/components/datatable.js`) |
| **Sub-grids** | 6 | `dash-bathrooms-contracts.php`, `dash-bathrooms-contracts-status.php`, `dash-customers-item.php`, `dash-contracts-item.php`, `dash-services-bath.php`, `dash-invoices-detail.php` | `<table class="table table-nowrap align-middle">` plano, **sin sort/search/paginación** |
| **Preview/result de upload** | 2 | `dash-invoices-upload-preview.php`, `dash-invoices-upload-result.php` | `<table>` plano |
| **Sin tabla** (formularios / perfil / PDF / login) | 17 | add/edit/item/profile/print/index/auth-* | N/A |

**Conclusión 1.1:** los 7 listados principales ya están bien abstraídos detrás de `DataTable.init()` (un solo componente JS, idioma español unificado, soporte de `data-dt-state` y `data-dt-column-filters`). Las 6 sub-grids son la grieta principal — mismas tablas con UX inferior.

**Conclusión 1.2:** existen 6 vistas que usan el mismo ID `#datatable-buttons` que los listados. No chocan porque cada vista carga solo una tabla. Si en el futuro alguna vista necesita 2 tablas (caso `dash-bathrooms-contracts-status.php`, que hoy instancia DataTable dos veces con IDs distintos), hay que abandonar `#datatable-buttons` como ID genérico a favor de IDs específicos.

---

## 2. 🔴 Hallazgos críticos (seguridad / correctness)

### 2.1 IDs numéricos sin cast `(int)` al renderizar URLs

Los 7 listados principales y el sub-grid de baños imprimen IDs directo en `href` sin typecast. Aunque hoy la columna de ID es numérica en todas las entidades migradas, no hay defensa explícita — un cambio de modelo que devuelva string expone XSS.

| Archivo | Línea(s) | Variable implicada | Notas |
|---|---|---|---|
| `dash-bathrooms.php` | 139, 148, 149, 150, 153 | `$row['id_Bath']` | hrefs a `dash-bathrooms-edit.php`, `controller/bath-*.php` |
| `dash-certificates.php` | 86, 95, 98 | `$row['id_Certificado']`, `$row['id_Contrato']` | hrefs a item, pdf, remove |
| `dash-contracts.php` | 121, 130, 131, 133 | `$row['id_Contrato']` | hrefs a edit, item, active, inactive |
| `dash-customers.php` | 87, 95, 105 | `$customer->id` | **es objeto del modelo**, no se aplica `(int)` al render — falla por omisión |
| `dash-invoices-list.php` | 127, 136, 137, 140, 161, 162, 163 | `$row['id_Factura']`, `$row['id_Contrato']`, `$row['estado_Factura']` | `estado_Factura` tampoco está validado contra whitelist `{1,2,3}` antes de inyectar en URL |
| `dash-services.php` | 106, 115, 116, 119 | `$row['id_Servicio']` | hrefs a edit, bath, print, inactive |
| `dash-users-list.php` | 97, 110, 111, 113, 119-123 | `$row['id']`, `$row['category']` | ⚠️ `category` se inyecta como valor de querystring sin whitelist → usuario puede escalar permisos desde la URL si cambia el handler de `user-setadmin.php` |
| `dash-bathrooms-contracts.php` | (no inyecta href hoy, solo muestra) | — | OK por ahora |
| `dash-bathrooms-contracts-status.php` | (no inyecta href hoy, solo muestra) | — | OK por ahora |

**Fix modelo:** introducir un helper `e_url_int($value)` en `layouts/helpers.php` que combine `(int)$v` + `htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8')` (este último solo defensivo). Aplicar en cada uno de los 7 archivos. El sprint S1 cubre este hallazgo + el 2.2.

### 2.2 Eliminación por GET sin protección CSRF

Las acciones destructivas son `<a href="controller/...?id=...">`. El atributo `data-confirm-delete` dispara un modal de confirmación (SweetAlert2 vía `feedback.js`), pero la confirmación **es UX, no seguridad** — un `<img src="controller/bath-delete.php?id_Bath=42">` en cualquier página externa borra el baño si el admin está autenticado.

| Vista | Acción vulnerable | Endpoint |
|---|---|---|
| `dash-bathrooms.php` | Eliminar baño | `controller/bath-delete.php?id_Bath=...` |
| `dash-certificates.php` | Eliminar certificado | `controller/certificate-remove.php?id_Certificado=...` |
| `dash-customers.php` | Inactivar cliente | `controller/customer-inactive.php?id_Cliente=...` |
| `dash-invoices-list.php` | Anular factura | `controller/invoice-delete.php?id_Factura=...` |
| `dash-services.php` | Eliminar servicio | `controller/service-inactive.php?id_Servicio=...` |
| `dash-users-list.php` | Inactivar usuario | `controller/user-inactive.php?id_User=...` |
| `dash-users-list.php` | Reset password | `controller/user-default-pass.php?id_User=...` |
| `dash-users-list.php` | Set admin/user | `controller/user-setadmin.php?id_User=...&category=...` ⚠️ **el más grave** |
| `dash-bathrooms.php` | Activar / Inactivar / Deshacer asignación | `controller/bath-active.php`, `bath-inactive.php`, `bath-notassign.php` |

**Fix modelo:** convertir cada `dropdown-item` en un `<form method="POST" action="...">` con `<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf'] ?>">`. Los controllers validan el token antes de ejecutar la mutación. Para mantener el patrón de dropdown de Bootstrap, envolver cada `<li>` con un `<form>` inline (HTML válido).

### 2.3 `category` en `user-setadmin.php` sin whitelist en el render

`dash-users-list.php:111`:

```html
<a class="dropdown-item cat-admin" href="controller/user-setadmin.php?id_User=<?php echo $row['id'] ?>&category=<?php echo $row['category'] ?>">
```

La columna `category` se imprime sin cast y se pasa por querystring. Si el backend no valida whitelist, es trivial para un atacante escalar privilegios. **Verificar el handler de `user-setadmin.php` antes** — si el handler ya valida whitelist (esperable), el riesgo es solo "defensa por convención, no por código"; si no valida, es escalación directa.

**Fix modelo:** además del `(int)` + whitelist en el render, el handler debe rechazar cualquier `category` que no esté en `{1, 2}`.

---

## 3. 🟠 Hallazgos altos (consistencia / UX)

### 3.1 Sub-grids sin sort/search/paginación

Las siguientes 6 vistas tienen `<table>` HTML plano (clase `table table-nowrap align-middle`) sin inicializar DataTable. Para datasets de docenas/centenares de registros el usuario pierde ordenamiento, búsqueda y paginación gratuita.

| Vista | Sub-grilla | Volumen esperado | Líneas de la tabla |
|---|---|---|---|
| `dash-bathrooms-contracts.php` | Histórico baños × contratos (join 4 tablas en `mysqli_query` legacy) | cientos → miles | 67-127 |
| `dash-bathrooms-contracts-status.php` | Tabs "Contratos activos" + "Baños disponibles" (2 tablas en la misma vista) | cientos cada una | 99-127, 135-157 |
| `dash-customers-item.php` | Contactos del cliente | decenas | 152-203 |
| `dash-contracts-item.php` | Baños asignados a contrato | docenas | 168-213 |
| `dash-services-bath.php` | Baños asignados a servicio (con checkboxes de asignación al costado) | docenas | 269-296 |
| `dash-invoices-detail.php` | Líneas de factura (servicios facturados) | docenas | 204-230 |

**Fix modelo:** dar a cada `<table>` un `id` único (ej: `#datatable-contactos`, `#datatable-banos-contrato`, etc.) y reemplazar el `class="table table-nowrap align-middle"` por `class="table align-middle datatable dt-responsive nowrap w-100"` para que matchee el patrón existente. Luego invocar `DataTable.init('#<id>', {...})` en el `<script>` final. Costo estimado: ~1h para las 6 vistas.

Bonus: `dash-bathrooms-contracts-status.php` ya hoy define `dtLanguage` inline (líneas 196-211) — es duplicado literal del `spanishLanguage()` de `datatable.js:20-41`. Al refactorizar, eliminarlo entero y delegar al componente.

### 3.2 Contadores inconsistentes entre listados

El contrato de retorno de los use cases DDD no es uniforme — algunos devuelven `['items' => [...], 'total' => N]` y otros devuelven directamente un array indexado. Esto fuerza a las vistas a usar `count($x)` en algunos casos y `$x['total']` en otros.

| Vista | Código actual | Esperado | Estado del use case |
|---|---|---|---|
| `dash-bathrooms.php:63` | `$listado['total']` | `$listado['total']` | ✅ `ListBathrooms` ya devuelve `total` |
| `dash-certificates.php:53` | `$certificates['total']` | `$certificates['total']` | ✅ `ListCertificates` ya devuelve `total` |
| `dash-contracts.php:70` | `$listado['total']` | `$listado['total']` | ✅ `ListContracts` ya devuelve `total` |
| `dash-customers.php:61` | `$listado['total']` | `$listado['total']` | ✅ `ListCustomers` ya devuelve `total` |
| `dash-users-list.php:63` | `count($usuarios)` | `$usuarios['total']` | ❌ `ListActiveUsers` devuelve array indexado |
| `dash-services.php:60` | `count($servicios)` | `$servicios['total']` | ❌ `ListServices` devuelve array indexado |
| `dash-invoices-list.php:61` | `count($facturas)` | `$facturas['total']` | ❌ `ListInvoices` devuelve array indexado |

**Fix modelo:** normalizar los 3 use cases restantes para que devuelvan `{items: [...], total: N}` como hacen sus pares. Costo: ~30 min, todas las pruebas son humo (smoke test al dashboard).

### 3.3 Formato de moneda inconsistente

- `dash-contracts.php:117` y `:118` → `$ <?php echo (int) $row['valorMensual_Contrato'] ?>` — sin separador de miles. Para $1.500.000 se imprime `$ 1500000`.
- `dash-invoices-list.php:102` → `format_clp($row['valor_Factura'])` — sí formatea correctamente (`$ 1.500.000`).

**Fix modelo:** mover `format_clp()` a `layouts/helpers.php` (ya existe como función local en invoices-list) y aplicarlo en contratos. Costo: 10 min.

### 3.4 Email y teléfono sin enlace semántico

`dash-customers.php:91,92`:

```php
<td><?php echo htmlspecialchars($customer->phone) ?></td>
<td><?php echo htmlspecialchars($customer->email) ?></td>
```

Texto plano. Hoy cualquier visitor con un mail client configurado tiene que copiar-pegar.

**Fix modelo:**

```php
<td><a href="tel:<?= htmlspecialchars(preg_replace('/[^+\d]/', '', $customer->phone)) ?>"><?= htmlspecialchars($customer->phone) ?></a></td>
<td><a href="mailto:<?= htmlspecialchars($customer->email) ?>"><?= htmlspecialchars($customer->email) ?></a></td>
```

Aplicar también en `dash-customers-item.php:107,108` y en `dash-invoices-detail.php:111,112`.

---

## 4. 🟡 Hallazgos medios (estructura / boilerplate)

### 4.1 CSS/JS de DataTables inline repetidos en 7 vistas

Cada listado copy-pastea 6 líneas de `<link>` y 11 líneas de `<script>`:

```html
<link href="assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css"/>
<link href="assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet" type="text/css"/>
<!-- ... 11 <script src="..."> ... -->
```

Aparece en: `dash-bathrooms.php:27-32` + `:191-205`, `dash-certificates.php:23-27` + `:137-151`, `dash-contracts.php:26-31` + `:179-193`, `dash-customers.php:25-30` + `:143-157`, `dash-invoices-list.php:24-29` + `:223-237`, `dash-services.php:23-28` + `:159-173`, `dash-users-list.php:24-29` + `:165-179`.

**Fix modelo:** crear `layouts/datatables-css.php` y `layouts/datatables-js.php`. La primera emite los 2 `<link>` condicionalmente, la segunda los 11 `<script>` + el `<script src="assets/js/components/datatable.js">`. Las vistas quedan con:

```php
<?php $with_datatables = true; include 'layouts/datatables-css.php'; ?>
```

Costo: 30 min. Ahorro: ~119 LOC duplicadas.

### 4.2 Modo client-side en todas las tablas

Todas las tablas (incluso con miles de registros en producción según `.doc/Viejos/informe-produccion-banos-duplicados.md`) renderizan TODOS los rows en el HTML inicial, y DataTable filtra en el browser. Es la decisión de diseño más barata hoy, pero:

- Para `facturas` y `servicios` (las entidades que más crecen) el TTFB y el tamaño de payload HTML escalan lineal con el dataset.
- El buscador de la columna es por coincidencia de substring (no full-text). Con 5k facturas es usable, con 50k empieza a molestar.

**Fix modelo (futuro, no en sprint inmediato):** si los volúmenes superan cierto umbral (~10k registros por listado), pasar a `serverSide: true` con `ajax: { url: 'controller/<entidad>-list-ajax.php', ... }` y devolver JSON paginado desde el repositorio DDD.

**Mientras tanto:** documentar el límite esperado por listado en `layouts/datatables-js.php` y monitorear tamaño de respuesta HTML.

### 4.3 Comentarios desactualizados / dead code

- `dash-bathrooms.php:215` → el comentario dice `// Ordenar por columna Fecha Seguimiento (la 5ta columna)` pero `[[2, "desc"]]` ordena por la **3ra columna** (Observaciones). El comentario original era de otro proyecto y se copió sin adaptar — confunde al próximo que toque esta vista.
- `dash-invoices-list.php:246` → `order: []` con comentario `// Preservar el orden por created_at DESC que ya viene del SQL`. `order: []` no preserva nada — desactiva el orden por defecto de DataTables. Probablemente el comportamiento real es ORDER BY en SQL + DataTable respetando ese orden visual (porque no hay orden en JS hasta que el usuario clickea una columna).
- `dash-contracts.php:140-143` → 4 líneas de HTML comentado para "Eliminar contrato" con referencia a `plan-diseno-sistema-visual.md`. Verificar si esa decisión sigue vigente; si sí, mover a un comentario de bloque PHP único y limpiar el HTML muerto. Probable candidato para `archive/`.

**Fix modelo:** limpiar los 3 sitios en una pasada de ~10 min.

### 4.4 `data-confirm-delete` no tiene handler global visible

Los 6 listados usan atributos `data-confirm-delete`, `data-confirm-title`, `data-confirm-text`, `data-confirm-confirm-text`. Asumo que `feedback.js` lee esto y abre un SweetAlert2. **Verificar que esté inicializado en `layouts/vendor-scripts.php` o en `app.js` antes de declarar 3.x remediado.** Si no hay handler global, los "Eliminar" se ejecutan sin confirmar → hallazgo crítico, no medio.

**Acción previa a S3:** grep por `data-confirm-delete` en `assets/js/` para confirmar la implementación.

---

## 5. 🟢 Hallazgos bajos (deuda técnica nice-to-have)

### 5.1 Accesibilidad

- **Sin `<caption>` ni `aria-describedby`** en ninguna `<table>` del proyecto. Los lectores de pantalla anuncian la tabla por el `id` (`#datatable-buttons`), que no es semántico.
- **`<th scope="col">`** se usa consistentemente ✅.
- Falta `<th scope="row">` en celdas-hijo de los sub-grids (`dash-contracts-item.php:183` imprime `$bano['id_Relacion']` dentro de un `<td>` sin scope).
- **Botones de acción** dependen solo de `title=` para describir su función, no hay `aria-label`. Los iconos (`fas fa-pencil-alt`, `fas fa-trash-alt`, etc.) son invisibles para screen readers sin texto alternativo.

**Fix modelo:** agregado a S3, ~1h.

### 5.2 Skeleton / loading state

DataTables pinta un `processing` overlay nativo mientras filtra, pero no hay skeleton inicial mientras se carga la página (PHP render + primer paint de DataTables). Para listados grandes se ve "flash de tabla vacía → 1000s de rows".

**Fix modelo:** bajo prioridad, dejar para una fase futura de UX.

### 5.3 Responsive real no probado

La clase `dt-responsive nowrap w-100` está en todos lados, pero:

- En `dash-services.php` la columna "Acción" tiene `width:140px; min-width: 140px` hardcodeado. En mobile `< 576px`, DataTable va a priorizar el ancho de esa columna y comprimir otras a ilegibles.
- En `dash-invoices-list.php:88` la columna "Fecha de Pago" tiene clase `no-filter` (intencional, el filtro es por flatpickr arriba), pero la columna "Otros" (`<td>` del dropdown de estado) está mal ubicada en el orden visual — el ojo humano lee izquierda-a-derecha y ese dropdown debería estar junto a "Acciones".

**Fix modelo:** ajustes menores de CSS + repensar orden de columnas en invoices.

### 5.4 Inconsistencia de clases: `badge-status` vs `badge item-activo`

En `dash-bathrooms.php:113,119,129,133` y similares se usa `class="badge-status is-success"` / `is-danger` / `is-info` / `is-warn`. En `dash-bathrooms-contracts-status.php:120,121,153` y `dash-contracts-item.php:190,196` se usa `class="badge item-activo"` / `item-disponible` directamente.

Dos sistemas de clases para el mismo concepto visual. **Revisar `plan-diseno-sistema-visual.md` (Sección 1, paleta)** — el sistema unificado que ese documento introduce debería absorber la diferencia.

**Fix modelo:** fuera de esta auditoría, deriva del plan visual ya en marcha.

---

## 6. Plan de remediación

3 sprints independientes, ejecutables en cualquier orden. Cada uno se verifica con smoke test manual en `http://localhost` (sin tests automatizados en el proyecto).

### Sprint 1 — Seguridad + consistencia de IDs (~4-6h)

**Alcance:**

1. Helper `e_url_int($v)` en `layouts/helpers.php`.
2. Aplicar helper a todos los hrefs de IDs listados en §2.1 (7 archivos).
3. Convertir las acciones destructivas listadas en §2.2 a formularios POST con CSRF token (8 archivos: 7 listados + `dash-users-list.php` que tiene 4 acciones destructivas).
4. Agregar whitelist `{1, 2}` en `user-setadmin.php` para `$_GET['category']` (verificar si ya existe, sumar si falta).
5. Normalizar `ListServices`, `ListInvoices`, `ListActiveUsers` para devolver `{items, total}` (§3.2) — 30 min.

**QA:** clikear cada acción destructiva desde el panel y verificar que pide confirmación y ejecuta vía POST (no GET). Probar que modificar manualmente la URL con `category=999` da 403.

**Riesgo bajo** — no toca lógica de negocio, solo render y mecanismo de mutación.

### Sprint 2 — DataTable en sub-grids + DRY CSS/JS (~3-4h)

**Alcance:**

1. `layouts/datatables-css.php` + `layouts/datatables-js.php` con flag `<?php $with_datatables = true; ?>` (similar al patrón de modales que ya existe).
2. Reemplazar los bloques inline de CSS/JS de DataTables en los 7 listados (§4.1) — ahorra ~119 LOC.
3. Convertir las 6 sub-grids (§3.1) a `DataTable.init()`. Para las 2 tablas de `dash-bathrooms-contracts-status.php`, usar IDs específicos (`#datatable-contratos-activos`, `#datatable-banos-disponibles`) y eliminar el `dtLanguage` inline duplicado.
4. Aplicar `format_clp()` a las celdas de currency en `dash-contracts.php` (§3.3).

**QA:** verificar que las 13 vistas con grilla rendericen en español, persistan estado entre refrescos cuando aplica (`data-dt-state="true"`), y que el orden inicial de las sub-grids sea por columna 0 ascendente (ya es el caso en `dash-bathrooms-contracts-status.php`, replicar ahí).

**Riesgo bajo** — la abstracción ya existe (`DataTable.init()`), el cambio es usar la abstracción donde falta.

### Sprint 3 — Higiene, accesibilidad y limpieza (~2-3h)

**Alcance:**

1. Limpiar comentarios desactualizados y dead code en `dash-bathrooms.php:215`, `dash-invoices-list.php:246`, `dash-contracts.php:140-143` (§4.3).
2. Verificar que `data-confirm-delete` tiene handler global (§4.4 — confirmar antes de S1 si hace falta, o mover a S3).
3. Agregar `<caption>` descriptivo a cada `<table>` (clase `.visually-hidden` para que no rompa el diseño).
4. Agregar `aria-label` a botones de acción con icono (donde falte).
5. Aplicar links `tel:` y `mailto:` en `dash-customers.php`, `dash-customers-item.php`, `dash-invoices-detail.php` (§3.4).
6. `<th scope="row">` en celdas-hijo de sub-grids donde corresponda.

**QA:** pasar axe-core (DevTools o extensión) por las 13 vistas. Apuntar a 0 violaciones de tipo serious/critical.

**Riesgo muy bajo** — sin tocar lógica ni librería.

---

## 7. Métricas e impacto

| Métrica | Hoy | Después de S1+S2+S3 |
|---|---|---|
| Vistas con grilla usando `DataTable.init` | 7 / 13 (54%) | 13 / 13 (100%) |
| LOC de CSS/JS DataTables inline duplicados | ~119 | 0 |
| Acciones destructivas con CSRF | 0 / ~14 | 14 / 14 |
| Acciones destructivas con confirmación UX | ~14 | 14 (mantenidas o mejoradas) |
| Vistas con formato de moneda localizado (CLP) | 1 | 8 (todos los `valor_*` visibles) |
| Lectores de pantalla pueden identificar tablas por nombre semántico | 0 | 13 |
| Total LOC ahorradas (estimado) | — | ~150 |

---

## 8. Lo que esta auditoría NO cubre

- **`<form>` de las vistas de add/edit** (búsqueda de clientes por select, validaciones, máscaras de RUT): fuera de scope, no son grilla.
- **CSS de badges/botones/landscape visual del dashboard**: cubierto por `.doc/plan-diseno-sistema-visual.md` (en curso).
- **Tests automatizados**: el proyecto no tiene, los QA son manuales.
- **Performance real**: no medí TTFB ni tamaño de HTML en producción — los hallazgos de §4.2 y §5.3 son observaciones de código, no de runtime. Para datos cuantitativos, capturar `curl http://localhost/dash-invoices-list.php | wc -c` en local antes/después.
- **Accesibilidad exhaustiva**: hice observación manual. Para una auditoría completa, correr axe-core / Lighthouse / WAVE con cada sprint cerrado.

---

## 9. Notas para el siguiente que abra este doc

- El componente `assets/js/components/datatable.js` está **bien diseñado** — soporta `data-dt-state`, `data-dt-column-filters`, idioma español, y devuelve la instancia para extender con lógica custom. Toda la base para S2 ya existe, no hay que escribirla.
- El patrón "Acciones = botón directo + dropdown con más acciones" está consolidado en los 7 listados. No lo discutas, síguelo.
- El ID `#datatable-buttons` se usa en 7 vistas con una sola tabla cada una — si en algún momento una vista necesita 2 tablas (raro pero posible), hay que migrar a IDs específicos. Adelantarse a eso en S2.
- Los archivos marcados como ❌ en §3.2 (`user`, `service`, `invoice`) tienen `List*` use cases que quedaron de la migración DDD. El patrón correcto está en `Bathroom` — `ListBathrooms` ya devuelve `{items, total}`. Copiar ese patrón.
- Hay tareas relacionadas que NO están en este doc pero conviene revisar antes de tocar el código:
  - `.doc/Viejos/checklist-php85-prerrequisitos.md` — recordar que la migración PHP 8.5 está solo en local, queda replicar en staging/prod.
  - `controller/servicio-pdf.php` — el legacy NO migrado conocido (con bug de columna `SR.id_Bath`). Si S2 toca `dash-services.php`, NO tocar este controller en el mismo sprint (mezclar refactor + fix conocido → receta de desastre).
