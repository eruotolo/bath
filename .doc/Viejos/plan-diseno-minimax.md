# Plan de diseño — Capa de Markup de Vistas (HTML/PHP)

**Asignado a:** MiniMax (IA 2 de 2)
**Par simultáneo:** `plan-diseno-claude.md` (Claude — capa de CSS, tokens y backend)
**Origen:** derivado de `.doc/plan-diseno-sistema-visual.md` (consensuado con Edgardo, 2026-07-10), dividido para paralelización sin colisión.
**Estado:** 📋 plan — nada implementado todavía.

---

## 0. Cómo usar este documento

Este documento es **una de dos mitades** del plan de diseño visual del panel. Está pensado para que **MiniMax lo ejecute de forma autónoma y completa**, sin coordinarse con la otra mitad durante el trabajo. La división garantiza **cero colisión de archivos**: cada IA es propietaria exclusiva de un conjunto de archivos distinto.

Cada fase lista los archivos exactos a tocar, el código exacto (antes/después) y un checklist de aceptación. **No implementar dos fases a la vez** — el proyecto no tiene tests automatizados, cada fase se verifica manualmente en `http://localhost` antes de pasar a la siguiente.

### 0.1 Orden recomendado dentro de este documento

```
B1 (confirmaciones)  →  B2 (limpieza inline style)  →  B3 (badges + botones + iconos)  →  B4 (formularios grid)
```

B1 y B2 son cambios quirúrgicos de bajo riesgo y deben ir primero. B3 reorganiza el markup de las vistas de listado (y **preserva** los `data-confirm-*` que B1 agregó). B4 es el cambio más extenso (12 archivos) y va último para validar el patrón de grid en un formulario simple antes de los grandes.

### 0.2 Reglas del proyecto (aplican a todo este documento)

De `AGENTS.md` del proyecto, no se repiten en cada fase:

- Conversación en español; código, variables, funciones y mensajes de commit en inglés.
- `snake_case` para variables/funciones PHP, SQL en MAYÚSCULAS.
- Después de cualquier cambio de código PHP: `docker-compose up -d --force-recreate php` (o `docker-compose restart php` si no se tocó `composer.json`/`src/`) + smoke test manual en `http://localhost`.
- No se introduce build tooling — el CSS se edita directo. **MiniMax no edita archivos `.css`** (eso es de Claude); solo escribe markup que consume las clases CSS que Claude define (sección 2).
- No tocar `app/public/archive/` ni `app/public/assets/libs/`.
- No commitear ni pushear sin pedido explícito de Edgardo.

### 0.3 Convención de escaping (importante para vistas)

Toda salida dinámica a HTML debe escaparse con `htmlspecialchars($var, ENT_QUOTES, 'UTF-8')`. Las vistas existentes sin escape son deuda pendiente — al tocar una vista en este plan, escapar los valores dinámicos que se editen o agreguen en el mismo bloque. No hacer una pasada global de escaping fuera del alcance de cada fase (sería un refactor aparte).

---

## 1. Propiedad de archivos — quién hace qué

### 1.1 Archivos de ESTE documento (MiniMax — Markup & Views)

MiniMax es **propietario exclusivo** de los siguientes archivos. Claude no los toca:

**7 vistas de listado** (Fases B1, B2, B3):
- `app/public/dash-bathrooms.php`
- `app/public/dash-customers.php`
- `app/public/dash-contracts.php`
- `app/public/dash-services.php`
- `app/public/dash-invoices-list.php`
- `app/public/dash-certificates.php`
- `app/public/dash-users-list.php`

**12 formularios de alta/edición** (Fase B4):
- `app/public/dash-bathrooms-add.php`, `dash-bathrooms-edit.php`
- `app/public/dash-certificates-add.php`
- `app/public/dash-contracts-add.php`, `dash-contracts-edit.php`
- `app/public/dash-customers-add.php`
- `app/public/dash-invoices-add.php`, `dash-invoices-edit.php`
- `app/public/dash-services-add.php`, `dash-services-edit.php`
- `app/public/dash-users-add.php`, `dash-public/dash-users-edit.php`

**Modales dentro de vista** (si Edgardo confirma, Fase B4 extendida):
- Los 2 modales de contacto en `app/public/dash-customers-item.php`

### 1.2 Archivos del documento paralelo (Claude — no tocar)

MiniMax **no edita** ningún `.css`, ni `app/public/index.php`, ni `app/src/`. Eso es trabajo de Claude. El contrato de interfaz (qué clases CSS existen para consumir) está en la sección 2.

---

## 2. Contrato de interfaz entre MiniMax y Claude

MiniMax escribe markup HTML que consume clases CSS que **Claude define** en `app/public/assets/css/style.css`. Esta tabla es la fuente de verdad compartida — MiniMax debe usar **exactamente** estos nombres:

| Clase / Variable CSS | Qué es | Markup que la usa |
|---|---|---|
| `.badge-status` | Badge de estado (contenedor) | `<span class="badge-status is-success">Activo</span>` |
| `.badge-status.is-success` | Verde — Activo / Pagado / Disponible-activo | badge de estado positivo |
| `.badge-status.is-danger` | Rojo — Inactivo / Anulado | badge de estado negativo |
| `.badge-status.is-info` | Azul — Disponible | badge de disponibilidad |
| `.badge-status.is-warn` | Ámbar — Asignado / Pendiente | badge nuevo para "Asignado a Obra" |
| `.item-activo`, `.item-inactivo`, `.item-disponible` | Alias de compat (a eliminar al migrar) | Se reemplazan por `.badge-status.is-*` en Fase B3 |
| `.dropdown-item.text-danger` | Acción destructiva en dropdown | `<a class="dropdown-item text-danger" ...>` |
| `--fs-page-title`, `--fs-section-title`, `--fs-caption` | Escala tipográfica (variables CSS) | Uso opcional en estilos inline; principalmente las consume Claude |
| `--color-*` | Paleta semántica (variables CSS) | Uso opcional; principalmente las consume Claude |

**Principio clave:** el markup HTML con clases es válido aunque el CSS aún no esté aplicado (Claude puede estar trabajando en paralelo). Las clases simplemente "no pintan nada especial" hasta que Claude las define — no rompen la página. **Ambas mitades pueden ejecutarse en paralelo real.**

---

## 3. Especificación de componentes de markup

### 3.1 Badges de estado — markup nuevo

Reemplazar `<div class="badge item-...">` por `<span class="badge-status is-...">`:

```html
<span class="badge-status is-success">Activo</span>
<span class="badge-status is-danger">Inactivo</span>
<span class="badge-status is-info">Disponible</span>
<span class="badge-status is-warn">Asignado</span>
```

Notas:
- Se cambia `<div>` por `<span>` porque un badge de estado no es un bloque — usar `div` es semánticamente incorrecto (el template original ya lo hacía mal).
- **Hallazgo clave:** la columna "Asignado a Obra" de `dash-bathrooms.php:157` reutiliza hoy `.item-activo` (verde) para "Asignado" — visualmente "Activo" (estado del baño) e "Asignado" (estado de asignación) son indistinguibles si aparecen en la misma fila. Al migrar, "Asignado" pasa a `is-warn` (ámbar).

### 3.2 Botones y acciones de fila — patrón agrupado

**Hallazgo (inventario completo, las 7 entidades):**

| Vista | Botones hoy | Ícono | Confirmación antes de ejecutar |
|---|---|---|---|
| `dash-bathrooms.php` | Editar, Activar, Inactivar, Deshacer asignación, Eliminar (5 sueltos, `btn-outline-secondary`) | Font Awesome (`fa-pencil-alt`, `fa-lock-open`, `fa-lock`, `fa-level-down-alt`, `fa-trash-alt`) | Solo Eliminar, vía `data-confirm-delete` |
| `dash-customers.php` | Ver, Eliminar (`customer-inactive.php`) | `fa-eye`, `fa-trash-alt` | **Ninguna** |
| `dash-contracts.php` | Agregar Baños, Editar, Inactivar, Activar | `fa-toilet`, `fa-pencil-alt`, `fa-lock`, `fa-lock-open` | **Ninguna** |
| `dash-services.php` | Asignar Baños, Editar, Eliminar/Inactivar | `fa-toilet`, `fa-pencil-alt`, `fa-trash-alt` | **Ninguna** |
| `dash-invoices-list.php` | Imprimir, Agregar Servicios, Editar, Anular | `fa-print`, `fa-plus`, `fa-pencil-alt`, `fa-trash-alt` | Solo Anular, vía `data-confirm-delete` |
| `dash-certificates.php` | Ver, Imprimir, Eliminar | `fa-eye`, `fa-print`, `fa-trash-alt` | **Ninguna** |
| `dash-users-list.php` | dropdown: Editar, Inactivar, Password Default, Set Admin/User | texto plano, sin íconos | **Ninguna** |

**Esto es un problema de seguridad de datos, no estético: 5 de 7 entidades permiten borrar/inactivar/anular de un solo click, sin aviso.** El mecanismo para arreglarlo ya existe (`feedback.js`, wrapper de SweetAlert2, ver 3.3) — falta solo agregar el atributo. Se incluye como Fase B1 por ser el cambio de menor riesgo y mayor impacto; no se toca la lógica de los controllers.

**Patrón visual a aplicar (todas las entidades):** la acción no-destructiva más usada visible (Editar), el resto agrupado en un menú `⋯` (dropdown de Bootstrap, mismo patrón que ya usa `dash-users-list.php`), y la acción destructiva SIEMPRE en rojo dentro del menú, separada del resto por un `<hr>` en el dropdown.

```html
<div class="d-flex align-items-center gap-1 justify-content-center">
    <a href="dash-bathrooms-edit.php?id_Bath=<?php echo htmlspecialchars((string)$row['id_Bath'], ENT_QUOTES, 'UTF-8') ?>"
       class="btn btn-outline-secondary btn-sm" title="Editar">
        <i class="fas fa-pencil-alt"></i>
    </a>
    <div class="dropdown">
        <button class="btn btn-outline-secondary btn-sm dropdown-toggle dropdown-toggle-split"
                type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-ellipsis-h"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="controller/bath-active.php?id_Bath=<?php echo htmlspecialchars((string)$row['id_Bath'], ENT_QUOTES, 'UTF-8') ?>"><i class="fas fa-lock-open me-2"></i>Activar</a></li>
            <li><a class="dropdown-item" href="controller/bath-inactive.php?id_Bath=<?php echo htmlspecialchars((string)$row['id_Bath'], ENT_QUOTES, 'UTF-8') ?>"><i class="fas fa-lock me-2"></i>Inactivar</a></li>
            <li><a class="dropdown-item" href="controller/bath-notassign.php?id_Bath=<?php echo htmlspecialchars((string)$row['id_Bath'], ENT_QUOTES, 'UTF-8') ?>"><i class="fas fa-level-down-alt me-2"></i>Deshacer asignación</a></li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <a class="dropdown-item text-danger" href="controller/bath-delete.php?id_Bath=<?php echo htmlspecialchars((string)$row['id_Bath'], ENT_QUOTES, 'UTF-8') ?>"
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

### 3.3 Confirmaciones destructivas — mecanismo

`feedback.js` ya auto-engancha cualquier elemento con `[data-confirm-delete]` (ver `feedback.js:53-71`) — **no requiere JS nuevo**, solo agregar los atributos a los `<a>` existentes:

| Atributo | Para qué |
|---|---|
| `data-confirm-delete` | Marca el link para que SweetAlert2 intercepte el click |
| `data-confirm-title` | Título del modal de confirmación |
| `data-confirm-text` | Cuerpo del modal |
| `data-confirm-confirm-text` | Texto del botón de confirmación |

### 3.4 Iconografía — consistencia por categoría

El panel mezcla 3 librerías de íconos (Font Awesome `fa`/`fas`, Boxicons `bx`, Material Design Icons `mdi`, Feather `data-feather`). Este plan **no unifica a una sola librería** (alto esfuerzo, bajo beneficio visible). La regla es más acotada: **dentro de cada categoría, ser consistente**.

- **Botones de acción en tablas de listado** → Font Awesome (`fas fa-*`), ya estándar en 6 de 7 entidades. Al tocar `dash-users-list.php` (que hoy no usa íconos), agregar: `fa-pencil-alt` (Editar), `fa-lock` (Inactivar), `fa-key` (Password Default).
- **Navegación lateral y header** → se deja Material Design Icons + Feather como están, no se tocan.

---

## 4. Fases de implementación

### Fase B1 — Confirmaciones destructivas faltantes

**Por qué va primero:** no es cambio visual, es corregir que 5 de 7 entidades permiten borrar/inactivar/anular sin aviso. Cero riesgo de romper nada (solo agrega atributos HTML a links existentes), el mecanismo JS ya funciona en producción hoy en 2 vistas.

**Archivos a tocar:** `dash-customers.php`, `dash-contracts.php`, `dash-services.php`, `dash-certificates.php`, `dash-users-list.php`.

**Qué hacer en cada uno:** agregar a los `<a>` de la tabla de acciones los atributos `data-confirm-*` de la sección 3.3. Bash de referencia para ubicarlos:

```bash
grep -n "controller/.*inactive\|controller/.*delete\|controller/.*remove" \
  app/public/dash-customers.php app/public/dash-contracts.php \
  app/public/dash-services.php app/public/dash-certificates.php \
  app/public/dash-users-list.php
```

**Texto sugerido por vista:**

| Archivo | Link a modificar | `data-confirm-title` | `data-confirm-text` |
|---|---|---|---|
| `dash-customers.php` | `<a>` hacia `customer-inactive.php` | `¿Inactivar este cliente?` | `Sus contratos activos no se ven afectados, pero no podrá asignársele contratos nuevos.` |
| `dash-contracts.php` | `<a>` hacia inactivar/activar contrato | `¿Inactivar este contrato?` | `Los baños asignados quedarán disponibles para otros contratos.` |
| `dash-services.php` | `<a>` hacia eliminar/inactivar servicio | `¿Eliminar este servicio?` | `Esta acción no se puede deshacer.` |
| `dash-certificates.php` | `<a>` hacia `certificate-remove.php` | `¿Eliminar este certificado?` | `Esta acción no se puede deshacer.` |
| `dash-users-list.php` | `<a>` hacia `user-inactive.php` | `¿Inactivar este usuario?` | `No podrá iniciar sesión hasta que se reactive.` |

**No se toca:** ningún controller PHP, ningún archivo JS, `user-setadmin.php` (cambiar rol no es destructivo), `user-default-pass.php` (queda fuera de alcance a criterio de Edgardo). `dash-bathrooms.php` y `dash-invoices-list.php` ya tenían confirmación — no se tocan en esta fase.

**Checklist de aceptación:**
- [ ] En cada una de las 5 vistas, hacer click en la acción destructiva dispara un `Swal.fire` de confirmación antes de navegar
- [ ] Cancelar la confirmación no ejecuta la acción (la URL no cambia)
- [ ] Confirmar sí ejecuta la acción (comportamiento idéntico al actual, solo con el paso previo)
- [ ] `dash-bathrooms.php` y `dash-invoices-list.php` (ya tenían confirmación) siguen funcionando igual — no se tocan en esta fase

---

### Fase B2 — Limpieza residuo violeta inline

**Archivos a tocar:** `app/public/dash-users-list.php:135`.

**Cambio:**
```diff
- <a class="dropdown-item cat-admin" href="..." style="color: #5156be">Set Admin/User</a>
+ <a class="dropdown-item cat-admin" href="...">Set Admin/User</a>
```

El color inline no aporta nada semántico — es el único caso de violeta hardcodeado fuera de los CSS. Si se quiere destacar esta acción por ser sensible, usar `text-warning` de Bootstrap en vez de un hex suelto; a criterio de Edgardo, no bloqueante.

**Checklist de aceptación:**
- [ ] El link "Set Admin/User" ya no tiene `style="color: #5156be"`
- [ ] `grep -rn "5156be" app/public/dash-*.php` devuelve 0 resultados

---

### Fase B3 — Badges, botones agrupados e iconografía

**Archivos a tocar:** las 7 vistas de listado: `dash-bathrooms.php`, `dash-customers.php`, `dash-contracts.php`, `dash-services.php`, `dash-invoices-list.php`, `dash-certificates.php`, `dash-users-list.php`.

**Por vista:**

1. **Badges:** reemplazar `<div class="badge item-...">` por `<span class="badge-status is-...">` (ver 3.1). Aplica a `dash-bathrooms.php` (Estado + Asignación, con la corrección de "Asignado" a `is-warn` en vez de reusar `is-success`), y cualquier otra vista con badges de estado (`dash-contracts.php`, `dash-services.php`, `dash-invoices-list.php`, `dash-certificates.php` — verificar cuáles tienen badges antes de tocar, no todas los usan).

2. **Botones agrupados:** aplicar el patrón de 3.2 — 1 botón visible (Editar u otra acción primaria de esa entidad) + dropdown `⋯` con el resto + la acción destructiva en rojo (`text-danger`) al final, separada con `<hr class="dropdown-divider">`.

3. **`dash-users-list.php` iconografía:** agregar íconos Font Awesome a los `dropdown-item` que hoy son texto plano (ver 3.4).

**⚠️ Dependencia con Fase B1:** los `data-confirm-delete` agregados en Fase B1 se **mantienen** al mover el link dentro del dropdown — no se pierden, solo cambia el contenedor HTML alrededor del `<a>`. Verificar después de reorganizar que cada link destructivo sigue teniendo sus atributos `data-confirm-*`.

**Checklist de aceptación:**
- [ ] En las 7 vistas de listado, cada fila muestra máximo 2 elementos de acción visibles (botón + dropdown), no 3+ botones sueltos
- [ ] La acción destructiva es visualmente roja dentro del dropdown en las 7 vistas
- [ ] Los badges de estado usan `.badge-status`, no las clases viejas `.item-*` (excepto donde se decida no migrar por bajo uso — documentar cuál si aplica)
- [ ] "Asignado" y "Activo" en `dash-bathrooms.php` ya no comparten el mismo color
- [ ] Los `data-confirm-*` de Fase B1 siguen presentes en cada link destructivo después de la reorganización (probar cada confirmación)

---

### Fase B4 — Formularios a grid de 2 columnas

**Hallazgo (inventario completo, 12 formularios de alta/edición):** las 7 entidades no tienen ningún formulario en grid — 0 coincidencias de `col-md-*` en los 12 archivos. Todos son una columna con inputs de ancho fijo desproporcionado (`dash-bathrooms-add.php`: input de "Código del Baño" mide 851px para un valor tipo `AT055`).

**Patrón de grid a aplicar** (Bootstrap `row`/`col-md-6`, sin librería nueva):

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

**⚠️ Importante — no romper la validación existente:** `form-validator.js:31` usa `classTo: 'mb-4'` — el wrapper de cada campo **debe seguir siendo `<div class="mb-4">`** (o el error/success de PristineJS deja de pintarse, ver `style.css:162-168`). Al mover a grid, el `.mb-4` va **dentro** del `.col-md-*`, nunca se reemplaza por él.

**Mapeo campo por campo, las 7 entidades** (columna = ancho a aplicar):

| Entidad | Archivo(s) | Campos → columna |
|---|---|---|
| Bathrooms | `dash-bathrooms-add.php`, `dash-bathrooms-edit.php` | `codigo_Bath`→6, `fechaCompra_Bath`→6, `estado_Bath`→6, `observacion_Bath`→12 |
| Certificates | `dash-certificates-add.php` (no tiene edit) | `id_Cliente`→6, `id_Contrato`→6, `mts_Certificado`→6, `fecha_Servicio`→6, `fechahoy_Certificado`→12 |
| Contracts | `dash-contracts-add.php`, `dash-contracts-edit.php` | `id_Cliente`→6, `obra_Contrato`→6, `direccion_Contrato`→12, `estado_Contrato`→6 (solo edit), `fechaInicio_Contrato`→6, `fechaFin_Contrato`→6, `valorMensual_Contrato`→6, `valorTotal_Contrato`→6, `observacion_Contrato`→12 (textarea) |
| Customers | `dash-customers-add.php` (edición es modal, ver nota) | `rut_Cliente`→6, `nombre_Cliente`→6, `telefono_Cliente`→6, `email_Cliente`→6, `direccion_Cliente`→12, `region_Cliente`→6, `ciudad_Cliente`→6, `comuna_Cliente`→6 |
| Invoices | `dash-invoices-add.php`, `dash-invoices-edit.php` | `numero_Factura`→6, `fecha_Factura`→6, `id_Cliente`→6, `id_Contrato`→6, `valor_Factura`→6 |
| Services | `dash-services-add.php`, `dash-services-edit.php` | `id_Cliente`→6, `id_Contrato`→6, 9 checkboxes de tipo servicio→12 (grupo, ver nota abajo), `fecha_Servicio`→6, `observaciones_Servicio`→12 (textarea) |
| Users | `dash-users-add.php`, `dash-users-edit.php` | `useremail`→6, `username`→6, `name`→6, `lastname`→6, `password`→6 (solo add), `file`→12 (Dropzone, ya ocupa ancho completo) |

Nota Services: los 9 checkboxes (`instalacion_Tipo` … `retiro_Tipo`) se agrupan en un único `col-12` con `d-flex flex-wrap gap-3` — no cada uno en su propia columna, son un solo grupo lógico de selección.

Nota Customers: la edición de cliente es modal (`dash-customers-item.php` vía `ModalEditor.js`). Los 2 modales de contacto en el mismo archivo son candidatos directos a aplicar el mismo grid — incluirlos en esta fase **solo si Edgardo confirma**.

**Orden recomendado dentro de la fase** (de menor a mayor cantidad de campos, para validar el patrón en un caso simple antes de los formularios grandes): Bathrooms (4 campos) → Invoices (5) → Certificates (5) → Customers (8) → Contracts (9) → Users (6, pero con Dropzone que ya ocupa ancho completo) → Services (11-13, el más complejo por el grupo de checkboxes).

**Checklist de aceptación (repetir por cada uno de los 12 archivos):**
- [ ] El formulario se ve en 2 columnas en desktop (≥768px) y colapsa a 1 columna en mobile (Bootstrap `col-md-6` ya hace esto automático, verificar que no se rompió)
- [ ] Enviar el formulario vacío sigue mostrando los mensajes de error de PristineJS en rojo bajo cada campo (confirma que `.mb-4` no se movió fuera de la columna)
- [ ] Ancho de cada input es proporcional a su contenido esperado (código corto no mide 850px)
- [ ] El submit del formulario sigue guardando correctamente (no se tocó ningún `name` de input, solo el wrapper visual)

---

## 5. Modales — nota sobre estilo

`ModalEditor.js` es agnóstico de estructura HTML — no impone clases fijas, solo requiere que los campos destino tengan `id` coincidente con el `fieldMap` de la config JS. Hoy solo se usa en `dash-customers-item.php` (edición de cliente + 2 modales de contacto). Este plan **no cambia el mecanismo JS**, solo el estilo visual del modal (header, botones, spacing) — y ese estilo lo aplica **Claude** vía CSS global (`.modal-header`, `.modal-footer`, `.modal-body`).

Los formularios **dentro** de modales siguen la misma regla de grid de la Fase B4 (los 2 modales de contacto en `dash-customers-item.php` son candidatos directos — listarlos en B4 si Edgardo confirma).

---

## 6. Verificación de integración final (cuando Claude también terminó)

Cuando ambas mitades del plan están implementadas, verificar que el resultado combinado es coherente:

1. **Badges:** en cada vista de listado, los `.badge-status` se ven con el color correcto del rol (verde=Activo/Pagado, rojo=Inactivo/Anulado, azul=Disponible, ámbar=Asignado). "Asignado" y "Activo" en `dash-bathrooms.php` ya no comparten color.
2. **Botones agrupados:** cada fila de las 7 vistas muestra máximo 2 elementos de acción visibles; la destructiva es roja dentro del dropdown.
3. **Confirmaciones:** las 5 vistas que antes no tenían aviso ahora muestran `Swal.fire` antes de ejecutar la acción destructiva.
4. **Formularios:** los 12 formularios se ven en 2 columnas en desktop, 1 en mobile, y los errores de PristineJS siguen pintándose bajo cada campo.
5. **Sin violeta residual:** `grep -rn "5156be\|81, 86, 190\|#5b73e8" app/public/assets/css/ app/public/dash-*.php` devuelve 0 resultados fuera de comentarios.

---

## 7. Fuera de alcance de este documento

- Cualquier cambio a archivos `.css`, `app/public/index.php`, o `app/src/` — eso es Claude.
- Migración de tipografía a Inter/Geist — descartada, Open Sans se mantiene.
- Unificación de las 4 librerías de íconos a una sola — alto esfuerzo, bajo beneficio.
- Dark mode — no hay toggle activo.
- Cambios a `app/public/archive/` o `app/public/assets/libs/`.
- Cambios a `feedback.js`, `ModalEditor.js`, `form-validator.js` o cualquier JS — el mecanismo ya funciona, este plan solo consume sus APIs existentes.
