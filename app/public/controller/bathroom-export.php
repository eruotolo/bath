<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Bathroom\ListBathroomsWithAssignment;
use App\Infrastructure\Export\ExportRenderer;
use App\Infrastructure\Persistence\MysqliBathroomRepository;

global $link;
include('../layouts/session.php');
include('../layouts/config.php');
include('../layouts/helpers.php');
include('../layouts/permissions.php');
require_permission('export');
require_once '../layouts/activity_logger.php';

$format = isset($_GET['format']) && is_string($_GET['format']) ? strtolower($_GET['format']) : '';
if (!in_array($format, ['csv', 'pdf'], true)) {
    http_response_code(400);
    exit('Formato inválido. Use format=csv o format=pdf.');
}

$listado = (new ListBathroomsWithAssignment(new MysqliBathroomRepository($link)))->handle();
$bathrooms = $listado['items'];

$columns = [
    'codigo' => 'Codigo',
    'fecha_adquisicion' => 'F. Adquisicion',
    'ubicacion_actual' => 'Ubicacion Actual',
    'observaciones' => 'Observaciones',
    'estado_tecnico' => 'Estado Tecnico',
];
$rows = [];

foreach ($bathrooms as $bathroom) {
    $has_assignment = $bathroom['obra_Contrato'] !== null && $bathroom['nombre_Cliente'] !== null;
    $ubicacion_actual = $has_assignment
        ? $bathroom['obra_Contrato'] . ' (' . $bathroom['nombre_Cliente'] . ')'
        : 'Sin asignar';

    $rows[] = [
        'codigo' => $bathroom['codigo_Bath'],
        'fecha_adquisicion' => format_fecha($bathroom['fechaCompra_Bath']),
        'ubicacion_actual' => $ubicacion_actual,
        'observaciones' => $bathroom['observacion_Bath'],
        'estado_tecnico' => bath_estado_tecnico((int) $bathroom['estado_Bath'])['label'],
    ];
}

log_activity_ctx($link, 'EXPORT', [
    'entidad' => 'Bathroom',
    'entidad_id' => null,
    'descripcion' => 'Exportó inventario de baños (' . $format . ', ' . count($rows) . ' registros)',
    'datos' => null,
]);

$filename_base = 'banos-' . date('Y-m-d');
(new ExportRenderer())->stream($format, 'Inventario de Baños', $filename_base, $columns, $rows);
exit;
