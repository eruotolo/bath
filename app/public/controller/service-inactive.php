<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Service\DeactivateService;
use App\Infrastructure\Persistence\MysqliServiceRepository;

require '../layouts/config.php';
require_once '../layouts/session.php';
require_once '../layouts/permissions.php';
global $link;

$id_Servicio = (int) $_GET['id_Servicio'];
require_permission('update', 'Service', $id_Servicio);

$useCase = new DeactivateService(new MysqliServiceRepository($link));

try {
    $useCase->handle($id_Servicio);
    header("Location: ../dash-services.php");
} catch (\mysqli_sql_exception $e) {
    header("Location: ../index.php");
}

// Cerrar la conexión
$link->close();
