<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Contract\SetContractState;
use App\Infrastructure\Persistence\MysqliContractRepository;

global $link;
require '../layouts/config.php';
require_once '../layouts/session.php';
require_once '../layouts/permissions.php';
require_once '../layouts/activity_logger.php';

$id_Contrato = (int) $_GET['id_Contrato'];
require_permission('update', 'Contract', $id_Contrato);

$useCase = new SetContractState(new MysqliContractRepository($link));

try {
    $useCase->handle($id_Contrato, 2);
    log_activity_ctx($link, 'ACTIVATE', [
        'entidad' => 'Contract',
        'entidad_id' => $id_Contrato,
        'descripcion' => "Activó contrato id $id_Contrato",
    ]);
} catch (\mysqli_sql_exception $e) {
    log_activity_ctx($link, 'ACTIVATE', [
        'entidad' => 'Contract',
        'entidad_id' => $id_Contrato,
        'descripcion' => "Error al activar contrato id $id_Contrato",
        'resultado' => 'error',
    ]);
    // silencioso, igual que el original
}

header("Location: ../dash-contracts.php");

// Cerrar la conexión
$link->close();
