# Plan de migración: PHP 8.1 → PHP 8.5

**Proyecto:** php-bathroom
**Estado actual verificado en producción:** PHP 8.1.27 (`php:8.1-fpm-alpine3.18`, contenedor `php-bathroom`)
**Fecha del análisis:** 2026-07-06

---

## 1. Resumen ejecutivo

El código propio del proyecto es **100% procedural** (no se encontró ninguna clase PHP definida, ni una sola asignación de propiedad dinámica `$this->x =`). Esto es una buena noticia para la migración: la gran mayoría de las rupturas y deprecations introducidas entre PHP 8.2 y 8.5 apuntan a características orientadas a objetos (propiedades dinámicas, `readonly`, constantes tipadas, enums, property hooks) que **no aplican a este código**.

El escaneo automático no encontró ninguno de los patrones de riesgo típicos (funciones eliminadas, sintaxis obsoleta, casts no canónicos, operador backtick real, `extract()`, `global`). Sí se detectaron dos puntos que conviene resolver **antes** de subir de versión, detallados en la sección 4.

**Conclusión:** la migración es de **riesgo bajo-medio**, pero no se recomienda saltar directo de 8.1 a 8.5. Se debe pasar por 8.2 → 8.3 → 8.4 → 8.5, validando en cada escalón (ver sección 5).

---

## 2. Inventario del proyecto

| Ítem | Detalle |
|---|---|
| Runtime actual | PHP 8.1.27 (Alpine 3.18, FPM) |
| Gestor de dependencias | No hay `composer.json` a nivel de proyecto — librerías vendorizadas manualmente |
| Librerías de terceros | TCPDF `6.6.5`, PHPMailer (vendorizado en `public/vendor/phpmailer`) |
| Archivos `.php` propios | 190 |
| Uso de base de datos | `mysqli` procedural exclusivamente (51 archivos) — **no hay PDO ni mysqli orientado a objetos** |
| Clases PHP propias | 0 (proyecto puramente procedural) |
| Tests automatizados | No se encontraron (sin PHPUnit ni carpeta `tests/`) |
| Stack Docker | Nginx 1.25.3, MariaDB 11.2, phpMyAdmin 5.2.1 |

---

## 3. Hallazgos del escaneo de código (qué NO es un problema)

Se buscaron explícitamente y **no se encontraron** en el código propio:

- Propiedades dinámicas (`$this->x = ...` sin declarar) → deprecado en 8.2. **N/A**, no hay clases.
- `readonly`, enums con propiedades, constantes de clase → cambios de 8.1–8.3. **N/A**.
- Funciones `mysql_*` (extensión eliminada hace años).
- `create_function()`, `each()`, acceso a strings/arrays con `{}` (removidos en PHP 8.0).
- `extract()`, `global $var`.
- `FILTER_SANITIZE_STRING` (deprecado desde 8.1).
- `utf8_encode()` / `utf8_decode()` (deprecados en 8.2).
- Interpolación `"${var}"` en strings (deprecada en 8.2, la sintaxis `{$var}` sigue siendo válida).
- Casts no canónicos `(boolean)`, `(integer)`, `(double)`, `(binary)` (deprecados en 8.5).
- Operador backtick real (alias de `shell_exec`, deprecado en 8.5) — los dos matches encontrados eran comentarios HTML con comillas invertidas de Markdown, no código PHP.
- `strftime()` / `gmstrftime()` (eliminados en PHP 8.1/9.0).
- Parámetros con tipo explícito y nulo implícito por default (`function foo(string $x = null)`) — deprecado en PHP 8.4. **No se encontró ningún caso** en código propio.

---

## 4. Riesgos reales detectados (a resolver antes de migrar)

### 4.1 — Credenciales de base de datos hardcodeadas en texto plano
**Archivo:** `app/public/layouts/config.php`

El archivo contiene usuario y contraseña de **producción, testing y desarrollo** en texto plano, incluyendo la contraseña real (`Guns026772`) repetida tres veces, y está trackeado por git.

> Esto no es un bloqueante técnico para la migración de versión, pero es un riesgo de seguridad serio e independiente que se detectó durante el análisis y conviene resolver en paralelo (mover a variables de entorno / `.env`, y rotar la contraseña ya que quedó expuesta en el repositorio).

### 4.2 — Manejo de errores de `mysqli_connect()` ya es código muerto desde PHP 8.1
**Archivo:** `app/public/layouts/config.php`, línea 31

```php
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if($link === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}
```

Desde PHP 8.1, el modo de reporte por defecto de `mysqli` es `MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT`, por lo que **una conexión fallida lanza una excepción `mysqli_sql_exception` en vez de devolver `false`**. El bloque `if($link === false)` nunca se ejecuta desde que el proyecto corre en 8.1, y un error real de conexión hoy produce un error fatal no controlado (stack trace crudo) en vez del mensaje `die()` esperado.

**Recomendación:** envolver la conexión (y, idealmente, las 51 llamadas a `mysqli_*` del proyecto) en `try/catch (\mysqli_sql_exception $e)`, o volver explícitamente al modo de reporte clásico con `mysqli_report(MYSQLI_REPORT_OFF)` si se prefiere mantener el estilo de chequeo por valor de retorno. Esto no es nuevo en 8.5, pero es el momento de corregirlo ya que se va a auditar todo el uso de `mysqli` de todos modos.

### 4.3 — Dependencias vendorizadas sin actualizar
TCPDF está en `6.6.5`. No se pudo determinar la versión exacta de PHPMailer vendorizada (no declara `"version"` en su `composer.json`). Ambas deben verificarse contra sus changelogs oficiales para confirmar soporte de PHP 8.5 antes de migrar, ya que al no gestionarse por Composer, las actualizaciones son manuales.

---

## 5. Ruta de actualización recomendada

**No saltar directo de 8.1 a 8.5.** Motivo: los `E_DEPRECATED` de cada versión intermedia son la única señal en tiempo de ejecución de qué se rompería en la siguiente; saltearlos hace que todos los problemas aparezcan de golpe y sin atribución clara a la causa.

```
8.1.27 (actual)
   │
   ├─► 8.2.x   → validar, correr en staging 1-2 semanas
   │
   ├─► 8.3.x   → validar, correr en staging 1-2 semanas
   │
   ├─► 8.4.x   → validar, correr en staging 1-2 semanas
   │
   └─► 8.5.x   → validar, correr en staging, luego producción
```

Para un proyecto sin tests automatizados como este, el tiempo de "quemado" en staging por escalón es lo que compensa la falta de cobertura automática.

### 5.1 — PHP 8.2 (nov. 2022)
Cambios relevantes para este proyecto: **ninguno bloqueante** (dynamic properties y readonly classes no aplican, no hay clases). Verificar igual con `display_errors=On` en staging por si aparece algún deprecation notice no capturado por el escaneo estático.

### 5.2 — PHP 8.3 (nov. 2023)
Cambios relevantes: constantes tipadas, `json_validate()`, mejoras a readonly — **N/A** para código procedural. Sin acción requerida más allá de re-testear.

### 5.3 — PHP 8.4 (nov. 2024)
Cambio relevante clave: **deprecación de tipos nullable implícitos** (`function foo(string $x = null)` → debe ser `?string $x = null`). No se encontró en código propio, pero **sí revisar TCPDF y PHPMailer vendorizados**, ya que librerías viejas sin actualizar suelen tener este patrón. Si aparecen deprecation warnings desde vendor, la solución es actualizar esas librerías a una versión compatible con 8.4+, no parchear el vendor a mano.

Otros cambios de 8.4 (property hooks, asymmetric visibility, nueva API de DOM) son features nuevas, no rupturas — no requieren acción.

### 5.4 — PHP 8.5 (nov. 2025 — versión objetivo)
Deprecations a verificar en staging:
- `null` como offset de array o en `array_key_exists()` — revisar accesos a `$_GET`/`$_POST`/`$_SESSION` sin `isset()` previo, común en código procedural antiguo.
- Métodos mágicos `__sleep()`/`__wakeup()` soft-deprecados — **N/A**, no hay clases.
- El operador backtick como alias de `shell_exec()` — confirmado que no se usa.
- Casts no canónicos — confirmado que no se usan.

Cambio de infraestructura (no de código): **OpCache pasa a ser obligatorio** y viene siempre en el binario — revisar que la imagen Docker final no dependa de que OpCache sea opcional (impacto esperado: nulo, pero validar el `Dockerfile` al actualizar la imagen base).

---

## 6. Plan de acción paso a paso

1. **Rotar la contraseña de base de datos** expuesta en `config.php` y moverla a variables de entorno (independiente de la migración, pero se toca el mismo archivo).
2. Corregir el manejo de excepciones de `mysqli` en `config.php` (sección 4.2).
3. Verificar/actualizar TCPDF y PHPMailer a versiones con soporte confirmado de PHP 8.4/8.5.
4. Crear un entorno de staging idéntico a producción (mismo `docker-compose.yml`, misma base de datos con datos de prueba).
5. Cambiar la imagen base en el `Dockerfile` a `php:8.2-fpm-alpine` → rebuild → correr QA manual completo (ver checklist sección 7) → dejar corriendo en staging.
6. Repetir el paso 5 para `8.3`, luego `8.4`, activando `display_errors=On` y `error_reporting(E_ALL)` en cada escalón para capturar deprecations antes de pasar al siguiente.
7. Repetir para `8.5-fpm-alpine` (verificar el tag exacto disponible al momento de ejecutar el plan).
8. Congelar staging en 8.5 durante al menos una semana con tráfico/uso real simulado.
9. Programar ventana de mantenimiento, hacer backup de base de datos y de `/bath` en el droplet, y desplegar a producción.
10. Monitorear logs de PHP-FPM y Nginx las primeras 48-72 hs post-migración.

## 7. Checklist de QA manual (por cada escalón de versión)

- [ ] Login y gestión de usuarios
- [ ] Alta/edición/borrado en cada módulo CRUD del sistema
- [ ] Generación de PDFs (TCPDF) — probar al menos un reporte de cada tipo
- [ ] Envío de emails (PHPMailer)
- [ ] Filtros y tablas con DataTables
- [ ] Subida de archivos/adjuntos, si aplica
- [ ] Revisar logs de PHP en busca de `Deprecated:` o `Warning:` nuevos
- [ ] Verificar que no aparezcan errores fatales no controlados en conexión a DB

## 8. Plan de rollback

Dado que la imagen base se cambia en el `Dockerfile`, el rollback es: revertir el tag de imagen (`php:8.1-fpm-alpine3.18`), rebuild, y restaurar el backup de base de datos si hubo cambios de esquema incompatibles durante la ventana de prueba. Mantener el backup del droplet (`~/Sites/php_projects/php-bathroom-backup-2026-07-06/`) como referencia del estado pre-migración.

---

## 9. Fuentes consultadas

- [PHP 8.5 Release Announcement](https://www.php.net/releases/8.5/en.php)
- [PHP.Watch — PHP 8.5: What's New and Changed](https://php.watch/versions/8.5)
- [What's new in PHP 8.5 | stitcher.io](https://stitcher.io/blog/new-in-php-85)
- [Laravel News — PHP 8.5 is released](https://laravel-news.com/php-8-5-0)
- [PHP.Watch — Implicitly nullable parameter declarations deprecated (8.4)](https://php.watch/versions/8.4/implicitly-marking-parameter-type-nullable-deprecated)
- [PHP.Watch — Dynamic Properties are deprecated (8.2)](https://php.watch/versions/8.2/dynamic-properties-deprecated)
- [PHP.Watch — readonly Classes (8.2)](https://php.watch/versions/8.2/readonly-classes)
- [PHP Manual — New Features (8.3)](https://www.php.net/manual/en/migration83.new-features.php)
- [Deprecated Features cheatsheet (todas las versiones)](https://eusonlito.github.io/php-changes-cheatsheet/deprecated.html)
- [Backward Incompatible Changes cheatsheet (todas las versiones)](https://eusonlito.github.io/php-changes-cheatsheet/incompatible.html)
