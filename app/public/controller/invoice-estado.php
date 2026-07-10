<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Invoice\SetInvoiceState;
use App\Infrastructure\Persistence\MysqliInvoiceRepository;

include ('../layouts/config.php');
global $link;

$id_Factura = (int) $_GET['id_Factura'];
$estado_Factura = (int) $_GET['estado_Factura'];

if (!in_array($estado_Factura, [1, 2, 3], true)) {
    $estado_Factura = 3;
}

$useCase = new SetInvoiceState(new MysqliInvoiceRepository($link));

try {
    $useCase->handle($id_Factura, $estado_Factura);
    header('Location: ../dash-invoices-list.php?status=success&msg=' . urlencode('Estado de la factura actualizado'));
} catch (\mysqli_sql_exception $e) {
    header('Location: ../dash-invoices-list.php?status=error&msg=' . urlencode('No se pudo actualizar el estado de la factura'));
}

$link->close();
