<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Service\UpdateService;
use App\Infrastructure\Persistence\MysqliServiceRepository;

global $link;
include ('../layouts/config.php');

if (isset($_POST['update'])){
    $id_Servicio = (int) $_POST['id_Servicio'];

    $useCase = new UpdateService(new MysqliServiceRepository($link));

    try {
        $useCase->handle($id_Servicio, $_POST);
        header("Location: ../dash-services-bath.php?id_Servicio=$id_Servicio&status=success&msg=" . urlencode('Servicio actualizado correctamente'));
    } catch (\mysqli_sql_exception $e) {
        header("Location: ../dash-services-bath.php?id_Servicio=$id_Servicio&status=error&msg=" . urlencode('No se pudo actualizar el servicio'));
    }

}
// Cerrar la conexión
$link->close();
