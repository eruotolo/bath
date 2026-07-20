<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Customer\ListCustomers;
use App\Infrastructure\Persistence\MysqliCustomerRepository;

global $link;
include('../layouts/session.php');
include('../layouts/config.php');
include('../layouts/permissions.php');
require_permission('export');
require_once '../layouts/activity_logger.php';

$format = isset($_GET['format']) ? strtolower((string) $_GET['format']) : '';
if (!in_array($format, ['csv', 'pdf'], true)) {
    http_response_code(400);
    exit('Formato inválido. Use format=csv o format=pdf.');
}

$listado = (new ListCustomers(new MysqliCustomerRepository($link)))->handle();
$clientes = $listado['items'];

log_activity_ctx($link, 'EXPORT', [
    'entidad' => 'Customer',
    'entidad_id' => null,
    'descripcion' => 'Exportó listado de clientes (' . $format . ', ' . count($clientes) . ' registros)',
    'datos' => null,
]);

$filenameBase = 'clientes-' . date('Y-m-d');

if ($format === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filenameBase . '.csv"');

    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");

    fputcsv($out, ['ID', 'RUT', 'Nombre', 'Email', 'Teléfono', 'Dirección', 'Región', 'Ciudad', 'Comuna', 'Estado'], ';');

    foreach ($clientes as $c) {
        fputcsv($out, [
            (int) $c->id,
            $c->rut,
            $c->name,
            $c->email,
            $c->phone,
            $c->address,
            $c->region,
            $c->city,
            $c->commune,
            $c->active ? 'Activo' : 'Inactivo',
        ], ';');
    }

    fclose($out);
    exit;
}

require_once('../assets/tcpdf/tcpdf.php');

$logo_zl = __DIR__ . '/../assets/images/logo_zl.png';
$logo_rc = __DIR__ . '/../assets/images/logo_rc.png';

$rows_html = '';
foreach ($clientes as $c) {
    $rows_html .= '<tr>
        <td>' . htmlspecialchars((string) $c->id, ENT_QUOTES, 'UTF-8') . '</td>
        <td>' . htmlspecialchars($c->rut, ENT_QUOTES, 'UTF-8') . '</td>
        <td>' . htmlspecialchars($c->name, ENT_QUOTES, 'UTF-8') . '</td>
        <td>' . htmlspecialchars($c->phone, ENT_QUOTES, 'UTF-8') . '</td>
        <td>' . htmlspecialchars($c->email, ENT_QUOTES, 'UTF-8') . '</td>
        <td>' . htmlspecialchars($c->commune, ENT_QUOTES, 'UTF-8') . '</td>
        <td>' . htmlspecialchars($c->city, ENT_QUOTES, 'UTF-8') . '</td>
        <td>' . ($c->active ? 'Activo' : 'Inactivo') . '</td>
    </tr>';
}

$content = '
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="width: 50%;"><img src="' . $logo_zl . '" height="55"></td>
            <td style="width: 50%; text-align: right;"><img src="' . $logo_rc . '" height="55"></td>
        </tr>
    </table>
    <p style="font-size: 9px; margin-top: 4px;">Blanco Servicios e Inversiones Spa</p>
    <h3 style="font-size: 13px; margin-top: 8px;">Listado de Clientes &mdash; ' . count($clientes) . ' registros</h3>
    <p style="font-size: 8px; color: #64748b;">Generado el ' . date('d/m/Y H:i') . '</p>
    <table style="width: 100%; border: 0.5px solid #cbd5e1; margin-top: 6px;" cellpadding="4" cellspacing="0">
        <thead>
            <tr style="background-color: #eef2ff; color: #4338ca;">
                <th style="font-size: 8px; text-align: left;">ID</th>
                <th style="font-size: 8px; text-align: left;">RUT</th>
                <th style="font-size: 8px; text-align: left;">Nombre</th>
                <th style="font-size: 8px; text-align: left;">Tel&eacute;fono</th>
                <th style="font-size: 8px; text-align: left;">Email</th>
                <th style="font-size: 8px; text-align: left;">Comuna</th>
                <th style="font-size: 8px; text-align: left;">Ciudad</th>
                <th style="font-size: 8px; text-align: left;">Estado</th>
            </tr>
        </thead>
        <tbody style="font-size: 8px;">
            ' . $rows_html . '
        </tbody>
    </table>
';

ob_start();
$pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(12, 12, 12);
$pdf->SetAutoPageBreak(true, 12);
$pdf->AddPage();
$pdf->writeHTML($content, true, false, true, false, '');
ob_end_clean();

$pdf->Output($filenameBase . '.pdf', 'D');
