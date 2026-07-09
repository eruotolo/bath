# Documento técnico — Mejoras julio 2026

Detalle técnico interno que acompaña a `.doc/cotizacion-mejoras-julio-2026.md`. Este documento **no se envía al cliente**.

## Hallazgo transversal: no existe `created_at` en ninguna tabla

Se revisó `mysql/database/donbano.sql` completo: ninguna tabla (`certificados`, `facturas`, `contratos`, `servicios`, `bathrooms`, `clientes`, etc.) tiene columna de fecha de creación. Todos los pedidos de "ordenar por createdAt desc" requieren:

```sql
ALTER TABLE certificados ADD COLUMN created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE facturas     ADD COLUMN created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE contratos    ADD COLUMN created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE servicios    ADD COLUMN created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;
```

Los registros existentes quedarán con `created_at` = momento de la migración (no hay forma de reconstruir la fecha real de creación histórica). Si el orden histórico importa, usar `id_*` como fallback de orden secundario (`ORDER BY created_at DESC, id_X DESC`), ya que el autoincremental sí refleja el orden real de inserción pasado.

No hay ningún framework de migraciones — los `ALTER TABLE` se aplican a mano contra `mysql/database/donbano.sql` y contra la base viva (documentar en README o carpeta `mysql/migrations/` si se decide formalizar, fuera de alcance de este trabajo).

---

## 1. Certificados — orden + botón imprimir (`dash-certificates.php`)

- Migración: `created_at` en `certificados`.
- Query línea 86-89: agregar `ORDER BY CR.created_at DESC`.
- Botón nuevo en columna Acciones (junto a "Ver" y "Eliminar", líneas 104-109): enlace a `dash-certificates-item.php?id_Certificado=...&id_Contrato=...` — la página destino **ya tiene** el botón de impresión (`javascript:window.print()`, línea 132-136 de `dash-certificates-item.php`), no hace falta crear una vista de impresión nueva. Opcional: abrir en `target="_blank"` para no perder el listado.

## 2-5. Facturas (`dash-invoices-list.php`)

- Migración: `created_at` y `fecha_Pago` (nullable `DATE`) en `facturas`.
- Orden: query líneas 105-109, agregar `created_at` al `ORDER BY`.
- **Editar factura**: no existe `dash-invoices-edit.php` ni `invoice-edit.php`. Crear siguiendo el patrón de `dash-invoices-add.php` / `controller/invoice-new.php`, precargando los valores actuales. Campos editables razonables: `numero_Factura`, `fecha_Factura`, `valor_Factura`, `id_Contrato`. **Usar prepared statements desde el día uno** (el resto del código legacy usa interpolación directa; no repetir el patrón en código nuevo, según regla del proyecto).
- **Fecha de Pago editable desde la grilla**: patrón más simple = modal rápido o input inline + un endpoint `controller/invoice-fecha-pago.php` vía POST/AJAX que actualiza solo esa columna. Reusar el dropdown de estado ya existente (líneas 142-153) como referencia de UI, o agregar un ícono de lápiz que abre un pequeño modal con un `<input type="date">`.
- **Cascada al anular**: se verificó `controller/invoice-delete.php` e `invoice-estado.php` — **ninguno borra realmente `factura_servicio`**; ambos solo hacen `UPDATE facturas SET estado_Factura = 3`. El problema real es el inverso al que sugiere el pedido: los servicios asociados (tabla `factura_servicio`) **quedan atados para siempre** a una factura anulada, porque nada libera la relación. Fix: al setear `estado_Factura = 3`, ejecutar también `DELETE FROM factura_servicio WHERE id_Factura = ?` (esto no borra el servicio en sí, solo la relación — el servicio vuelve a aparecer como "No Facturado" en `dash-services.php`, que ya filtra por `NOT EXISTS` en `factura_servicio` — ver `dash-invoices-detail.php` líneas 173-182 para el patrón exacto que ya usa esa misma lógica de disponibilidad).

## 6-8. Contratos (`dash-contracts.php`, `dash-contracts-item.php`)

- Migración: `created_at` en `contratos`.
- **Submenú Activos/Inactivos**: hoy `dash-contracts.php` línea 90 trae `estado_Contrato IN (1, 2)` (ambos mezclados). Dos opciones: (a) un solo archivo + parámetro `?estado=1|2` en la URL, o (b) duplicar en dos entradas de menú con el mismo archivo y distinto query string. Recomendado (a) para no duplicar vista. Editar `layouts/vertical-menu.php` líneas 118-127 agregando dos `<li>` en el submenu de "Obra / Contratos" apuntando a `dash-contracts.php?estado=2` y `dash-contracts.php?estado=1`.
- **Histórico de baños asignados/no asignados — hallazgo crítico**: revisando `controller/contract-inactive.php` (líneas 13-28) y `controller/contract-bath-notassign.php` (línea 13), **ambos hacen `DELETE FROM contrato_bathroom`** al desasignar un baño o al inactivar un contrato completo. Esto es lo que hoy destruye el historial — no es una "eliminación en cascada de facturas", es en la tabla puente de contratos↔baños. Fix necesario:
  1. Migración: agregar columna `activo_Relacion TINYINT(1) NOT NULL DEFAULT 1` a `contrato_bathroom`.
  2. `contract-bath-notassign.php`: reemplazar el `DELETE FROM contrato_bathroom` por `UPDATE contrato_bathroom SET activo_Relacion = 0 WHERE id_Relacion = ?` (mantener el `UPDATE bathrooms SET asignado_Bath = 0` como está).
  3. `contract-inactive.php`: mismo cambio — reemplazar el `DELETE` del loop (línea 25) por `UPDATE ... SET activo_Relacion = 0`.
  4. `dash-contracts-item.php` (líneas 174-215): la query debe filtrar `activo_Relacion = 1` para la card "Baños Asignados", y agregar una segunda card con `activo_Relacion = 0` para "Baños No Asignados (histórico)". El botón "No Asignar" (línea 206) solo debe aparecer en la primera card.
- **Cierre automático de obra sin baños**: agregar al final de `contract-bath-notassign.php` (después del `UPDATE`/soft-delete) un `SELECT COUNT(*) FROM contrato_bathroom WHERE id_Contrato = ? AND activo_Relacion = 1` — si el resultado es 0, ejecutar el mismo `UPDATE contratos SET estado_Contrato = 1` que usa `contract-inactive.php`. Mismo control debería aplicarse si en el futuro se agrega una opción de desasignar múltiples baños a la vez.

## 9-11. Servicios (`dash-services.php`, `dash-services-add.php`)

- Migración: `created_at` en `servicios`.
- Orden: query líneas 95-101, agregar al `ORDER BY`.
- Select cliente alfabético (`dash-services-add.php` línea 65): `SELECT * FROM clientes ORDER BY nombre_Cliente ASC`.
- **Nuevo tipo "Retiro de Baños"**: tabla `tipo_servicio` (línea 1084 del dump) tiene 8 columnas tinyint de tipo. Agregar `retiro_Tipo TINYINT(1) DEFAULT 0`. Archivos a tocar en cascada (mismo patrón que los 8 tipos existentes):
  - `dash-services-add.php` (checkbox nuevo, líneas 90-154)
  - `controller/service-new.php` (líneas 9-25, agregar variable + columna al INSERT)
  - `dash-services-edit.php` y su controller correspondiente (no leídos aún — **revisar antes de implementar**, deben seguir el mismo patrón)
  - `dash-services-print.php` (no leído aún — probablemente itera las mismas 8 columnas para mostrar el detalle impreso, **revisar antes de implementar**)

## 12-13. Baños (`dash-bathrooms.php`, `dash-bathrooms-add.php`)

- **Filtros tipo pestañas**: no requiere cambios de servidor — los datos de estado (`estado_Bath`) y asignación (`asignado_Bath`) ya están en cada `<tr>` renderizada. Implementar como filtro client-side sobre el DataTable ya inicializado (`assets/js/pages/datatables.init.js` o script inline de la vista), usando `data-*` attributes en cada fila + botones que llaman a `table.column(n).search()` o `.draw()` con filtro custom de DataTables. Cero cambios de backend.
- **Código único**: migración `ALTER TABLE bathrooms ADD UNIQUE KEY codigo_Bath_unique (codigo_Bath)`. Ojo: **los datos de ejemplo ya tienen un duplicado** (`AT060` aparece en id_Bath 18 y 65, ver dump líneas 48 y 94) — antes de aplicar el `UNIQUE` en producción hay que limpiar duplicados existentes o la migración falla. Validar también en `controller/bath-new.php` (y en el edit equivalente) con un `SELECT COUNT(*) WHERE codigo_Bath = ?` antes del INSERT/UPDATE, para dar un mensaje de error amigable en vez de que falle por el constraint de la base.

## 14. Módulo nuevo — Carga masiva de facturas por Excel

### Restricción de stack a respetar

No hay `composer.json` en el proyecto (regla explícita de `CLAUDE.md`: no introducir Composer fuera del plan DDD) y no hay ninguna librería de lectura de Excel vendorizada (se verificó `app/public/vendor/` — solo contiene PHPMailer). Opciones evaluadas:

1. **Vendorizar PhpOffice/PhpSpreadsheet a mano** (sin Composer): inviable en la práctica — tiene múltiples dependencias transitivas (`markbaker/matrix`, `markbaker/complex`, PSR interfaces) que normalmente resuelve Composer. Vendorizar todo el árbol a mano es fricción alta y frágil ante actualizaciones.
2. **Parser XLSX propio, liviano, sin dependencias externas** (recomendado): un archivo `.xlsx` es un ZIP con XML adentro. PHP trae nativo `ZipArchive` y `SimpleXMLElement` — se puede escribir un lector mínimo (~100-150 líneas) que abre `xl/worksheets/sheet1.xml` y `xl/sharedStrings.xml` del ZIP y arma un array de filas, sin ninguna librería externa. Cubre el caso de uso (una sola hoja, columnas fijas, sin fórmulas). Es la opción elegida para el estimado de horas.
3. Alternativa aún más simple si el cliente lo acepta: exigir `.csv` en vez de `.xlsx` real (Excel exporta CSV nativamente). Reduce el parser a `fgetcsv()` nativo de PHP, elimina el riesgo del punto 2. **Recomendar esto al cliente si prioriza robustez sobre "que sea un Excel real".**

### Flujo propuesto

1. Nuevo ítem en `layouts/vertical-menu.php` dentro del submenu de Facturas: "Cargar Facturas" → `dash-invoices-upload.php`.
2. `dash-invoices-upload.php`: formulario de subida (`<input type="file">`) + link de descarga a una plantilla estática (`assets/templates/plantilla-facturas.xlsx` o `.csv`, servida como archivo fijo en el repo).
3. POST a `controller/invoice-upload-parse.php`: parsea el archivo, **no inserta nada todavía**. Por cada fila, busca el cliente por `rut_Cliente` (columna del archivo) y trae sus contratos activos (mismo query que ya existe en `controller/obtener_contratos.php`, reutilizable). Guarda el resultado parseado en `$_SESSION` (dataset pequeño, aceptable para este volumen) y redirige a una vista de previsualización.
4. `dash-invoices-upload-preview.php`: tabla editable — cada fila con los datos leídos + un `<select name="obra_Contrato[]">` poblado con los contratos del RUT detectado en esa fila (si el RUT no matchea ningún cliente, la fila se marca en rojo y no puede confirmarse). Validación JS: deshabilitar "Confirmar" mientras haya alguna fila sin obra seleccionada.
5. Botón "Cancelar": limpia la sesión y vuelve al listado. Botón "Confirmar": POST a `controller/invoice-upload-confirm.php`.
6. `controller/invoice-upload-confirm.php`: recorre las filas de la sesión, por cada una hace `SELECT COUNT(*) FROM facturas WHERE numero_Factura = ?` — si existe, la rechaza (acumula en un array de rechazadas con motivo); si no existe, inserta con **prepared statement**. Al final limpia la sesión y muestra un resumen: N cargadas, M rechazadas (con el detalle de números duplicados).

### Riesgos / decisiones pendientes a validar con el cliente antes de codear

- Formato final del archivo (¿`.xlsx` real o `.csv`? define si se implementa el parser custom o `fgetcsv`).
- Columnas exactas de la plantilla (mínimo: RUT cliente, número de factura, fecha, valor — falta definir si la plantilla trae contrato/obra o si siempre se completa a mano en la previsualización, según el pedido original "esa columna va a estar vacía").
- Qué pasa si el RUT del archivo no existe en `clientes` — ¿se rechaza la fila entera o se detiene toda la carga? (asumido: se rechaza esa fila, seguí el resto, mismo criterio que duplicados).
- Tamaño máximo de archivo / cantidad de filas esperada (afecta si conviene seguir usando `$_SESSION` o pasar a tabla temporal en BD).

---

## Deuda técnica que se toca de paso

Todos los controllers listados arriba (`invoice-*.php`, `contract-*.php`, `bath-new.php`, `service-new.php`) usan hoy interpolación directa de variables en SQL (deuda crítica ya documentada en `CLAUDE.md`). Cualquier línea que se modifique como parte de este trabajo se reescribe con `mysqli_prepare` + `bind_param`, sin migrar el archivo completo al patrón Repository (eso queda para el plan DDD, fuera de este alcance). Es una migración incremental, entidad por entidad, tal como indica la sección "Deuda técnica conocida" de `CLAUDE.md`.

## Archivos pendientes de revisar antes de implementar (no leídos en este research)

- `dash-services-edit.php` + su controller de edición
- `dash-services-print.php`
- `dash-bathrooms-edit.php` + `controller/bath-edit.php` (para aplicar la misma validación de código único)
- `controller/certificate-remove.php` (confirmar que no rompe nada al agregar el botón de impresión al listado)
