<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Bathroom\DeleteBathroom;
use App\Infrastructure\Persistence\MysqliBathroomRepository;

include "../layouts/config.php";
global $link;

$id_Bath = (int) $_GET['id_Bath'];

$useCase = new DeleteBathroom(new MysqliBathroomRepository($link));

try {
    $useCase->handle($id_Bath);
    header('Location: ../dash-bathrooms.php?status=success&msg=' . urlencode('Baño eliminado correctamente'));
} catch (\mysqli_sql_exception $e) {
    header('Location: ../dash-bathrooms.php?status=error&msg=' . urlencode('No se pudo eliminar el baño'));
}
// Cerrar la conexión
$link->close();
