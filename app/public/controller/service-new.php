<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Service\CreateService;
use App\Application\Service\SyncBathroomsToService;
use App\Infrastructure\Persistence\MysqliServiceRepository;

global $link;
include ('../layouts/config.php');
require_once '../layouts/session.php';
require_once '../layouts/permissions.php';
require_once '../layouts/activity_logger.php';
require_permission('create', 'Service');

if (isset($_POST['crear'])){
    $serviceRepository = new MysqliServiceRepository($link);
    $useCase = new CreateService($serviceRepository);

    try {
        $id_Servicio = $useCase->handle($_POST);
        (new SyncBathroomsToService($serviceRepository))->handle($id_Servicio, $_POST['id_Bath'] ?? []);
        log_activity_ctx($link, 'CREATE', [
            'entidad' => 'Service',
            'entidad_id' => $id_Servicio,
            'descripcion' => "Creó servicio id $id_Servicio",
            'datos' => $_POST,
        ]);
        header('Location: ../dash-services.php?flash=success&msg=' . urlencode('Servicio creado correctamente'));
    } catch (\mysqli_sql_exception $e) {
        log_activity_ctx($link, 'CREATE', [
            'entidad' => 'Service',
            'descripcion' => 'Error al crear servicio',
            'datos' => $_POST,
            'resultado' => 'error',
        ]);
        header('Location: ../dash-services.php?action=new&err=' . urlencode('No se pudo crear el servicio'));
    }

}
// Cerrar la conexión
$link->close();
