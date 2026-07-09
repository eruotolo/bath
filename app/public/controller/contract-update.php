<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Contract\UpdateContract;
use App\Infrastructure\Persistence\MysqliContractRepository;

global $link;

include ('../layouts/config.php');

if (isset($_POST['update'])){
    $id_Contrato = (int) $_POST['id_Contrato'];

    $useCase = new UpdateContract(new MysqliContractRepository($link));

    try {
        $useCase->handle($id_Contrato, $_POST);
        header("Location: ../dash-contracts-edit.php?id_Contrato=$id_Contrato&status=success&msg=" . urlencode('Contrato actualizado correctamente'));
    } catch (\mysqli_sql_exception $e) {
        header('Location: ../dash-contracts.php?status=error&msg=' . urlencode('No se pudo actualizar el contrato'));
    }
}else{
    header('Location: ../dash-contracts.php?status=error&msg=' . urlencode('No se pudo actualizar el contrato'));
}
// Cerrar la conexión
$link->close();
