<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Invoice\FindInvoiceWithCustomerAndContract;
use App\Application\Invoice\ListAssignedServices;
use App\Infrastructure\Persistence\MysqliInvoiceRepository;

global $link;
include('../layouts/config.php');
require_once('../assets/tcpdf/tcpdf.php');

$id_Factura = isset($_GET['id_Factura']) ? (int) $_GET['id_Factura'] : 0;
$id_Contrato = isset($_GET['id_Contrato']) ? (int) $_GET['id_Contrato'] : 0;

if (!$id_Factura || !$id_Contrato) {
    http_response_code(400);
    exit('Parámetros inválidos.');
}

$repository = new MysqliInvoiceRepository($link);
$row = (new FindInvoiceWithCustomerAndContract($repository))->handle($id_Factura, $id_Contrato);

if (!$row) {
    http_response_code(404);
    exit('Factura no encontrada.');
}

$servicios = (new ListAssignedServices($repository))->handle($id_Factura);

$rows_html = '';
foreach ($servicios as $s) {
    $rows_html .= '<tr>
        <td>' . htmlspecialchars($s['nro_Servicio']) . '</td>
        <td>' . date('d/m/Y', strtotime($s['fecha_Servicio'])) . '</td>
        <td>' . htmlspecialchars($s['observaciones_Servicio']) . '</td>
    </tr>';
}

$logo_zl = __DIR__ . '/../assets/images/logo_zl.png';
$logo_rc = __DIR__ . '/../assets/images/logo_rc.png';

$content = '
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="width: 50%;"><img src="' . $logo_zl . '" height="60"></td>
            <td style="width: 50%; text-align: right;"><img src="' . $logo_rc . '" height="60"></td>
        </tr>
    </table>
    <p style="font-size: 9px; margin-top: 4px;">Blanco Servicios e Inversiones Spa<br>
    jsanchez@expanda.cl &nbsp;|&nbsp; +56 9 9293 7371</p>
    <h3 style="text-align: right; font-size: 14px;">N&deg; DE FACTURA: ' . htmlspecialchars($row['numero_Factura']) . '</h3>
    <hr>
    <table style="width: 100%;">
        <tr>
            <td style="width: 50%; vertical-align: top;">
                <h4 style="font-size: 11px;">Factura a:</h4>
                <p style="font-size: 9px; line-height: 13px;">
                    <b>' . htmlspecialchars($row['nombre_Cliente']) . '</b><br>
                    RUT: ' . htmlspecialchars($row['rut_Cliente']) . '<br>
                    Dir: ' . htmlspecialchars($row['direccion_Cliente']) . '<br>
                    Email: ' . htmlspecialchars($row['email_Cliente']) . '<br>
                    Tel&eacute;fono: ' . htmlspecialchars($row['telefono_Cliente']) . '
                </p>
            </td>
            <td style="width: 50%; vertical-align: top;">
                <h4 style="font-size: 11px;">Fecha de factura:</h4>
                <p style="font-size: 9px;">' . date('d/m/Y', strtotime($row['fecha_Factura'])) . '</p>
                <h4 style="font-size: 11px;">Contrato:</h4>
                <p style="font-size: 9px; line-height: 13px;">
                    ' . htmlspecialchars($row['obra_Contrato']) . '<br>
                    Dir: ' . htmlspecialchars($row['direccion_Contrato']) . '
                </p>
            </td>
        </tr>
    </table>
    <h4 style="font-size: 11px; margin-top: 10px;">Detalle de Factura:</h4>
    <table style="width: 100%; border: 0.5px solid #dedfdf;" cellpadding="4">
        <thead>
            <tr style="background-color: #f8f9fa;">
                <th style="font-size: 9px;">N&uacute;mero de Servicio</th>
                <th style="font-size: 9px;">Fecha de Servicio</th>
                <th style="font-size: 9px;">Observaciones</th>
            </tr>
        </thead>
        <tbody style="font-size: 9px;">
            ' . $rows_html . '
        </tbody>
    </table>
    <h3 style="text-align: right; margin-top: 10px;">Total: $' . number_format($row['valor_Factura'], 0, ',', '.') . '</h3>
';

ob_start();
$pdf = new TCPDF();
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(15, 15, 15);
$pdf->AddPage();
$pdf->writeHTML($content, true, false, true, false, '');
ob_end_clean();

$pdf->Output('factura-' . $row['numero_Factura'] . '.pdf', 'I');
