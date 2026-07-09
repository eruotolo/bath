# CLAUDE.md — php-bathroom

Guía operativa para Claude Code / opencode en este proyecto. Complementa `AGENTS.md` (reglas) y `README.md` (overview).

## Quick reference

```bash
docker-compose up -d --build   # levantar stack
docker-compose restart php     # reiniciar solo PHP tras cambios en código
docker-compose logs -f php     # ver logs
docker-compose exec mysql mariadb -u$USER_DB_USER -p$USER_DB_PASSWORD $USER_DB_NAME
```

App: `http://localhost`. phpMyAdmin en el mismo puerto.

## Estado actual del proyecto

| Aspecto | Estado |
|---|---|
| PHP | 8.1.27 procedural puro, Alpine 3.18 FPM |
| Base de datos | MariaDB 11.2 vía `mysqli` (sin PDO) |
| Compositor | Sin `composer.json`; TCPDF/PHPMailer vendorizados |
| Tests | Sin tests automatizados |
| CI/CD | Sin CI/CD; deploy manual por `git pull` |
| Framework | Sin framework; PHP plano + jQuery |
| Entidades | 7 dominios (customers, bathrooms, contracts, services, invoices, certificates, users) |
| Tablas DB | 14 (incluyendo junctions y catálogos) |
| Controllers | 45 archivos en `app/public/controller/` |
| Vistas | 27 archivos `dash-*.php` |

## Planificaciones vigentes en `.doc/`

Hay dos planes estratégicos que **no se deployan** — son documentos de trabajo:

| Plan | Propósito | Estado |
|---|---|---|
| `.doc/plan-mejoras-frontend.md` | Componentes UI reutilizables (DataTable potenciado, selects con buscador, datepickers, RUT mask, feedback con SweetAlert2) — usa librerías ya instaladas en `assets/libs/` | Pendiente |
| `.doc/plan-migracion-php-8.5.md` | Migración incremental 8.1 → 8.5 con QA por escalón | Pendiente |
| `.doc/plan-migracion-ddd-drycode.md` | Refactor a DDD liviano (Entity / Repository / Use Case) | Pendiente |

**Orden de ejecución recomendado: Mejoras frontend → DDD → PHP 8.5.** Motivo: las mejoras frontend son visibles, de bajo riesgo y de impacto inmediato; el plan DDD introduce Composer y reorganiza el bootstrap de la conexión a DB; el plan PHP 8.5 viene al final, una vez validada la arquitectura.

## Deuda técnica conocida (priorizada)

### Crítico
1. **Credenciales en `app/public/layouts/config.php`** — usuario y pass de producción hardcodeados y trackeados en git. **Rotar antes de cualquier deploy público.**
2. **SQL Injection sistemática en 45 controllers** — `mysqli_query` con concatenación directa de `$_POST`. Resolver con prepared statements + patrón Repository del plan DDD.

### Alto
3. **XSS en vistas** — `echo $row[...]` sin `htmlspecialchars()`. Se puede resolver en paralelo al plan DDD, entidad por entidad.
4. **`mysqli_connect` con manejo de error muerto** — desde PHP 8.1, `mysqli` lanza excepción por defecto; el `if($link === false)` ya no se ejecuta (ver `config.php:31`).

### Medio
5. **Versión de PHP** — 8.1.27 es la última minor de una línea ya fuera de soporte activo. Migración a 8.5 planificada.
6. **TCPDF 6.6.5 y PHPMailer sin Composer** — verificar compatibilidad 8.4/8.5 antes de migrar.

## Reglas de operación en este proyecto

### Idioma
- Conversación y respuestas en **español**.
- Código, variables, funciones, mensajes de commit en **inglés**.

### Estilo de código
- Mantener procedural. **No introducir clases ni namespaces** hasta ejecutar el plan DDD.
- `snake_case` para variables y funciones.
- Sentencias SQL en MAYÚSCULAS (`SELECT`, `FROM`, `WHERE`, `INSERT INTO`, `VALUE`).
- Sin abstracciones prematuras. DRY, KISS, YAGNI.

### SQL
- **Nunca** SQL con string interpolation. Ejemplo malo: `"... WHERE id = $id"`.
- **Siempre** prepared statements: `$link->prepare()` + `bind_param()`.
- Si tenés que tocar un controller existente → considerar migrar esa entidad completa al patrón Repository.

### Frontend
- PHP plano + jQuery + assets en `app/public/assets/`.
- Vistas en `app/public/dash-{entidad}[-{sub}].php`.

### Git
- Mensajes en inglés, imperativo, minúsculas: `feat: add user auth`, `fix: null session crash`.
- Commits atómicos.
- **No commitear sin pedido explícito del usuario.** No commitear `.env`.

### Docker
- Imagen PHP definida en `Dockerfile`, actualmente `php:8.1-fpm-alpine3.18`.
- Cambiar versión PHP → actualizar `.doc/plan-migracion-php-8.5.md` sección 5.

## Antes de tocar cualquier archivo

1. **¿Es un controller?** → leer `.doc/plan-migracion-ddd-drycode.md` sección 4 (patrón Repository con Customer de ejemplo).
2. **¿Cambia la versión de PHP en `Dockerfile`?** → leer `.doc/plan-migracion-php-8.5.md` sección 5 (ruta 8.1 → 8.5) y validar QA por escalón.
3. **¿Es una vista (`dash-*.php`)?** → verificar que escapea output con `htmlspecialchars()`. Si no, agregarlo (deuda XSS pendiente).
4. **¿Es `config.php` o toca credenciales?** → **avisar primero**, no modificar credenciales reales.
5. **¿Vas a agregar una dependencia nueva?** → vendorizar en `app/public/vendor/<lib>/` salvo que sea parte del refactor DDD (donde se introduce Composer).

## Comandos

```bash
# Stack
docker-compose up -d --build
docker-compose restart php
docker-compose down          # preserva datos
docker-compose down -v       # borra volumen — SOLO dev

# Logs
docker-compose logs -f php
docker-compose logs -f nginx

# DB
docker-compose exec mysql mariadb -u$USER_DB_USER -p$USER_DB_PASSWORD $USER_DB_NAME
docker-compose exec mysql mariadb-dump -u root -p$MYSQL_ROOT_PASSWORD $USER_DB_NAME > backup-$(date +%F).sql

# PHP CLI
docker-compose exec php php -v
docker-compose exec php php -l /var/www/html/path/file.php

# Backup completo (producción)
cp -r /bath /bath-backup-$(date +%F)
```

## NO hacer

- **No** introducir `composer.json` sin ejecutar antes el plan DDD (es prerrequisito de la sección 4.1 de ese plan).
- **No** introducir framework (Laravel/Symfony) — el plan DDD lo descarta explícitamente.
- **No** saltar directo a PHP 8.5 — pasar 8.2 → 8.3 → 8.4 → 8.5.
- **No** modificar `mysql/database/donbano.sql` con datos de producción.
- **No** borrar `app/public/archive/` sin confirmar con el usuario — puede tener templates referenciados.
- **No** `dd()`, `var_dump()`, `print_r()` en código que va a producción.
- **No** commitear `.env`, `mysql/mysql-data/`, `.idea/`, `/06-07-2026`.
- **No** `docker-compose down -v` en producción sin backup confirmado.
- **No** commitear credenciales reales.
- **No** commitear sin pedido explícito.

## Documentos de referencia

- `README.md` — overview, stack, deploy
- `AGENTS.md` — reglas operativas más detalladas
- `.doc/plan-migracion-php-8.5.md` — estrategia de migración PHP
- `.doc/plan-migracion-ddd-drycode.md` — estrategia de refactor arquitectónico