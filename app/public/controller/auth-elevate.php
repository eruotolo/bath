<?php

session_start();

require_once '../layouts/permissions.php';
require_login();
require_once '../layouts/config.php';

header('Content-Type: application/json; charset=utf-8');

function elevation_response(bool $ok, int $status_code = 200): never
{
    http_response_code($status_code);
    echo json_encode(['ok' => $ok]);
    exit;
}

function register_elevation_failure(): bool
{
    $attempts = (int) ($_SESSION['elevation_attempts'] ?? 0) + 1;
    $_SESSION['elevation_attempts'] = $attempts;

    if ($attempts > 5) {
        $_SESSION['elevation_blocked_until'] = time() + 300;
        return true;
    }

    return false;
}

$blocked_until = (int) ($_SESSION['elevation_blocked_until'] ?? 0);
if ($blocked_until > time()) {
    elevation_response(false, 429);
}

if ($blocked_until !== 0) {
    unset($_SESSION['elevation_blocked_until'], $_SESSION['elevation_attempts']);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    elevation_response(false, 405);
}

$input = $_POST;
$content_type = $_SERVER['CONTENT_TYPE'] ?? '';
if (str_starts_with(strtolower($content_type), 'application/json')) {
    $json_input = json_decode((string) file_get_contents('php://input'), true);
    $input = is_array($json_input) ? $json_input : [];
}

$email = isset($input['email']) && is_string($input['email']) ? trim($input['email']) : '';
$password = isset($input['password']) && is_string($input['password']) ? $input['password'] : '';
$action = isset($input['action']) && is_string($input['action']) ? $input['action'] : '';
$entidad = isset($input['entidad']) && is_string($input['entidad']) ? $input['entidad'] : '';
$id = isset($input['id']) && is_scalar($input['id']) && ctype_digit((string) $input['id'])
    ? (int) $input['id']
    : 0;

$valid_entities = ['Bathroom', 'Customer', 'Contract', 'Service', 'Invoice', 'Certificate', 'User'];
if (
    $email === ''
    || $password === ''
    || !filter_var($email, FILTER_VALIDATE_EMAIL)
    || !in_array($action, ['update', 'delete'], true)
    || !in_array($entidad, $valid_entities, true)
    || $id <= 0
) {
    elevation_response(false, register_elevation_failure() ? 429 : 400);
}

$admin = null;

try {
    $stmt = $link->prepare(
        'SELECT U.id, U.username, U.name, U.lastname, U.password, U.state, C.nivel_category
         FROM users U
         INNER JOIN category C ON C.id_category = U.category
         WHERE U.useremail = ?
         LIMIT 1'
    );
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $admin = $stmt->get_result()->fetch_assoc() ?: null;
    $stmt->close();
} catch (\mysqli_sql_exception $e) {
    elevation_response(false, register_elevation_failure() ? 429 : 500);
}

$is_valid_admin = $admin !== null
    && (int) $admin['state'] === 1
    && (int) $admin['nivel_category'] >= NIVEL_ADMIN
    && password_verify($password, (string) $admin['password']);

if (!$is_valid_admin) {
    elevation_response(false, register_elevation_failure() ? 429 : 401);
}

unset($_SESSION['elevation_attempts'], $_SESSION['elevation_blocked_until']);

$admin_name = trim((string) $admin['name'] . ' ' . (string) $admin['lastname']);
if ($admin_name === '') {
    $admin_name = (string) $admin['username'];
}

$_SESSION['elevation'] = [
    'action' => $action,
    'entidad' => $entidad,
    'id' => $id,
    'by_id' => (int) $admin['id'],
    'by_name' => $admin_name,
    'expires' => time() + 120,
    'nonce' => bin2hex(random_bytes(16)),
];

// TODO(plan-log-actividad): registrar evento AUTHORIZE cuando el helper y la tabla estén disponibles.
// Firma esperada (ver .doc/plan-log-actividad.md §helper):
//   log_activity_ctx($link, 'AUTHORIZE', [
//       'entidad'    => $entidad,
//       'entidad_id' => $id,
//       'id_usuario' => (int) $admin['id'],     // admin que autorizó
//       'descripcion'=> "Autorizó {$action} sobre {$entidad} #{$id}",
//   ]);
// Se insertará acá (después de escribir $_SESSION['elevation'], antes de elevation_response(true))
// cuando exista layouts/activity_logger.php + tabla logs_actividad. Mientras tanto NO loguear
// (este controller no debe romper ni acoplarse a un helper inexistente).
// Ver .doc/plan-roles-permisos.md §5 paso 6 y §9.

elevation_response(true);
