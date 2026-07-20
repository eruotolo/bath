<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Invoice\SetInvoicePaymentDate;
use App\Infrastructure\Persistence\MysqliInvoiceRepository;

include '../layouts/config.php';
require_once '../layouts/session.php';
require_once '../layouts/permissions.php';
require_once '../layouts/activity_logger.php';
global $link;

$id_Factura = (int) $_POST['id_Factura'];
require_permission('update', 'Invoice', $id_Factura);
$fecha_Pago = trim($_POST['fecha_Pago']);

if ($fecha_Pago !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_Pago)) {
    header("Location: ../dash-invoices-list.php");
    exit();
}

$useCase = new SetInvoicePaymentDate(new MysqliInvoiceRepository($link));
$useCase->handle($id_Factura, $fecha_Pago === '' ? null : $fecha_Pago);

log_activity_ctx($link, 'STATE_CHANGE', [
    'entidad' => 'Invoice',
    'entidad_id' => $id_Factura,
    'descripcion' => 'Registró fecha de pago de factura (id ' . $id_Factura . ')',
    'datos' => $_POST,
]);

header("Location: ../dash-invoices-list.php");
$link->close();
