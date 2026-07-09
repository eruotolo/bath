# Checklist — Roadmap interno / Mejoras frontend / Sprint 4: Datepickers + modal unificado + Glightbox + Dropzone

**Origen:** `.doc/plan-mejoras-frontend.md` (sección 11, Sprint 4) — roadmap interno, no cotizado al cliente
**Estado:** ✅ implementado y verificado en navegador (2026-07-09)

---

## Alcance de este sprint

1. `assets/js/components/datepicker.js` (Flatpickr)
2. Modal de edición unificado (`modal-editor.js`) — elimina `customer-edit.js`, `contactoEdit.js`, `contactoVer.js`
3. Glightbox en facturas y certificados
4. Dropzone en carga de foto de usuario

## Punto de partida al abrir esta sesión

Gran parte del sprint ya estaba implementada en el working tree sin commitear (sesión anterior): `datepicker.js` y `modal-editor.js` creados, cableados en `vendor-scripts.php`/`head-style.php`, y ya migrados: `dash-customers-item.php` (cliente + 2 modales de contacto) y 4 de 12 inputs de fecha. `bathEdit.js`, `contactoEdit.js`, `contactoVer.js`, `customer-edit.js` y `layouts/modal-edit-bath.php` ya estaban borrados — el modal de edición de baño no se migró a `ModalEditor`, se reemplazó por la página completa `dash-bathrooms-edit.php` (decisión ya tomada, no se tocó). Esta sesión completó lo que faltaba.

## 1. Datepicker (Flatpickr) — lo que faltaba

`datepicker.js` se auto-inicializa sobre **todo** `input[type="date"]:not([hidden])` con solo incluir `vendor-scripts.php` — no requiere `data-datepicker` en cada campo. Relevados los 12 inputs del plan, la mayoría ya funcionaban sin tocar nada (heredan el datepicker global). Solo faltaban:

- **`dash-services-add.php`**: se agregó `data-datepicker-min="today"` a `fecha_Servicio` (un servicio nuevo no debería agendarse en el pasado). No se aplicó a `dash-services-edit.php` a propósito — al editar un servicio existente no corresponde bloquear fechas pasadas.
- **`dash-certificates-add.php`**: el campo `fechahoy_Certificado` es `type="date" hidden` con valor por defecto `date('Y-m-d')` — el selector `:not([hidden])` lo excluye correctamente, no necesita Flatpickr visible.
- **`dash-invoices-list.php`**: tenía un `<link>`/`<script>` de Flatpickr duplicado y suelto desde el Sprint 2 (para un input de rango de fechas que en los hechos nunca se inicializó con `.flatpickr()` — markup muerto del template Skote, sin JS que lo active). Se eliminaron los includes duplicados; el datepicker global ya cubre el campo real (`modalFechaPagoInput`, en el modal de Fecha de Pago).

## 2. Modal editor unificado — ya estaba completo

Verificado en navegador: `dash-customers-item.php` usa `ModalEditor.init()` para cliente, editar contacto y ver contacto (3 instancias). No quedan referencias a los 3 scripts eliminados en ningún archivo activo.

**Deuda encontrada, no resuelta (fuera de alcance):** `controller/bath-get.php` quedó huérfano — ya no lo referencia ninguna vista. Se deja para que Edgardo decida si se borra (no se toca sin confirmar, por la regla del proyecto de no eliminar código sin pedido explícito).

## 3. Glightbox — implementado con un giro respecto al plan original

El plan original asumía que "Imprimir" en facturas/certificados abría PDFs. En los hechos abría `dash-invoices-print.php` / `dash-certificates-item.php`, páginas PHP que renderizan el dashboard completo (sidebar, menú) — inadecuadas para un iframe de lightbox. Se decidió con Edgardo generar PDFs reales con TCPDF (ya vendorizado, con precedente en `controller/servicio-pdf.php`) replicando el formato visual de esas páginas, y previsualizarlos con Glightbox.

### Archivos nuevos
- `controller/invoice-pdf.php` — genera el PDF de una factura (logos, cliente, contrato, tabla de servicios, total). Prepared statements (`mysqli_prepare` + `bind_param`), `htmlspecialchars()` en todo el output. Salida inline (`Output(..., 'I')`), sin guardar en disco.
- `controller/certificate-pdf.php` — mismo patrón para certificados (título, datos del cliente, texto legal, firma).
- `assets/js/components/lightbox-preview.js` — inicializa GLightbox sobre `[data-glightbox-preview]`.

### Bugs encontrados en la build vendorizada de GLightbox (no documentados así en ningún lado, se resolvieron por prueba en navegador)
1. **No existe el tipo `"iframe"`** en esta build — el tipo correcto para embeber una URL propia dentro de un iframe es `"external"`. `data-type="iframe"` no tira error, simplemente no renderiza nada (el `<div class="gslide-media gslide-iframe">` queda vacío).
2. **El slide de tipo `external` colapsa a 0px de alto** dentro de `.ginner-container` (flex-column) porque `.gslide-description` (el título) toma `flex-basis: 100%` y no deja espacio al iframe. Se corrigió con un override en `assets/css/style.css` (sección "Sprint 4"): `.gslide-description.description-bottom { flex: none }` + `.gslide-media.gslide-external { flex: none; height: 80vh }`.
3. **`width`/`height` sí se leen** de `data-width`/`data-height` en el trigger (`<a data-width="900px" data-height="90vh">`), vía `element.dataset`.

### Bug de infraestructura encontrado al generar el primer PDF
TCPDF tiró `TCPDF ERROR: TCPDF requires the Imagick or GD extension to handle PNG images with alpha channel` — el contenedor PHP no tenía GD instalada. Se agregó al `Dockerfile`:
```dockerfile
libpng-dev, libjpeg-turbo-dev, freetype-dev
docker-php-ext-configure gd --with-freetype --with-jpeg
docker-php-ext-install mysqli gd
```
Rebuild con `docker-compose up -d --build php` — verificado `php -m | grep gd`. **Esto aplica también en producción/testing**: si esos ambientes corren la misma imagen base sin GD, `invoice-pdf.php`/`certificate-pdf.php` van a fallar igual hasta que se actualice el Dockerfile ahí.

### Cambios en las vistas
- `dash-invoices-list.php`: el botón "Imprimir" ahora apunta a `controller/invoice-pdf.php` con `data-glightbox-preview data-type="external" data-width="900px" data-height="90vh"` en vez de navegar a `dash-invoices-print.php`.
- `dash-certificates.php`: mismo cambio para `controller/certificate-pdf.php`. El botón "Ver" no se tocó — sigue abriendo `dash-certificates-item.php` (detalle completo dentro de la app).

`dash-invoices-print.php` y `dash-certificates-item.php` **no se modificaron ni se borraron** — siguen accesibles si se navega directo, pero ya no están enlazadas desde "Imprimir".

## 4. Dropzone — imagen de perfil de usuario

### Archivo nuevo
- `assets/js/components/dropzone-upload.js` — sobre `div.dropzone[data-dropzone-target="#file"]`. Corre con `autoProcessQueue: false` y copia el archivo elegido al `input[type=file]` real (oculto) vía `DataTransfer`, así el form sigue mandando `multipart/form-data` sin tocar `user-new.php` ni `user-update.php`.

### Bug encontrado y corregido durante la implementación
`Dropzone.autoDiscover` (default `true`) se desactivaba dentro de `init()`, que corre en el mismo evento `DOMContentLoaded` que el auto-attach interno de la librería — y como el auto-attach de Dropzone.js se registra primero (el script de la librería carga antes que el componente propio), adjuntaba el elemento con opciones por defecto (mensaje en inglés, sin límite de archivos) antes de que corriera la config custom, y la instancia custom fallaba silenciosamente con "Dropzone already attached". Se movió `Dropzone.autoDiscover = false` a nivel de módulo (fuera de `init()`), ejecutándose de forma síncrona al cargar el script, antes de que `DOMContentLoaded` dispare el auto-attach.

También: la clase `dropzone` en el `<div>` **es obligatoria** — es el selector interno que usa Dropzone.js para decidir si inyecta el mensaje/estilo por defecto (`element.classList.contains("dropzone")`). Un nombre de clase custom como `dropzone-upload` no matchea y deja el div vacío sin avisar del error.

### Cambios en las vistas
- `dash-users-add.php`: `<input type="file">` → `<div class="dropzone" data-dropzone-target="#file"></div>` + input oculto.
- `dash-users-edit.php`: mismo cambio, preservando el `<img>` de la foto actual arriba del Dropzone.

## Checklist

- [x] Todos los date inputs relevantes tienen Flatpickr en español (la mayoría ya lo tenían por el init global; se agregó la regla de negocio faltante en `dash-services-add.php`)
- [x] `customer-edit.js`, `bathEdit.js`, `contactoEdit.js` eliminados (ya lo estaban al empezar esta sesión) — verificado que no quedan referencias
- [x] Preview de PDF en `dash-invoices-list.php` y `dash-certificates.php` — con el giro documentado arriba (PDF real generado con TCPDF en vez de iframe sobre la página de impresión con el dashboard completo)
- [x] Dropzone con drag & drop + preview en alta y edición de usuario
- [x] **Verificado en navegador real:** datepicker con español + restricción de fecha mínima en `dash-services-add.php`; Dropzone con mensaje en español, preview de imagen y botón "Quitar"; PDF de factura (#1896) y de certificado con logos, datos reales y formato correcto dentro del lightbox

## Pendiente / fuera de alcance de este sprint

- `controller/bath-get.php` huérfano — a decidir por Edgardo si se borra.
- El Dockerfile ahora requiere GD — replicar el mismo cambio en el ambiente de producción/testing antes de que ese ambiente dependa de `invoice-pdf.php`/`certificate-pdf.php`.
- `dash-invoices-print.php` y `dash-certificates-item.php` (imprimir viejo) quedaron sin uso desde el listado pero sin borrar — se podrían remover en una pasada de limpieza si se confirma que no se usan en ningún otro lado.
- Este era el último sprint del plan de mejoras frontend (`.doc/plan-mejoras-frontend.md`) — con esto el roadmap interno queda completo, 4 de 4.

---

## Reglas a respetar durante la implementación (de `CLAUDE.md`)

- SQL nuevo (`invoice-pdf.php`, `certificate-pdf.php`) → prepared statements — cumplido
- Output sin escapar → `htmlspecialchars()` en todo el contenido armado para el PDF — cumplido
- Sin Composer, sin npm, sin librerías nuevas más allá de Dropzone/GLightbox (ya vendorizadas) — cumplido
- Estilo procedural en el PHP tocado — cumplido
- Cambio de `Dockerfile` (agregar GD) — no es cambio de versión de PHP, es una extensión adicional; no requería el plan de migración PHP 8.5, pero se avisa igual por ser infraestructura compartida
- No se commiteó nada sin pedido explícito
