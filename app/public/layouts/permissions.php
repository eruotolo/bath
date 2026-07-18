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

// Token de elevación one-time, ligado a action+entidad+id (ver §5).
// Se invalida SIEMPRE (pase o no) → un solo uso por request.
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
