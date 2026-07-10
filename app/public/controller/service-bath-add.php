<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Service\AssignBathroomsToService;
use App\Infrastructure\Persistence\MysqliServiceRepository;

include ('../layouts/config.php');

global $link;

if (isset($_POST['update'])){
    $id_Servicio = (int) $_POST['id_Servicio'];

    // Verifica si $_POST['id_Bath'] está definido y es un array
    if (isset($_POST['id_Bath']) && is_array($_POST['id_Bath'])) {
        $useCase = new AssignBathroomsToService(new MysqliServiceRepository($link));

        try {
            $useCase->handle($id_Servicio, $_POST['id_Bath']);
            header("Location: ../dash-services-bath.php?id_Servicio=$id_Servicio");
        } catch (\mysqli_sql_exception $e) {
            header("Location: ../dash-services-bath.php?id_Servicio=$id_Servicio&status=error&msg=" . urlencode('No se pudieron asignar los baños'));
        }
    } else {
        // Maneja el caso donde no se seleccionó ningún baño
        echo "No se seleccionaron baños";
    }
} else {
    // Maneja el caso donde no se activó el formulario
    header("../index.php");
}
