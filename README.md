# php-bathroom

Panel administrativo (PHP + MariaDB) para gestión de baños portátiles, contratos, clientes, servicios, certificados y facturación.

## Stack

| Capa | Tecnología | Versión |
|---|---|---|
| Runtime | PHP-FPM | 8.1.27 (Alpine 3.18) — objetivo 8.5 |
| Web server | Nginx | 1.25.3-alpine |
| Base de datos | MariaDB | 11.2 |
| Administración DB | phpMyAdmin | 5.2.1 |
| PDFs | TCPDF | 6.6.5 (vendorizado en `app/public/vendor/tcpdf`) |
| Emails | PHPMailer | vendorizado en `app/public/vendor/phpmailer` |
| ORM / Composer | — | **no usa** (código 100% procedural, dependencias vendorizadas a mano) |

## Arquitectura actual

```
php-bathroom/
├── app/
│   └── public/                        ← document root (Nginx apunta acá)
│       ├── dash-*.php                 ← 27 vistas del panel
│       ├── auth-*.php / pages-*.php   ← login, recuperación, errores
│       ├── controller/                ← 45 endpoints de acción (uno por operación)
│       ├── layouts/                   ← config, session, menús, modales compartidos
│       ├── assets/                    ← CSS, JS, fonts, libs frontend
│       ├── vendor/                    ← TCPDF y PHPMailer vendorizados
│       ├── archive/                   ← 77 archivos legacy de template (no usados)
│       └── uploads/                   ← archivos subidos por usuarios
├── mysql/
│   ├── database/donbano.sql           ← dump inicial con schema + datos
│   └── mysql-data/                    ← volumen Docker (datos persistentes, gitignored)
├── nginx/
│   └── nginx.conf
├── php/
│   ├── php.ini                        ← configuración custom (uploads 256M, memory 512M)
│   └── conf.d/
├── docker-compose.yml
├── Dockerfile                         ← php:8.1-fpm-alpine3.18 + extensión mysqli
└── .doc/                              ← planificación estratégica (no deployar)
    ├── plan-migracion-php-8.5.md
    └── plan-migracion-ddd-drycode.md
```

## Modelo de datos (14 tablas)

`bathrooms`, `certificados`, `clientes`, `contactos`, `contratos`, `contrato_bathroom` (junction), `facturas`, `factura_estado`, `factura_servicio` (junction), `servicios`, `servicios_bathrooms` (junction), `tipo_servicio`, `users`, `category`.

Convenciones de columnas: snake con sufijo del nombre de tabla (ej: `rut_Cliente`, `estado_Bath`, `fechaCompra_Bath`). Mantener este patrón al agregar columnas.

## Entidades de negocio (7 dominios)

| Dominio | Tabla principal | Vistas |
|---|---|---|
| Customers | `clientes` | `dash-customers*.php` |
| Bathrooms | `bathrooms` | `dash-bathrooms*.php` |
| Contracts | `contratos` + `contrato_bathroom` | `dash-contracts*.php` |
| Services | `servicios` + `servicios_bathrooms` + `tipo_servicio` | `dash-services*.php` |
| Invoices | `facturas` + `factura_servicio` + `factura_estado` | `dash-invoices*.php` |
| Certificates | `certificados` | `dash-certificates*.php` |
| Users | `users` | `dash-users*.php` |

## Entorno de desarrollo

### Levantar el stack

```bash
# Copiar .env (ver sección Variables de entorno)
cp .env .env.local  # editar valores

docker-compose up -d --build
docker-compose ps
```

Aplicación disponible en `http://localhost`. phpMyAdmin en el mismo host (mismo puerto 80).

### Comandos frecuentes

```bash
docker-compose logs -f php          # logs PHP-FPM
docker-compose exec php sh          # shell dentro del contenedor PHP
docker-compose exec mysql mariadb -u$USER_DB_USER -p$USER_DB_PASSWORD $USER_DB_NAME
docker-compose down                 # baja servicios (preserva datos)
docker-compose down -v              # baja servicios + borra volumen (CUIDADO en prod)
```

### Variables de entorno (`.env`)

```env
SITE_NAME=bath
MYSQL_ROOT_PASSWORD=changeme
USER_DB_NAME=donbano
USER_DB_USER=eruotolo
USER_DB_PASSWORD=changeme
MYSQL_DB_HOST=mysql
```

⚠️ **No commitear `.env`** — ya está en `.gitignore`.

## Configuración PHP custom

`php/php.ini` se monta en `/usr/local/etc/php/php.ini:ro`. Valores relevantes:

| Directiva | Valor |
|---|---|
| `upload_max_filesize` | 256M |
| `post_max_size` | 256M |
| `max_input_vars` | 8000 |
| `max_input_time` | -1 (sin límite) |
| `max_execution_time` | 500s |
| `memory_limit` | 512M |

Si necesitás regenerar el `php.ini` después de un bump de versión de imagen base:

```bash
docker cp php-bathroom:/usr/local/etc/php ./php
```

## Seguridad — problemas conocidos pendientes

Documentados en `.doc/Viejos/plan-migracion-php-8.5.md` y `plan-migracion-ddd-drycode.md`:

1. **Credenciales hardcodeadas en `app/public/layouts/config.php`** — usuario y contraseña de producción en texto plano, trackeadas en git. **Rotar y mover a `.env` antes de cualquier deploy.**
2. **SQL Injection sistemática** — los 45 controllers arman SQL por concatenación directa de `$_POST` (sin prepared statements). Resolver con el refactor DDD documentado en `.doc/Viejos/plan-migracion-ddd-drycode.md`.
3. **Manejo de errores de `mysqli_connect` muerto desde PHP 8.1** — el bloque `if($link === false)` ya no se ejecuta; una falla de conexión lanza una excepción no controlada.

## Planificaciones estratégicas

El directorio `.doc/` contiene tres planes de evolución del proyecto. **No son deployables** — son documentos de trabajo:

- [`.doc/Viejos/plan-mejoras-frontend.md`](.doc/Viejos/plan-mejoras-frontend.md) — componentes UI reutilizables (DataTable potenciado, selects con buscador y orden alfabético vía Choices.js, datepickers Flatpickr, máscara de RUT, feedback con SweetAlert2). Usa librerías ya instaladas en `assets/libs/` — cero downloads nuevos.
- [`.doc/Viejos/plan-migracion-php-8.5.md`](.doc/Viejos/plan-migracion-php-8.5.md) — migración incremental de PHP 8.1 → 8.2 → 8.3 → 8.4 → 8.5, con riesgos identificados y checklist de QA por escalón.
- [`.doc/Viejos/plan-migracion-ddd-drycode.md`](.doc/Viejos/plan-migracion-ddd-drycode.md) — refactor a arquitectura DDD liviana (Entity / Repository / Use Case) que elimina SQL Injection de raíz y centraliza el acceso a datos.

Orden recomendado: **Frontend → DDD → PHP 8.5**. Ver análisis en `CLAUDE.md`.

## Despliegue en producción

Servidor: droplet con `/bath` como directorio de la app. Variables en `.env` (no en el repo).

```bash
# Pull
cd /bath
git pull https://<usuario>:<TOKEN>@github.com/eruotolo/bath.git

# Rebuild + restart
docker-compose down
docker-compose up -d --build

# Verificar
docker-compose ps
docker-compose logs -f --tail=100
```

## Convenciones de código

- **PHP procedural puro** — no usar clases ni namespaces hasta que se ejecute el plan DDD.
- **Consultas a DB** — usar `mysqli_query` con concatenación (estado actual). Migrar a prepared statements según `.doc/Viejos/plan-migracion-ddd-drycode.md`.
- **Frontend** — assets en `app/public/assets/`, sin frameworks (jQuery está OK por ahora, presente en `assets/libs/`).
- **Sin `composer.json`** — si necesitás una librería nueva, vendorizar en `app/public/vendor/<lib>/` (patrón actual del proyecto) salvo que se introduzca Composer como parte del refactor DDD.

## Comandos rápidos

```bash
# Backup de DB antes de cualquier cambio
docker-compose exec mysql mariadb-dump -u root -p$MYSQL_ROOT_PASSWORD $USER_DB_NAME > backup-$(date +%F).sql

# Backup del droplet completo (ejemplo)
cp -r /bath /bath-backup-$(date +%F)

# Ver versión PHP exacta corriendo en el contenedor
docker-compose exec php php -v
```

## Estado del proyecto

- **Producción:** PHP 8.1.27, MariaDB 11.2, Nginx 1.25.3 — corriendo y en uso.
- **Refactor arquitectónico:** pendiente (plan en `.doc/`).
- **Tests automatizados:** no existen. Toda validación es QA manual.
- **CI/CD:** no existe. Deploy manual vía `git pull` + `docker-compose up`.