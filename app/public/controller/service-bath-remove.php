<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Service\RemoveAssignedBathroom;
use App\Infrastructure\Persistence\MysqliServiceRepository;

require '../layouts/config.php';
global $link;

$id_Relacion = (int) $_GET['id_Relacion'];
$id_Servicio = (int) $_GET['id_Servicio'];

$useCase = new RemoveAssignedBathroom(new MysqliServiceRepository($link));

try {
    $useCase->handle($id_Relacion);
    header("Location: ../dash-services-bath.php?id_Servicio=$id_Servicio");
} catch (\mysqli_sql_exception $e) {
    header("Location: ../index.php");
}

// Cerrar la conexión
$link->close();
