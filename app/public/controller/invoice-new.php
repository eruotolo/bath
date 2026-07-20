<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Invoice\AssignServiceToInvoice;
use App\Application\Invoice\CreateInvoice;
use App\Infrastructure\Persistence\MysqliInvoiceRepository;

include '../layouts/config.php';
require_once '../layouts/session.php';
require_once '../layouts/permissions.php';
require_once '../layouts/activity_logger.php';
global $link;
require_permission('create', 'Invoice');

if(isset($_POST['crear'])){
    $input = $_POST;
    $input['valor_Factura'] = str_replace('.', '', trim((string) ($input['valor_Factura'] ?? '')));

    $invoiceRepository = new MysqliInvoiceRepository($link);
    $useCase = new CreateInvoice($invoiceRepository);

    try {
        $id_factura_creada = $useCase->handle($input);

        $serviciosSeleccionados = array_filter(array_map('intval', $_POST['servicios'] ?? []));
        $assignUseCase = new AssignServiceToInvoice($invoiceRepository);
        foreach ($serviciosSeleccionados as $id_Servicio) {
            $assignUseCase->handle($id_factura_creada, $id_Servicio);
        }

        log_activity_ctx($link, 'CREATE', [
            'entidad' => 'Invoice',
            'entidad_id' => $id_factura_creada,
            'descripcion' => 'Creó factura' . (isset($_POST['numero_Factura']) ? ' N° ' . $_POST['numero_Factura'] : ''),
            'datos' => $_POST,
        ]);

        header("Location: ../dash-invoices-list.php");
    } catch (\mysqli_sql_exception $e) {
        log_activity_ctx($link, 'CREATE', [
            'entidad' => 'Invoice',
            'entidad_id' => $id_factura_creada ?? null,
            'descripcion' => 'No se pudo crear la factura',
            'datos' => $_POST,
            'resultado' => 'error',
        ]);
        header("Location: ../index.php");
    }
}else{
    header("Location: ../index.php");
}
$link->close();
