<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Service\DeactivateService;
use App\Infrastructure\Persistence\MysqliServiceRepository;

require '../layouts/config.php';
require_once '../layouts/session.php';
require_once '../layouts/permissions.php';
require_once '../layouts/activity_logger.php';
global $link;

$id_Servicio = (int) $_GET['id_Servicio'];
require_permission('update', 'Service', $id_Servicio);

$useCase = new DeactivateService(new MysqliServiceRepository($link));

try {
    $useCase->handle($id_Servicio);
    log_activity_ctx($link, 'DEACTIVATE', [
        'entidad' => 'Service',
        'entidad_id' => $id_Servicio,
        'descripcion' => "Desactivó servicio id $id_Servicio",
    ]);
    header("Location: ../dash-services.php");
} catch (\mysqli_sql_exception $e) {
    log_activity_ctx($link, 'DEACTIVATE', [
        'entidad' => 'Service',
        'entidad_id' => $id_Servicio,
        'descripcion' => "Error al desactivar servicio id $id_Servicio",
        'resultado' => 'error',
    ]);
    header("Location: ../index.php");
}

// Cerrar la conexión
$link->close();
