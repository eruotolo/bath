<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Invoice\UpdateInvoice;
use App\Infrastructure\Persistence\MysqliInvoiceRepository;

include '../layouts/config.php';
global $link;

if (isset($_POST['editar'])) {
    $id_Factura = (int) $_POST['id_Factura'];

    $useCase = new UpdateInvoice(new MysqliInvoiceRepository($link));

    try {
        $useCase->handle($id_Factura, $_POST);
        header("Location: ../dash-invoices-list.php");
    } catch (\mysqli_sql_exception $e) {
        header("Location: ../index.php");
    }
} else {
    header("Location: ../index.php");
}
$link->close();
