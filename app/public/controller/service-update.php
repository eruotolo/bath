<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Service\UpdateService;
use App\Application\Service\SyncBathroomsToService;
use App\Infrastructure\Persistence\MysqliServiceRepository;

global $link;
include ('../layouts/config.php');

if (isset($_POST['update'])){
    $id_Servicio = (int) $_POST['id_Servicio'];

    $serviceRepository = new MysqliServiceRepository($link);
    $useCase = new UpdateService($serviceRepository);

    try {
        $useCase->handle($id_Servicio, $_POST);
        (new SyncBathroomsToService($serviceRepository))->handle($id_Servicio, $_POST['id_Bath'] ?? []);
        header('Location: ../dash-services.php?flash=success&msg=' . urlencode('Servicio actualizado correctamente'));
    } catch (\mysqli_sql_exception $e) {
        header('Location: ../dash-services.php?flash=error&msg=' . urlencode('No se pudo actualizar el servicio'));
    }

}
// Cerrar la conexión
$link->close();
