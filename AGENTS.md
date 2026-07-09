# AGENTS.md — php-bathroom

Reglas operativas específicas para este proyecto. Complementa (no reemplaza) el AGENTS.md global en `~/.config/opencode/AGENTS.md`.

## Idioma

Responder siempre en **español**. El código, variables, funciones, mensajes de commit en **inglés**.

## Stack y entorno

- **PHP 8.1.27** procedural puro en contenedor FPM Alpine
- **MariaDB 11.2** vía `mysqli` (no PDO)
- **Nginx 1.25.3** + **phpMyAdmin 5.2.1** vía `docker-compose`
- **Sin `composer.json`** — TCPDF y PHPMailer vendorizados en `app/public/vendor/`
- **Sin tests automatizados** — toda validación es QA manual
- **Sin CI/CD** — deploy manual con `git pull` + `docker-compose up -d --build`
- **Sin framework** — PHP plano + jQuery para frontend

## Hosting

Producción en droplet con la app en `/bath`. Deploy por `git pull` directo al servidor.

## Filosofía de código

- Mantener el estilo procedural del proyecto — **no introducir clases ni namespaces hasta que se ejecute `.doc/plan-migracion-ddd-drycode.md`**
- DRY, KISS, YAGNI
- Funciones pequeñas con una sola responsabilidad
- No abstracciones prematuras
- Solo agregar manejo de errores/validaciones explícitamente pedidos
- **No crear archivos `.md` adicionales** salvo pedido explícito — el `README.md`, este `AGENTS.md` y `CLAUDE.md` son la documentación oficial

## Convenciones PHP del proyecto

- Variables y funciones en `snake_case` (no `camelCase`)
- Columnas de DB con sufijo del nombre de tabla: `rut_Cliente`, `estado_Bath`, `nombre_Cliente`
- Sentencias SQL en MAYÚSCULAS (`SELECT`, `FROM`, `WHERE`, `INSERT INTO`, `VALUE`)
- `$link` global para conexión mysqli (en `layouts/config.php`)
- Cada controller hace `include ('../layouts/config.php')` para obtener `$link`

## SQL — reglas críticas

⚠️ **El proyecto actualmente tiene SQL Injection sistemática** (concatenación directa de `$_POST`). Esto está documentado y en plan de resolución.

- **No escribir código nuevo que use `mysqli_query` con concatenación de variables** — usar `mysqli` orientado a objetos con prepared statements (`$link->prepare()` + `bind_param()`)
- Si tenés que tocar un controller existente, considerar migrar esa entidad completa al patrón Repository según `.doc/plan-migracion-ddd-drycode.md`
- Nunca SQL con string interpolation: `"... WHERE id = $id"` → usar `prepare(... WHERE id = ?)` + `bind_param('i', $id)`

## Seguridad — no negociable

- **NO commitear credenciales reales** — están en `.gitignore` (`/.env`) pero verificar siempre antes de `git add`
- **NO commitear `app/public/layouts/config.php` con valores reales** — mover a variables de entorno (tarea pendiente del plan PHP 8.5)
- **Validar TODO input de usuario** — `$_POST`, `$_GET`, `$_SESSION`
- **Escapar TODO output HTML** — usar `htmlspecialchars($var, ENT_QUOTES, 'UTF-8')`. Las vistas actuales tienen XSS por `echo $row[...]` sin escapar (riesgo conocido, en plan)
- **Verificar sesión** en cualquier controller que no sea público (login, recoverpw)

## Naming conventions (ya establecidas en el proyecto)

- Models/tablas: plural (`clientes`, `bathrooms`, `contratos`)
- Controllers: `controller/{entidad}-{acción}.php` (`customer-new.php`, `bath-update.php`)
- Vistas: `dash-{entidad}[-{subsección}].php` (`dash-customers.php`, `dash-bathrooms-contracts.php`)
- Columnas: `{nombre}_{Tabla}` (`rut_Cliente`, `email_Cliente`)
- Variables PHP: `snake_case` (`$rut_Cliente`, `$nombre_Cliente`)

## Git

- Mensajes de commit en inglés, imperativo, minúsculas
- Commits atómicos y descriptivos
- **NO commitear ni pushear sin pedido explícito del usuario**
- **NO `git push --force`** sin confirmación
- **NO `--no-verify`** en commits
- Branch actual: `master` (también `origin/master`)

## Docker

- Las credenciales vienen de `.env` (no hardcodear en `docker-compose.yml`)
- El volumen `./mysql/mysql-data:/var/lib/mysql` persiste datos entre restarts — **NO** correr `docker-compose down -v` en producción
- Imagen PHP definida en `Dockerfile`, actualmente `php:8.1-fpm-alpine3.18`
- Cambiar de versión PHP requiere también actualizar `.doc/plan-migracion-php-8.5.md`

## Workflow de cambios

1. **Antes de tocar cualquier controller:** leer `.doc/plan-migracion-ddd-drycode.md` sección 4 para entender el patrón target
2. **Antes de cambiar versión de PHP:** leer `.doc/plan-migracion-php-8.5.md` sección 5 (ruta 8.1 → 8.2 → 8.3 → 8.4 → 8.5)
3. Después de cualquier cambio: `docker-compose build` + `docker-compose up -d` + smoke test manual
4. **No agregar features no pedidas** — esto es un proyecto en producción con deuda técnica conocida; cada cambio debe justificarse

## Comandos

```bash
# Levantar / reiniciar stack
docker-compose up -d --build
docker-compose restart php

# Ver logs
docker-compose logs -f php
docker-compose logs -f nginx

# DB
docker-compose exec mysql mariadb -u$USER_DB_USER -p$USER_DB_PASSWORD $USER_DB_NAME
docker-compose exec mysql mariadb-dump -u root -p$MYSQL_ROOT_PASSWORD $USER_DB_NAME > backup.sql

# Backup completo
cp -r /bath /bath-backup-$(date +%F)

# PHP CLI dentro del contenedor
docker-compose exec php php -v
docker-compose exec php php -l /var/www/html/ruta/archivo.php
```

## NO hacer

- **NO** introducir `composer.json` sin antes coordinar con el plan DDD (es prerrequisito)
- **NO** introducir un framework (Laravel/Symfony) — el plan DDD explícitamente lo descarta
- **NO** migrar PHP a 8.5 directo — pasar por 8.2 → 8.3 → 8.4 → 8.5 (ver plan)
- **NO** modificar `mysql/database/donbano.sql` con datos de producción — es solo el dump inicial
- **NO** borrar la carpeta `app/public/archive/` sin confirmar — puede contener templates legacy aún referenciados
- **NO** usar `dd()`, `var_dump()`, `print_r()` en código que va a producción
- **NO** commitear `.env`, `mysql/mysql-data/`, `.idea/`, `/06-07-2026` (todo en `.gitignore`)
- **NO** hacer `docker-compose down -v` en producción sin backup confirmado

## Documentación oficial

- `README.md` — overview, setup, deploy
- `AGENTS.md` (este archivo) — reglas operativas para agentes AI
- `CLAUDE.md` — guía de Claude Code / opencode para este proyecto
- `.doc/plan-migracion-php-8.5.md` — plan migración PHP
- `.doc/plan-migracion-ddd-drycode.md` — plan refactor arquitectónico