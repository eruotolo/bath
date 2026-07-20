<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Service\FindServiceForPrint;
use App\Infrastructure\Persistence\MysqliServiceRepository;

global $link;
include('../layouts/config.php');
session_start();
require_once '../layouts/activity_logger.php';
require_once('../assets/tcpdf/tcpdf.php');

$id_Servicio = isset($_GET['id_Servicio']) ? (int) $_GET['id_Servicio'] : 0;

if (!$id_Servicio) {
    http_response_code(400);
    exit('Parámetros inválidos.');
}

$useCase = new FindServiceForPrint(new MysqliServiceRepository($link));
$impresion = $useCase->handle($id_Servicio);

if (!$impresion) {
    http_response_code(404);
    exit('Servicio no encontrado.');
}

$row = $impresion['service'];
$banosTratados = $impresion['bathrooms'];

$tipos = [
    'instalacion_Tipo' => 'Instalación',
    'reparacion_Tipo' => 'Reparación',
    'limpieza_Tipo' => 'Limpieza',
    'desinfeccion_Tipo' => 'Desinfección',
    'sanitizacion_Tipo' => 'Sanitización',
    'higienico_Tipo' => 'Entrega Papel Higiénico',
    'jabon_Tipo' => 'Entrega de Jabón Líquido',
    'otros_Tipo' => 'Otros Servicios',
    'retiro_Tipo' => 'Retiro de Baños',
];
$tiposActivos = [];
foreach ($tipos as $field => $label) {
    if (isset($row[$field]) && $row[$field] == 1) {
        $tiposActivos[] = $label;
    }
}

$logo_zl = __DIR__ . '/../assets/images/logo_zl.png';
$firma = __DIR__ . '/../assets/images/firma_01.png';

// writeHTML() no resuelve <img src="{ruta absoluta filesystem}"> de forma confiable
// cuando corre vía PHP-FPM (funciona en CLI). El logo y la firma se insertan con
// $pdf->Image() directamente en vez de embebidos en el HTML.
$headerContent = '
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="width: 70%;">&nbsp;</td>
            <td style="width: 30%; text-align: right;">
                <h4 style="font-size: 11px; font-weight: 600;">Servicios Blanco</h4>
                <p style="font-size: 8px; line-height: 12px;">jsanchez@expanda.cl</p>
                <p style="font-size: 8px; line-height: 12px;">+56 9 9293 7371</p>
            </td>
        </tr>
    </table>
';

$content = '
    <div style="text-align: center; margin-top: 10px; margin-bottom: 10px;">
        <h3 style="font-size: 15px;">COMPROBANTE DE EJECUCIÓN N&deg;: ' . (int) $row['nro_Servicio'] . '</h3>
    </div>
    <hr>
    <table style="width: 100%; border-collapse: collapse; margin-top: 8px;">
        <tr>
            <td style="width: 50%; vertical-align: top;">
                <h5 style="font-size: 10px; font-weight: 600;">Cliente:</h5>
                <p style="font-size: 9px; line-height: 13px;">' . htmlspecialchars($row['nombre_Cliente'], ENT_QUOTES, 'UTF-8') . '<br>
                ' . htmlspecialchars($row['direccion_Cliente'], ENT_QUOTES, 'UTF-8') . '<br>
                ' . htmlspecialchars($row['email_Cliente'], ENT_QUOTES, 'UTF-8') . '<br>
                +56 ' . htmlspecialchars($row['telefono_Cliente'], ENT_QUOTES, 'UTF-8') . '<br>
                ' . htmlspecialchars($row['ciudad_Cliente'], ENT_QUOTES, 'UTF-8') . ' | ' . htmlspecialchars($row['region_Cliente'], ENT_QUOTES, 'UTF-8') . '</p>
            </td>
            <td style="width: 50%; vertical-align: top;">
                <h5 style="font-size: 10px; font-weight: 600;">Información:</h5>
                <p style="font-size: 9px; line-height: 13px;">
                    <b>Obra:</b> ' . htmlspecialchars($row['obra_Contrato'], ENT_QUOTES, 'UTF-8') . '<br>
                    <b>Dirección de la obra:</b> ' . htmlspecialchars($row['direccion_Contrato'], ENT_QUOTES, 'UTF-8') . '<br>
                    <b>Fecha del Servicio:</b> ' . date('d/m/Y', strtotime($row['fecha_Servicio'])) . '<br>
                    <b>Tipo de Servicio:</b> ' . htmlspecialchars(implode(', ', $tiposActivos), ENT_QUOTES, 'UTF-8') . '<br>
                    <b>Baños Tratados:</b> ' . htmlspecialchars(implode(', ', array_column($banosTratados, 'codigo_Bath')), ENT_QUOTES, 'UTF-8') . '
                </p>
            </td>
        </tr>
    </table>
    <table style="width: 100%; border: 0.5px solid #dedfdf; margin-top: 8px;">
        <tr>
            <td style="padding: 4px;">
                <h5 style="font-size: 10px; font-weight: 600;">Observaciones Generales:</h5>
                <p style="font-size: 9px;">' . htmlspecialchars($row['observaciones_Servicio'] ?? '', ENT_QUOTES, 'UTF-8') . '</p>
            </td>
        </tr>
    </table>
';

$footerContent = '
    <hr>
    <table style="width: 100%; font-size: 8px; text-align: center;">
        <tr>
            <td style="width: 50%;">
                Blanco Servicios e Inversiones SPA<br>
                Servicios de disposición final
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

$pdf->Image($logo_zl, 15, 15, 20, 0, 'PNG');
$pdf->writeHTML($headerContent, true, false, true, false, '');
$pdf->SetY(max($pdf->GetY(), 42));

$pdf->writeHTML($content, true, false, true, false, '');

$firmaY = $pdf->GetY() + 5;
$pdf->Image($firma, 15, $firmaY, 180, 0, 'PNG');
$pdf->SetY($firmaY + 24);

$pdf->writeHTML($footerContent, true, false, true, false, '');
ob_end_clean();

log_activity_ctx($link, 'PDF', [
    'entidad' => 'Service',
    'entidad_id' => $id_Servicio,
    'descripcion' => 'Generó PDF de servicio N° ' . $row['nro_Servicio'],
    'datos' => null,
]);

$pdf->Output('servicio-' . $row['nro_Servicio'] . '.pdf', 'I');
