<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Bathroom\CreateBathroom;
use App\Infrastructure\Persistence\MysqliBathroomRepository;

global $link;

include ('../layouts/config.php');

if (isset($_POST['crear'])){
    $codigo_Bath = $_POST['codigo_Bath'];

    $useCase = new CreateBathroom(new MysqliBathroomRepository($link));

    try {
        $id = $useCase->handle($_POST);

        if ($id === null) {
            header('Location: ../dash-bathrooms-add.php?status=error&msg=' . urlencode("Ya existe un baño con el código '$codigo_Bath'. Ingresá un código distinto."));
        } else {
            header('Location: ../dash-bathrooms.php?status=success&msg=' . urlencode('Baño creado correctamente'));
        }
    } catch (\mysqli_sql_exception $e) {
        header('Location: ../dash-bathrooms-add.php?status=error&msg=' . urlencode('No se pudo crear el baño'));
    }

}else{
    header('Location: ../dash-bathrooms-add.php?status=error&msg=' . urlencode('No se pudo crear el baño'));
}
// Cerrar la conexión
$link->close();
