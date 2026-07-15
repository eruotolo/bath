# Plan — Servicios rebranding (Seguimiento de Servicios)

**Origen (template de diseño):** `rebranding/src/components/ServiciosView.tsx` (React + Tailwind, 309 líneas).
**Patrón de referencia en producción:** `app/public/dash-bathrooms.php` + `app/public/assets/js/components/bathrooms.js` — **este es el patrón canónico a replicar** para el toggle card/tabla (grid con cards + tabla propia en Tailwind, un solo buscador cliente-side que filtra ambas vistas, preferencia persistida en `localStorage`). NO usar `layouts/native-table.php` para la vista de lista: ese componente trae su propio buscador + paginación autocontenida y duplicaría el buscador del toolbar superior — el precedente de `dash-bathrooms.php` ya resuelve esto con tabla hecha a mano.
**Objetivo:** reescribir `app/public/dash-services.php` (hoy 100% DataTables/Bootstrap) para que tenga **vista de card** y **vista de lista**, alternables, con datos reales (tipos de servicio realizado, bitácora, estado de facturación), retirando DataTables de esta vista.
**Fuera de alcance:** `dash-services-add.php`, `dash-services-edit.php`, `dash-services-print.php`, `dash-services-bath.php` — ya migrados a Tailwind (`dt-card`, `dt-input`, etc.) y no forman parte de este plan. Los controllers (`service-new.php`, `service-update.php`, `service-inactive.php`, `service-bath-add/remove.php`) ya son 100% DDD y no requieren cambios.
**Decisión de producto (confirmada con Edgardo):** las pills de filtro no replican el "Completados/Pendientes" del mockup (el modelo real no tiene un estado "pendiente" — todo servicio registrado ya fue ejecutado). Se reemplaza por **Todos / Facturados / No Facturados**, que sí es un dato real y ya existe como badge en la tabla actual.
**Estado:** 🔴 planificado — nada implementado todavía.

---

## 0. Contrato de datos y convenciones (CONGELAR ANTES DE IMPLEMENTAR)

### 0.1 Modelo de datos real

Tabla `servicios` (vía `MysqliServiceRepository::insert/update`, `app/src/Infrastructure/Persistence/MysqliServiceRepository.php:13-58`):

| Columna | Tipo | Significado |
|---|---|---|
| `id_Servicio` | int PK | ID |
| `id_Contrato` | int FK | Obra/contrato asociado |
| `nro_Servicio` | int | Correlativo, 1:1 con `tipo_servicio.nro_Servicio` |
| `fecha_Servicio` | date | Fecha de la visita |
| `observaciones_Servicio` | text nullable | Bitácora |
| `estado_Servicio` | int | `1` = activo (visible en listado), `0` = inactivo (soft delete vía `DeactivateService`) |
| `created_at` | timestamp | Orden del listado |

Tabla `tipo_servicio` (1:1 con `servicios` por `nro_Servicio`, 9 flags booleanos — mapean directo a `ServiceType[]` del mockup):

| Columna | `ServiceType` (mockup) |
|---|---|
| `instalacion_Tipo` | Instalación |
| `reparacion_Tipo` | Reparación |
| `limpieza_Tipo` | Limpieza |
| `desinfeccion_Tipo` | Desinfección |
| `sanitizacion_Tipo` | Sanitización |
| `higienico_Tipo` | Entrega Papel Higiénico |
| `jabon_Tipo` | Entrega de Jabón Líquido |
| `otros_Tipo` | Otros |
| `retiro_Tipo` | Retiro de Baños |

Facturación (para el footer "Facturado en #X" / "Pendiente de Cobro"):
`facturas(id_Factura, numero_Factura, ...)` ← `factura_servicio(id_Factura, id_Servicio)` ← `servicios.id_Servicio`. Un servicio se asume ligado a **una sola** factura (mismo supuesto que ya usa el `EXISTS` actual).

Obra y cliente: `contratos.obra_Contrato`, `contratos.id_Cliente → clientes.nombre_Cliente` (ya joineados hoy).

### 0.2 Contrato del listado (lo que el repositorio debe entregar)

**Hallazgo clave que motiva este plan:** `MysqliServiceRepository::listActiveWithDetails()` (líneas 94-107) hoy **NO** joinea `tipo_servicio` ni trae el número real de factura — solo el boolean `facturado`. Sin esto no se pueden pintar los tags de tipo ni la bitácora completa de la card. Hay que **extender este método en el lugar** (no crear uno nuevo — es el único consumidor, vía `ListServices->handle()`):

```sql
SELECT SR.*, CT.obra_Contrato, CL.nombre_Cliente,
       TS.instalacion_Tipo, TS.reparacion_Tipo, TS.limpieza_Tipo, TS.desinfeccion_Tipo,
       TS.sanitizacion_Tipo, TS.higienico_Tipo, TS.jabon_Tipo, TS.otros_Tipo, TS.retiro_Tipo,
       EXISTS(SELECT 1 FROM factura_servicio FS WHERE FS.id_Servicio = SR.id_Servicio) AS facturado,
       F.numero_Factura
FROM servicios SR
JOIN contratos CT ON SR.id_Contrato = CT.id_Contrato
JOIN clientes CL ON CT.id_Cliente = CL.id_Cliente
JOIN tipo_servicio TS ON SR.nro_Servicio = TS.nro_Servicio
LEFT JOIN factura_servicio FS ON FS.id_Servicio = SR.id_Servicio
LEFT JOIN facturas F ON F.id_Factura = FS.id_Factura
WHERE SR.estado_Servicio = 1
ORDER BY SR.created_at DESC, SR.id_Servicio DESC
```

Cada fila resultante debe tener **exactamente** estas claves adicionales a las actuales: `instalacion_Tipo, reparacion_Tipo, limpieza_Tipo, desinfeccion_Tipo, sanitizacion_Tipo, higienico_Tipo, jabon_Tipo, otros_Tipo, retiro_Tipo, numero_Factura` (nullable).

> **Riesgo a validar en Fase 1:** confirmar que no hay servicios activos sin fila en `tipo_servicio` (el `JOIN` normal los excluiría del listado — sería un bug de datos huérfanos, no de la query). Si aparecen, decidir `LEFT JOIN` + defaults en `false` antes de cerrar la fase.

### 0.3 Mapeo de estado → UI

No hay estado "Pendiente" real. Toda fila de `listActiveWithDetails()` se pinta con el badge fijo **"Completado"** (como ya hace `ServiciosView.tsx` para todo lo que crea). El estado de facturación es el único dato variable:

| Condición | Badge |
|---|---|
| `facturado = 0` | `Pendiente de Cobro` (ámbar, ya existe como `.badge-status.is-warn`) |
| `facturado = 1` | `Facturado en #{numero_Factura}` (indigo) |

Pills de filtro (decisión confirmada): **Todos** · **Facturados** · **No Facturados** — filtrado **server-side** vía query param `?filter=`, mismo patrón que `dash-bathrooms.php` (no client-side, para no duplicar lógica de estado en PHP y JS).

### 0.4 IDs / clases DOM (congelados para que markup y JS no se desincronicen)

```
#servicios-local-search        input de búsqueda (comparte vista card + tabla)
#servicios-grid                contenedor grid de cards
#servicios-table                contenedor de la tabla (oculto por defecto)
.servicio-card / .servicio-row  clase de cada item buscable (data-* para filtrar)
  data-search-cliente, data-search-obra, data-search-nro, data-search-tipos
data-view-toggle="grid|table"  botones del selector de vista
aria-controls="servicios-grid servicios-table"
data-services-view (en <html>)  atributo que controla display (anti-flash CSS)
localStorage key: "services-view"
```

JS nuevo: `assets/js/components/services.js`, clon adaptado de `bathrooms.js` (mismo algoritmo de `applyView`/`setToggleState`/normalización de búsqueda, ids y storageKey distintos). **No** reusar `bathrooms.js` tal cual (está hardcodeado a IDs `banos-*`).

### 0.5 Paleta

`--color-primary` del proyecto ya es indigo-600 (`tw/theme.css:18`, dirección declarada del rebranding) — coincide con lo que `ServiciosView.tsx` ya usa. **No hace falta un accent nuevo** (a diferencia de Baños, que usa emerald como override scopeado): Servicios usa el indigo por defecto del sistema, sin override de `--color-primary`.

---

## Fase 1 — Backend: extender el repositorio

**Archivo:** `app/src/Infrastructure/Persistence/MysqliServiceRepository.php`

- Extender `listActiveWithDetails()` con la query de §0.2 (JOIN `tipo_servicio` + LEFT JOIN facturación).
- Validar con una consulta manual (`docker-compose exec mysql mariadb ...`) que ningún servicio activo quede excluido por el `JOIN` a `tipo_servicio` (ver riesgo §0.2).
- No tocar `ServiceRepositoryInterface` (la firma del método no cambia, solo las columnas que trae).
- `docker-compose exec php composer dump-autoload` no hace falta (no hay clases nuevas), pero sí `docker-compose restart php` para recargar el archivo.

**Checklist de salida:** `ListServices->handle()` devuelve filas con los 9 flags + `numero_Factura` sin romper nada que ya consuma `listActiveWithDetails()` (único consumidor: `dash-services.php`).

## Fase 2 — Vista: toolbar + toggle + pills

**Archivo:** `app/public/dash-services.php`

- Agregar `<style>` anti-flash + `<script>` de lectura de `localStorage` en el `<head>` (calco de `dash-bathrooms.php:215-225`, adaptado a `data-services-view` / `services-view`).
- Reemplazar el `table-toolbar` actual (línea 35-41) por: buscador (`#servicios-local-search`) + pills `Todos/Facturados/No Facturados` (server-side, `?filter=`) + botón toggle grid/tabla (`layout-grid` / `list`, calco de `dash-bathrooms.php:266-297`) + botón "Agregar Nuevo Servicio" (ya existe, mantiene el `href="dash-services-add.php"`).
- Función PHP auxiliar `service_types_labels(array $row): array` que arma la lista de labels visibles a partir de los 9 flags (análogo a `bath_estado_meta()` en `dash-bathrooms.php:189-195`).

## Fase 3 — Vista de card (`#servicios-grid`)

- Grid `grid grid-cols-1 md:grid-cols-2 gap-5` (igual proporción que `ServiciosView.tsx:147`, ajustable a 3 columnas en `lg` si se ve muy ancho con datos reales).
- Cada card (`.servicio-card`, `rounded-3xl border-slate-100 shadow-sm hover:shadow-md`, siguiendo el precedente de `.bano-card` — utilities inline, no forzar `.dt-card` genérica):
  - Badge "Completado" fijo (esquina superior, `CheckCircle2` lucide).
  - Código: `#<?php echo (int) $row['nro_Servicio']; ?>`, obra (`obra_Contrato`), cliente (`nombre_Cliente`).
  - Fecha de visita (`fecha_Servicio`, formateada `d-m-Y`).
  - Tags de tipos de servicio realizados (`service_types_labels($row)`).
  - Bitácora: `observaciones_Servicio` (o texto default si es null, igual que el mockup).
  - Footer: badge de facturación según §0.3.
- Estado vacío (`filteredServices.length === 0` en el mockup) → mensaje `col-span-full` con `border-dashed`, igual espíritu que `ServiciosView.tsx:198-202`.

## Fase 4 — Vista de tabla (`#servicios-table`)

- Tabla Tailwind hecha a mano (mismo patrón visual que `#banos-table` de `dash-bathrooms.php`, **no** `native-table.php` — ver §0 sobre por qué).
- Columnas: Número de Servicio, Cliente, Obra, Factura (badge), Fecha, Acción — mismas que la tabla DataTables actual (`dash-services.php:46-53`), para no perder información al migrar.
- Cada `<tr class="servicio-row" data-search-*="...">` con las mismas acciones que hoy: Editar (`dash-services-edit.php`), dropdown con "Asignar Baños" (`dash-services-bath.php`), "Imprimir" (`dash-services-print.php`), "Eliminar" (`controller/service-inactive.php`, con `data-confirm-delete` existente vía SweetAlert2).
- Reusar `dt-cell-action`, `badge-status`, dropdown Bootstrap shim (`bs-shim.js`) — ya disponibles, sin cambios de CSS.

## Fase 5 — JS: `assets/js/components/services.js`

- Clonar `bathrooms.js`, adaptar:
  - `storageKey = 'services-view'`
  - ids `servicios-grid` / `servicios-table` / `servicios-local-search`
  - `document.documentElement.dataset.servicesView`
  - selector de items buscables: `.servicio-card, .servicio-row`
  - dataset de búsqueda: `data-search-cliente`, `data-search-obra`, `data-search-nro`, `data-search-tipos` (concatenados y normalizados, mismo algoritmo de `normalize()` sin acentos)
- **No** portar `bindUnassignConfirmations()` (es específico de baños) — el confirm-delete de servicios ya lo maneja el hook genérico de SweetAlert2 en `app.js`/`datatable.js` actual; verificar que siga funcionando sin DataTables cargado (si el hook vive en `datatable.js`, moverlo a `app.js` o a `services.js`).

## Fase 6 — Retirar DataTables de esta vista

**Archivo:** `app/public/dash-services.php`

- Quitar los `<link>` de `datatables.net-bs4` / `responsive-bs4` (líneas 20-21).
- Quitar todos los `<script>` de DataTables/JSZip/pdfmake (líneas 105-116) y el bloque `DataTable.init(...)` (líneas 120-127).
- Agregar `<script src="assets/js/components/services.js"></script>`.
- Confirmar que ningún otro `<script>` de la página dependía de `datatable.js` para algo más que la tabla (dropdown/confirm-delete son del shim genérico, no de DataTables).

## Fase 7 — QA manual (sin tests automatizados en este proyecto)

En `http://localhost/dash-services.php`:

1. Cargar la vista → debe abrir en modo card por defecto (o el último guardado en `localStorage`).
2. Alternar a vista tabla y volver → sin parpadeos, contador de tarjetas correcto.
3. Recargar la página → la vista elegida persiste (`localStorage`).
4. Buscar por número de servicio, cliente y obra → filtra ambas vistas por igual.
5. Cambiar pills Todos/Facturados/No Facturados → recarga con el filtro correcto aplicado server-side.
6. Verificar que las cards muestren los tags de tipo de servicio correctos contra al menos 2 servicios reales con combinaciones distintas de flags.
7. Verificar footer de facturación: un servicio facturado debe mostrar el número real de factura (no solo el badge genérico).
8. Probar las 4 acciones del dropdown de la tabla (Editar, Asignar Baños, Imprimir, Eliminar con confirm) — sin regresiones.
9. Responsive: probar en mobile (grid a 1 columna, tabla con scroll horizontal).
10. Confirmar que no quedan requests a los assets de DataTables removidos (Network tab).

---

## Resumen ejecutivo (para retomar sesión)

1. **Backend:** 1 método a extender (`listActiveWithDetails()`), sin cambios de interfaz ni Use Cases.
2. **Frontend:** reescribir `dash-services.php` (toolbar + 2 vistas) + 1 JS nuevo (`services.js`, clon de `bathrooms.js`) — sin tocar `add`/`edit`/`print`/`bath` (ya migrados, fuera de alcance).
3. **Sin dependencias nuevas**, sin tocar `composer.json`, sin migraciones de DB (las tablas y columnas ya existen).
4. Después de cada fase de código PHP: `docker-compose restart php` + smoke test manual (regla del proyecto, no hay CI).
