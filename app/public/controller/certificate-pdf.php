<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Certificate\FindCertificateForPrint;
use App\Infrastructure\Persistence\MysqliCertificateRepository;

global $link;
include('../layouts/config.php');
require_once('../assets/tcpdf/tcpdf.php');

$id_Certificado = isset($_GET['id_Certificado']) ? (int) $_GET['id_Certificado'] : 0;
$id_Contrato = isset($_GET['id_Contrato']) ? (int) $_GET['id_Contrato'] : 0;

if (!$id_Certificado || !$id_Contrato) {
    http_response_code(400);
    exit('Parámetros inválidos.');
}

$useCase = new FindCertificateForPrint(new MysqliCertificateRepository($link));
$row = $useCase->handle($id_Certificado, $id_Contrato);

if (!$row) {
    http_response_code(404);
    exit('Certificado no encontrado.');
}

$certificado = $row['certificado'];

$logo_zl = __DIR__ . '/../assets/images/logo_zl.png';
$logo_rc = __DIR__ . '/../assets/images/logo_rc.png';
$firma = __DIR__ . '/../assets/images/firma.jpeg';

// writeHTML() no resuelve <img src="{ruta absoluta filesystem}"> de forma confiable
// cuando corre vía PHP-FPM (funciona en CLI). Logos y firma se insertan con
// $pdf->Image() directamente en vez de embebidos en el HTML (mismo patrón que service-pdf.php).
$headerContent = '
    <div style="text-align: center; margin-top: 10px;">
        <h3 style="font-size: 13px;">Certificado de disposici&oacute;n final de residuos</h3>
        <p style="font-size: 10px;">NRO: ' . htmlspecialchars($certificado) . ' - Fecha: ' . date('d/m/Y', strtotime($row['fechahoy_Certificado'])) . '</p>
    </div>
    <p style="font-size: 9px; margin-top: 10px;">BLANCO SERVICIOS E INVERSIONES SPA, RUT 76.654.452-7, con domicilio en Nercon Alto S/N, Castro, Chilo&eacute;, deja constancia que realiz&oacute; una disposici&oacute;n final de residuos a la empresa:</p>
    <p style="font-size: 9px; line-height: 15px;">
        <b>Cliente</b>: ' . htmlspecialchars($row['nombre_Cliente']) . '<br>
        <b>Rut</b>: ' . htmlspecialchars($row['rut_Cliente']) . '<br>
        <b>Fecha del Servicio</b>: ' . htmlspecialchars($row['fecha_Servicio']) . '<br>
        <b>Cantidad metros c&uacute;bicos</b>: ' . htmlspecialchars($row['mts_Certificado']) . ' Mts<br>
        <b>Sector de Origen</b>: ' . htmlspecialchars($row['obra_Contrato']) . '
    </p>
    <p style="font-size: 9px;">Lo residuos retirados fueron trasladados y posteriormente tratados en la planta de tratamiento en Castro, de la empresa SURALIS, de acuerdo a contrato vigente.</p>
    <p style="font-size: 9px;">Los residuos fueron gestionados seg&uacute;n lo dispuesto por la legislaci&oacute;n chilena y a la normativa que regula este tipo de servicios. Resoluci&oacute;n sanitaria del Cami&oacute;n transportador Nro. 2310428351 de fecha 17/10/2023.</p>
    <p style="font-size: 9px;">Se extiende el presente documento, a petici&oacute;n del cliente para ser utilizada como respaldo frente fiscalizaci&oacute;n.</p>
';

$footerContent = '
    <hr>
    <table style="width: 100%; font-size: 8px;">
        <tr>
            <td style="width: 50%;">
                Blanco Servicios e Inversiones SPA<br>
                Servicios de disposici&oacute;n final
            </td>
            <td style="width: 50%;">
                RataCop Servicios e Inversiones SPA<br>
                Control de Plagas
            </td>
        </tr>
        <tr>
            <td colspan="2">Fonos: +56 9 9293 7371 | +56 9 2071 5438 | +56 652 630505 | email: jsanchez@expanda.cl</td>
        </tr>
    </table>
';

ob_start();
$pdf = new TCPDF();
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(15, 15, 15);
$pdf->AddPage();

// Logos lado a lado (reemplaza la tabla <img> que no renderizaba bajo PHP-FPM)
$pdf->Image($logo_zl, 15, 15, 25, 0, 'PNG');
$pdf->Image($logo_rc, 170, 15, 25, 0, 'PNG');
$pdf->SetY(max($pdf->GetY(), 45));

$pdf->writeHTML($headerContent, true, false, true, false, '');

$firmaY = $pdf->GetY() + 5;
$pdf->Image($firma, 85, $firmaY, 40, 0, 'JPEG');
$pdf->SetY($firmaY + 24);

$pdf->writeHTML($footerContent, true, false, true, false, '');
ob_end_clean();

$pdf->Output('certificado-' . $certificado . '.pdf', 'I');
