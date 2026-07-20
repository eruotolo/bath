<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Customer\CreateCustomer;
use App\Infrastructure\Persistence\MysqliCustomerRepository;

global $link;
include ('../layouts/config.php');
require_once '../layouts/session.php';
require_once '../layouts/permissions.php';
require_once '../layouts/activity_logger.php';
require_permission('create', 'Customer');

if (isset($_POST['crear'])){
    $rut = $_POST['rut_Cliente'] ?? '';
    $useCase = new CreateCustomer(new MysqliCustomerRepository($link));

    try {
        $id = $useCase->handle($_POST);
        log_activity_ctx($link, 'CREATE', [
            'entidad' => 'Customer',
            'entidad_id' => $id,
            'descripcion' => "Creó cliente id $id (RUT $rut)",
            'datos' => $_POST,
        ]);
        header('Location: ../dash-customers.php?status=success&msg=' . urlencode('Cliente creado correctamente'));
    } catch (\mysqli_sql_exception $e) {
        log_activity_ctx($link, 'CREATE', [
            'entidad' => 'Customer',
            'descripcion' => "Error al crear cliente RUT $rut",
            'datos' => $_POST,
            'resultado' => 'error',
        ]);
        header('Location: ../dash-customers-add.php?status=error&msg=' . urlencode('No se pudo crear el cliente'));
    }

}else{
    header('Location: ../dash-customers-add.php?status=error&msg=' . urlencode('No se pudo crear el cliente'));
}
// Cerrar la conexión
$link->close();
