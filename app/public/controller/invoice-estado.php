<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Invoice\SetInvoiceState;
use App\Infrastructure\Persistence\MysqliInvoiceRepository;

include ('../layouts/config.php');
require_once '../layouts/session.php';
require_once '../layouts/permissions.php';
require_once '../layouts/activity_logger.php';
global $link;

$id_Factura = (int) $_GET['id_Factura'];
$estado_Factura = (int) $_GET['estado_Factura'];
require_permission('update', 'Invoice', $id_Factura);

if (!in_array($estado_Factura, [1, 2, 3], true)) {
    $estado_Factura = 3;
}

$useCase = new SetInvoiceState(new MysqliInvoiceRepository($link));

try {
    $useCase->handle($id_Factura, $estado_Factura);
    log_activity_ctx($link, 'STATE_CHANGE', [
        'entidad' => 'Invoice',
        'entidad_id' => $id_Factura,
        'descripcion' => 'Cambió estado de factura (id ' . $id_Factura . ') a ' . $estado_Factura,
        'datos' => $_GET,
    ]);
    header('Location: ../dash-invoices-list.php?status=success&msg=' . urlencode('Estado de la factura actualizado'));
} catch (\mysqli_sql_exception $e) {
    log_activity_ctx($link, 'STATE_CHANGE', [
        'entidad' => 'Invoice',
        'entidad_id' => $id_Factura,
        'descripcion' => 'No se pudo cambiar el estado de la factura (id ' . $id_Factura . ')',
        'datos' => $_GET,
        'resultado' => 'error',
    ]);
    header('Location: ../dash-invoices-list.php?status=error&msg=' . urlencode('No se pudo actualizar el estado de la factura'));
}

$link->close();
