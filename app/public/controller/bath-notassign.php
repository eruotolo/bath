<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Bathroom\SetBathroomAssigned;
use App\Infrastructure\Persistence\MysqliBathroomRepository;

global $link;
require '../layouts/config.php';
require_once '../layouts/session.php';
require_once '../layouts/permissions.php';
require_once '../layouts/activity_logger.php';

$id_Bath = (int) $_GET['id_Bath'];
require_permission('update', 'Bathroom', $id_Bath);

$useCase = new SetBathroomAssigned(new MysqliBathroomRepository($link));

try {
    $useCase->handle($id_Bath, 0);
    log_activity_ctx($link, 'UNASSIGN', [
        'entidad' => 'Bathroom',
        'entidad_id' => $id_Bath,
        'descripcion' => "Desasignó baño id $id_Bath",
    ]);
    header("Location: ../dash-bathrooms.php");
} catch (\mysqli_sql_exception $e) {
    log_activity_ctx($link, 'UNASSIGN', [
        'entidad' => 'Bathroom',
        'entidad_id' => $id_Bath,
        'descripcion' => "Error al desasignar baño id $id_Bath",
        'resultado' => 'error',
    ]);
    header("Location: ../index.php");
}

// Cerrar la conexión
$link->close();
