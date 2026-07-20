<?php

declare(strict_types=1);

namespace App\Infrastructure\Export;

/**
 * Streams normalized export data in the formats used by the listing reports.
 *
 * Controllers are responsible for validating the request, authorizing it and
 * normalizing every row to the column keys passed to this renderer.
 */
final class ExportRenderer
{
    /**
     * @param array<string, string> $columns Ordered key => label pairs.
     * @param array<int, array<string, scalar|null>> $rows
     */
    public function stream(string $format, string $title, string $filename_base, array $columns, array $rows): void
    {
        if ($format === 'csv') {
            $this->stream_csv($filename_base, $columns, $rows);

            return;
        }

        if ($format === 'pdf') {
            $this->stream_pdf($title, $filename_base, $columns, $rows);

            return;
        }

        throw new \InvalidArgumentException('Formato de exportación no soportado.');
    }

    /**
     * @param array<string, string> $columns Ordered key => label pairs.
     * @param array<int, array<string, scalar|null>> $rows
     */
    public function stream_csv(string $filename_base, array $columns, array $rows): void
    {
        $filename_base = $this->sanitize_filename_base($filename_base);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename_base . '.csv"');

        $output = fopen('php://output', 'w');
        if ($output === false) {
            throw new \RuntimeException('No se pudo abrir el stream de salida CSV.');
        }

        fwrite($output, "\xEF\xBB\xBF");
        fputcsv($output, array_values($columns), ';');

        foreach ($rows as $row) {
            $values = [];
            foreach ($columns as $key => $_label) {
                $values[] = $this->csv_value($row[$key] ?? null);
            }
            fputcsv($output, $values, ';');
        }

        fclose($output);
    }

    /**
     * @param array<string, string> $columns Ordered key => label pairs.
     * @param array<int, array<string, scalar|null>> $rows
     */
    public function stream_pdf(string $title, string $filename_base, array $columns, array $rows): void
    {
        $this->load_tcpdf();

        $filename_base = $this->sanitize_filename_base($filename_base);
        $headers_html = '';
        foreach ($columns as $label) {
            $headers_html .= '<th style="font-size: 8px; text-align: left;">'
                . $this->escape_html($label)
                . '</th>';
        }

        $rows_html = '';
        foreach ($rows as $row) {
            $rows_html .= '<tr>';
            foreach ($columns as $key => $_label) {
                $rows_html .= '<td>' . $this->escape_html($row[$key] ?? null) . '</td>';
            }
            $rows_html .= '</tr>';
        }

        $content = $this->institutional_header_html()
            . '<h3 style="font-size: 13px; margin-top: 8px;">'
            . $this->escape_html($title) . ' &mdash; ' . count($rows) . ' registros</h3>
            <p style="font-size: 8px; color: #64748b;">Generado el ' . date('d/m/Y H:i') . '</p>
            <table style="width: 100%; border: 0.5px solid #cbd5e1; margin-top: 6px;" cellpadding="4" cellspacing="0">
                <thead>
                    <tr style="background-color: #eef2ff; color: #4338ca;">'
                    . $headers_html . '
                    </tr>
                </thead>
                <tbody style="font-size: 8px;">'
                    . $rows_html . '
                </tbody>
            </table>';

        ob_start();
        $pdf = new \TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetCreator('Bathroom');
        $pdf->SetAuthor('Blanco Servicios e Inversiones Spa');
        $pdf->SetTitle($title);
        $pdf->SetMargins(12, 12, 12);
        $pdf->SetAutoPageBreak(true, 12);
        $pdf->AddPage();
        $pdf->writeHTML($content, true, false, true, false, '');
        ob_end_clean();

        $pdf->Output($filename_base . '.pdf', 'D');
    }

    private function load_tcpdf(): void
    {
        if (class_exists(\TCPDF::class)) {
            return;
        }

        $application_root = dirname(__DIR__, 3);
        $tcpdf_paths = [
            $application_root . '/html/assets/tcpdf/tcpdf.php',
            $application_root . '/public/assets/tcpdf/tcpdf.php',
        ];

        foreach ($tcpdf_paths as $tcpdf_path) {
            if (is_file($tcpdf_path)) {
                require_once $tcpdf_path;

                return;
            }
        }

        throw new \RuntimeException('No se encontró la instalación vendorizada de TCPDF.');
    }

    private function sanitize_filename_base(string $filename_base): string
    {
        $filename_base = preg_replace('/[^A-Za-z0-9._-]+/', '-', $filename_base) ?? '';
        $filename_base = trim($filename_base, '.-');

        return $filename_base !== '' ? $filename_base : 'export';
    }

    private function institutional_header_html(): string
    {
        $logo_zl = $this->public_asset_path('images/logo_zl.png');
        $logo_rc = $this->public_asset_path('images/logo_rc.png');

        return '
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="width: 50%;"><img src="' . $this->escape_html($logo_zl) . '" height="55"></td>
                    <td style="width: 50%; text-align: right;"><img src="' . $this->escape_html($logo_rc) . '" height="55"></td>
                </tr>
            </table>
            <p style="font-size: 9px; margin-top: 4px;">Blanco Servicios e Inversiones Spa</p>';
    }

    private function public_asset_path(string $asset): string
    {
        $application_root = dirname(__DIR__, 3);
        $asset_paths = [
            $application_root . '/html/assets/' . $asset,
            $application_root . '/public/assets/' . $asset,
        ];

        foreach ($asset_paths as $asset_path) {
            if (is_file($asset_path)) {
                return $asset_path;
            }
        }

        throw new \RuntimeException('No se encontró el recurso institucional de exportación.');
    }

    private function csv_value(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        return (string) $value;
    }

    private function escape_html(mixed $value): string
    {
        return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}
