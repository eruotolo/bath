# Plan — Roles y Permisos (RBAC + elevación por acción)

**Estado:** propuesta (pendiente de aprobación) — **decisiones de diseño ya confirmadas (ver §11)**
**Fecha:** 2026-07-18
**Objetivo:** definir 3 roles jerárquicos y controlar los permisos a nivel de **cada acción CRUD** de la aplicación, con enforcement **server-side** (no solo UI).

---

## 0. Estado actual (hallazgos)

- La tabla **`category` ya existe** con 2 filas: `(1, 'Administrador')`, `(2, 'Usuario')`. **No hay que crearla**, hay que extenderla.
- Los usuarios referencian el rol por `users.category` (int FK a `category.id_category`). Los 3 usuarios actuales tienen `category = 1` (Administrador).
- **El control de permisos hoy es solo cosmético (UI):** unos pocos `if ($_SESSION['category'] == 1)` en `dash-users-list.php`, `dash-users-profile.php` y `header.php` esconden botones. **Ningún controller valida permisos** → un Usuario puede ejecutar cualquier acción pegándole directo a la URL del controller (`controller/bath-delete.php?id_Bath=…`). **Este es el hueco de seguridad central que el plan cierra.**
- `$_SESSION['category']` se setea en `auth-login.php`. No hay tabla de settings ni de configuración.

---

## 1. Modelo de roles

Tres roles jerárquicos. La jerarquía se codifica con un **nivel numérico** en la tabla `category`, **independiente del `id_category`** (para no reordenar ids ya referenciados por `users.category`).

| Rol | `id_category` | `nivel_category` | Descripción |
|---|---|---|---|
| **SuperAdministrador** | 3 (nuevo) | 3 | Permiso absoluto. Único que ve la página de Logs. Único que puede otorgar el rol SuperAdministrador. |
| **Administrador** | 1 (existente) | 2 | Puede ver y hacer todo excepto Logs. Gestiona usuarios/roles (salvo otorgar SuperAdmin). |
| **Usuario** | 2 (existente) | 1 | Puede **crear**. Para **editar** o **eliminar** debe elevar con credenciales de un Admin/SuperAdmin. No ve Logs ni gestiona usuarios. |

**Por qué `nivel` y no el `id`:** el `id_category` de Administrador (1) es menor que el de un SuperAdmin nuevo (3), así que la magnitud del id **no** refleja jerarquía. Toda la lógica de permisos compara `nivel_category`, nunca el id. Además es extensible (agregar roles intermedios sin renumerar).

---

## 2. Matriz de permisos

| Acción | SuperAdmin (n3) | Admin (n2) | Usuario (n1) |
|---|:---:|:---:|:---:|
| Ver páginas / listados (read) | ✅ | ✅ | ✅ |
| **Crear** (create) | ✅ | ✅ | ✅ |
| **Editar** (update) | ✅ | ✅ | 🔐 requiere elevación |
| **Eliminar / inactivar** (delete) | ✅ | ✅ | 🔐 requiere elevación |
| Exportar / PDF | ✅ | ✅ | ✅ |
| **Ver página de Logs** | ✅ | ❌ | ❌ |
| Gestionar usuarios (crear/editar/inactivar) | ✅ | ✅ | ❌ |
| **Otorgar rol SuperAdministrador** | ✅ | ❌ | ❌ |

🔐 = la acción se ejecuta solo si en la misma request hay una **elevación válida** (ver §5).

---

## 3. Cambios en base de datos

Migración `mysql/migrations/2026-07-roles-permisos.sql` (idempotente):

```sql
-- 1. Nivel jerárquico en category (independiente del id)
ALTER TABLE `category`
  ADD COLUMN `nivel_category` INT(11) NOT NULL DEFAULT 1 AFTER `name_category`;

-- 2. Asignar niveles a los roles existentes
UPDATE `category` SET `nivel_category` = 2 WHERE `id_category` = 1; -- Administrador
UPDATE `category` SET `nivel_category` = 1 WHERE `id_category` = 2; -- Usuario

-- 3. Nuevo rol SuperAdministrador
INSERT INTO `category` (`id_category`, `name_category`, `nivel_category`)
VALUES (3, 'SuperAdministrador', 3)
ON DUPLICATE KEY UPDATE `name_category` = VALUES(`name_category`), `nivel_category` = VALUES(`nivel_category`);

-- 4. Promover al SuperAdmin inicial (Edgardo, users.id = 1)
UPDATE `users` SET `category` = 3 WHERE `id` = 1;
```

**Notas:**
- Los otros usuarios quedan como Administrador (nivel 2), igual que hoy. Solo cambia Edgardo → SuperAdministrador.
- No se crea tabla de settings: la elevación usa **credenciales reales de un admin** (§5), no un secreto compartido.
- Correr la migración en cada entorno (local → testing → prod).

---

## 4. Enforcement server-side — el núcleo

Helper procedural transversal `layouts/permissions.php` (misma filosofía que `activity_logger.php`: cross-cutting, no entidad DDD). Se incluye al inicio de cada controller y vista protegida.

```php
<?php
// layouts/permissions.php — control de acceso centralizado. Fuente de verdad server-side.

const NIVEL_USUARIO    = 1;
const NIVEL_ADMIN      = 2;
const NIVEL_SUPERADMIN = 3;

function current_nivel(): int
{
    return (int) ($_SESSION['nivel'] ?? 0); // se carga en login (§6)
}

function require_login(): void
{
    if (empty($_SESSION['loggedin'])) { header('Location: ../auth-login.php'); exit; }
}

// Bloquea la request si el nivel no alcanza
function require_min_nivel(int $nivel): void
{
    require_login();
    if (current_nivel() < $nivel) {
        http_response_code(403);
        header('Location: ../index.php?status=error&msg=' . urlencode('No tenés permiso para esta acción'));
        exit;
    }
}

// ¿El rol puede la acción por sí solo? (sin contar elevación)
function can(string $action): bool
{
    $n = current_nivel();
    switch ($action) {
        case 'read':
        case 'create':
        case 'export':      return $n >= NIVEL_USUARIO;
        case 'update':
        case 'delete':      return $n >= NIVEL_ADMIN;   // Usuario NO puede solo → necesita elevación
        case 'manage_users':return $n >= NIVEL_ADMIN;
        case 'grant_superadmin':
        case 'view_logs':   return $n >= NIVEL_SUPERADMIN;
        default:            return false;
    }
}

// Guard principal para controllers. Para update/delete, un Usuario pasa solo con elevación válida.
function require_permission(string $action, ?string $entidad = null, ?int $id = null): void
{
    require_login();
    if (can($action)) return;

    // Único camino alternativo: update/delete elevados por un admin (§5)
    if (in_array($action, ['update', 'delete'], true) && consume_elevation($action, $entidad, $id)) {
        return;
    }

    http_response_code(403);
    header('Location: ../index.php?status=error&msg=' . urlencode('No tenés permiso para esta acción'));
    exit;
}
```

**Regla de oro:** la UI que esconde botones es solo comodidad. **El gate real es `require_permission()` al inicio de cada controller de mutación.** Sin esto, el sistema de roles es decorativo.

---

## 5. Elevación por acción (step-up con credenciales de admin)

**Decidido (§11):** la "clave" es el **email + password de un Administrador o SuperAdministrador real**, validado contra `users`. Es de **un solo uso por acción** (se pide en cada editar/eliminar). Queda registrado **quién autorizó**.

### Flujo
1. **UI:** el Usuario hace click en Editar/Eliminar. JS intercepta (submit del form de edición o click del link de borrado) y abre un modal SweetAlert2 pidiendo **email + password de un admin**.
2. **AJAX:** POST a `controller/auth-elevate.php` con `{ email, password, action, entidad, id }`.
3. **Servidor valida:** busca el admin por email, `password_verify(password, hash)`, `state = 1` y `nivel >= NIVEL_ADMIN`. Si es válido, genera un **token de elevación de un solo uso** guardado en sesión, **ligado a la acción exacta**:
   ```php
   $_SESSION['elevation'] = [
     'action'   => $action,          // 'update' | 'delete'
     'entidad'  => $entidad,         // 'Bathroom', 'Customer', ...
     'id'       => (int) $id,        // id del registro objetivo
     'by_id'    => $admin_id,        // quién autorizó
     'by_name'  => $admin_name,
     'expires'  => time() + 120,     // TTL corto (~2 min)
     'nonce'    => bin2hex(random_bytes(16)),
   ];
   ```
   Responde `{ ok: true }`. Devuelve error genérico si falla (sin revelar si el email existe).
4. **UI:** al recibir OK, ejecuta la acción original (submit del form / navegación al controller de borrado).
5. **Controller objetivo** (`bath-update.php`, `bath-delete.php`, …) llama `require_permission('update'|'delete', 'Bathroom', $id)`. Para el Usuario, `consume_elevation()`:
   - Verifica que `$_SESSION['elevation']` exista, **coincida** en `action` + `entidad` + `id`, y no esté expirado.
   - **Lo invalida inmediatamente** (`unset`) → un solo uso.
   - Devuelve `true` solo si todo cuadra.
6. **Log:** se registran DOS eventos en `logs_actividad` (ver plan de logs): un `AUTHORIZE` (quién autorizó, sobre qué acción/entidad/id) y la acción CRUD real, con nota "autorizado por {by_name}".

```php
function consume_elevation(string $action, ?string $entidad, ?int $id): bool
{
    $e = $_SESSION['elevation'] ?? null;
    if (!$e) return false;
    $ok = $e['action'] === $action
       && $e['entidad'] === $entidad
       && (int)$e['id'] === (int)$id
       && $e['expires'] >= time();
    unset($_SESSION['elevation']); // un solo uso, pase o no
    return $ok;
}
```

### Seguridad de la elevación (crítico)
- **Ligada a acción + entidad + id:** autorizar "editar baño 5" no habilita "borrar baño 9". Evita reutilización cruzada.
- **Un solo uso + TTL corto (~120 s):** se consume al primer chequeo, expira sola.
- **Server-side:** el token vive en `$_SESSION`, nunca en el cliente. El cliente no puede forjarlo.
- **`password_verify`**, nunca comparación en texto plano. **Nunca** loguear el password.
- **Rate limiting:** bloquear `auth-elevate.php` tras N intentos fallidos por sesión/IP (evita fuerza bruta contra passwords de admin). Contador en sesión + espera incremental.
- El endpoint exige sesión de Usuario logueado (no es un login alternativo).

---

## 6. Login — cargar el nivel en sesión

En `auth-login.php`, además de `$_SESSION['category']` (id), setear el nivel para no golpear la DB en cada request:

```php
// tras validar credenciales y traer $category (id):
$_SESSION['category'] = $category;
$_SESSION['nivel'] = /* SELECT nivel_category FROM category WHERE id_category = ? */;
```

Igual en `auth-lock-screen.php` al reactivar la sesión.

---

## 7. Gating de UI y menús

Cosmético (comodidad), **siempre respaldado por el gate server-side**:

- **Sidebar → Logs:** visible solo si `current_nivel() === NIVEL_SUPERADMIN`.
- **Sidebar → Usuarios:** visible si `current_nivel() >= NIVEL_ADMIN`.
- **Botones Editar/Eliminar en listados:**
  - Admin/SuperAdmin: normales.
  - Usuario: **visibles pero marcados** `data-requires-elevation` → JS dispara el modal antes de ejecutar. (Se dejan visibles para que el Usuario sepa que puede pedir autorización, no se ocultan.)
- **Opción "Set Admin/User" y otorgar SuperAdmin:** el `<option>` SuperAdministrador en el selector de rol solo aparece si `current_nivel() === NIVEL_SUPERADMIN`.
- Reemplazar los `if ($_SESSION['category'] == 1)` actuales (`dash-users-list.php`, `dash-users-profile.php`, `header.php`) por chequeos basados en `current_nivel()`. Actualizar el mapa `$category_names` de `header.php` a los 3 roles.

---

## 8. Gestión de roles (usuarios)

- `user-new.php` / `user-update.php`: selector de rol con las 3 categorías. **Regla:** solo un SuperAdmin puede asignar/otorgar SuperAdministrador (`require_permission('grant_superadmin')` si el rol elegido es 3; si no, `require_permission('manage_users')`).
- `user-setadmin.php`: extender de toggle binario Admin/User a las 3 categorías, con la misma regla de otorgamiento.
- Un Admin no puede editar/degradar a un SuperAdmin (protección: `require_permission('grant_superadmin')` para tocar cualquier fila con `category = 3`).

---

## 9. Interacción con el plan de Log de Actividad

- La página de Logs (`dash-activity-log.php` del `.doc/plan-log-actividad.md`) cambia su gate de "solo `category == 1`" a **`require_min_nivel(NIVEL_SUPERADMIN)`**. *(Se corrige esa referencia en el otro plan.)*
- Las elevaciones se registran como acción `AUTHORIZE` en `logs_actividad`, y la acción CRUD elevada anota quién autorizó. Esto da trazabilidad completa: quién pidió, quién autorizó, qué se tocó.
- Orden sugerido de implementación: **este plan (roles) antes o junto con el de logs**, porque el de logs depende del nivel de SuperAdmin para su gate.

---

## 10. Fases de implementación

Cada fase es una **tarea secuencial de orquestación Orca** (Fase N depende de Fase N-1). El modelo se asigna por complejidad (ver `AGENTS.md § Orquestación de planes`): **Sonnet 5** = complejo/crítico (seguridad), **GLM-5.2** = mediano, **MiniMax-M3** = rápido/repetitivo.

| Tarea | Fase — Entregable | Riesgo | Modelo (Orca) | Depende de |
|---|---|---|---|---|
| **T0** | Migración `category.nivel_category` + SuperAdmin + promover Edgardo | Bajo | MiniMax-M3 | — |
| **T1** | `auth-login.php` / lock-screen cargan `$_SESSION['nivel']` | Bajo | MiniMax-M3 | T0 |
| **T2** | Helper `layouts/permissions.php` (`can` / `require_permission` / niveles) | Bajo | GLM-5.2 | T1 |
| **T3** | Guard `require_permission()` en los ~57 controllers de mutación (por acción/entidad) | **Alto** (volumen + es el gate real, seguridad) | Sonnet 5 | T2 |
| **T4** | Endpoint `controller/auth-elevate.php` + token de elevación one-time + rate limit | Medio (seguridad) | Sonnet 5 | T2 |
| **T5** | Frontend: modal SweetAlert2 de elevación en editar/eliminar del Usuario (intercept submit/click) | Medio | GLM-5.2 | T4 |
| **T6** | Gating de UI/menús (Logs solo SuperAdmin, Usuarios n≥2, otorgar SuperAdmin) | Bajo | MiniMax-M3 | T2 |
| **T7** | Gestión de roles: `user-new`/`user-update`/`user-setadmin` con 3 categorías + regla de otorgamiento | Medio | GLM-5.2 | T3 |
| **T8** | Integración con `logs_actividad` (AUTHORIZE + quién autorizó) | Bajo (depende del plan de logs) | MiniMax-M3 | T4 |
| **T9** | QA por rol (SuperAdmin / Admin / Usuario) incluyendo intento de bypass por URL directa | Medio | Sonnet 5 | T3, T4, T5, T6, T7 |

> Secuencial estricto: T0 → T1 → T2 → T3 → T4 → T5 → T6 → T7 → T8 → T9. La columna "Depende de" marca el mínimo real de precedencia; el orquestador ejecuta en ese orden. Las tareas de seguridad (T3, T4, T9) van en **Sonnet 5** por criticidad, no solo por complejidad.

**QA obligatorio (sin tests automatizados):** por cada rol, verificar que:
1. La UI muestra/esconde lo correcto.
2. **El bypass por URL directa se bloquea** (loguearse como Usuario y pegarle a `controller/bath-delete.php?id_Bath=X` sin elevación → 403/redirect).
3. La elevación funciona una sola vez y ligada al registro correcto.

---

## 11. Decisiones (confirmadas 2026-07-18)

1. **Roles:** SuperAdministrador (n3) / Administrador (n2) / Usuario (n1). Jerarquía por `nivel_category`, no por id.
2. **Clave de elevación:** ✅ **email + password de un Admin/SuperAdmin real** (validado contra `users`, `nivel >= 2`). Queda registrado quién autorizó.
3. **Duración de la elevación:** ✅ **solo esa acción** — se pide en cada editar/eliminar; token one-time ligado a acción+entidad+id, TTL ~120 s.
4. **Gestión de usuarios:** ✅ **SuperAdmin y Administrador** pueden gestionar usuarios; **solo SuperAdmin** otorga el rol SuperAdministrador.
5. **Página de Logs:** solo SuperAdministrador.
6. **Enforcement:** helper procedural `layouts/permissions.php` + `require_permission()` server-side en cada controller (no solo UI).

## 12. Decisiones abiertas menores

1. **Rate limit de elevación:** ¿cuántos intentos fallidos antes de bloquear y por cuánto? (Propuesta: 5 intentos, luego espera de 5 min por sesión.)
2. **Nombre de columna:** `nivel_category` (propuesto) — consistente con `id_category`/`name_category`.
3. ¿El Usuario puede **inactivar/eliminar** también con elevación, o solo **editar**? (Propuesta: ambos update y delete, como dice la matriz.)
