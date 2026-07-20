<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Invoice\RemoveAssignedService;
use App\Infrastructure\Persistence\MysqliInvoiceRepository;

require '../layouts/config.php';
require_once '../layouts/session.php';
require_once '../layouts/permissions.php';
require_once '../layouts/activity_logger.php';
global $link;

$id_Relacion = (int) $_GET['id_Relacion'];
$id_Factura = (int) $_GET['id_Factura'];
$origen = $_GET['origen'] ?? '';
require_permission('update', 'Invoice', $id_Factura);

$useCase = new RemoveAssignedService(new MysqliInvoiceRepository($link));

try {
    $useCase->handle($id_Relacion);
    log_activity_ctx($link, 'UPDATE', [
        'entidad' => 'Invoice',
        'entidad_id' => $id_Factura,
        'descripcion' => 'Quitó un servicio de factura (id ' . $id_Factura . ')',
        'datos' => $_GET,
    ]);
    if ($origen === 'edit-factura') {
        header("Location: ../dash-invoices-list.php?action=edit&id_Factura=$id_Factura");
    } else {
        header("Location: ../dash-invoices-detail.php?id_Factura=$id_Factura");
    }
} catch (\mysqli_sql_exception $e) {
    log_activity_ctx($link, 'UPDATE', [
        'entidad' => 'Invoice',
        'entidad_id' => $id_Factura,
        'descripcion' => 'No se pudo quitar el servicio de factura (id ' . $id_Factura . ')',
        'datos' => $_GET,
        'resultado' => 'error',
    ]);
    header("Location: ../index.php");
}

// Cerrar la conexión
$link->close();
