# Plan: Eliminar archivos .min.css y consolidar CSS sin minificar

**Fecha:** 2026-07-13
**Proyecto:** php-bathroom
**Autor del plan:** Edgardo Ruotolo (vía opencode)
**Objetivo:** Trabajar siempre con CSS legible, eliminar la duplicación `.css` / `.min.css`, terminar con la dependencia de regenerar minificados a mano.

---

## Contexto y problema

El proyecto carga versiones `.min.css` de bootstrap, icons y app (`layouts/head-style.php:5,7,9`). El bundle `app.min.css` tiene **500 selectores únicos** mientras que `app.css` solo tiene **238** — el `.min` no es solo `app.css` minificado, es un bundle combinado con CSS extra (probablemente `bootstrap-rtl.css`, librería Waves, etc.) que `app.css` no incluye.

Resultado: cada cambio en CSS requiere regenerar `.min` a mano, y `clean-css-cli` rompió el bundle la última vez (inirtió `margin-left` ↔ `margin-right`, paddings, etc.).

**Decisión tomada:** apuntar siempre a los `.css` sin minificar. Sacrificar ~300KB de payload (aceptable — tráfico interno, sin CDN crítico). Ganar editabilidad en vivo y eliminar la deuda de sincronización.

---

## Pre-requisitos

- Acceso al repositorio en `/Users/edgardoruotolo/Sites/php_projects/php-bathroom`
- PHP/MySQL Docker levantado (`docker-compose ps` debe mostrar `php`, `nginx`, `mysql`, `phpmyadmin` corriendo)
- Navegador con DevTools abierto para validar

---

## Pasos

### Paso 1 — Backup de seguridad

```bash
cd /Users/edgardoruotolo/Sites/php_projects/php-bathroom
mkdir -p .doc/backups/css-2026-07-13
cp app/public/layouts/head-style.php .doc/backups/css-2026-07-13/
cp app/public/assets/css/app.css .doc/backups/css-2026-07-13/
cp app/public/assets/css/bootstrap.css .doc/backups/css-2026-07-13/
cp app/public/assets/css/icons.css .doc/backups/css-2026-07-13/
cp app/public/assets/css/app.min.css .doc/backups/css-2026-07-13/
cp app/public/assets/css/bootstrap.min.css .doc/backups/css-2026-07-13/
cp app/public/assets/css/icons.min.css .doc/backups/css-2026-07-13/
cp app/public/assets/css/table-overrides.css .doc/backups/css-2026-07-13/
echo "Backup OK"
```

### Paso 2 — Identificar qué le falta a `app.css` vs `app.min.css`

Comparar selectores únicos entre los dos archivos para saber exactamente qué reglas hay que mergear.

```bash
cd /Users/edgardoruotolo/Sites/php_projects/php-bathroom
grep -oE '^\.[a-z][a-z0-9_-]+' app/public/assets/css/app.css | sort -u > /tmp/css_selectors.txt
grep -oE '\.[a-z][a-z0-9_-]+' app/public/assets/css/app.min.css | sort -u > /tmp/min_selectors.txt
comm -23 /tmp/min_selectors.txt /tmp/css_selectors.txt > /tmp/selectores_solo_en_min.txt
wc -l /tmp/selectores_solo_en_min.txt
```

**Resultado esperado:** ~262 selectores que están SOLO en `.min`. Guardar la lista:

```bash
cp /tmp/selectores_solo_en_min.txt .doc/backups/css-2026-07-13/selectores_solo_en_min.txt
```

**Acción manual:** leer el archivo y agrupar selectores por origen probable (RTL, Waves, custom). Esto le dice al siguiente paso qué mergear.

### Paso 3 — Localizar las fuentes del bundle `.min`

El `app.min.css` es probablemente una concatenación de:
- `app.css` (la fuente oficial de estilos del template)
- `bootstrap-rtl.css` (soporte RTL que alguien metió hace años)
- Waves (`fian.my.id/Waves` — librería de efectos)
- Otras cosas custom

Verificar qué archivos fuente existen en `assets/css/`:

```bash
cd /Users/edgardoruotolo/Sites/php_projects/php-bathroom
ls app/public/assets/css/*.css
```

Si existen `bootstrap-rtl.css`, `app-rtl.css`, etc., esos son candidatos a ser mergeados a `app.css` (o mantenerse como archivos separados que el layout cargue después).

### Paso 4 — Mergear CSS faltante a `app.css`

**Decisión según lo encontrado en pasos 2-3:**

- **Si los selectores faltantes vienen de archivos fuente existentes** (ej: `bootstrap-rtl.css`): dejar esos archivos como están y hacer que el layout los cargue después de `app.css`. NO duplicar reglas.
- **Si vienen de código embebido en el `.min`** sin archivo fuente: extraer las reglas del `.min` y agregarlas al final de `app.css` (con comentario indicando el origen).

Para extraer reglas del `.min` (método crudo):

```bash
cd /Users/edgardoruotolo/Sites/php_projects/php-bathroom
# Re-formatear el .min con cleancss SIN opciones destructivas
npx --yes clean-css-cli --format beautify app/public/assets/css/app.min.css > /tmp/app_min_beautified.css 2>/dev/null
diff app/public/assets/css/app.css /tmp/app_min_beautified.css > /tmp/css_diff.txt
wc -l /tmp/css_diff.txt
cp /tmp/app_min_beautified.css .doc/backups/css-2026-07-13/app_min_beautified.css
cp /tmp/css_diff.txt .doc/backups/css-2026-07-13/
```

**Acción manual:** revisar `css_diff.txt` y mergear selectivamente al final de `app.css` lo que sea legítimo (RTL, fixes de sprint, etc.) y descartar lo que sea duplicado.

⚠️ **NO usar** `clean-css-cli -o` para regenerar el `.min` — ya probamos que invierte márgenes/paddings y rompe el layout.

### Paso 5 — Cambiar `head-style.php` para apuntar a `.css`

Editar `app/public/layouts/head-style.php`:

- Línea 5: `bootstrap.min.css` → `bootstrap.css`
- Línea 7: `icons.min.css` → `icons.css`
- Línea 9: `app.min.css` → `app.css`
- Línea 15: eliminar el `<link>` a `table-overrides.css` (ya no aplica, la regla ahora vive en `app.css`)
- Línea 2: `preloader.min.css` → `preloader.css` (si existe `preloader.css`, si no, dejar el `.min`)

```bash
cd /Users/edgardoruotolo/Sites/php_projects/php-bathroom
# Verificar qué archivos fuente existen
for f in bootstrap icons app preloader; do
  echo -n "$f: "
  ls app/public/assets/css/$f.css 2>/dev/null && echo "OK" || echo "FALTA"
done
```

Solo cambiar los que existen como `.css`.

### Paso 6 — Verificar visualmente

1. Refrescar navegador con hard refresh (`Cmd + Shift + R`)
2. Ir a `http://localhost/dash-customers.php`
3. Comparar con captura mental del estado anterior:
   - Sidebar a la izquierda con su ancho correcto
   - Topbar fija arriba
   - Tabla con datos renderizados
   - **Texto de tabla en 12px** (este era el objetivo original)
4. Verificar 2-3 vistas más: `dash-bathrooms.php`, `dash-contracts.php`, login
5. DevTools → Network → CSS: confirmar que se cargan los `.css` (no `.min.css`)

**Si algo se ve mal:** restaurar el backup de `head-style.php`:
```bash
cp .doc/backups/css-2026-07-13/head-style.php app/public/layouts/head-style.php
```

### Paso 7 — Eliminar archivos `.min.css` (solo si paso 6 fue OK)

```bash
cd /Users/edgardoruotolo/Sites/php_projects/php-bathroom
rm app/public/assets/css/app.min.css
rm app/public/assets/css/bootstrap.min.css
rm app/public/assets/css/icons.min.css
rm app/public/assets/css/preloader.min.css 2>/dev/null  # solo si existe .css
# NO borrar:
# - app-rtl.min.css / bootstrap-rtl.min.css (si existen — son bundles de RTL, evaluar aparte)
# - las versiones minified en assets/libs/ (esas son de vendor)
```

**Validar que ningún archivo del proyecto referencia los `.min` borrados:**

```bash
cd /Users/edgardoruotolo/Sites/php_projects/php-bathroom
grep -rE 'app\.min\.css|bootstrap\.min\.css|icons\.min\.css' app/public/ --include='*.php' --include='*.html'
```

Si aparece alguna referencia (que no sea el propio `head-style.php`), corregir manualmente.

### Paso 8 — Eliminar `table-overrides.css`

La regla de `.table { font-size: 12px }` ya debe estar en `app.css` (mergeada en paso 4) o aplicarse vía otra estrategia. Eliminar el archivo y su `<link>`.

```bash
cd /Users/edgardoruotolo/Sites/php_projects/php-bathroom
rm app/public/assets/css/table-overrides.css
```

(El `<link>` ya se eliminó en paso 5.)

### Paso 9 — Refrescar Docker (PHP)

El cambio en `head-style.php` y `app.css` es en disco. PHP-FPM cachea opcodes pero para cambios en `.php` de includes no hace falta reiniciar, pero por las dudas:

```bash
cd /Users/edgardoruotolo/Sites/php_projects/php-bathroom
docker-compose restart php
```

### Paso 10 — Validación final

1. Hard refresh navegador
2. Recorrer 5 vistas distintas (clientes, baños, contratos, certificados, login)
3. Confirmar:
   - Diseño idéntico al estado pre-cambio
   - Tablas en 12px
   - DevTools → Network: solo `.css` (sin `.min.css`)
   - Sin errores 404 en consola

---

## Rollback completo (si algo sale mal)

```bash
cd /Users/edgardoruotolo/Sites/php_projects/php-bathroom
cp .doc/backups/css-2026-07-13/head-style.php app/public/layouts/head-style.php
# Si ya borraste los .min, restaurarlos desde git
git checkout HEAD -- app/public/assets/css/app.min.css app/public/assets/css/bootstrap.min.css app/public/assets/css/icons.min.css
# Restaurar app.css si lo modificaste
cp .doc/backups/css-2026-07-13/app.css app/public/assets/css/app.css
docker-compose restart php
```

---

## Lo que NO se hace

- NO se regenera `app.min.css` con `clean-css-cli` (ya probamos que rompe el layout).
- NO se commitea nada automáticamente — esperar confirmación del usuario.
- NO se toca `mysql/`, `vendor/`, ni credenciales.
- NO se borra `app/public/archive/` ni archivos de vendor en `assets/libs/`.

---

## Output esperado al terminar

- `app/public/layouts/head-style.php` apunta a versiones `.css` sin minificar
- `app/public/assets/css/app.css` contiene la regla `.table { font-size: 12px; }` y todo el CSS necesario para que el layout se vea correcto
- No quedan archivos `.min.css` de los principales (`app`, `bootstrap`, `icons`) en `assets/css/`
- `table-overrides.css` eliminado
- Diseño visual idéntico al estado anterior + tablas en 12px
- Backups en `.doc/backups/css-2026-07-13/`