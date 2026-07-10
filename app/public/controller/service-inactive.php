<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Service\DeactivateService;
use App\Infrastructure\Persistence\MysqliServiceRepository;

require '../layouts/config.php';
global $link;

$id_Servicio = (int) $_GET['id_Servicio'];

$useCase = new DeactivateService(new MysqliServiceRepository($link));

try {
    $useCase->handle($id_Servicio);
    header("Location: ../dash-services.php");
} catch (\mysqli_sql_exception $e) {
    header("Location: ../index.php");
}

// Cerrar la conexión
$link->close();
