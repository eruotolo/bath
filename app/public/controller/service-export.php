<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Service\ListServices;
use App\Infrastructure\Export\ExportRenderer;
use App\Infrastructure\Persistence\MysqliServiceRepository;

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

$allowed_filters = ['todos', 'facturados', 'no-facturados'];
$raw_filter = $_GET['filter'] ?? 'todos';
$filter = in_array($raw_filter, $allowed_filters, true) ? $raw_filter : 'todos';

$services = (new ListServices(new MysqliServiceRepository($link)))->handle();
$filtered_services = [];

foreach ($services as $service) {
    $facturado = (int) ($service['facturado'] ?? 0);
    if ($filter === 'facturados') {
        $keep = $facturado === 1;
    } elseif ($filter === 'no-facturados') {
        $keep = $facturado === 0;
    } else {
        $keep = true;
    }

    if ($keep) {
        $filtered_services[] = $service;
    }
}

$columns = [
    'numero_servicio' => 'Numero de Servicio',
    'cliente' => 'Cliente',
    'obra' => 'Obra',
    'factura' => 'Factura',
    'fecha' => 'Fecha',
];
$rows = [];

foreach ($filtered_services as $service) {
    $facturado = (int) ($service['facturado'] ?? 0);
    $factura = $facturado === 1
        ? 'Si — ' . (string) ($service['numero_Factura'] ?? '')
        : 'No';

    $rows[] = [
        'numero_servicio' => $service['nro_Servicio'],
        'cliente' => $service['nombre_Cliente'],
        'obra' => $service['obra_Contrato'],
        'factura' => $factura,
        'fecha' => format_fecha($service['fecha_Servicio']),
    ];
}

log_activity_ctx($link, 'EXPORT', [
    'entidad' => 'Service',
    'entidad_id' => null,
    'descripcion' => 'Exportó listado de servicios (' . $format . ', filter=' . $filter . ', ' . count($rows) . ' registros)',
    'datos' => null,
]);

$filename_base = 'servicios-' . date('Y-m-d');
(new ExportRenderer())->stream($format, 'Listado de Servicios', $filename_base, $columns, $rows);
exit;
