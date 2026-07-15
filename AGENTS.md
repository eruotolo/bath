# AGENTS.md — php-bathroom

Reglas operativas específicas para este proyecto. Complementa (no reemplaza) `~/.config/opencode/AGENTS.md`.

## Idioma

Conversación en **español**. Código, variables, funciones, mensajes de commit en **inglés**.

## Estado actual del stack

| Capa | Versión | Notas |
|---|---|---|
| PHP | 8.5.0 (Alpine 3.21, FPM) | Migrado desde 8.1.27 el 2026-07-10 (8.1→8.2→8.3→8.4→8.5, ver `.doc/Viejos/checklist-php85-prerrequisitos.md`); solo local, falta replicar en staging/producción. Solo PHP-FPM; `pidof` para healthcheck |
| MariaDB | 11.2 | Volumen `./mysql/mysql-data:/var/lib/mysql` (gitignored) |
| Nginx | 1.25.3-alpine | Puerto 80 |
| phpMyAdmin | 5.2.1 | Mismo host/puerto |
| Composer | 2 | Multi-stage `COPY --from=composer:2` en `Dockerfile` |
| **TailwindCSS** | **4.3.2** (pnpm) | **Sistema de estilos activo desde Fase 9 del plan-migracion. Output commiteado en `app/public/assets/css/tailwind.css` (~106K minificado). Ver `§TailwindCSS build pipeline` más abajo. Preflight activado; `important` flag activo en `tailwind.css` por seguridad mientras coexistan `.modal`/`.dropdown-menu` Bootstrap. |
| TCPDF | 6.11.3 (vendorizado en `app/public/assets/tcpdf/`) | Actualizado desde 6.6.5 el 2026-07-10 junto con la migración PHP |
| PHPMailer | 6.1.7 (vendorizado en `app/public/vendor/`) | Sin incompatibilidad documentada con PHP 8.5, se mantiene |

**Nombre del sitio:** `SITE_NAME=bathroom` en `.env` (NO `bath` — producción usa `/bath`, container `php-bathroom`).

Sin tests automatizados, sin CI/CD, sin framework. App en `http://localhost`; phpMyAdmin en el mismo host/puerto.

## Estructura clave

```
app/
├── public/                 ← docroot Nginx (vistas + controllers + layouts)
│   ├── dash-{entidad}*.php ← 32 vistas (todas migradas a Tailwind, plan cerrado)
│   ├── auth-{login,register,recoverpw,lock-screen}.php ← 4 vistas auth (migradas)
│   ├── controller/         ← 51 endpoints de acción
│   ├── layouts/            ← config.php ($link), session.php, helpers.php,
│   │                          sidebar.php / header.php (Tailwind Fase 2),
│   │                          6 modales compartidos (Tailwind Fase 3),
│   │                          head-style.php / vendor-scripts.php
│   ├── assets/css/         ← solo tailwind.css compilado + style.css (legacy)
│   │   └── tw/             ← entry + theme (tokens) + layout (keyframes) + components (dt-*)
│   ├── assets/js/components/← datatable.js, modal-editor.js, sidebar.js + bs-shim.js
│   │                          (reemplaza bootstrap.bundle.min.js — modal/dropdown/tab)
│   ├── assets/libs/        ← jquery, datatables, choices, flatpickr, sweetalert2,
│   │                          dropzone, glightbox, lucide, pristinejs, pdfmake, jszip,
│   │                          bootstrap (legacy, lo usan vistas en archive/), etc.
│   ├── archive/            ← 77 templates legacy — NO borrar sin confirmar (pueden estar referenciados)
│   └── uploads/            ← archivos subidos
└── src/                    ← nuevo, código DDD (con namespaces)
    ├── Domain/{Entity}/    ← {Entity}.php + {Entity}RepositoryInterface.php
    ├── Application/{Entity}/ ← Use Cases
    └── Infrastructure/Persistence/ ← Mysqli{Entity}Repository.php
```

**autoload PSR-4:** `"App\\": "src/"` — el path es relativo a donde queda `composer.json` **dentro del contenedor** (`/var/www/src`, por mount `./app/src:/var/www/src` en `docker-compose.yml`). Si ves `App\` con clases faltantes, casi siempre es autoload no regenerado.

## Carpeta `rebranding/`

La carpeta `rebranding/` contiene un prototipo funcional del nuevo diseño de la aplicación, desarrollado con tecnología moderna (React, Vite, Tailwind CSS).

**Objetivo:** Este diseño es la referencia visual y funcional para la modernización de la interfaz que se encuentra en `app/public/`. El plan es migrar las vistas de PHP a este nuevo sistema visual de forma incremental. **No es código legacy, sino el futuro de la interfaz.**

## Skills

### `frontend-design`

Skill instalada a nivel proyecto en `.agents/skills/frontend-design/` (vía `npx skills add`), registrada en `opencode.json` → `skills.paths` para que opencode la cargue para **todos los modelos/agents**. Gestionada por el CLI `skills` (actualizar con `npx skills update`).

**Regla — uso obligatorio al crear features de UI:**

Al crear una feature nueva de UI/frontend — nueva vista `dash-*.php` / `auth-*.php`, nuevo componente en `rebranding/`, migrar una vista a Tailwind, o rediseñar una pantalla existente — **cargar la skill `frontend-design` con la tool `skill` ANTES de escribir markup/CSS** y seguir su guía (dirección estética, tipografía, jerarquía visual, layout, toma de un riesgo estético justificado). No es opcional para UI nueva.

**No requiere la skill** (excepciones):
- Fix puntual de bug, typo o ajuste menor de spacing/margen en vista existente sin tocar el diseño.
- Cambios puramente de backend / controller / SQL / DB.

**Convivencia con los design tokens del proyecto:** la skill empuja a decisiones visuales distintivas y opinadas; en este proyecto **conviven con los tokens ya fijados** en `app/public/assets/css/tw/theme.css` y los componentes `dt-*` (`tw/components.css`). Usar la skill para dirección estética de pantallas nuevas; **NO** para reemplazar tokens existentes. Si la skill sugiere algo que choca con un token, documentar la decisión antes de modificar `tw/theme.css`. Después de cualquier cambio en `tw/`: `pnpm tw:build`.

## Migración DDD — completa

El proyecto migró de **100% procedural** a **DDD liviano** (Entity / Repository / Use Case). Las 7 entidades del sistema están migradas:

| Entidad | Estado DDD |
|---|---|
| **Certificate** | ✅ Migrada (piloto) |
| **Bathroom** | ✅ Migrada |
| **User** | ✅ Migrada |
| **Customer** (+ Contact) | ✅ Migrada |
| **Contract** | ✅ Migrada |
| **Service** | ✅ Migrada |
| **Invoice** | ✅ Migrada |

**Patrón usado** (referencia si aparece código legacy no migrado, o para nuevas features): estructura de **Bathroom** — `app/src/Domain/Bathroom/`, `app/src/Application/Bathroom/`, `app/src/Infrastructure/Persistence/MysqliBathroomRepository.php`, y un controller migrado como `app/public/controller/bath-new.php`. Detalles en `.doc/Viejos/plan-migracion-ddd-drycode.md` sección 4 y checklists por entidad en `.doc/Viejos/checklist-ddd-*.md`.

**Regla clave:** un controller existente NO migrado se queda **procedural** (con `mysqli_query` como está). Solo se introduce código namespaced/classes para entidades que toca migrar — no se reescribe todo de una.

## Convenciones PHP

- `snake_case` para variables y funciones (NO `camelCase`)
- SQL en MAYÚSCULAS (`SELECT`, `FROM`, `WHERE`, `INSERT INTO`, `VALUE`)
- Columnas: `{nombre}_{Tabla}` (`rut_Cliente`, `estado_Bath`, `fechaCompra_Bath`)
- Tablas: plural (`clientes`, `bathrooms`, `contratos`)
- Controllers: `controller/{entidad}-{acción}.php`
- Vistas: `dash-{entidad}[-{subsección}].php`
- Conexión: `$link` global desde `layouts/config.php`; cada controller hace `include ('../layouts/config.php')`. Las clases migradas reciben el `mysqli` por constructor.
- Sin namespaces en código legacy; `namespace App\...` en `app/src/`

## SQL — reglas críticas

La migración DDD de las 7 entidades eliminó la SQL injection sistemática del código en su alcance. **Excepción conocida:** `controller/servicio-pdf.php` quedó fuera del alcance de la migración de Services (no estaba en su checklist) y sigue con SQL injection activa + un bug de columna inexistente (`SR.id_Bath`, la relación real es vía `servicios_bathrooms`) — ver `.doc/Viejos/checklist-php85-prerrequisitos.md`. Requiere su propia migración de entidad.

- **Código nuevo:** prepared statements siempre — `$link->prepare()` + `$stmt->bind_param('s...', ...)`. Nunca interpolación.
- **Controller legacy que ya tenés que tocar:** NO metas prepared statements puntuales — **migrá la entidad completa al patrón Repository** de Bathroom. Lo otro es peor que el estado actual.
- **Vistas:** escapar TODO output con `htmlspecialchars($var, ENT_QUOTES, 'UTF-8')`. Las existentes sin escape son deuda pendiente.

## Seguridad — no negociable

- **⚠️ `.env` NO está en `.gitignore`.** `git check-ignore -v .env` confirma. **Verificar antes de `git add`** — un `git add .` filtraría `MYSQL_ROOT_PASSWORD` y `USER_DB_PASSWORD` reales. (El `AGENTS.md` previo decía lo contrario; es un error.)
- **⚠️ `app/public/layouts/config.php` tiene passwords hardcodeados en producción** (`DB_PASSWORD='Guns026772'`, server cPanel `cch94190_sistema`). Movido a variables de entorno es tarea del plan PHP 8.5 — antes de cualquier deploy público, rotar.
- El chequeo `if($link === false)` de `config.php` era código muerto desde PHP 8.1 (mysqli lanza excepción por defecto) — ya corregido, ahora envuelto en `try/catch(\mysqli_sql_exception)`.
- **¿Vas a tocar `config.php` o algo con credenciales?** Avisar primero — nunca modificar credenciales reales sin confirmación.
- Validar TODO `$_POST` / `$_GET` / `$_SESSION` en boundaries.
- Verificar sesión (`layouts/session.php`) en cualquier controller que no sea login/recoverpw/lock.

## Gotcha: editar `composer.json` en contenedor corriendo

Ya pasó una vez: el bind mount `./composer.json:/var/www/composer.json` (archivo único, no carpeta) se rompe cuando el host reescribe el archivo atómicamente (write to tempfile + rename). El contenedor queda con un inodo que ya no existe y el archivo "desaparece" adentro.

**Después de cualquier edición de `composer.json`:** `docker-compose up -d --force-recreate php` para reestablecer el mount. Si no, las clases `App\` no cargan.

Después de agregar/modificar código en `app/src/`: `docker-compose exec php composer dump-autoload`.

## Docker

- Credenciales vienen de `.env` (verificar el gotcha de `.gitignore` arriba — **no confiar** en que está excluido).
- Volumen `./mysql/mysql-data:/var/lib/mysql` persiste datos — **NO `docker-compose down -v` en producción sin backup**.
- Volúmenes del servicio `php` (todos requeridos, no quitar):
  - `./app/public:/var/www/html/`
  - `./php/php.ini:/usr/local/etc/php/php.ini:ro`
  - `./composer.json:/var/www/composer.json`
  - `./app/src:/var/www/src`
  - `./vendor:/var/www/vendor`
- Migración PHP 8.1 → 8.5 completa en local (2026-07-10) — detalle de cada escalón en `.doc/Viejos/checklist-php85-prerrequisitos.md`. **Falta replicar en staging/producción.** Si se cambia la versión de PHP en `Dockerfile` de nuevo (downgrade, o versión futura), seguir el mismo patrón: un escalón a la vez, con `display_errors` temporal + QA por versión.

## Workflow

0. **¿Vas a crear o rediseñar UI?** Cargar la skill `frontend-design` con la tool `skill` ANTES de empezar (ver `§Skills`). Obligatorio para UI nueva; no para fixes puntuales ni backend.
1. **¿Vas a tocar un controller?** Buscar primero si la entidad ya está migrada (`grep -r "use App\\\\" app/public/controller/{entidad}*.php`). Si sí, extender el patrón DDD. Si no, **migrar la entidad completa** o dejar el controller procedural — no mezclar.
2. **¿Cambio de versión PHP?** Leer `.doc/Viejos/plan-migracion-php-8.5.md` sección 5 antes.
3. **Después de cualquier cambio de código PHP:** `docker-compose up -d --force-recreate php` + smoke test manual en `http://localhost`.
4. **Después de cambios en `composer.json`:** el gotcha de arriba.
5. **Después de cambios en `app/src/`:** `docker-compose exec php composer dump-autoload`.
6. **¿Dependencia nueva?** Vendorizar en `app/public/vendor/<lib>/`, salvo que sea parte del refactor DDD (donde entra por Composer).
7. Sin tests → QA manual. No commitear sin pedido explícito.

## Comandos

```bash
# Stack
docker-compose up -d --build
docker-compose restart php                # tras cambios en código (suficiente si no se tocó composer.json ni src)
docker-compose up -d --force-recreate php # tras editar composer.json
docker-compose down                       # preserva datos
docker-compose down -v                    # ⚠️ borra volumen — solo dev
docker-compose logs -f php
docker-compose logs -f nginx

# DB
docker-compose exec mysql mariadb -u$USER_DB_USER -p$USER_DB_PASSWORD $USER_DB_NAME
docker-compose exec mysql mariadb-dump -u root -p$MYSQL_ROOT_PASSWORD $USER_DB_NAME > backup-$(date +%F).sql

# PHP / Composer
docker-compose exec php php -v
docker-compose exec php php -l /var/www/html/path/file.php
docker-compose exec php composer dump-autoload

# TailwindCSS (Fase 0-9 del plan-migracion-bootstrap-tailwind cerrado)
pnpm install                                # instala tailwindcss + @tailwindcss/cli (Node 22)
pnpm tw:build                               # build minificado → app/public/assets/css/tailwind.css
pnpm tw:watch                                # watch para desarrollo local
# Edicion de CSS de Tailwind:
#   - tokens/colors/font/radius -> app/public/assets/css/tw/theme.css
#   - componentes reusables (.dt-card, .dt-input, .table-card, etc.) -> app/public/assets/css/tw/components.css
#   - keyframes + estados JS + .main-content margen del sidebar -> tw/layout.css
#   - @source/@layer/@import -> tw/main.css
# Despues de cualquier cambio en tw/: pnpm tw:build (el watch queda corriendo en otra terminal).

# Backup completo (producción)
cp -r /bath /bath-backup-$(date +%F)

# Auditoría de archivos ignorados (run antes de commits grandes)
git check-ignore -v path/to/file
```

## TailwindCSS build pipeline

Tailwind v4.3.2 se compila con `@tailwindcss/cli` desde `app/public/assets/css/tw/main.css`. El output commiteado (`assets/css/tailwind.css`, ~106K minificado) es lo que se sirve al navegador.

- **Preflight activado** (Fase 9). Resetea defaults globales (`h1-h6`, `button`, `ul/ol`, etc.). Convive con las clases estructurales Bootstrap que sobreviven (`.modal`, `.dropdown-menu`, `.nav-tabs`) — las activa `bs-shim.js` (ver §Bootstrap JS shim).
- **`important` flag activo** en `tw/main.css`. Las utilities de Tailwind se emiten con `!important`. Esto garantiza que pisen a Bootstrap, SweetAlert2, Choices y Flatpickr (todos CSS sin `@layer`) a igualdad de specificity. **Re-evaluar en una fase futura** después de migrar los re-skins definitivos de libs.
- **Tree-shaking**: Tailwind 4 NO tree-shakea el theme default. Para forzar tree-shaking usar `@theme { --color-*: initial; ... }` con tu propia escala. Tradeoff: pierdes `bg-red-500`, `text-slate-900`, etc. built-in. **No aplicado todavía** — si bajamos de 80K, considerar.
- **Cobertura de `@source`**: `app/public/**/*.{php,js}` excluyendo `archive/`, `template/`, `vendor/`, `assets/tcpdf/`, `assets/libs/`. Si agregás vistas en `archive/` y querés que Tailwind las escanee, ajustá `tw/main.css`.
- **¿Editar `tw/` o cambiar `main.css`?** No hay necesidad de reiniciar PHP ni de tocar Docker. `pnpm tw:watch` o `pnpm tw:build` regenera el CSS.

## Bootstrap JS shim

`assets/js/components/bs-shim.js` reemplaza `bootstrap.bundle.min.js` (removido de `vendor-scripts.php` en Fase 8). Mantiene compatibilidad con `data-bs-toggle="modal"`, `data-bs-toggle="dropdown"`, `data-bs-toggle="tab"`, `data-bs-dismiss="modal"`, etc. NO requiere cambios en el markup.

**Lo que reemplaza (3 behaviours):**
- `data-bs-toggle="modal"` + `data-bs-target="#id"` — abre/cierra el modal. **Soporte de `<dialog>` queda para una fase futura**; por ahora usa la clase Bootstrap `.show` + `display: block/none`.
- `data-bs-toggle="dropdown"` — toggle de `.show` en `.dropdown`. **Sin posicionamiento Popper**: el `.dropdown-menu` fluye natural en flexbox (suficiente para los 9 dropdowns del proyecto). Si en el futuro aparece un dropdown que necesita posicionamiento absoluto con flip, evaluar Popper.js por separado.
- `data-bs-toggle="tab"` + `data-bs-target="#id"` — cambia paneles activos. Sin animación de fade (vanilla).

## Selects con buscador (Choices.js) en drawers

Todo `<select>` que necesite buscador (asignar cliente, baño, etc. dentro de un drawer lateral tipo `#contract-drawer`) usa el atributo `data-enhanced-select` (+ opcionalmente `data-search-placeholder="..."`). Esto inicializa Choices.js sobre el `<select>` nativo.

**Gotcha crítico:** Choices.js oculta el `<select>` original y construye su propio DOM (`.choices`, `.choices__inner`, `.choices__list--dropdown`, etc.) con su tema por defecto (Open Sans, gris, radios de 2.5px). Las clases Tailwind puestas en el `<select>` (`rounded-xl`, `border-slate-200`, `focus:border-emerald-500`...) **no tienen ningún efecto** sobre ese DOM generado — son cosméticas solo para el elemento oculto.

El re-skin real vive en `app/public/assets/css/tw/components.css` (~línea 716), como reglas `!important` **scoped por ID de drawer**: `#bath-drawer`, `#contract-drawer`. Si creás un drawer nuevo con un `data-enhanced-select` adentro y su `id` no está en esa lista de selectores, el select se ve con el tema crudo de Choices.js — mismas clases Tailwind, apariencia completamente distinta (fuente, padding, colores de foco/hover, bordes del dropdown).

**Regla:** al agregar un `data-enhanced-select` dentro de un drawer nuevo, agregar el `id` de ese drawer a los 8 selectores scoped en `components.css` (el bloque completo del re-skin) y correr `pnpm tw:build`. Verificar abriendo el dropdown en el navegador — no alcanza con mirar el select cerrado, el tema roto se nota recién al desplegar la lista.

## NO hacer

- **NO** `git add .` a ciegas — `.env` NO está gitignored (ver sección Seguridad).
- **NO** commitear ni pushear sin pedido explícito.
- **NO** `git push --force`, `NO --no-verify`.
- **NO** commitear `.env`, `mysql/mysql-data/`, `vendor/`, `.idea/`, `/06-07-2026`.
- **NO** meter clases/namespaces en archivos legacy de `app/public/`. Solo en `app/src/` y solo siguiendo el patrón Bathroom.
- **NO** introducir framework (Laravel/Symfony) — el plan DDD lo descarta.
- **NO** saltar directo a PHP 8.5 — pasar 8.2 → 8.3 → 8.4 → 8.5.
- **NO** modificar `mysql/database/donbano.sql` con datos de producción (es solo dump inicial).
- **NO** borrar `app/public/archive/` sin confirmar — puede tener templates referenciados.
- **NO** `dd()`, `var_dump()`, `print_r()` en código que va a producción.
- **NO** `docker-compose down -v` en producción sin backup confirmado.
- **NO** meter prepared statements puntuales en un controller que no vas a migrar completo — empeora el estado.

## Documentación oficial

- `README.md` — overview, stack, deploy
- `AGENTS.md` (este archivo) — reglas operativas del proyecto
- `CLAUDE.md` — symlink a este archivo
- `.doc/Viejos/` — histórico de planes/checklists/informes (DDD, frontend, PHP 8.5, cotización, fases 1-3, **plan-migracion-bootstrap-tailwind cerrado en Fase 10**). Incluye `.doc/Viejos/informe-produccion-banos-duplicados.md` / `.pdf` — **pendiente real**: ajuste de 41 baños duplicados sin replicar en producción, no confundir con "cerrado" solo porque está en `Viejos/`.
