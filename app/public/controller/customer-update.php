<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Customer\UpdateCustomer;
use App\Infrastructure\Persistence\MysqliCustomerRepository;

session_start();
include ('../layouts/config.php');
global $link;

if (isset($_POST['update'])){
    $id_Cliente = (int) $_POST['idCliente'];

    $useCase = new UpdateCustomer(new MysqliCustomerRepository($link));

    try {
        $useCase->handle($id_Cliente, $_POST);
        header("Location: ../dash-customers-item.php?id_Cliente=$id_Cliente&status=success&msg=" . urlencode('Cliente actualizado correctamente'));
    } catch (\mysqli_sql_exception $e) {
        header('Location: ../dash-customers.php?status=error&msg=' . urlencode('No se pudo actualizar el cliente'));
    }
}else{
    header('Location: ../dash-customers.php?status=error&msg=' . urlencode('No se pudo actualizar el cliente'));
}
// Cerrar la conexión
$link->close();
