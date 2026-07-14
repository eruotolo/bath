# Checklist — Fase 3: Carga masiva de facturas por Excel

**Origen:** `.doc/Viejos/Cotización/cotizacion-mejoras-julio-2026.md` (ítem 14) + `.doc/Viejos/tecnico-mejoras-julio-2026.md` + decisiones de Edgardo del 2026-07-08
**Estado:** ✅ implementado y verificado en navegador (2026-07-08)

---

## Decisiones tomadas antes de codear

- **Formato de archivo: `.xlsx` real**, no CSV — decisión explícita de Edgardo ("el cliente no va a realizar un CSV"), aunque el documento técnico recomendaba CSV por simplicidad.
- **Columnas de la plantilla** (primera fila = encabezado, izquierda a derecha): `RUT del Cliente`, `Numero de Factura`, `Fecha Factura`, `Monto`.
- **RUT no encontrado en Clientes:** se rechaza solo esa fila, la carga sigue con el resto (mismo criterio que facturas duplicadas).
- **Formato de moneda:** todo monto se muestra como `142.800 CLP` (separador de miles con punto, sufijo CLP) — tanto en la previsualización de la carga como en `dash-invoices-list.php`.

## Hallazgo técnico durante la implementación

El documento técnico asumía que PHP tendría disponible `ZipArchive` (extensión `zip`) para leer/escribir el `.xlsx` (que por dentro es un ZIP con XML). Se verificó que **la imagen `php:8.1-fpm-alpine` de este proyecto no tiene la extensión `zip` instalada** (`php -m` no la lista). Para no modificar el `Dockerfile` (cambio de infraestructura fuera de lo pedido), se implementó lectura y escritura de ZIP a mano usando solo funciones nativas de PHP (`gzinflate`, `crc32`, `pack`/`unpack`), sin agregar ninguna dependencia nueva. Limitación conocida y documentada en el código: no soporta archivos ZIP en modo streaming con "data descriptor" (bit 3 de flags) — no es un caso real en archivos `.xlsx` generados por Excel o LibreOffice.

## Archivos nuevos

- `app/public/layouts/helpers.php` — `format_clp()`, `leer_xlsx()` (+ `zip_leer_archivo()`, `columna_a_indice()`), `excel_a_fecha()`, `normalizar_rut()`
- `app/public/assets/templates/plantilla-facturas.xlsx` — plantilla descargable generada a mano (mismo enfoque sin librerías)
- `app/public/dash-invoices-upload.php` — pantalla de subida + link de descarga a la plantilla
- `app/public/controller/invoice-upload-parse.php` — parsea el archivo, matchea RUT contra clientes, trae contratos activos, guarda en `$_SESSION['carga_facturas']` (no inserta nada todavía)
- `app/public/dash-invoices-upload-preview.php` — grilla editable, filas con error en rojo, botón Confirmar deshabilitado hasta completar todas las obras
- `app/public/controller/invoice-upload-cancel.php` — limpia la sesión y vuelve al listado
- `app/public/controller/invoice-upload-confirm.php` — inserta con prepared statements, rechaza número de factura duplicado, guarda resumen en `$_SESSION['carga_resultado']`
- `app/public/dash-invoices-upload-result.php` — muestra N cargadas / M rechazadas con motivo

## Archivos tocados

- `app/public/layouts/vertical-menu.php` — nueva entrada "Cargar Facturas" en el submenú de Facturas
- `app/public/dash-invoices-list.php` — columna Monto Factura ahora usa `format_clp()`

## Checklist

- [x] Pantalla de subida con validación de extensión `.xlsx`
- [x] Plantilla descargable con las 4 columnas en el orden correcto
- [x] Parseo: fecha como número de serie de Excel (ej. `46211` → `2026-07-08`) y como texto (`2026-07-01`) — ambos casos probados
- [x] RUT no encontrado → fila rechazada individualmente, resto de la carga sigue
- [x] Previsualización: selects de obra poblados con los contratos activos del RUT detectado
- [x] Botón "Confirmar Carga" deshabilitado hasta que todas las filas válidas tengan obra seleccionada (JS)
- [x] Número de factura duplicado → rechazado en la confirmación, con motivo en el resumen final
- [x] Inserción con prepared statements desde el día uno
- [x] Vista de resultado final con detalle de rechazos y motivo
- [x] Monto formateado en CLP en la previsualización y en el listado de facturas
- [x] **Prueba end-to-end real en navegador:** archivo con 4 filas (2 válidas con fecha en distinto formato, 1 RUT inexistente, 1 número de factura ya existente) → resultado exacto: 2 cargadas, 2 rechazadas con el motivo correcto cada una. Verificado en la base y luego revertido (se borraron las 2 facturas de prueba)

---

## Reglas a respetar durante la implementación (de `CLAUDE.md`)

- SQL nuevo → siempre prepared statements, nunca interpolación (cumplido)
- Vistas nuevas → `htmlspecialchars()` en los outputs (cumplido)
- Sin Composer, sin librerías vendorizadas nuevas (cumplido — lector/escritor ZIP propio)
- Estilo procedural, `snake_case`, sin introducir clases (cumplido)
