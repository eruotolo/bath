<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Customer\FindCustomer;
use App\Infrastructure\Persistence\MysqliCustomerRepository;

session_start();
include ('../layouts/config.php');
global $link;

$id_Cliente = (int) $_POST['id_Cliente'];

$useCase = new FindCustomer(new MysqliCustomerRepository($link));
$customer = $useCase->handle($id_Cliente);

if ($customer !== null) {
    echo json_encode([
        'id_Cliente' => $customer->id,
        'rut_Cliente' => $customer->rut,
        'nombre_Cliente' => $customer->name,
        'telefono_Cliente' => $customer->phone,
        'email_Cliente' => $customer->email,
        'direccion_Cliente' => $customer->address,
        'comuna_Cliente' => $customer->commune,
        'ciudad_Cliente' => $customer->city,
        'region_Cliente' => $customer->region,
    ]);
}else {
    echo "No se encontraron datos para este cliente";
}
