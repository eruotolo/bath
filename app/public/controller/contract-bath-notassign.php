<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Bathroom\UnassignBathroomFromContract;
use App\Infrastructure\Persistence\MysqliBathroomRepository;

global $link;
require '../layouts/config.php';
require_once '../layouts/session.php';
require_once '../layouts/permissions.php';
require_once '../layouts/activity_logger.php';

$id_Contrato = (int) $_GET['id_Contrato'];
$id_Bath = (int) $_GET['id_Bath'];
$id_Relacion = (int) $_GET['id_Relacion'];
require_permission('update', 'Contract', $id_Contrato);

$useCase = new UnassignBathroomFromContract(new MysqliBathroomRepository($link));

try {
    $useCase->handle($id_Relacion, $id_Bath, $id_Contrato);
    log_activity_ctx($link, 'UNASSIGN', [
        'entidad' => 'Contract',
        'entidad_id' => $id_Contrato,
        'descripcion' => "Desasignó baño id $id_Bath del contrato id $id_Contrato",
    ]);
    header("Location: ../dash-contracts.php?action=manage&id_Contrato=$id_Contrato");
} catch (\mysqli_sql_exception $e) {
    log_activity_ctx($link, 'UNASSIGN', [
        'entidad' => 'Contract',
        'entidad_id' => $id_Contrato,
        'descripcion' => "Error al desasignar baño id $id_Bath del contrato id $id_Contrato",
        'resultado' => 'error',
    ]);
    header("Location: ../dash-contracts.php?action=manage&id_Contrato=$id_Contrato&err=" . urlencode('No se pudo desasignar el baño.'));
}

// Cerrar la conexión
$link->close();
