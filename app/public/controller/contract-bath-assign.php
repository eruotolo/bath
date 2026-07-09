<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Bathroom\SetBathroomAssigned;
use App\Infrastructure\Persistence\MysqliBathroomRepository;

global $link;
require '../layouts/config.php';

$id_Contrato = (int) $_GET['id_Contrato'];
$id_Bath = (int) $_GET['id_Bath'];

$useCase = new SetBathroomAssigned(new MysqliBathroomRepository($link));

try {
    $useCase->handle($id_Bath, 1);
    header("Location: ../dash-contracts-item.php?id_Contrato=$id_Contrato");
} catch (\mysqli_sql_exception $e) {
    header("Location: ../index.php");
}

// Cerrar la conexión
$link->close();
