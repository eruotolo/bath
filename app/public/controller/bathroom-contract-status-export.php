<?php

include('../layouts/session.php');
include('../layouts/permissions.php');
require_permission('export');

global $link;
include('../layouts/config.php');
require_once '../layouts/activity_logger.php';

$format = isset($_GET['format']) ? strtolower((string) $_GET['format']) : '';
if (!in_array($format, ['csv', 'pdf'], true)) {
    http_response_code(400);
    exit('Formato inválido. Use format=csv o format=pdf.');
}

$tab = isset($_GET['tab']) ? strtolower((string) $_GET['tab']) : '';
if (!in_array($tab, ['contratos', 'banos'], true)) {
    http_response_code(400);
    exit('Tab inválida. Use tab=contratos o tab=banos.');
}

if ($tab === 'contratos') {
    $titulo = 'Contratos Activos';
    $filenameBase = 'contratos-activos-' . date('Y-m-d');
    $query = "SELECT BT.codigo_Bath, CT.fechaInicio_Contrato,
                     CT.obra_Contrato, CL.nombre_Cliente
              FROM contratos CT
                  JOIN contrato_bathroom CB ON CB.id_Contrato = CT.id_Contrato
                  JOIN bathrooms BT ON BT.id_Bath = CB.id_Bath
                  JOIN clientes CL ON CL.id_Cliente = CT.id_Cliente
              WHERE CT.estado_Contrato = 2
                AND CT.fechaInicio_Contrato <= CURDATE()
              ORDER BY BT.codigo_Bath ASC";
} else {
    $titulo = 'Todos los Baños Disponibles';
    $filenameBase = 'banos-disponibles-' . date('Y-m-d');
    $query = "SELECT BT.codigo_Bath, BT.fechaCompra_Bath
              FROM bathrooms BT
              WHERE BT.estado_Bath = 1
                AND NOT EXISTS (
                    SELECT 1 FROM contrato_bathroom CB
                        JOIN contratos CT ON CT.id_Contrato = CB.id_Contrato
                    WHERE CB.id_Bath = BT.id_Bath
                      AND CT.estado_Contrato = 2
                      AND CT.fechaInicio_Contrato <= CURDATE()
                )
              ORDER BY BT.codigo_Bath ASC";
}

$result = mysqli_query($link, $query);
$rows = mysqli_fetch_all($result, MYSQLI_ASSOC);

log_activity_ctx($link, 'EXPORT', [
    'entidad' => ($tab === 'contratos') ? 'Contract' : 'Bathroom',
    'entidad_id' => null,
    'descripcion' => 'Exportó reporte "' . $titulo . '" (' . $format . ', ' . count($rows) . ' registros)',
    'datos' => null,
]);

if ($format === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filenameBase . '.csv"');

    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");

    if ($tab === 'contratos') {
        fputcsv($out, ['Código de Baño', 'Fecha de Inicio de Contrato', 'Nombre de la Obra', 'Nombre del Cliente'], ';');
        foreach ($rows as $r) {
            fputcsv($out, [$r['codigo_Bath'], $r['fechaInicio_Contrato'], $r['obra_Contrato'], $r['nombre_Cliente']], ';');
        }
    } else {
        fputcsv($out, ['Código del Baño', 'Fecha de Compra', 'Estado'], ';');
        foreach ($rows as $r) {
            fputcsv($out, [$r['codigo_Bath'], $r['fechaCompra_Bath'], 'Disponible'], ';');
        }
    }

    fclose($out);
    exit;
}

require_once('../assets/tcpdf/tcpdf.php');

$logo_zl = __DIR__ . '/../assets/images/logo_zl.png';
$logo_rc = __DIR__ . '/../assets/images/logo_rc.png';

$rows_html = '';
if ($tab === 'contratos') {
    foreach ($rows as $r) {
        $rows_html .= '<tr>
            <td>' . htmlspecialchars($r['codigo_Bath'], ENT_QUOTES, 'UTF-8') . '</td>
            <td>' . htmlspecialchars($r['fechaInicio_Contrato'], ENT_QUOTES, 'UTF-8') . '</td>
            <td>' . htmlspecialchars($r['obra_Contrato'], ENT_QUOTES, 'UTF-8') . '</td>
            <td>' . htmlspecialchars($r['nombre_Cliente'], ENT_QUOTES, 'UTF-8') . '</td>
        </tr>';
    }
    $headers_html = '
        <th style="font-size: 8px; text-align: left;">Código de Baño</th>
        <th style="font-size: 8px; text-align: left;">Fecha de Inicio de Contrato</th>
        <th style="font-size: 8px; text-align: left;">Nombre de la Obra</th>
        <th style="font-size: 8px; text-align: left;">Nombre del Cliente</th>
    ';
} else {
    foreach ($rows as $r) {
        $rows_html .= '<tr>
            <td>' . htmlspecialchars($r['codigo_Bath'], ENT_QUOTES, 'UTF-8') . '</td>
            <td>' . htmlspecialchars($r['fechaCompra_Bath'], ENT_QUOTES, 'UTF-8') . '</td>
            <td>Disponible</td>
        </tr>';
    }
    $headers_html = '
        <th style="font-size: 8px; text-align: left;">Código del Baño</th>
        <th style="font-size: 8px; text-align: left;">Fecha de Compra</th>
        <th style="font-size: 8px; text-align: left;">Estado</th>
    ';
}

$content = '
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="width: 50%;"><img src="' . $logo_zl . '" height="55"></td>
            <td style="width: 50%; text-align: right;"><img src="' . $logo_rc . '" height="55"></td>
        </tr>
    </table>
    <p style="font-size: 9px; margin-top: 4px;">Blanco Servicios e Inversiones Spa</p>
    <h3 style="font-size: 13px; margin-top: 8px;">' . htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') . ' &mdash; ' . count($rows) . ' registros</h3>
    <p style="font-size: 8px; color: #64748b;">Generado el ' . date('d/m/Y H:i') . '</p>
    <table style="width: 100%; border: 0.5px solid #cbd5e1; margin-top: 6px;" cellpadding="4" cellspacing="0">
        <thead>
            <tr style="background-color: #eef2ff; color: #4338ca;">
                ' . $headers_html . '
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
