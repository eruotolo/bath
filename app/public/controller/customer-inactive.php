<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Customer\DeactivateCustomer;
use App\Infrastructure\Persistence\MysqliCustomerRepository;

require '../layouts/config.php';
global $link;

$id_Cliente = (int) $_GET['id_Cliente'];

$useCase = new DeactivateCustomer(new MysqliCustomerRepository($link));

try {
    $useCase->handle($id_Cliente);
    header("Location: ../dash-customers.php");
} catch (\mysqli_sql_exception $e) {
    header("Location: ../index.php");
}

// Cerrar la conexión
$link->close();
