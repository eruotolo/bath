<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Bathroom\DeleteBathroom;
use App\Infrastructure\Persistence\MysqliBathroomRepository;

include "../layouts/config.php";
require_once '../layouts/session.php';
require_once '../layouts/permissions.php';
require_once '../layouts/activity_logger.php';
global $link;

$id_Bath = (int) $_GET['id_Bath'];
require_permission('delete', 'Bathroom', $id_Bath);

$useCase = new DeleteBathroom(new MysqliBathroomRepository($link));

try {
    $useCase->handle($id_Bath);
    log_activity_ctx($link, 'DELETE', [
        'entidad' => 'Bathroom',
        'entidad_id' => $id_Bath,
        'descripcion' => "Eliminó baño id $id_Bath",
    ]);
    header('Location: ../dash-bathrooms.php?status=success&msg=' . urlencode('Baño eliminado correctamente'));
} catch (\mysqli_sql_exception $e) {
    log_activity_ctx($link, 'DELETE', [
        'entidad' => 'Bathroom',
        'entidad_id' => $id_Bath,
        'descripcion' => "Error al eliminar baño id $id_Bath",
        'resultado' => 'error',
    ]);
    header('Location: ../dash-bathrooms.php?status=error&msg=' . urlencode('No se pudo eliminar el baño'));
}
// Cerrar la conexión
$link->close();
