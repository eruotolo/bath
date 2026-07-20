<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Invoice\AssignServiceToInvoice;
use App\Infrastructure\Persistence\MysqliInvoiceRepository;

include '../layouts/config.php';
require_once '../layouts/session.php';
require_once '../layouts/permissions.php';
require_once '../layouts/activity_logger.php';
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
        log_activity_ctx($link, 'UPDATE', [
            'entidad' => 'Invoice',
            'entidad_id' => $id_Factura,
            'descripcion' => 'Agregó servicio ' . $id_Servicio . ' a factura (id ' . $id_Factura . ')',
            'datos' => $_POST,
        ]);
        if ($origen === 'edit-factura') {
            header("Location: ../dash-invoices-list.php?action=edit&id_Factura=$id_Factura");
        } else {
            header("Location: ../dash-invoices-detail.php?id_Factura=$id_Factura&id_Contrato=$id_Contrato");
        }
    } catch (\mysqli_sql_exception $e) {
        log_activity_ctx($link, 'UPDATE', [
            'entidad' => 'Invoice',
            'entidad_id' => $id_Factura,
            'descripcion' => 'No se pudo agregar el servicio ' . $id_Servicio . ' a factura (id ' . $id_Factura . ')',
            'datos' => $_POST,
            'resultado' => 'error',
        ]);
        header("../index.php");
    }
}
