<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Invoice\SetInvoiceState;
use App\Infrastructure\Persistence\MysqliInvoiceRepository;

include "../layouts/config.php";
require_once '../layouts/session.php';
require_once '../layouts/permissions.php';
global $link;

$id_Factura = (int) $_GET['id_Factura'];
require_permission('delete', 'Invoice', $id_Factura);

$useCase = new SetInvoiceState(new MysqliInvoiceRepository($link));

try {
    $useCase->handle($id_Factura, 3);
    header('Location: ../dash-invoices-list.php?status=success&msg=' . urlencode('Factura anulada correctamente'));
} catch (\mysqli_sql_exception $e) {
    header('Location: ../dash-invoices-list.php?status=error&msg=' . urlencode('No se pudo anular la factura'));
}

$link->close();
