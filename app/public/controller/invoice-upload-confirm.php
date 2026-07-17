<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Domain\Invoice\Invoice;
use App\Infrastructure\Persistence\MysqliInvoiceRepository;

session_start();
include '../layouts/config.php';
global $link;

$invoiceRepository = new MysqliInvoiceRepository($link);

if (!isset($_SESSION['carga_facturas'])) {
    header('Location: ../dash-invoices-upload.php');
    exit();
}

$filas = $_SESSION['carga_facturas'];
$obras_seleccionadas = $_POST['obra'] ?? [];

$mensajes_error = [
    'rut_no_encontrado' => 'RUT no encontrado en Clientes',
    'numero_factura_vacio' => 'Falta el número de factura',
    'fecha_invalida' => 'Fecha inválida',
    'monto_invalido' => 'Monto inválido',
];

$cargadas = 0;
$rechazadas = [];

foreach ($filas as $indice => $fila) {
    if ($fila['error'] !== null) {
        $rechazadas[] = [
            'numero_Factura' => $fila['numero_Factura'],
            'motivo' => $mensajes_error[$fila['error']] ?? 'Error desconocido',
        ];
        continue;
    }

    $id_Contrato = isset($obras_seleccionadas[$indice]) ? intval($obras_seleccionadas[$indice]) : 0;

    if ($id_Contrato <= 0) {
        $rechazadas[] = [
            'numero_Factura' => $fila['numero_Factura'],
            'motivo' => 'No se seleccionó una obra',
        ];
        continue;
    }

    if ($invoiceRepository->existsByNumber($fila['numero_Factura'])) {
        $rechazadas[] = [
            'numero_Factura' => $fila['numero_Factura'],
            'motivo' => 'Ya existe una factura con ese número',
        ];
        continue;
    }

    $invoiceRepository->insert(new Invoice(
        id: null,
        customerId: (int) $fila['id_Cliente'],
        contractId: $id_Contrato,
        number: $fila['numero_Factura'],
        date: $fila['fecha_Factura'],
        value: (int) $fila['valor_Factura'],
        state: 1,
    ));

    $cargadas++;
}

$msg = "{$cargadas} factura(s) cargada(s) correctamente.";
if (count($rechazadas) > 0) {
    $detalle = implode('; ', array_map(fn($r) => "#{$r['numero_Factura']}: {$r['motivo']}", $rechazadas));
    $msg .= " " . count($rechazadas) . " fila(s) rechazada(s): {$detalle}";
}
$status = count($rechazadas) === 0 ? 'success' : ($cargadas > 0 ? 'warning' : 'error');

unset($_SESSION['carga_facturas']);

header('Location: ../dash-invoices-list.php?status=' . $status . '&msg=' . urlencode($msg));

$link->close();
