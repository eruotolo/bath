<?php

include('../layouts/session.php');

global $link;
include('../layouts/config.php');

$format = isset($_GET['format']) ? strtolower((string) $_GET['format']) : '';
if (!in_array($format, ['csv', 'pdf'], true)) {
    http_response_code(400);
    exit('Formato inválido. Use format=csv o format=pdf.');
}

$titulo = 'Histórico de Baños con Contratos';
$filenameBase = 'historico-banos-contratos-' . date('Y-m-d');

$query = "SELECT * FROM bathrooms BT
         JOIN contrato_bathroom CB ON BT.id_Bath = CB.id_Bath
         JOIN contratos CT ON CB.id_Contrato = CT.id_Contrato
        JOIN clientes CL ON CT.id_Cliente = CL.id_Cliente
         WHERE BT.estado_Bath = 1 ORDER BY fechaCompra_Bath DESC";
$result = mysqli_query($link, $query);
$rows = mysqli_fetch_all($result, MYSQLI_ASSOC);

if ($format === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filenameBase . '.csv"');

    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");

    fputcsv($out, ['Código', 'Fecha de Inicio de Contrato', 'Estado', 'Asignado a Obra', 'Nombre de Obra', 'Cliente'], ';');
    foreach ($rows as $r) {
        fputcsv($out, [
            $r['codigo_Bath'],
            $r['fechaInicio_Contrato'],
            $r['estado_Bath'] == 1 ? 'Activo' : 'Inactivo',
            $r['asignado_Bath'] == 0 ? 'Disponible' : 'Asignado',
            $r['obra_Contrato'],
            $r['nombre_Cliente'],
        ], ';');
    }

    fclose($out);
    exit;
}

require_once('../assets/tcpdf/tcpdf.php');

$logo_zl = __DIR__ . '/../assets/images/logo_zl.png';
$logo_rc = __DIR__ . '/../assets/images/logo_rc.png';

$rows_html = '';
foreach ($rows as $r) {
    $rows_html .= '<tr>
        <td>' . htmlspecialchars($r['codigo_Bath'], ENT_QUOTES, 'UTF-8') . '</td>
        <td>' . htmlspecialchars($r['fechaInicio_Contrato'], ENT_QUOTES, 'UTF-8') . '</td>
        <td>' . ($r['estado_Bath'] == 1 ? 'Activo' : 'Inactivo') . '</td>
        <td>' . ($r['asignado_Bath'] == 0 ? 'Disponible' : 'Asignado') . '</td>
        <td>' . htmlspecialchars($r['obra_Contrato'], ENT_QUOTES, 'UTF-8') . '</td>
        <td>' . htmlspecialchars($r['nombre_Cliente'], ENT_QUOTES, 'UTF-8') . '</td>
    </tr>';
}
$headers_html = '
    <th style="font-size: 8px; text-align: left;">Código</th>
    <th style="font-size: 8px; text-align: left;">Fecha de Inicio de Contrato</th>
    <th style="font-size: 8px; text-align: left;">Estado</th>
    <th style="font-size: 8px; text-align: left;">Asignado a Obra</th>
    <th style="font-size: 8px; text-align: left;">Nombre de Obra</th>
    <th style="font-size: 8px; text-align: left;">Cliente</th>
';

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
