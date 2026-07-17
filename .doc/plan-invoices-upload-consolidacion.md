# Plan — Consolidar importación de Excel en `dash-invoices-list.php` (drawer + modal)

## Contexto

`FacturasView.tsx` (mockup rebranding) muestra un drawer de importación Excel simulado. En producción ya existe el flujo real (3 páginas: `dash-invoices-upload.php` → `dash-invoices-upload-preview.php` → `dash-invoices-upload-result.php`, con parseo `.xlsx` real, matching de RUT, selección de obra y confirmación transaccional). El plan `.doc/plan-invoices-rebranding.md` había decidido no tocar este flujo y solo linkearlo desde el toolbar.

Edgardo pidió ir más allá: en vez de 3 páginas separadas, quiere **todo dentro de `dash-invoices-list.php`** — el paso de subida de archivo en un **panel lateral (drawer)**, igual que ya existe para "Emitir Factura Manual", y el paso de previsualización (hoy `dash-invoices-upload-preview.php`) en un **modal**. Objetivo: mejor usabilidad, sin salir nunca de la lista de facturas.

**No se toca la lógica de negocio** (parseo de xlsx, matching de RUT, inserción, detección de duplicados) — todo eso sigue en los controllers actuales (`invoice-upload-parse.php`, `invoice-upload-confirm.php`, `invoice-upload-cancel.php`), que ya son 100% funcionales. Este plan es puramente de **enrutamiento y presentación**: cambiar a dónde redirigen esos controllers, y mover el HTML de las 3 páginas a drawer/modal dentro de `dash-invoices-list.php`.

## Patrón a reutilizar (ya existe en el archivo)

`dash-invoices-list.php` ya implementa exactamente este patrón para "Emitir Factura Manual":
- `$_GET['action']` decide qué overlay renderizar (`new` hoy, se agrega `upload`).
- `$_GET['err']` para mostrar un error dentro del drawer.
- El drawer es HTML condicional server-side (`<?php if ($drawerMode === 'new'): ?>`), sin AJAX.
- Resultados de acciones (crear/editar/borrar) via redirect con `?status=success|error&msg=...`, leído por `feedback.js` (ya cargado en `vendor-scripts.php`) → toast (success) o modal SweetAlert2 (error). Ya usado por `invoice-delete.php` e `invoice-estado.php` en esta misma vista.

Para el modal de preview reutilizo el sistema `.modal`/`.modal-dialog` ya definido en `tw/components.css` (línea ~703) — es 100% CSS, sin backdrop JS aparte (`.modal.show` ya pinta el overlay oscuro). `bs-shim.js` ya bindea automáticamente el cierre por click-fuera/ESC/`data-bs-dismiss` a **cualquier** `.modal` presente en el DOM (no hace falta tocar `bs-shim.js`). Como el flag `important` está activo en Tailwind, para agrandar el modal (la tabla de preview tiene 5 columnas) alcanza con poner una clase Tailwind como `max-w-3xl` en el `.modal-dialog` — no hace falta CSS nuevo.

## Cambios

### 1. `app/public/dash-invoices-list.php`

**Botón toolbar:** cambiar `href="dash-invoices-upload.php"` → `href="?action=upload"` (línea 131).

**Nuevo drawer `#invoice-upload-drawer`** (junto al `if ($drawerMode === 'new')` existente, como un `elseif ($drawerMode === 'upload')`):
- Calco de contenido de `dash-invoices-upload.php`: link de descarga de plantilla (`assets/templates/plantilla-facturas.xlsx`), input de archivo `.xlsx`, form `POST` a `controller/invoice-upload-parse.php` con `enctype="multipart/form-data"`.
- Bloque de error (`$_GET['err']`) igual al patrón ya usado, mapeando los mismos 3 códigos que hoy usa `dash-invoices-upload.php` (`sin_archivo`, `formato_invalido`, `sin_filas`).

**Nuevo modal `#modalPreviewCarga`** (renderizado si `isset($_SESSION['carga_facturas'])` y hay filas — sin necesitar `$_GET['action']`, el propio dato de sesión es la señal, igual que hoy hace `dash-invoices-upload-preview.php`):
- `class="modal fade show"` con `data-modal-autoshow` (atributo informativo, no requiere cambios en JS — el `show` ya lo pinta el CSS) y `.modal-dialog.max-w-3xl`.
- Contenido: calco de la tabla de `dash-invoices-upload-preview.php` (mensajes de error por fila, select de obra condicionado, contador de válidas/con-error).
- Form `POST` a `controller/invoice-upload-confirm.php` (sin cambios en el controller salvo el redirect final).
- Botón "Cancelar" → `controller/invoice-upload-cancel.php` (ya redirige a `dash-invoices-list.php`, sin cambios).
- Script inline (scope a este bloque, calco del que ya trae `dash-invoices-upload-preview.php`) que habilita "Confirmar Carga" solo cuando todos los `select.select-obra` tienen valor, más `document.body.classList.add('modal-open')` para el scroll-lock (ya que no hay trigger-click que dispare `openModal()` de `bs-shim.js`).

### 2. `app/public/controller/invoice-upload-parse.php`

Cambiar los 3 redirects de error:
```php
header('Location: ../dash-invoices-upload.php?error=X');
```
→
```php
header('Location: ../dash-invoices-list.php?action=upload&err=X');
```
Y el redirect de éxito:
```php
header('Location: ../dash-invoices-upload-preview.php');
```
→
```php
header('Location: ../dash-invoices-list.php');
```
(el modal se autoabre solo por la presencia de `$_SESSION['carga_facturas']`, no hace falta query param).

### 3. `app/public/controller/invoice-upload-confirm.php`

Cambiar el redirect final. Se agrega una tercera categoría de status (`warning`) a `feedback.js` para el caso de éxito parcial (ver punto 4):
```php
$msg = "{$cargadas} factura(s) cargada(s) correctamente.";
if (count($rechazadas) > 0) {
    $detalle = implode('; ', array_map(fn($r) => "#{$r['numero_Factura']}: {$r['motivo']}", $rechazadas));
    $msg .= " " . count($rechazadas) . " fila(s) rechazada(s): {$detalle}";
}
$status = count($rechazadas) === 0 ? 'success' : ($cargadas > 0 ? 'warning' : 'error');
header('Location: ../dash-invoices-list.php?status=' . $status . '&msg=' . urlencode($msg));
```

### 4. `app/public/assets/js/components/feedback.js`

Agregar `warning()` (mismo shape que `error()`, ícono `warning`, botón ámbar) y una rama `status === 'warning'` en `readStatusFromQueryString()` que la invoque. Esto permite distinguir "todo cargó bien" (toast success) de "cargó parcial, revisar rechazadas" (modal ámbar, no auto-cierra) de "no cargó nada" (modal de error).

### 5. Archivos a retirar (a confirmar con Edgardo antes de borrar)

`dash-invoices-upload.php`, `dash-invoices-upload-preview.php`, `dash-invoices-upload-result.php` quedan sin ningún link que apunte a ellos una vez hecho el cambio — su lógica vive ahora en el drawer/modal de `dash-invoices-list.php`. Recomiendo borrarlos para no dejar código muerto/confuso. Los controllers (`invoice-upload-parse.php`, `invoice-upload-confirm.php`, `invoice-upload-cancel.php`) se mantienen intactos salvo los redirects.

## Fuera de alcance

- No se toca el parseo de xlsx, matching de RUT, ni la detección de duplicados (`invoice-upload-parse.php` líneas de lógica, `invoice-upload-confirm.php` líneas de lógica) — solo los `header('Location: ...')`.
- No se convierte a AJAX/fetch — sigue siendo full-page POST + redirect, igual que el resto de la app (sin JS framework, sin fetch de archivos).
- `dash-invoices-add.php` y su drawer equivalente no se tocan (ya está resuelto, fuera de este pedido).

## Verificación

1. `docker-compose exec php php -l dash-invoices-list.php` + lint de los 2 controllers tocados.
2. `docker-compose restart php` (no se tocó `composer.json` ni `app/src/`).
3. QA manual en `http://localhost/dash-invoices-list.php`:
   - Click "Subir Excel / CSV" → abre drawer (URL pasa a `?action=upload`), subir un `.xlsx` sin filas → vuelve al drawer con el error correspondiente.
   - Subir plantilla válida con filas OK y con RUT inexistente → redirige a la lista (sin `?action`) y el modal de preview se auto-abre mostrando ambas filas (una con error en rojo, otra editable con select de obra).
   - Botón "Confirmar Carga" queda deshabilitado hasta completar el select de obra de la fila válida.
   - Confirmar → redirige a la lista con toast/modal según el resultado (success/warning/error) y las filas nuevas aparecen en la tabla.
   - Cancelar desde el modal → vuelve a la lista sin haber insertado nada.
   - Repetir con un número de factura duplicado para confirmar el caso `warning` (cargó 0, rechazó 1 por duplicado, o mezcla).
4. Confirmar que no quedan links rotos a `dash-invoices-upload*.php` antes de borrarlos.
