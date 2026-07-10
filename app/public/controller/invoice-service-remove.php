<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Invoice\RemoveAssignedService;
use App\Infrastructure\Persistence\MysqliInvoiceRepository;

require '../layouts/config.php';
global $link;

$id_Relacion = (int) $_GET['id_Relacion'];
$id_Factura = (int) $_GET['id_Factura'];

$useCase = new RemoveAssignedService(new MysqliInvoiceRepository($link));

try {
    $useCase->handle($id_Relacion);
    header("Location: ../dash-invoices-detail.php?id_Factura=$id_Factura");
} catch (\mysqli_sql_exception $e) {
    header("Location: ../index.php");
}

// Cerrar la conexión
$link->close();
