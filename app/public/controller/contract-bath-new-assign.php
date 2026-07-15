<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Bathroom\AssignBathroomToContract;
use App\Infrastructure\Persistence\MysqliBathroomRepository;

global $link;

include ('../layouts/config.php');

if (isset($_POST['update'])){
    $id_Contrato = (int) $_POST['id_Contrato'];
    $idBanos = array_map('intval', (array) ($_POST['id_Bath'] ?? []));

    $useCase = new AssignBathroomToContract(new MysqliBathroomRepository($link));

    foreach ($idBanos as $id_Bath) {
        if ($id_Bath <= 0) {
            continue;
        }
        try {
            $useCase->handle($id_Contrato, $id_Bath);
        } catch (\Throwable $e) {
            // Best-effort: un baño puntual puede fallar (p. ej. ya asignado por otro usuario)
            // sin abortar la asignación del resto.
        }
    }

    header("Location: ../dash-contracts.php?action=manage&id_Contrato=$id_Contrato");

    // Cerrar la conexión
    $link->close();

}
