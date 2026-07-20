<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Contract\UpdateContract;
use App\Infrastructure\Persistence\MysqliContractRepository;

global $link;

include ('../layouts/config.php');
require_once '../layouts/session.php';
require_once '../layouts/permissions.php';
require_once '../layouts/activity_logger.php';

if (isset($_POST['update'])){
    $id_Contrato = (int) $_POST['id_Contrato'];
    require_permission('update', 'Contract', $id_Contrato);
    $obra = $_POST['obra_Contrato'] ?? '';

    $input = $_POST;
    $input['valorMensual_Contrato'] = str_replace('.', '', trim((string) ($input['valorMensual_Contrato'] ?? '')));
    $input['valorTotal_Contrato'] = str_replace('.', '', trim((string) ($input['valorTotal_Contrato'] ?? '')));

    $useCase = new UpdateContract(new MysqliContractRepository($link));

    try {
        $useCase->handle($id_Contrato, $input);
        log_activity_ctx($link, 'UPDATE', [
            'entidad' => 'Contract',
            'entidad_id' => $id_Contrato,
            'descripcion' => "Actualizó contrato id $id_Contrato (obra '$obra')",
            'datos' => $_POST,
        ]);
        header('Location: ../dash-contracts.php?status=success&msg=' . urlencode('Contrato actualizado correctamente'));
    } catch (\mysqli_sql_exception $e) {
        log_activity_ctx($link, 'UPDATE', [
            'entidad' => 'Contract',
            'entidad_id' => $id_Contrato,
            'descripcion' => "Error al actualizar contrato id $id_Contrato (obra '$obra')",
            'datos' => $_POST,
            'resultado' => 'error',
        ]);
        header("Location: ../dash-contracts.php?action=edit&id_Contrato=$id_Contrato&err=" . urlencode('No se pudo actualizar el contrato. Intente nuevamente.'));
    }
}else{
    header('Location: ../dash-contracts.php?status=error&msg=' . urlencode('No se pudo actualizar el contrato'));
}
// Cerrar la conexión
$link->close();
