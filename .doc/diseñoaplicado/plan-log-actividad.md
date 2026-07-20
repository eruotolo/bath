# Plan — Sistema de Log de Actividad del Usuario

**Estado:** ✅ **EJECUTADO 2026-07-18** por olas (Codex-terra high / GLM-5.2 / MiniMax-M3 + Sonnet 5 orquestador y ejecutor de T6). QA manual completa por tarea + smoke test final de T6 en navegador real (curl con usuarios temporales SuperAdmin/Usuario, sin credenciales reales). Hallazgo colateral: `servicio-pdf.php` ya no tenía la SQL injection documentada como "excepción conocida" — fue neutralizado a redirect en el commit `3c1d1d0` (mismo día, plan roles-permisos); `AGENTS.md` quedó desactualizado en ese punto.
**Fecha:** 2026-07-18
**Objetivo:** registrar en base de datos **las acciones CRUD y de negocio del usuario logueado** sobre las 7 entidades del sistema, más los eventos de sesión (login/logout) y de salida (export/PDF).

> **Decisión de alcance (2026-07-18):** se registran **solo acciones**, no la navegación. No se loguean page-views ("visitó pantalla X"). Esto simplifica el sistema: no hay hook en `session.php`, no existe el evento `VIEW`, y el volumen de la tabla queda acotado a lo que el usuario efectivamente ejecuta.

---

## 1. Alcance

Se captura **una sola superficie**: las **acciones** del usuario.

1. **Acciones CRUD y de negocio:** cada mutación ejecutada por un controller (crear/editar/eliminar/activar/desactivar/asignar/etc.) sobre las 7 entidades.
2. **Eventos de sesión:** `LOGIN`, `LOGOUT` (incluye login fallido como `ERROR`).
3. **Salidas:** exportaciones (Excel/CSV) y generación de PDF.

Lo que **NO** se loguea:
- **Navegación / page-views** — descartado por decisión (§10.2). No se instrumenta `session.php`.
- Endpoints AJAX de solo-lectura (`obtener_contratos.php`, `obtener_banos_contrato.php`, `obtener_servicios.php`, `contact-get.php`, `customer-get.php`) — solo lectura, sin valor de auditoría (§10.3).
- Assets estáticos (CSS/JS/img) — nunca pasan por PHP.
- `password`, `token`, `hashed_password` — **jamás** se escriben al log, ni siquiera dentro del payload.

---

## 2. Superficies de captura y punto de enganche

| Superficie | Punto de enganche | Cobertura | Tipo de evento |
|---|---|---|---|
| Login / Logout | `auth-login.php`, `controller/logout.php`, `auth-lock-screen.php` | auth | `LOGIN` / `LOGOUT` / `ERROR` |
| CRUD DDD (53 controllers) | tras `->handle()` exitoso, en el controller | 7 entidades | `CREATE`/`UPDATE`/`DELETE`/`ACTIVATE`/`DEACTIVATE` |
| CRUD procedural (4 controllers) | tras el `mysqli_query` exitoso | los que queden sin migrar | idem |
| Export / PDF | tras generar el archivo, antes del output | `*-pdf.php`, `*-export.php` | `EXPORT` / `PDF` |

**Clave:** el enganche vive **dentro de cada controller**, justo después de que la mutación tuvo éxito (o en el `catch` para registrar el intento fallido). Como 53 de 60 controllers ya usan Use Cases DDD, el `$id` que devuelve `handle()` da el `entidad_id` a loguear.

---

## 3. Modelo de datos

### Tabla `logs_actividad`

Convención del proyecto: tabla en plural + snake, columnas `{campo}_Log`.

```sql
CREATE TABLE `logs_actividad` (
  `id_Log`          INT(11)      NOT NULL AUTO_INCREMENT,
  `id_Usuario`      INT(11)      DEFAULT NULL,          -- FK lógica a users.id (nullable: login fallido / sin sesión)
  `username_Log`    VARCHAR(50)  DEFAULT NULL,          -- snapshot: sobrevive si se borra el usuario
  `accion_Log`      VARCHAR(20)  NOT NULL,              -- CREATE|UPDATE|DELETE|ACTIVATE|DEACTIVATE|LOGIN|LOGOUT|EXPORT|PDF|ERROR
  `entidad_Log`     VARCHAR(40)  DEFAULT NULL,          -- Bathroom|Customer|Contract|Service|Invoice|Certificate|User (NULL en LOGIN/LOGOUT)
  `entidad_id_Log`  INT(11)      DEFAULT NULL,          -- id del registro afectado
  `descripcion_Log` VARCHAR(255) DEFAULT NULL,          -- texto legible: "Creó baño código B-102"
  `pantalla_Log`    VARCHAR(120) DEFAULT NULL,          -- controller que ejecutó la acción: controller/bath-new.php
  `metodo_Log`      VARCHAR(8)   DEFAULT NULL,          -- GET|POST
  `datos_Log`       TEXT         DEFAULT NULL,          -- payload sanitizado (JSON), sin secretos
  `resultado_Log`   VARCHAR(10)  NOT NULL DEFAULT 'success', -- success|error
  `ip_Log`          VARCHAR(45)  DEFAULT NULL,          -- soporta IPv6
  `user_agent_Log`  VARCHAR(255) DEFAULT NULL,
  `fecha_Log`       DATETIME     NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_Log`),
  KEY `idx_log_usuario` (`id_Usuario`),
  KEY `idx_log_entidad` (`entidad_Log`, `entidad_id_Log`),
  KEY `idx_log_accion`  (`accion_Log`),
  KEY `idx_log_fecha`   (`fecha_Log`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

Migración: `mysql/migrations/2026-07-logs-actividad.sql` (idempotente, `CREATE TABLE IF NOT EXISTS`).

**Notas de diseño:**
- Sin FK física dura sobre `users` para no bloquear inserts si el user fue borrado; se usa `id_Usuario` + snapshot `username_Log`.
- `datos_Log` como `TEXT`/JSON string, no columna JSON nativa, por compatibilidad y simplicidad. Se guarda solo el subconjunto relevante del `$_POST`, con blacklist de campos sensibles.
- Índices pensados para la vista de administración (filtrar por usuario, entidad, acción, rango de fechas).

---

## 4. Componente de logging

### Decisión confirmada: helper procedural (§10.5)

**Helper `layouts/activity_logger.php`.**
Es infraestructura transversal usada por los ~60 controllers; no pertenece a ninguna entidad DDD. KISS/YAGNI: una función con prepared statement.

```php
<?php
// layouts/activity_logger.php
// Cross-cutting activity logger. Never throws — a logging failure must not break the user request.

function log_activity(mysqli $link, array $data): void
{
    // Never log secrets
    static $blacklist = ['password', 'token', 'hashed_password', 'confirm_password', 'newpassword'];

    $payload = null;
    if (!empty($data['datos']) && is_array($data['datos'])) {
        $clean = array_diff_key($data['datos'], array_flip($blacklist));
        $payload = json_encode($clean, JSON_UNESCAPED_UNICODE);
    }

    $sql = "INSERT INTO logs_actividad
              (id_Usuario, username_Log, accion_Log, entidad_Log, entidad_id_Log,
               descripcion_Log, pantalla_Log, metodo_Log, datos_Log, resultado_Log,
               ip_Log, user_agent_Log)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?)";

    try {
        $stmt = $link->prepare($sql);
        $stmt->bind_param(
            'issssssssss' . 's',
            $data['id_usuario'], $data['username'], $data['accion'], $data['entidad'],
            $data['entidad_id'], $data['descripcion'], $data['pantalla'], $data['metodo'],
            $payload, $data['resultado'], $data['ip'], $data['user_agent']
        );
        $stmt->execute();
        $stmt->close();
    } catch (\Throwable $e) {
        // Swallow: logging must never interrupt the app. (Opcional: error_log del fallo.)
    }
}

// Helper que arma el contexto desde $_SESSION + $_SERVER
function log_activity_ctx(mysqli $link, string $accion, array $extra = []): void
{
    log_activity($link, array_merge([
        'id_usuario'  => $_SESSION['id']       ?? null,
        'username'    => $_SESSION['username']  ?? null,
        'accion'      => $accion,
        'entidad'     => null,
        'entidad_id'  => null,
        'descripcion' => null,
        'pantalla'    => basename($_SERVER['SCRIPT_NAME'] ?? ''),
        'metodo'      => $_SERVER['REQUEST_METHOD'] ?? null,
        'datos'       => null,
        'resultado'   => 'success',
        'ip'          => $_SERVER['REMOTE_ADDR'] ?? null,
        'user_agent'  => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
    ], $extra));
}
```

> El `bind_param` se escribe con el string de tipos completo (`'isssssssssss'` = 1 int + 11 strings); arriba está partido solo por claridad.

> Alternativa DDD (`ActivityLog` + `MysqliActivityLogRepository` + `RecordActivity`) descartada por ahora: más pesada para un cross-cutting. El helper se puede envolver en un repositorio más adelante sin reescribir los call-sites.

---

## 5. Enganche por superficie

### 5.1 CRUD DDD — patrón por controller
Se loguea **después** del `handle()` exitoso, con id devuelto por el use case:

```php
// controller/bath-new.php (tras crear)
$id = $useCase->handle($_POST);
if ($id !== null) {
    log_activity_ctx($link, 'CREATE', [
        'entidad' => 'Bathroom', 'entidad_id' => $id,
        'descripcion' => "Creó baño código {$_POST['codigo_Bath']}",
        'datos' => $_POST,
    ]);
    header('Location: ../dash-bathrooms.php?status=success...');
}
```
En el `catch` → mismo log con `'resultado' => 'error'` y `accion` correspondiente, para auditar intentos fallidos.

### 5.2 Login / Logout
- `auth-login.php`: tras setear `$_SESSION`, `log_activity_ctx($link,'LOGIN')`. Login fallido → `'ERROR'` con `id_usuario` NULL + email intentado en descripción (sin password).
- `logout`: `LOGOUT` antes de `session_destroy()`.

---

## 6. Mapa de controllers → acción / entidad

Referencia para implementar Fase 4-6 (60 controllers). Resumen por entidad:

| Entidad | Controllers | Acciones a loguear |
|---|---|---|
| **Bathroom** | bath-new, bath-update, bath-delete, bath-active, bath-inactive, bath-maintenance, bath-assign-drawer, bath-unassign-drawer, bath-create/edit-drawer | CREATE, UPDATE, DELETE, ACTIVATE, DEACTIVATE, ASSIGN, UNASSIGN |
| **Customer** (+Contact) | customer-new, customer-update, customer-inactive, customer-create/edit-drawer, contact-new, contact-update, contact-remove | CREATE, UPDATE, DEACTIVATE, DELETE |
| **Contract** | contract-new, contract-update, contract-down, contract-active, contract-inactive, contract-bath-new-assign | CREATE, UPDATE, TERMINATE, (DE)ACTIVATE |
| **Service** | service-new, service-update, service-inactive | CREATE, UPDATE, DEACTIVATE |
| **Invoice** | invoice-new, invoice-update, invoice-delete, invoice-estado, invoice-fecha-pago, invoice-service-add/remove, invoice-upload-parse/confirm/cancel | CREATE, UPDATE, DELETE, STATE_CHANGE, IMPORT |
| **Certificate** | certificate-new, certificate-remove | CREATE, DELETE |
| **User** | user-new, user-update, user-inactive, user-setadmin, user-default-pass, user-profile-newpassword | CREATE, UPDATE, DEACTIVATE, ROLE_CHANGE, PASSWORD_RESET |
| **Export/PDF** | certificate-pdf, invoice-pdf, service-pdf, servicio-pdf, customer-export, bathroom-contract-*-export | PDF, EXPORT |

*(La lista exacta se cierra al abrir cada controller; algunos comparten lógica.)*

---

## 7. Vista de administración — `dash-activity-log.php`

- **Acceso:** solo **SuperAdministrador** (`require_min_nivel(NIVEL_SUPERADMIN)`, ver `.doc/plan-roles-permisos.md`). Guard explícito además de session.php. *(Antes era `category == 1`; cambió al introducir el rol SuperAdmin.)*
- **UI:** reutiliza `native-table.js` (patrón ya usado en invoices-list, 2026-07-17), estilo Tailwind consistente con las vistas migradas.
- **Filtros:** usuario, entidad, acción, resultado, rango de fechas. Paginación server-side (la tabla crece rápido).
- **Columnas:** fecha, usuario, acción (badge por color), entidad + id, descripción, pantalla, IP, resultado.
- **Detalle:** click en fila → drawer con `datos_Log` (JSON formateado).
- **Seguridad:** todo output con `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`.
- Se agrega item en `sidebar.php` visible solo para Admin.

---

## 8. Consideraciones críticas

- **No romper la app:** el insert va en `try/catch` que traga la excepción. Un fallo de log **nunca** aborta la acción del usuario.
- **Seguridad:** prepared statements siempre; blacklist de campos sensibles en `datos_Log`; escape en la vista.
- **Volumen / performance:** al registrar solo acciones (no navegación), el volumen queda acotado a lo que el usuario efectivamente ejecuta. Un `INSERT` indexado por acción es barato.
- **Retención (Fase 7, opcional):** purga de registros > N meses vía evento MariaDB o cron. Definir política (ej. 12 meses).
- **Sesión en boundaries:** validar `$_SESSION['id']` antes de usarlo; en login aún no existe (se pasa explícito).
- **Producción:** la tabla es nueva y aislada; no afecta datos existentes. La migración se corre en cada entorno (local → testing → prod).

---

## 9. Fases de implementación

Orquestación por olas (no estrictamente secuencial): toda tarea cuyas dependencias ya cerraron se despacha en paralelo con otras en la misma situación, salvo que compartan archivos o **modelo** (una sola terminal por modelo a la vez — ver nota bajo la tabla). Asignación de modelo (revisada 2026-07-18, sesión de despacho):

- **Sonnet 5**: orquestador de la ola + tareas complejas donde la superficie de permisos ya está auditada/reutilizada (sin riesgo nuevo de seguridad).
- **Codex gpt-5.6-terra (high)**: tareas complejas con superficie de seguridad **nueva o sensible** (passwords, cambio de rol, gates ya bypasseados antes). Manda sobre complejidad pura.
- **GLM-5.2**: tareas medianas sin superficie crítica.
- **MiniMax-M3**: tareas mecánicas/repetitivas, patrón ya 100% especificado en el plan.

| Tarea | Entregable | Ola | Modelo (Orca) | Depende de | Riesgo |
|---|---|---|---|---|---|
| **T0** | Migración `logs_actividad` + correr en local | 1 | MiniMax-M3 | — | Bajo |
| **T1** | `layouts/activity_logger.php` (`log_activity` + `log_activity_ctx`) | 2 | GLM-5.2 | T0 | Bajo-medio (blacklist secrets) |
| **T2** | LOGIN / LOGOUT (auth-login, logout, lock-screen) | 3 | GLM-5.2 | T1 | Bajo (toca auth) |
| **T3a** | CRUD DDD — Bathroom | 3 | MiniMax-M3 | T1 | Bajo |
| **T3b** | CRUD DDD — Customer + Contact | 3 | MiniMax-M3 | T1 | Bajo |
| **T3c** | CRUD DDD — Contract | 3 | MiniMax-M3 | T1 | Bajo |
| **T3d** | CRUD DDD — Service | 3 | MiniMax-M3 | T1 | Bajo |
| **T3e** | CRUD DDD — Invoice (incl. import/upload-parse/confirm/cancel) | 3 | GLM-5.2 | T1 | Medio (más variedad de acciones) |
| **T3f** | CRUD DDD — Certificate | 3 | MiniMax-M3 | T1 | Bajo |
| **T3g** | CRUD DDD — User (setadmin, password-reset, role-change) | 3 | **Codex-terra high** | T1 | **Alto** — mismo controller que ya tuvo bypass crítico (roles/permisos); no debe filtrar `hashed_password` en `datos_Log` |
| **T4** | CRUD en 4 controllers procedurales restantes | 3 | MiniMax-M3 | T1 | Bajo |
| **T5** | EXPORT / PDF | 3 | MiniMax-M3 | T1 | Bajo |
| **T6** | Vista `dash-activity-log.php` (SuperAdmin) + item sidebar | 4 | Sonnet 5 | T3a-g, T4, T5 | Medio — guard reutiliza `require_min_nivel(NIVEL_SUPERADMIN)` ya auditado, riesgo real es UI/paginación/filtros |
| **T7** | Retención / limpieza (opcional) | 4 | MiniMax-M3 | T0 | Bajo |

**Nota de paralelismo real:** dentro de la Ola 3 hay 9 tareas candidatas, pero varias comparten modelo y una sola terminal por modelo puede estar activa a la vez:
- Cola MiniMax-M3 (secuencial entre sí): T3a → T3b → T3c → T3d → T3f → T4 → T5
- Cola GLM-5.2 (secuencial entre sí): T2 → T3e
- Codex-terra high: T3g (sola, sin cola)

Es decir, 3 terminales concurrentes reales en la Ola 3, no 9. La Ola 4 (T6, T7) abre recién cuando cierran **todas** las colas de la Ola 3.

**QA:** sin tests automatizados → smoke test manual por tarea en `http://localhost` (`docker-compose restart php`), verificando filas en `logs_actividad` vía phpMyAdmin tras cada acción. QA de T3g y T6 incluye intento adversarial (bypass de guard, intento de filtrar password) antes de dar luz verde a la Ola 4.

---

## 10. Decisiones (confirmadas 2026-07-18)

1. **Nombre de tabla:** `logs_actividad` — *(pendiente de confirmar contra `actividad_log` / `logs`; se toma `logs_actividad` por defecto).*
2. **Navegación:** ✅ **Solo CRUD, sin page-views.** No se instrumenta `session.php` ni existe el evento `VIEW`.
3. **AJAX de lectura** (`obtener_*`, `*-get`): ✅ **No se registran.**
4. **Retención:** se deja crecer; purga automática queda como Fase 7 **opcional**.
5. **Componente:** ✅ **Helper procedural** (`layouts/activity_logger.php`). Entidad DDD descartada.
