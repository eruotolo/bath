<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Contract\DeactivateContract;
use App\Infrastructure\Persistence\MysqliBathroomRepository;
use App\Infrastructure\Persistence\MysqliContractRepository;

require '../layouts/config.php';
global $link;

$id_Contrato = (int) $_GET['id_Contrato'];

$useCase = new DeactivateContract(
    new MysqliContractRepository($link),
    new MysqliBathroomRepository($link),
);

try {
    $useCase->handle($id_Contrato);
} catch (\mysqli_sql_exception $e) {
    // silencioso, igual que el original
}

header("Location: ../dash-contracts.php");

// Cerrar la conexión
$link->close();
