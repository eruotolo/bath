<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Invoice\FindInvoice;
use App\Application\Invoice\UpdateInvoice;
use App\Infrastructure\Persistence\MysqliInvoiceRepository;

include '../layouts/config.php';
require_once '../layouts/session.php';
require_once '../layouts/permissions.php';
global $link;

if (isset($_POST['editar'])) {
    $id_Factura = (int) $_POST['id_Factura'];
    require_permission('update', 'Invoice', $id_Factura);
    $invoiceRepository = new MysqliInvoiceRepository($link);

    // Una factura pagada no se puede editar (mismo criterio que el drawer):
    // se revalida aca por si llega un POST directo sin pasar por la UI.
    $facturaActual = (new FindInvoice($invoiceRepository))->handle($id_Factura);
    if ($facturaActual === null || $facturaActual->state === 2) {
        header("Location: ../dash-invoices-list.php");
        $link->close();
        exit();
    }

    $input = $_POST;
    $input['valor_Factura'] = str_replace('.', '', trim((string) ($input['valor_Factura'] ?? '')));

    $useCase = new UpdateInvoice($invoiceRepository);

    try {
        $useCase->handle($id_Factura, $input);
        header("Location: ../dash-invoices-list.php");
    } catch (\mysqli_sql_exception $e) {
        header("Location: ../index.php");
    }
} else {
    header("Location: ../index.php");
}
$link->close();
