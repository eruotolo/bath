# Checklist — Plan PHP 8.5 / Prerrequisitos (pasos 2 y 3)

**Origen:** `.doc/Viejos/plan-migracion-php-8.5.md`, sección 6, pasos 2 y 3
**Estado:** ✅ implementado y verificado (2026-07-10)

---

## Alcance

Antes de tocar el `Dockerfile` (paso 5 del plan), se resolvieron los dos riesgos de código detectados en el análisis original (sección 4) que no dependen de rotar credenciales:

### Paso 2 — Manejo de excepciones de `mysqli_connect()` (sección 4.2 del plan)

**Archivo:** `app/public/layouts/config.php`

El bloque `if($link === false){ die(...) }` era código muerto desde PHP 8.1 (mysqli lanza `mysqli_sql_exception` por defecto en vez de devolver `false`). Se reemplazó por:

```php
try {
    $link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
} catch (\mysqli_sql_exception $e) {
    die("ERROR: Could not connect. " . $e->getMessage());
}
```

Se verificó que ningún otro archivo del proyecto (51 archivos con `mysqli_*`) repite el mismo patrón de chequeo muerto tras `mysqli_query`/`mysqli_prepare` — el resto ya deja propagar la excepción, que es el comportamiento correcto desde 8.1. No se tocó nada más ahí (YAGNI).

**No se tocó el password hardcodeado** (`Guns026772`) — decisión explícita de Edgardo, queda como tarea aparte (paso 1 del plan, pendiente).

### Paso 3 — Verificar TCPDF y PHPMailer contra PHP 8.4/8.5 (sección 4.3 del plan)

**PHPMailer 6.1.7** (`app/public/vendor/phpmailer/`) — se investigó su compatibilidad con PHP 8.4/8.5; no se encontró incompatibilidad documentada para esta versión. **Se mantiene sin cambios.** Uso real: solo `auth-recoverpw.php`.

**TCPDF 6.6.5 → 6.11.3** (`app/public/assets/tcpdf/`) — riesgo real confirmado: la versión vendorizada usa activamente `curl_close()` (2 veces, descarga de imágenes remotas) e `imagedestroy()` (6 veces, generación de barcodes/imágenes), funciones con deprecations reales en PHP 8.5 que TCPDF corrigió recién en 6.11.0. Se actualizó a **6.11.3** (última de la rama 6.x — la 7.x es una reescritura con namespaces/Composer, fuera de alcance ya que el proyecto no gestiona vendors por Composer).

**Detalle del reemplazo:**
- Se comparó estructura completa (`fonts/`, `config/`, `include/`, `tools/`) contra la versión vendorizada — sin fuentes ni configuración custom del proyecto, seguro reemplazar entero.
- Se excluyeron del reemplazo `examples/` (demos no usadas, no estaban antes tampoco — reduce superficie expuesta en el docroot) y `Makefile` (no aplica, no había antes).
- **`tcpdf_import.php` y `tcpdf_parser.php` fueron eliminados por TCPDF en versiones recientes** (funcionalidad de importar PDFs existentes) — se confirmó por grep que el proyecto no usa esa funcionalidad (`setSourceFile`, `importPage`, etc.) en ningún controller.
- Backup de la versión 6.6.5 anterior conservado en el scratchpad de la sesión (no versionado en git) por si hace falta comparar.

## Verificación

- [x] `php -l` sobre todos los `.php` de la carpeta `tcpdf/` nueva, dentro del contenedor — sin errores
- [x] Generación real de PDF de **factura** (`controller/invoice-pdf.php?id_Factura=45&id_Contrato=17`) — HTTP 200, PDF válido (`PDF document, version 1.7, 1 pages`)
- [x] Generación real de PDF de **certificado** (`controller/certificate-pdf.php?id_Certificado=27&id_Contrato=17`) — HTTP 200, PDF válido
- [x] `docker-compose logs php` sin warnings/deprecations nuevos durante ambas generaciones
- [x] `git diff --stat` confirma que no se tocó `servicio-pdf.php` ni ningún otro controller

## Hallazgo fuera de alcance (no corregido)

Al probar la generación de PDF de **servicio** (`controller/servicio-pdf.php?id_Servicio=1565`) apareció un error preexistente, **no relacionado con este cambio**: la query hace `JOIN bathrooms BT ON SR.id_Bath = BT.id_Bath`, pero `servicios` no tiene columna `id_Bath` — la relación real es vía la tabla intermedia `servicios_bathrooms` (`id_Servicio`, `id_Bath`), como confirma `MysqliServiceRepository.php`. Este controller quedó fuera del alcance de la migración DDD de Services (no estaba en su checklist) y sigue siendo 100% legacy con SQL injection activa además de este bug. Requiere su propia migración de entidad — no se tocó acá.

## Pendiente / fuera de alcance

- Paso 1 del plan (rotar password de DB expuesto en `config.php`) — diferido, decisión explícita de Edgardo.
- `controller/servicio-pdf.php` — bug de columna inexistente + SQL injection, ver hallazgo arriba.
- Pasos 4 en adelante del plan (Dockerfile → PHP 8.2, staging, escalones sucesivos) — no iniciados todavía.

---

## Reglas respetadas (de `CLAUDE.md`)

- No se tocaron credenciales sin confirmación explícita.
- No se introdujo TCPDF 7.x (reescritura con Composer) — se respetó el patrón de vendorización manual existente.
- No se corrigió el bug de `servicio-pdf.php` con un parche puntual — sigue la regla "no meter prepared statements puntuales en un controller que no vas a migrar completo".

---

# Checklist — Plan PHP 8.5 / Escalón 8.2 (paso 5)

**Origen:** `.doc/Viejos/plan-migracion-php-8.5.md`, sección 5.1 y sección 6 paso 5
**Estado:** ✅ Dockerfile actualizado y QA manual local completo (2026-07-10) — pendiente que Edgardo decida cuánto tiempo "quemarlo" en staging real antes de pasar a 8.3

## Cambio

`Dockerfile`: `FROM php:8.1-fpm-alpine3.18` → `FROM php:8.2-fpm-alpine3.18` (mismo Alpine, solo sube PHP — aísla la variable que se está probando). Rebuild local con `docker-compose up -d --build php` — build sin errores, contenedor `healthy`, `php -v` confirma `PHP 8.2.19`.

## QA manual (sección 7 del plan)

Se activó temporalmente `display_errors=On` y `error_reporting=E_ALL` en `php/php.ini` (revertido a los valores de producción al terminar) para que cualquier deprecation quedara visible, tal como pide el plan en cada escalón.

- [x] Login (`auth-login.php`) — OK
- [x] Listados con DataTable: Clientes (90), Baños Químicos (100, con filtros Estado/Asignado), Contratos (195), Facturas, Certificados (660), Servicios/Seguimientos (1643, filtros por columna + badge Facturado/No Facturado) — todos cargan y filtran bien
- [x] Alta de Cliente — formulario + validación PristineJS (mensajes "Este campo es obligatorio") funcionando igual que en 8.1
- [x] Generación de PDF de **factura** y **certificado** vía TCPDF 6.11.3 — HTTP 200, PDF válido, sin warnings en logs
- [x] `docker-compose logs php` de toda la sesión de QA — sin ningún `Deprecated:` nuevo atribuible a PHP 8.2

**Hallazgos (ambos preexistentes, no relacionados con el cambio de versión ni con TCPDF, solo visibles ahora porque se prendió `display_errors`):**
1. `auth-login.php:58` — `session_start()` duplicado (ya se llama en la línea 3); genera un `Notice` inofensivo pero prescindible.
2. `dash-services-bath.php:17` — `$_GET['id_Servicio']` sin `isset()` previo; genera `Warning: Undefined array key` si se accede sin el parámetro (encontrado navegando a la URL directamente, sin parámetro, algo que no ocurre desde el flujo normal de la UI).

Ninguno de los dos es bloqueante ni fue introducido por este cambio — se documentan para una futura pasada de limpieza, no se tocaron ahora (fuera de alcance).

## Pendiente

- Replicar el mismo cambio de imagen base en el ambiente de staging/producción cuando corresponda (no se tocó nada fuera del entorno local).
- Ver escalones 8.3/8.4/8.5 completados el mismo día más abajo — decisión explícita de Edgardo de completar todo el proceso en una sola sesión, saltando el "quemado" de 1-2 semanas por escalón que sugiere el plan original.

---

# Checklist — Plan PHP 8.5 / Escalones 8.3, 8.4 y 8.5 (pasos 6-7)

**Origen:** `.doc/Viejos/plan-migracion-php-8.5.md`, secciones 5.2-5.4 y sección 6 pasos 6-7
**Estado:** ✅ completo (2026-07-10) — **decisión explícita de Edgardo: completar todo el proceso en una sola sesión**, sin esperar el "quemado" de 1-2 semanas por escalón que recomienda el plan original. El local queda en PHP 8.5.0; replicar en staging/producción cuando Edgardo lo decida.

## Metodología (igual en los 3 escalones)

Por cada versión: cambiar `FROM` en `Dockerfile` → `docker-compose up -d --build php` → confirmar `php -v` y contenedor `healthy` → activar temporalmente `display_errors=On` + `error_reporting=E_ALL` en `php/php.ini` → QA (curl a páginas clave + generación de PDF de factura/certificado + recorrido en navegador: login, listados grandes, alta de cliente con validación) → revisar `docker-compose logs php` buscando `Deprecated`/`Warning`/`Fatal` nuevos → revertir `php.ini` a valores de producción antes de pasar al siguiente escalón.

**Nota sobre Alpine:** a partir de 8.4 ya no existe build con Alpine 3.18 (EOL para esas versiones) — se usó la Alpine más antigua disponible en cada caso: 8.3→`alpine3.18`, 8.4→`alpine3.19`, 8.5→`alpine3.21`. Es un cambio esperado del lado de la imagen base, no algo a resolver.

## PHP 8.3 (`php:8.3-fpm-alpine3.18`)

- [x] Build y `php -v` → `PHP 8.3.7`, contenedor `healthy`
- [x] QA en navegador (login, clientes, seguimientos con 1643 filas) — sin novedad
- [x] Logs: solo el `Notice` preexistente de `session_start()` duplicado ([[checklist-php85-prerrequisitos]] arriba) — **cero deprecations nuevas de 8.3**, tal como anticipaba el plan (constantes tipadas, `json_validate()` no aplican a código procedural)

## PHP 8.4 (`php:8.4-fpm-alpine3.19`)

Antes de subir, se re-escaneó **todo** el código (`app/public`, `app/src`, TCPDF 6.11.3 nuevo, PHPMailer) buscando el patrón de parámetro nullable implícito (`function foo(string $x = null)`, deprecado en 8.4) — **cero coincidencias** en los tres.

- [x] Build y `php -v` → `PHP 8.4.1`, contenedor `healthy`
- [x] PDF factura/certificado — HTTP 200, válidos
- [x] QA en navegador — sin novedad
- [x] Logs: apareció una deprecation real y nueva, **corregida**:

**Hallazgo corregido — `session.sid_length` / `session.sid_bits_per_character` deprecadas.** El `php/php.ini` del proyecto tenía ambas directivas descomentadas con los valores del template original (`26` y `5`, nunca personalizados). Desde PHP 8.4 el formato del session ID quedó fijo y ambas directivas ya no tienen efecto — generaban `PHP Deprecated: PHP Startup: ... INI setting is deprecated` en cada arranque de PHP-FPM. Se comentaron ambas líneas (no se borraron, se dejaron documentadas) — verificado que la deprecation desaparece de los logs tras el cambio y que las sesiones/login siguen funcionando igual.

## PHP 8.5 (`php:8.5-fpm-alpine3.21`)

- [x] Build y `php -v` → `PHP 8.5.0` — el propio output confirma `with Zend OPcache v8.5.0` (OPcache obligatorio desde esta versión, tal como anticipaba el plan sección 5.4), contenedor `healthy`
- [x] PDF factura/certificado — HTTP 200, válidos (el punto de mayor riesgo detectado en el análisis original — confirma que actualizar TCPDF a 6.11.3 en el paso 3 fue la decisión correcta)
- [x] QA en navegador (login, seguimientos, alta de cliente con validación PristineJS) — sin novedad
- [x] Logs: solo el mismo `Notice` preexistente de `session_start()` duplicado — **cero deprecations nuevas de 8.5**

## Verificación final de cierre

- [x] `php -l` sobre los 190 archivos `.php` del docroot, dentro del contenedor en PHP 8.5 — sin errores de sintaxis en ninguno
- [x] `php/php.ini` revertido a `display_errors=Off` / `error_reporting=E_ALL & ~E_DEPRECATED & ~E_STRICT` (valores de producción) — único cambio persistente: las 2 líneas comentadas de `session.sid_*`
- [x] Contenedor `php-bathroom` `healthy` en PHP 8.5.0

## Resumen de hallazgos de toda la migración (8.1 → 8.5)

| Hallazgo | Tipo | Acción |
|---|---|---|
| `mysqli_connect()` sin manejo de excepción | Código muerto desde 8.1 | Corregido (prerrequisito paso 2) |
| TCPDF 6.6.5 con `curl_close()`/`imagedestroy()` deprecados | Riesgo real confirmado para 8.5 | Actualizado a 6.11.3 (prerrequisito paso 3) |
| `session.sid_length`/`sid_bits_per_character` | Deprecation real y nueva de 8.4 | Corregido (este documento) |
| `session_start()` duplicado en `auth-login.php:58` | Preexistente, inofensivo | Documentado, no corregido (fuera de alcance) |
| `servicio-pdf.php` — columna `id_Bath` inexistente + SQL injection | Preexistente, bug de datos + seguridad | Documentado, no corregido (pertenece a una migración DDD de Services que no incluyó este controller) |

**Ningún deprecation de PHP 8.2, 8.3 o 8.5 apareció en código propio ni en las librerías vendorizadas actualizadas** — confirma la conclusión del análisis original: proyecto 100% procedural, sin uso de features orientadas a objetos afectadas por estos cambios.

## Pendiente

- Replicar el mismo `Dockerfile` (PHP 8.5) y el fix de `php.ini` en staging/producción — no se tocó nada fuera del entorno local en esta sesión.
- Paso 1 del plan original (rotar password de DB) sigue diferido — decisión explícita de Edgardo, independiente de la versión de PHP.
- `servicio-pdf.php` y el `session_start()` duplicado quedan como deuda documentada, no bloqueante.
