# Procedimiento — Backup e importación de la base de datos (`donbano`)

**Objetivo:** documentar el procedimiento reutilizable para traer un backup de producción a local (y, cuando corresponda, para aplicar cambios de esquema contra producción). Basado en la ejecución real del 2026-07-19 (`.doc/plan-importacion-backup-produccion-local.md`).

**Contexto:** mismo contenedor `mariadb-bathroom` (MariaDB 11.2) y misma base `donbano` en local y en producción (droplet). Acceso SSH al droplet documentado en memoria del proyecto (`ssh php-bathroom-droplet`, ver `~/.claude/.../memory/reference_ssh_droplet.md`).

---

## 1. Backup de producción (solo lectura)

Descarga directa por pipe SSH — **nunca dejar el dump en el servidor de producción**, solo pasa por el pipe hacia el archivo local:

```bash
ssh php-bathroom-droplet 'docker exec mariadb-bathroom sh -c "mariadb-dump -u root -p\"\$MYSQL_ROOT_PASSWORD\" donbano"' \
  > backup/$(date +%d-%m-%Y)/donbano-prod-$(date +%F).sql
```

Verificar integridad antes de seguir:
- `grep -c "CREATE TABLE" <archivo>` debe dar 14.
- Tamaño razonable (referencia: ~400K con los datos reales actuales).

---

## 2. Backup de resguardo de la DB que se va a sobrescribir

**Siempre antes de importar sobre cualquier base con datos**, sea local o producción:

```bash
docker exec mariadb-bathroom sh -c 'mariadb-dump -u root -p"$MYSQL_ROOT_PASSWORD" donbano' \
  > backup/$(date +%d-%m-%Y)/donbano-<entorno>-pre-import-$(date +%F).sql
```

Sin esto, un import fallido o un error de destino (ej. apuntar al contenedor equivocado) no tiene forma de revertirse.

---

## 3. Verificación previa (antes de tocar datos reales)

Nunca importar directo sobre una base real sin antes chequear los casos conocidos que pueden romper las migraciones. Usar una DB temporal, nunca la real:

```bash
docker exec mariadb-bathroom sh -c 'mariadb -u root -p"$MYSQL_ROOT_PASSWORD" -e "CREATE DATABASE IF NOT EXISTS donbano_check;"'
docker exec -i mariadb-bathroom sh -c 'mariadb -u root -p"$MYSQL_ROOT_PASSWORD" donbano_check' < <archivo_a_importar>.sql

# Sección 0 de mysql/migrations/2026-07-fase1-fase2.sql, contra la DB temporal:
docker exec mariadb-bathroom sh -c 'mariadb -u root -p"$MYSQL_ROOT_PASSWORD" donbano_check -e "
  SELECT codigo_Bath, COUNT(*) FROM bathrooms GROUP BY codigo_Bath HAVING COUNT(*) > 1;"'
docker exec mariadb-bathroom sh -c 'mariadb -u root -p"$MYSQL_ROOT_PASSWORD" donbano_check -e "
  SELECT COUNT(*) FROM contratos WHERE estado_Contrato = 2
    AND id_Contrato NOT IN (SELECT DISTINCT id_Contrato FROM contrato_bathroom);"'

# Limpiar la DB temporal al terminar
docker exec mariadb-bathroom sh -c 'mariadb -u root -p"$MYSQL_ROOT_PASSWORD" -e "DROP DATABASE donbano_check;"'
```

- Si la primera query devuelve filas (`codigo_Bath` duplicado), **parar** — la migración de la Sección 3 (UNIQUE KEY) va a fallar. Resolver los duplicados a mano antes de seguir.
- La segunda query dice cuántos contratos va a cerrar automáticamente la Sección 4. Revisar el número antes de confirmar (no asumir que es el mismo que en un entorno de prueba viejo — en la corrida real de producción dio 8, no los 62 que daba con datos de prueba).

---

## 4. Importación real

```bash
docker exec -i mariadb-bathroom sh -c 'mariadb -u root -p"$MYSQL_ROOT_PASSWORD" donbano' \
  < backup/<fecha>/<archivo>.sql
```

**Cómo funciona:** el dump trae `DROP TABLE IF EXISTS` + `CREATE TABLE` + `INSERT` por cada tabla — reemplazo completo y controlado, no hace falta `TRUNCATE` manual.

**Gotcha real (ya visto):** si el destino tiene una tabla que el dump de origen **no tiene** (ej. `logs_actividad` existe en local pero no en un dump de producción viejo), esa tabla **sobrevive intacta** con sus datos previos — el `DROP TABLE IF EXISTS` nunca se ejecuta para ella porque no está en el dump. Si se quiere un estado realmente limpio, hay que vaciarla a mano después (`TRUNCATE TABLE <tabla>`, nunca sin confirmar primero si tiene datos que importa conservar).

---

## 5. Aplicar las migraciones pendientes (en este orden exacto)

```bash
docker exec -i mariadb-bathroom sh -c 'mariadb -u root -p"$MYSQL_ROOT_PASSWORD" donbano' < mysql/migrations/2026-07-fase1-fase2.sql
docker exec -i mariadb-bathroom sh -c 'mariadb -u root -p"$MYSQL_ROOT_PASSWORD" donbano' < mysql/migrations/2026-07-superadmin-category.sql
docker exec -i mariadb-bathroom sh -c 'mariadb -u root -p"$MYSQL_ROOT_PASSWORD" donbano' < mysql/migrations/2026-07-roles-permisos.sql
docker exec -i mariadb-bathroom sh -c 'mariadb -u root -p"$MYSQL_ROOT_PASSWORD" donbano' < mysql/migrations/2026-07-logs-actividad.sql
```

**El orden importa:** `roles-permisos.sql` asume que la fila `(3, 'SuperAdministrador')` ya existe en `category` — por eso `superadmin-category.sql` va antes. Los 4 scripts son idempotentes (`IF NOT EXISTS` / `ON DUPLICATE KEY UPDATE`), seguros de re-ejecutar si algo se corta a mitad de camino.

**Antes de dar por cerrado este paso, revisar si hay migraciones nuevas** en `mysql/migrations/` más allá de estas 4 — el archivo `2026-07-fase1-fase2.sql` está pensado para ampliarse con nuevas secciones (no se reescribe, se agrega abajo). Ver el hallazgo de la Sección 7 (`clientes.created_at`) más abajo.

---

## 6. QA post-import (obligatorio, sin tests automatizados)

1. **Esquema:** `DESCRIBE <tabla>` de cada tabla tocada por una migración — confirmar que las columnas nuevas están.
2. **Queries de verificación** que trae cada script de migración (al final de cada `.sql`).
3. **Conteo de filas** antes (post-import, antes de migrar) y después (post-migración) en las tablas de negocio — deben coincidir exactamente. Cualquier diferencia significa que una migración alteró datos que no debía.
4. **Smoke test en navegador** con sesión real o efímera (nunca usar contraseñas reales para pruebas — ver §7): abrir DevTools, revisar consola y network, cargar el dashboard (`index.php`) y al menos un listado por entidad (Clientes, Baños, Contratos, Servicios, Facturas, Certificados).

---

## 7. Probar sin usar credenciales reales

Para entrar a la app durante el QA sin tocar contraseñas de usuarios reales, usar un script PHP temporal que inyecta la sesión directamente (mismo patrón usado en el QA del plan de roles/permisos):

```php
<?php
session_start();
require_once "layouts/config.php";
$row = mysqli_fetch_assoc(mysqli_query($link, "SELECT id, useremail, username, image, name, lastname, category, state FROM users WHERE id = 1"));
$_SESSION['loggedin'] = true;
$_SESSION['id'] = $row['id'];
$_SESSION['useremail'] = $row['useremail'];
$_SESSION['username'] = $row['username'];
$_SESSION['hashed_password'] = '';
$_SESSION['image'] = $row['image'];
$_SESSION['name'] = $row['name'];
$_SESSION['lastname'] = $row['lastname'];
$_SESSION['category'] = $row['category'];
$_SESSION['state'] = $row['state'];
$_SESSION['nivel'] = 0;
$stmt = mysqli_prepare($link, "SELECT nivel_category FROM category WHERE id_category = ?");
mysqli_stmt_bind_param($stmt, "i", $row['category']);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $nivel);
if (mysqli_stmt_fetch($stmt)) $_SESSION['nivel'] = (int) $nivel;
header("location: index.php");
```

Guardarlo como `app/public/_debug_session_tmp.php`, navegar a esa URL una vez, **borrar el archivo apenas termina el QA**. Nunca commitear este archivo.

---

## 8. Gotchas conocidos (para no repetir el diagnóstico)

- **`clientes.created_at`** no está en ningún dump ni migración original — era un `ALTER TABLE` hecho a mano en el entorno local viejo, sin trackear. `index.php` (dashboard) y `dash-customers.php` (orden por defecto vía `MysqliCustomerRepository::listActive()`) dependen de ella. Ya corregido: Sección 7 agregada a `mysql/migrations/2026-07-fase1-fase2.sql`. Si se importa un dump que no tenga esta columna, **agregarla también** (no solo las 4 migraciones "oficiales").
- **`contactos`** vacía es un dato real de producción, no un error de parseo — confirmado con SQL directo.
- **41 baños con múltiples contratos "activos" simultáneos** en `contrato_bathroom` (`.doc/Viejos/informe-produccion-banos-duplicados.md`) es un problema de negocio distinto al `UNIQUE KEY` de `codigo_Bath` — sigue sin resolver, no lo toca ninguna migración de este procedimiento, requiere decisión caso por caso.
- Nunca `TRUNCATE`/`DELETE` sin `WHERE` en una tabla que pueda tener datos reales sin antes confirmar qué contiene.

---

## 9. Cuando se aplique este mismo procedimiento contra producción

Mismo procedimiento (§1-§6), con dos diferencias de origen/destino:

- El backup de resguardo (§2) y la importación (§4) corren **contra el `mariadb-bathroom` del droplet** (vía `ssh php-bathroom-droplet`), no el local.
- Producción no tiene tablas "solo locales" de test (como pasó con `logs_actividad` en local) — el reemplazo por el dump debería ser más limpio en ese sentido, pero igual conviene correr §3 (verificación previa) contra una copia, no en vivo.
- Las migraciones (§5) todavía no se aplicaron nunca en producción — correrlas ahí por primera vez, no como reimportación.
- Este es el único paso de este documento que modifica datos de producción reales — requiere confirmación explícita antes de ejecutar, backup reciente confirmado, y ventana de mantenimiento si aplica.
