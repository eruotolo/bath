<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Bathroom\AssignBathroomToContract;
use App\Infrastructure\Persistence\MysqliBathroomRepository;

global $link;

include ('../layouts/config.php');

if (isset($_POST['update'])){
    $id_Contrato = (int) $_POST['id_Contrato'];
    $id_Bath = (int) $_POST['id_Bath'];

    $useCase = new AssignBathroomToContract(new MysqliBathroomRepository($link));

    try {
        $useCase->handle($id_Contrato, $id_Bath);
        header("Location: ../dash-contracts-item.php?id_Contrato=$id_Contrato");
    } catch (\mysqli_sql_exception $e) {
        header("Location: ../index.php");
    }

    // Cerrar la conexión
    $link->close();

}
