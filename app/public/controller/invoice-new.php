<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Invoice\CreateInvoice;
use App\Infrastructure\Persistence\MysqliInvoiceRepository;

include '../layouts/config.php';
global $link;

if(isset($_POST['crear'])){
    $id_Contrato = (int) $_POST['id_Contrato'];

    $useCase = new CreateInvoice(new MysqliInvoiceRepository($link));

    try {
        $id_factura_creada = $useCase->handle($_POST);
        header("Location: ../dash-invoices-detail.php?id_Factura=$id_factura_creada&id_Contrato=$id_Contrato");
    } catch (\mysqli_sql_exception $e) {
        header("Location: ../index.php");
    }
}else{
    header("Location: ../index.php");
}
$link->close();
