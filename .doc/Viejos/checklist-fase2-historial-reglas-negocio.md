# Checklist — Fase 2: Historial y reglas de negocio (Contratos, Baños, Facturas)

**Origen:** `.doc/Viejos/Cotización/cotizacion-mejoras-julio-2026.md` (ítems 7-13) + `.doc/Viejos/tecnico-mejoras-julio-2026.md` + cambios de Edgardo del 2026-07-07 (reemplazan el enfoque original del ítem 8)
**Estado:** ✅ Fase 2 completa (ítems 7-13) — implementado y verificado en navegador (2026-07-08).

---

## 7a. Contratos: renombrar "Inactivo" → "Terminado" — ✅ implementado y verificado en navegador (2026-07-08)

- [x] `dash-contracts.php`: badge `Inactivo` → `Terminado`, y color cambiado de rojo (`item-inactivo`) a azul (`item-disponible`, la misma clase azul creada en Fase 1) — decisión de Edgardo: el rojo se reserva para bajas/canceladas, no para un estado neutro como "Terminado"
- [x] `dash-contracts-add.php`: `<option value="1">Inactivo</option>` → `Terminado`
- [x] `dash-contracts-edit.php`: `<option value="1">Inactivo</option>` → `Terminado`
- [x] Nota: `estado_Contrato == 0` sigue siendo "Eliminado" — sin cambios, no mencionado por Edgardo
- [x] Verificado en navegador en las 3 vistas: listado muestra "Terminado"/"Activo", selects de alta y edición muestran "Activo"/"Terminado" (edición además con "Eliminado" para el valor 0)

## 7b. Contratos: submenú Activos / Terminados — ✅ implementado y verificado en navegador (2026-07-08)

- [x] Migración `created_at` en `contratos`
- [x] Un solo archivo `dash-contracts.php` + parámetro `?estado=1|2` en la URL (opción recomendada del documento técnico), con prepared statement para el filtro
- [x] `layouts/vertical-menu.php`: submenú "Obra / Contratos" ahora tiene `Contratos Activos` (`?estado=2`) y `Contratos Terminados` (`?estado=1`), reemplazando el link genérico "Listado Obras / Contratos"
- [x] Ambos ordenados por `created_at DESC, id_Contrato DESC` (mismo patrón de fallback ya usado en certificados/facturas/servicios)
- [x] Título de la página cambia dinámicamente: "Contratos Activos" / "Contratos Terminados" / "Listado de contratos" (sin filtro)
- [x] **Compatibilidad hacia atrás verificada:** `dash-contracts.php` sin parámetro (usado en breadcrumbs de `dash-contracts-item.php`, `dash-contracts-edit.php`, `dash-contracts-add.php`, y en los redirects de `contract-active.php`/`contract-inactive.php`/`contract-down.php`) sigue mostrando ambos estados mezclados, igual que antes — no se rompió ningún link existente
- [x] Verificado en navegador: `?estado=2` → solo "Activo" (50 filas mostradas de esa página), `?estado=1` → solo "Terminado" (40 filas), sin parámetro → ambos mezclados (comportamiento original intacto)

## 8. Módulo nuevo: "Histórico" + "Baños & Contratos" — ✅ implementado y verificado en navegador (2026-07-08)

> El enfoque original de este ítem (agregar `activo_Relacion` a `contrato_bathroom` y mostrar 2 cards "Asignados/No Asignados" dentro de `dash-contracts-item.php`) queda reemplazado, confirmado por Edgardo: el módulo nuevo "suplanta" ese enfoque, no se implementa el soft-delete de `contrato_bathroom`.

### 8.1 — Renombrar página existente
- [x] `layouts/vertical-menu.php`: nueva entrada `dash-bathrooms-contracts.php` → texto `Histórico`
- [x] `dash-bathrooms-contracts.php`: título (`<title>` y `<h4>`) actualizado a "Histórico de Baños & Contratos"
- [x] Contenido/query de `dash-bathrooms-contracts.php` sin cambios, solo el nombre/título

### 8.2 — Nuevo componente "Baños & Contratos"
- [x] Archivo nuevo: `dash-bathrooms-contracts-status.php`
- [x] Entrada de menú "Baños & Contratos" en `layouts/vertical-menu.php` apuntando al archivo nuevo
- [x] Tabs Bootstrap (`nav-tabs` + `tab-content`/`tab-pane`, patrón del theme Skote), cada tab con su propia tabla DataTables independiente

**Tab "Todos los contratos activos"** — columnas Código de Baño, Fecha de Inicio de Contrato, Estado de Contrato, Asignado a Obra (fijo "Asignado"), Nombre de la Obra, Nombre del Cliente. Orden por Código de Baño.

**Tab "Todos los baños disponibles"** — columnas Código del Baño, Fecha de Compra, Estado (fijo "Disponible"). Orden por Código de Baño.

**Regla "comparativa con HOY" — AJUSTADA durante QA (2026-07-08), ver hallazgo abajo.**

Query final implementada:

```sql
-- Tab "Todos los contratos activos"
SELECT BT.codigo_Bath, CT.fechaInicio_Contrato, CT.estado_Contrato,
       CT.obra_Contrato, CL.nombre_Cliente
FROM contratos CT
JOIN contrato_bathroom CB ON CB.id_Contrato = CT.id_Contrato
JOIN bathrooms BT ON BT.id_Bath = CB.id_Bath
JOIN clientes CL ON CL.id_Cliente = CT.id_Cliente
WHERE CT.estado_Contrato = 2
  AND CT.fechaInicio_Contrato <= CURDATE()
ORDER BY BT.codigo_Bath ASC

-- Tab "Todos los baños disponibles"
SELECT BT.codigo_Bath, BT.fechaCompra_Bath
FROM bathrooms BT
WHERE BT.estado_Bath = 1
  AND NOT EXISTS (
    SELECT 1 FROM contrato_bathroom CB
    JOIN contratos CT ON CT.id_Contrato = CB.id_Contrato
    WHERE CB.id_Bath = BT.id_Bath
      AND CT.estado_Contrato = 2
      AND CT.fechaInicio_Contrato <= CURDATE()
  )
ORDER BY BT.codigo_Bath ASC
```

- [x] **Hallazgo crítico durante QA:** la condición original (`CURDATE() BETWEEN fechaInicio_Contrato AND fechaFin_Contrato`) dejaba la tab de "contratos activos" **siempre vacía** — los 143 contratos con `estado_Contrato = 2` tienen `fechaFin_Contrato = fechaInicio_Contrato` (mismo día), y esa fecha siempre está en el pasado. `fechaFin_Contrato` no se usa como fecha de término real en los datos actuales. **Decisión de Edgardo:** simplificar a `fechaInicio_Contrato <= CURDATE()` (solo verificar que ya empezó), sin exigir `fechaFin >= HOY`
- [x] Verificado en navegador: tab "contratos activos" muestra filas ordenadas por código; tab "disponibles" muestra "Disponible" en azul
- [x] **Verificación cruzada contra la base de datos:** 86 baños activos totales = 72 con contrato activo (distintos) + 14 disponibles (sin solapamiento, sin huecos)
- [x] **Agregado por Edgardo (2026-07-08):** contador de cantidad exacta en ambas tabs — badge numérico junto al nombre de cada tab (138 relaciones contrato-baño activas / 14 baños disponibles) + línea de texto "Cantidad de X: N" arriba de cada tabla. Nota: 138 cuenta relaciones contrato-baño (un mismo baño puede tener más de un contrato activo asociado), no baños únicos — para eso está la verificación cruzada de 72 baños distintos de arriba

## 9. Contratos: cierre automático sin baños — ✅ implementado y verificado en navegador (2026-07-08)

> Motivado por un hallazgo real durante el ítem 8: contratos que representan trabajos de un solo día nunca se marcaban "Terminado" manualmente, quedando "Activo" para siempre — un mismo baño aparecía en decenas de contratos "activos" simultáneos. Edgardo: "si una obra no tiene baños asignados debe estar como Terminada, no tiene contrato activo".

- [x] `controller/contract-bath-notassign.php`: al desasignar un baño, si el contrato queda con `COUNT(*) = 0` en `contrato_bathroom`, `UPDATE contratos SET estado_Contrato = 1` (Terminado)
- [x] De paso se migró todo el archivo a prepared statements (antes tenía SQL injection vía `$_GET`)
- [x] **Prueba end-to-end real, caso positivo:** contrato #39 con 1 solo baño (AT032) → al desasignarlo, el contrato pasó a `estado_Contrato = 1` automáticamente, la relación se eliminó y el baño quedó `asignado_Bath = 0`
- [x] **Prueba end-to-end real, caso negativo:** contrato #17 con 5 baños → al desasignar 1 (AT003), el contrato se mantuvo `estado_Contrato = 2` (Activo) con las 4 relaciones restantes intactas — no se cierra de más
- [x] **Limpieza retroactiva aplicada en testing (2026-07-08):** 62 contratos que estaban "Activos" sin ningún baño asignado pasaron a "Terminado". Verificado después: 0 contratos activos sin baños restantes
- [x] **Script para producción:** `mysql/migrations/2026-07-fase1-fase2.sql` — consolida TODOS los cambios de esquema/datos de Fase 1 y Fase 2 (columnas `created_at`, `retiro_Tipo`, `codigo_Bath_unique`, y esta limpieza retroactiva), con verificaciones previas de seguridad. Se va a seguir ampliando con cada cambio nuevo de las próximas fases, en vez de crear un archivo por ítem
- [x] **Nota sin resolver, investigada a fondo (2026-07-08):** baños en más de un contrato "activo" simultáneo (caso `AT060`) no se corrige con esta limpieza — requiere decisión caso por caso, no es seguro automatizarlo. Alcance real confirmado por consulta a la base: **41 baños distintos** (no solo AT060) con entre 2 y 4 contratos "activos" simultáneos cada uno. Ejemplo AT060: 4 contratos (#131, #133, #81, #163), todas obras distintas con `fechaInicio_Contrato` ya pasada — confirma que es un problema sistémico de falta de cierre manual histórico, no un caso aislado. **Queda fuera de este alcance a propósito** (ver razonamiento en `mysql/migrations/2026-07-fase1-fase2.sql`), pendiente de decisión de negocio de Edgardo sobre qué hacer con cada caso.
- [x] **QA en navegador de este ítem, etapa de testing final consolidada (2026-07-08):** se probó en vivo el flujo completo con el contrato #60 (1 solo baño, "FOSA-SEPTICA") — al desasignar el baño desde `dash-contracts-item.php`, el contrato pasó automáticamente a "Terminado" (verificado en base). Se reasignó el mismo baño vía la UI para restaurar el dato de prueba; como es esperable, la reasignación **no** reactiva el contrato automáticamente (fuera de alcance de este ítem), así que se reactivó manualmente a "Activo" desde `dash-contracts-edit.php` para dejar todo como estaba antes de la prueba.

---

## Ítem 10 — Baños: filtros tipo pestañas — ✅ implementado y verificado en navegador (2026-07-08)

- [x] `dash-bathrooms.php`: dos grupos de botones de filtro client-side (sin cambios de backend) — Estado (Todos/Activo/Inactivo) y Asignación (Todos/Asignado/Disponible), combinables entre sí
- [x] Implementado con `data-estado`/`data-asignado` en cada `<tr>` + `$.fn.dataTable.ext.search.push(...)` para filtrar sobre el DataTable ya inicializado
- [x] De paso se agregó `htmlspecialchars()` a las columnas de esa vista que no lo tenían (código, fecha de compra, observaciones) — deuda XSS pendiente, corregida en esta vista al tocarla
- [x] Verificado en navegador: filtro "Inactivo" solo, y combinación "Activo" + "Asignado" — ambos funcionan correctamente

## Ítem 11 — Facturas: botón Editar — ✅ implementado y verificado en navegador (2026-07-08)

- [x] Archivo nuevo `dash-invoices-edit.php` + `controller/invoice-update.php` (prepared statements desde el día uno), siguiendo el patrón de `dash-invoices-add.php`/`invoice-new.php`
- [x] Campos editables: `numero_Factura`, `fecha_Factura`, `id_Cliente`, `id_Contrato`, `valor_Factura` — el select de Contrato se precarga vía AJAX reutilizando `controller/obtener_contratos.php`, seleccionando el contrato actual de la factura
- [x] Botón "Editar" (lápiz) agregado en `dash-invoices-list.php`, columna "Otros"
- [x] Verificado en navegador: edición de la factura #1896 (monto), guardó y reflejó el cambio en el listado; valor de prueba revertido al original

## Ítem 12 — Facturas: no perder servicios al anular — ✅ implementado y verificado en navegador (2026-07-08)

- [x] `controller/invoice-delete.php` e `invoice-estado.php`: al setear `estado_Factura = 3` (Anulado), se ejecuta `DELETE FROM factura_servicio WHERE id_Factura = ?` para liberar los servicios asociados (vuelven a estar disponibles para facturar, mismo filtro `NOT EXISTS` que ya usa `dash-services.php`)
- [x] De paso ambos controllers se migraron a prepared statements (antes tenían SQL injection vía `$_GET`)
- [x] **Prueba end-to-end real en navegador:** factura #1887 (11 servicios asociados) anulada desde el dropdown de Acciones → verificado en base que `factura_servicio` quedó en 0 relaciones para esa factura. Dato de prueba restaurado después (factura vuelta a "Pendiente" con sus 11 relaciones originales, confirmado por Edgardo dado que la restauración requería una escritura directa en la base)

## Ítem 13 — Facturas: campo Fecha de Pago — ✅ implementado y verificado en navegador (2026-07-08)

- [x] Migración `fecha_Pago DATE NULL` en `facturas` (Sección 5 de `mysql/migrations/2026-07-fase1-fase2.sql`), aplicada en la base de testing
- [x] Columna nueva "Fecha de Pago" en `dash-invoices-list.php` con botón de lápiz que abre un modal Bootstrap compartido (patrón ya usado en `dash-invoices-detail.php`), precargado con la fecha actual vía `show.bs.modal` + `data-*` attributes
- [x] `controller/invoice-fecha-pago.php` (prepared statements), permite tanto fijar como limpiar la fecha (vuelve a "Sin definir")
- [x] Verificado en navegador: fijar fecha en factura #1897, guardar, ver reflejo en el listado, y volver a limpiarla — ambos casos funcionan

---

## Reglas a respetar durante la implementación (de `CLAUDE.md`)

- SQL nuevo o tocado → siempre prepared statements, nunca interpolación
- Vistas tocadas → verificar `htmlspecialchars()` en los outputs que se agreguen/modifiquen
- Mantener estilo procedural, `snake_case`, sin introducir clases
- Commit atómico por ítem
- No commitear sin pedido explícito

---

## Notas / cambios pendientes de agregar por el usuario

*(espacio para que Edgardo sume o modifique ítems antes de arrancar)*

-
