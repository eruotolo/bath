<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Customer\UpdateCustomer;
use App\Infrastructure\Persistence\MysqliCustomerRepository;

session_start();
include ('../layouts/config.php');
require_once '../layouts/permissions.php';
require_once '../layouts/activity_logger.php';
global $link;

if (isset($_POST['update'])){
    $id_Cliente = (int) $_POST['idCliente'];
    require_permission('update', 'Customer', $id_Cliente);
    $rut = $_POST['rut_Cliente'] ?? '';

    $useCase = new UpdateCustomer(new MysqliCustomerRepository($link));

    try {
        $useCase->handle($id_Cliente, $_POST);
        log_activity_ctx($link, 'UPDATE', [
            'entidad' => 'Customer',
            'entidad_id' => $id_Cliente,
            'descripcion' => "Actualizó cliente id $id_Cliente (RUT $rut)",
            'datos' => $_POST,
        ]);
        header("Location: ../dash-customers-item.php?id_Cliente=$id_Cliente&status=success&msg=" . urlencode('Cliente actualizado correctamente'));
    } catch (\mysqli_sql_exception $e) {
        log_activity_ctx($link, 'UPDATE', [
            'entidad' => 'Customer',
            'entidad_id' => $id_Cliente,
            'descripcion' => "Error al actualizar cliente id $id_Cliente (RUT $rut)",
            'datos' => $_POST,
            'resultado' => 'error',
        ]);
        header('Location: ../dash-customers.php?status=error&msg=' . urlencode('No se pudo actualizar el cliente'));
    }
}else{
    header('Location: ../dash-customers.php?status=error&msg=' . urlencode('No se pudo actualizar el cliente'));
}
// Cerrar la conexión
$link->close();
