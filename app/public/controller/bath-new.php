<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Bathroom\CreateBathroom;
use App\Infrastructure\Persistence\MysqliBathroomRepository;

global $link;

include ('../layouts/config.php');
require_once '../layouts/session.php';
require_once '../layouts/permissions.php';
require_once '../layouts/activity_logger.php';
require_permission('create', 'Bathroom');

if (isset($_POST['crear'])){
    $codigo_Bath = $_POST['codigo_Bath'];

    $useCase = new CreateBathroom(new MysqliBathroomRepository($link));

    try {
        $id = $useCase->handle($_POST);

        if ($id === null) {
            header('Location: ../dash-bathrooms-add.php?status=error&msg=' . urlencode("Ya existe un baño con el código '$codigo_Bath'. Ingresá un código distinto."));
        } else {
            log_activity_ctx($link, 'CREATE', [
                'entidad' => 'Bathroom',
                'entidad_id' => $id,
                'descripcion' => "Creó baño código $codigo_Bath",
                'datos' => $_POST,
            ]);
            header('Location: ../dash-bathrooms.php?status=success&msg=' . urlencode('Baño creado correctamente'));
        }
    } catch (\mysqli_sql_exception $e) {
        log_activity_ctx($link, 'CREATE', [
            'entidad' => 'Bathroom',
            'descripcion' => "Error al crear baño código $codigo_Bath",
            'datos' => $_POST,
            'resultado' => 'error',
        ]);
        header('Location: ../dash-bathrooms-add.php?status=error&msg=' . urlencode('No se pudo crear el baño'));
    }

}else{
    header('Location: ../dash-bathrooms-add.php?status=error&msg=' . urlencode('No se pudo crear el baño'));
}
// Cerrar la conexión
$link->close();
