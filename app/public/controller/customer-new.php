<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Customer\CreateCustomer;
use App\Infrastructure\Persistence\MysqliCustomerRepository;

global $link;
include ('../layouts/config.php');
require_once '../layouts/session.php';
require_once '../layouts/permissions.php';
require_permission('create', 'Customer');

if (isset($_POST['crear'])){
    $useCase = new CreateCustomer(new MysqliCustomerRepository($link));

    try {
        $useCase->handle($_POST);
        header('Location: ../dash-customers.php?status=success&msg=' . urlencode('Cliente creado correctamente'));
    } catch (\mysqli_sql_exception $e) {
        header('Location: ../dash-customers-add.php?status=error&msg=' . urlencode('No se pudo crear el cliente'));
    }

}else{
    header('Location: ../dash-customers-add.php?status=error&msg=' . urlencode('No se pudo crear el cliente'));
}
// Cerrar la conexión
$link->close();
