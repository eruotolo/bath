<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\User\ListActiveUsers;
use App\Infrastructure\Export\ExportRenderer;
use App\Infrastructure\Persistence\MysqliUserRepository;

global $link;
include('../layouts/session.php');
include('../layouts/config.php');
include('../layouts/permissions.php');
require_permission('export');
if (!can('manage_users')) {
    register_shutdown_function(static function (): void {
        http_response_code(403);
        header_remove('Location');
    });
}
require_permission('manage_users');
require_once '../layouts/activity_logger.php';

$format = isset($_GET['format']) && is_string($_GET['format']) ? strtolower($_GET['format']) : '';
if (!in_array($format, ['csv', 'pdf'], true)) {
    http_response_code(400);
    exit('Formato inválido. Use format=csv o format=pdf.');
}

$users = (new ListActiveUsers(new MysqliUserRepository($link)))->handle();

$columns = [
    'usuario' => 'Usuario',
    'nombre' => 'Nombre',
    'email' => 'Email',
    'categoria' => 'Categoria',
];
$rows = [];

foreach ($users as $user) {
    $rows[] = [
        'usuario' => $user['username'],
        'nombre' => trim((string) $user['name'] . ' ' . (string) $user['lastname']),
        'email' => $user['useremail'],
        'categoria' => $user['name_category'],
    ];
}

log_activity_ctx($link, 'EXPORT', [
    'entidad' => 'User',
    'entidad_id' => null,
    'descripcion' => 'Exportó listado de usuarios (' . $format . ', ' . count($rows) . ' registros)',
    'datos' => null,
]);

$filename_base = 'usuarios-' . date('Y-m-d');
(new ExportRenderer())->stream($format, 'Listado de Usuarios', $filename_base, $columns, $rows);
exit;
