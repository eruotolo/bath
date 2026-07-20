<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Bathroom\SetBathroomEstado;
use App\Infrastructure\Persistence\MysqliBathroomRepository;

global $link;
require '../layouts/config.php';
require_once '../layouts/session.php';
require_once '../layouts/permissions.php';
require_once '../layouts/activity_logger.php';

$id_Bath = (int) $_GET['id_Bath'];
require_permission('update', 'Bathroom', $id_Bath);

$useCase = new SetBathroomEstado(new MysqliBathroomRepository($link));
$useCase->handle($id_Bath, 1);

log_activity_ctx($link, 'ACTIVATE', [
    'entidad' => 'Bathroom',
    'entidad_id' => $id_Bath,
    'descripcion' => "Activó baño id $id_Bath",
]);

header("Location: ../dash-bathrooms.php");

// Cerrar la conexión
$link->close();
