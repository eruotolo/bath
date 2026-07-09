<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Contract\CreateContract;
use App\Infrastructure\Persistence\MysqliContractRepository;

global $link;
include ('../layouts/config.php');

if(isset($_POST['crear'])) {
    $useCase = new CreateContract(new MysqliContractRepository($link));

    try {
        $id_Contrato = $useCase->handle($_POST);
        header("Location: ../dash-contracts-item.php?id_Contrato=$id_Contrato&status=success&msg=" . urlencode('Contrato creado correctamente'));
    } catch (\mysqli_sql_exception $e) {
        header('Location: ../dash-contracts-add.php?status=error&msg=' . urlencode('No se pudo crear el contrato'));
    }

}else{
    header('Location: ../dash-contracts-add.php?status=error&msg=' . urlencode('No se pudo crear el contrato'));
}
// Cerrar la conexión
$link->close();
