<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Bathroom\UpdateBathroom;
use App\Infrastructure\Persistence\MysqliBathroomRepository;

include "../layouts/config.php";
require_once '../layouts/session.php';
require_once '../layouts/permissions.php';
global $link;

if (isset($_POST['update'])){
    $id_Bath = (int) $_POST['id_Bath'];
    require_permission('update', 'Bathroom', $id_Bath);
    $codigo_Bath = $_POST['codigo_Bath'];

    $useCase = new UpdateBathroom(new MysqliBathroomRepository($link));

    try {
        $ok = $useCase->handle($id_Bath, $_POST);

        if (!$ok) {
            header('Location: ../dash-bathrooms-edit.php?id_Bath=' . $id_Bath . '&status=error&msg=' . urlencode("Ya existe un baño con el código '$codigo_Bath'. Ingresá un código distinto."));
        } else {
            header('Location: ../dash-bathrooms.php?status=success&msg=' . urlencode('Baño actualizado correctamente'));
        }
    } catch (\mysqli_sql_exception $e) {
        header('Location: ../dash-bathrooms.php?status=error&msg=' . urlencode('No se pudo actualizar el baño'));
    }
}else{
    header('Location: ../dash-bathrooms.php?status=error&msg=' . urlencode('No se pudo actualizar el baño'));
}
// Cerrar la conexión
$link->close();
