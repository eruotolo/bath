<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Contract\DeactivateContract;
use App\Infrastructure\Persistence\MysqliBathroomRepository;
use App\Infrastructure\Persistence\MysqliContractRepository;

require '../layouts/config.php';
require_once '../layouts/session.php';
require_once '../layouts/permissions.php';
require_once '../layouts/activity_logger.php';
global $link;

$id_Contrato = (int) $_GET['id_Contrato'];
require_permission('update', 'Contract', $id_Contrato);

$useCase = new DeactivateContract(
    new MysqliContractRepository($link),
    new MysqliBathroomRepository($link),
);

try {
    $useCase->handle($id_Contrato);
    log_activity_ctx($link, 'DEACTIVATE', [
        'entidad' => 'Contract',
        'entidad_id' => $id_Contrato,
        'descripcion' => "Desactivó contrato id $id_Contrato",
    ]);
} catch (\mysqli_sql_exception $e) {
    log_activity_ctx($link, 'DEACTIVATE', [
        'entidad' => 'Contract',
        'entidad_id' => $id_Contrato,
        'descripcion' => "Error al desactivar contrato id $id_Contrato",
        'resultado' => 'error',
    ]);
    // silencioso, igual que el original
}

header("Location: ../dash-contracts.php");

// Cerrar la conexión
$link->close();
