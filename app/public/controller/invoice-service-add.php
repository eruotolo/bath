<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Invoice\AssignServiceToInvoice;
use App\Infrastructure\Persistence\MysqliInvoiceRepository;

include '../layouts/config.php';
global $link;

if(isset($_POST['update'])){
    $id_Factura = (int) $_POST['id_Factura'];
    $id_Servicio = (int) $_POST['id_Servicio'];
    $id_Contrato = (int) $_POST['id_Contrato'];

    $useCase = new AssignServiceToInvoice(new MysqliInvoiceRepository($link));

    try {
        $useCase->handle($id_Factura, $id_Servicio);
        header("Location: ../dash-invoices-detail.php?id_Factura=$id_Factura&id_Contrato=$id_Contrato");
    } catch (\mysqli_sql_exception $e) {
        header("../index.php");
    }
}
