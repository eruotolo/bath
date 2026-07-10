<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Service\CreateService;
use App\Infrastructure\Persistence\MysqliServiceRepository;

global $link;
include ('../layouts/config.php');

if (isset($_POST['crear'])){
    $useCase = new CreateService(new MysqliServiceRepository($link));

    try {
        $id_Servicio = $useCase->handle($_POST);
        header("Location: ../dash-services-bath.php?id_Servicio=$id_Servicio&status=success&msg=" . urlencode('Servicio creado correctamente'));
    } catch (\mysqli_sql_exception $e) {
        header('Location: ../dash-services-add.php?status=error&msg=' . urlencode('No se pudo crear el servicio'));
    }

}
// Cerrar la conexión
$link->close();
