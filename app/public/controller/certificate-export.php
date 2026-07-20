<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Certificate\ListCertificates;
use App\Infrastructure\Export\ExportRenderer;
use App\Infrastructure\Persistence\MysqliCertificateRepository;

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

$listado = (new ListCertificates(new MysqliCertificateRepository($link)))->handle();
$certificates = $listado['items'];

$columns = [
    'numero_certificado' => 'Nro. Certificado',
    'cliente' => 'Cliente',
    'rut_cliente' => 'RUT Cliente',
    'obra' => 'Obra',
    'fecha_servicio' => 'Fecha del Servicio',
];
$rows = [];

foreach ($certificates as $certificate) {
    $rows[] = [
        'numero_certificado' => $certificate['certificado'],
        'cliente' => $certificate['nombre_Cliente'],
        'rut_cliente' => $certificate['rut_Cliente'],
        'obra' => $certificate['obra_Contrato'],
        'fecha_servicio' => format_fecha($certificate['fecha_Servicio']),
    ];
}

log_activity_ctx($link, 'EXPORT', [
    'entidad' => 'Certificate',
    'entidad_id' => null,
    'descripcion' => 'Exportó listado de certificados (' . $format . ', ' . count($rows) . ' registros)',
    'datos' => null,
]);

$filename_base = 'certificados-' . date('Y-m-d');
(new ExportRenderer())->stream($format, 'Listado de Certificados', $filename_base, $columns, $rows);
exit;
