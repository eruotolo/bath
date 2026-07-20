<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Invoice\ListInvoices;
use App\Infrastructure\Export\ExportRenderer;
use App\Infrastructure\Persistence\MysqliInvoiceRepository;

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

$allowed_filters = ['todas', 'pagadas', 'pendientes'];
$raw_filter = $_GET['filter'] ?? 'todas';
$filter = in_array($raw_filter, $allowed_filters, true) ? $raw_filter : 'todas';

$invoices = (new ListInvoices(new MysqliInvoiceRepository($link)))->handle();
$filtered_invoices = [];

foreach ($invoices as $invoice) {
    $estado = (int) $invoice['estado_Factura'];
    if ($filter === 'pagadas') {
        $keep = $estado === 2;
    } elseif ($filter === 'pendientes') {
        $keep = $estado === 1;
    } else {
        $keep = true;
    }

    if ($keep) {
        $filtered_invoices[] = $invoice;
    }
}

$columns = [
    'numero_factura' => 'Nro. Factura',
    'fecha' => 'Fecha',
    'cliente' => 'Cliente',
    'obra' => 'Obra',
    'monto' => 'Monto',
    'estado' => 'Estado',
    'fecha_pago' => 'Fecha de Pago',
];
$rows = [];

foreach ($filtered_invoices as $invoice) {
    $estado = match ((int) $invoice['estado_Factura']) {
        1 => 'Pendiente',
        2 => 'Pagado',
        3 => 'Anulado',
        default => 'Anulado',
    };
    $fecha_pago = $invoice['fecha_Pago'] ? format_fecha($invoice['fecha_Pago']) : '—';

    $rows[] = [
        'numero_factura' => $invoice['numero_Factura'],
        'fecha' => format_fecha($invoice['fecha_Factura']),
        'cliente' => $invoice['nombre_Cliente'],
        'obra' => $invoice['obra_Contrato'] ?? '—',
        'monto' => $invoice['valor_Factura'],
        'estado' => $estado,
        'fecha_pago' => $fecha_pago,
    ];
}

log_activity_ctx($link, 'EXPORT', [
    'entidad' => 'Invoice',
    'entidad_id' => null,
    'descripcion' => 'Exportó listado de facturas (' . $format . ', filter=' . $filter . ', ' . count($rows) . ' registros)',
    'datos' => null,
]);

$filename_base = 'facturas-' . date('Y-m-d');
(new ExportRenderer())->stream($format, 'Listado de Facturas', $filename_base, $columns, $rows);
exit;
