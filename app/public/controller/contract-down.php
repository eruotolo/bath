<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Contract\SetContractState;
use App\Infrastructure\Persistence\MysqliContractRepository;

require '../layouts/config.php';
require_once '../layouts/session.php';
require_once '../layouts/permissions.php';
require_once '../layouts/activity_logger.php';
global $link;

$id_Contrato = (int) $_GET['id_Contrato'];
require_permission('update', 'Contract', $id_Contrato);

$useCase = new SetContractState(new MysqliContractRepository($link));

try {
    $useCase->handle($id_Contrato, 0);
    log_activity_ctx($link, 'TERMINATE', [
        'entidad' => 'Contract',
        'entidad_id' => $id_Contrato,
        'descripcion' => "Dio de baja contrato id $id_Contrato",
    ]);
} catch (\mysqli_sql_exception $e) {
    log_activity_ctx($link, 'TERMINATE', [
        'entidad' => 'Contract',
        'entidad_id' => $id_Contrato,
        'descripcion' => "Error al dar de baja contrato id $id_Contrato",
        'resultado' => 'error',
    ]);
    // silencioso, igual que el original
}

header("Location: ../dash-contracts.php");

// Cerrar la conexión
$link->close();
