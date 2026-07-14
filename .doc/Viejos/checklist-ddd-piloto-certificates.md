# Checklist — Plan DDD / Piloto: entidad Certificates

**Origen:** `.doc/Viejos/plan-migracion-ddd-drycode.md` (sección 6, orden de migración: Certificates es la entidad piloto)
**Estado:** ✅ implementado y verificado en navegador (2026-07-09)

---

## Alcance

Migrar la entidad Certificates del patrón procedural actual (SQL inline, 3 de 6 archivos con SQL injection activa) al patrón DDD liviano del plan: Entity + Repository + Use Case, con Composer para autoload PSR-4.

## Prerrequisito de infraestructura

- **Composer** no estaba instalado en el contenedor PHP — se agregó vía multi-stage `COPY --from=composer:2` en el `Dockerfile`.
- El `docker-compose.yml` solo montaba `app/public` dentro del contenedor (`/var/www/html`) — `composer.json` y el código de dominio (`app/src/`, fuera de `app/public/` por diseño del plan) no eran visibles ahí. Se agregaron 3 mounts nuevos al servicio `php`: `./composer.json:/var/www/composer.json`, `./app/src:/var/www/src`, `./vendor:/var/www/vendor`.
- **Gotcha encontrado:** el bind mount de un archivo único (`composer.json`) se rompe si se edita el archivo desde el host con una escritura atómica (crea un archivo temporal + rename, en vez de escribir en el mismo inodo) — el contenedor se queda apuntando a un inodo que ya no existe y el archivo "desaparece" adentro. Pasó una vez esta sesión al corregir el path del autoload. **Si hay que volver a editar `composer.json`, después hay que `docker-compose up -d --force-recreate php`** para restablecer el mount.
- `composer.json` con autoload PSR-4 `"App\\": "src/"` (el path es relativo a donde queda `composer.json` **dentro del contenedor**, que es `/var/www/`, sibling de `/var/www/src` — no `app/src/` como sugiere literalmente el ejemplo del plan, por el mount custom).

## Archivos nuevos

```
composer.json
app/src/
├── Domain/Certificate/
│   ├── Certificate.php                    — Entity (id, nroCertificado, idCliente, idContrato, fechaServicio, mts)
│   │                                         + Certificate::displayNumber() estático (fórmula "ddmmaaaaAnnnnn",
│   │                                         antes duplicada en 3 archivos distintos)
│   └── CertificateRepositoryInterface.php
├── Application/Certificate/
│   ├── CreateCertificate.php              — calcula correlativo, arma Entity, guarda
│   ├── DeleteCertificate.php
│   ├── FindCertificateForPrint.php        — usado por certificate-pdf.php Y dash-certificates-item.php
│   └── ListCertificates.php               — usado por dash-certificates.php
└── Infrastructure/Persistence/
    └── MysqliCertificateRepository.php    — único archivo con SQL de certificados, 100% prepared statements
```

## Archivos modificados (quedan delgados, delegan al Use Case)

- `Dockerfile`, `docker-compose.yml` — Composer (ver arriba)
- `controller/certificate-new.php` — de SQL injection directa a `CreateCertificate::handle($_POST)`
- `controller/certificate-remove.php` — de SQL injection directa a `DeleteCertificate::handle((int) $_GET['id_Certificado'])`
- `controller/certificate-pdf.php` — ya tenía prepared statements propios (sesión anterior); ahora usa `FindCertificateForPrint`, el mismo Repository que `dash-certificates-item.php` — cero SQL duplicado
- `dash-certificates.php` — de `SELECT *` + `COUNT(*)` inline a `ListCertificates`; se agregó `htmlspecialchars()` en las líneas de display reescritas
- `dash-certificates-item.php` — de SQL injection directa a `FindCertificateForPrint`; se agregó `htmlspecialchars()`; el chequeo `if ($query_run)` (que no detectaba "no encontrado") pasó a `if ($row)` (si no existe, cae al `else` con el aviso de error en vez de intentar renderizar con `$row = null`)

`dash-certificates-add.php` no se tocó — su único SQL (`SELECT * FROM clientes`) pertenece al dominio Customer, no migrado todavía.

## Correcciones de datos encontradas durante la migración (documentadas, no cambian comportamiento visible)

1. **JOIN inconsistente entre archivos**: `dash-certificates.php` hacía el join a `contratos` directo (`CR.id_Contrato = CT.id_Contrato`, correcto); `certificate-pdf.php` y `dash-certificates-item.php` lo hacían indirecto vía `CL.id_Cliente = CT.id_Cliente` (funcionaba solo porque el `WHERE` filtraba después, pero es frágil si un cliente tiene varios contratos). El Repository nuevo usa siempre el join directo en los 3 lugares.
2. **`certificate-new.php` insertaba el correlativo (`nro_Certificado`) sin comillas** en una columna `varchar` — funcionaba por conversión implícita de MySQL, pero era un antipatrón. Ahora va con bind type `'s'` explícito.

## Bugs encontrados y corregidos durante la implementación (no en el plan original)

1. **Mismatch de paths en el autoload PSR-4**: `composer.json` decía `"App\\": "app/src/"` (copiado literal del ejemplo del plan), pero el volumen de Docker monta `app/src` del host en `/var/www/src` del contenedor (no `/var/www/app/src`). Resultado: `Class "App\...\ListCertificates" not found`. Se corrigió el path del autoload a `"src/"` y se re-corrió `composer dump-autoload`.
2. **`bind_param` no acepta propiedades `readonly` por referencia**: `MysqliCertificateRepository::insert()` intentaba bindear `$certificate->nroCertificado` etc. directo (propiedades `readonly` de la Entity) — `mysqli_stmt::bind_param` necesita variables por referencia para poder reusarlas entre ejecuciones, y PHP no permite crear una referencia a una propiedad readonly. Error: `Cannot modify readonly property`. Se corrigió copiando los valores a variables locales mutables antes del `bind_param`.
3. El gotcha del bind mount de `composer.json` (ver sección de infraestructura arriba).

## Verificación en navegador real

- [x] `php -l` sobre los 5 archivos PHP tocados + los 7 nuevos (composer.json no aplica) — sin errores
- [x] Listado (`dash-certificates.php`): 660 certificados, mismo orden, mismo formato de número, RUT/obra/fecha correctos
- [x] Crear certificado nuevo (`dash-certificates-add.php`, cliente ARIDOS MARDONES): correlativo `#09072026A00001` — contador sube a 661
- [x] Crear un segundo certificado el mismo día (cliente APIA SPA): correlativo `#09072026A00002` — confirma que el correlativo **incrementa** dentro del mismo día, no se resetea
- [x] Ver detalle (`dash-certificates-item.php`) del certificado recién creado: número coincide con el del listado
- [x] Preview de PDF (`certificate-pdf.php` vía Glightbox) del mismo certificado: número, cliente, RUT, mts y obra coinciden con el detalle
- [x] Eliminar ambos certificados de prueba: contador vuelve a 660, desaparecen del listado
- [x] `docker-compose logs php` sin errores/warnings nuevos después de las pruebas

## Pendiente / fuera de alcance de este piloto

- Las otras 6 entidades (Bathrooms, Users, Customers, Contracts, Services, Invoices) — mismo patrón, en pasadas separadas, siguiendo el orden del plan.
- XSS del resto de `dash-certificates.php`/`dash-certificates-item.php` que no se reescribió en este cambio.
- `bootstrap-db.php` / `MysqliConnection.php` (mencionados en el ejemplo del plan) — se evalúan cuando haga falta compartir conexión entre más Repositories; por ahora cada controller sigue incluyendo `layouts/config.php` directamente.
- `dash-certificates-add.php` no se tocó (dominio Customer).

---

## Reglas a respetar durante la implementación (de `CLAUDE.md`)

- SQL 100% prepared statements en el Repository nuevo — cumplido, y elimina 3 SQL injections activas (`certificate-new.php`, `certificate-remove.php`, `dash-certificates-item.php`)
- `htmlspecialchars()` en las líneas de vista reescritas — cumplido
- Sin framework, sin abstracciones DDD de más (Aggregates, Domain Events, CQRS) — cumplido, siguiendo el propio alcance del plan
- No se commiteó nada sin pedido explícito
