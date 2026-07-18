<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Invoice\AssignServiceToInvoice;
use App\Infrastructure\Persistence\MysqliInvoiceRepository;

include '../layouts/config.php';
require_once '../layouts/session.php';
require_once '../layouts/permissions.php';
global $link;

if(isset($_POST['update'])){
    $id_Factura = (int) $_POST['id_Factura'];
    require_permission('update', 'Invoice', $id_Factura);
    $id_Servicio = (int) $_POST['id_Servicio'];
    $id_Contrato = (int) $_POST['id_Contrato'];
    $origen = $_POST['origen'] ?? '';

    $useCase = new AssignServiceToInvoice(new MysqliInvoiceRepository($link));

    try {
        $useCase->handle($id_Factura, $id_Servicio);
        if ($origen === 'edit-factura') {
            header("Location: ../dash-invoices-list.php?action=edit&id_Factura=$id_Factura");
        } else {
            header("Location: ../dash-invoices-detail.php?id_Factura=$id_Factura&id_Contrato=$id_Contrato");
        }
    } catch (\mysqli_sql_exception $e) {
        header("../index.php");
    }
}
