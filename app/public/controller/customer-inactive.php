<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Customer\DeactivateCustomer;
use App\Infrastructure\Persistence\MysqliCustomerRepository;

require '../layouts/config.php';
require_once '../layouts/session.php';
require_once '../layouts/permissions.php';
global $link;

$id_Cliente = (int) $_GET['id_Cliente'];
require_permission('update', 'Customer', $id_Cliente);

$useCase = new DeactivateCustomer(new MysqliCustomerRepository($link));

try {
    $useCase->handle($id_Cliente);
    header("Location: ../dash-customers.php");
} catch (\mysqli_sql_exception $e) {
    header("Location: ../index.php");
}

// Cerrar la conexión
$link->close();
