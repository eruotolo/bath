<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Contract\ListContracts;
use App\Infrastructure\Export\ExportRenderer;
use App\Infrastructure\Persistence\MysqliContractRepository;

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

$estado = null;
if (array_key_exists('estado', $_GET)) {
    $raw_estado = $_GET['estado'];
    if (!is_string($raw_estado) || !in_array($raw_estado, ['1', '2'], true)) {
        http_response_code(400);
        exit('Estado inválido. Use estado=1 o estado=2.');
    }
    $estado = (int) $raw_estado;
}

$allowed_sort_by = ['cliente', 'obra', 'estado'];
$sort_by = null;
if (array_key_exists('sort', $_GET)) {
    $raw_sort_by = $_GET['sort'];
    if (!is_string($raw_sort_by) || !in_array($raw_sort_by, $allowed_sort_by, true)) {
        http_response_code(400);
        exit('Orden inválido. Use sort=cliente, obra o estado.');
    }
    $sort_by = $raw_sort_by;
}

$allowed_sort_dir = ['ASC', 'DESC'];
$sort_dir = 'ASC';
if (array_key_exists('dir', $_GET)) {
    $raw_sort_dir = $_GET['dir'];
    if (!is_string($raw_sort_dir) || !in_array($raw_sort_dir, $allowed_sort_dir, true)) {
        http_response_code(400);
        exit('Dirección inválida. Use dir=ASC o dir=DESC.');
    }
    $sort_dir = $raw_sort_dir;
}

$listado = (new ListContracts(new MysqliContractRepository($link)))->handle(
    $estado,
    $sort_by ?? 'created_at',
    $sort_by !== null ? $sort_dir : 'DESC'
);
$contracts = $listado['items'];

$columns = [
    'cliente' => 'Cliente',
    'obra' => 'Obra',
    'estado' => 'Estado',
    'fecha_inicio' => 'Fecha de Inicio',
    'fecha_fin' => 'Fecha de Fin',
    'valor_mensual' => 'Valor Mensual',
    'valor_total' => 'Valor Total',
];
$rows = [];

foreach ($contracts as $contract) {
    $rows[] = [
        'cliente' => $contract['nombre_Cliente'],
        'obra' => $contract['obra_Contrato'],
        'estado' => (int) $contract['estado_Contrato'] === 2 ? 'Activo' : 'Terminada',
        'fecha_inicio' => format_fecha($contract['fechaInicio_Contrato']),
        'fecha_fin' => format_fecha($contract['fechaFin_Contrato']),
        'valor_mensual' => $contract['valorMensual_Contrato'],
        'valor_total' => $contract['valorTotal_Contrato'],
    ];
}

log_activity_ctx($link, 'EXPORT', [
    'entidad' => 'Contract',
    'entidad_id' => null,
    'descripcion' => 'Exportó listado de contratos (' . $format . ', ' . count($rows) . ' registros)',
    'datos' => null,
]);

$filename_base = 'contratos-' . date('Y-m-d');
(new ExportRenderer())->stream($format, 'Listado de Contratos', $filename_base, $columns, $rows);
exit;
