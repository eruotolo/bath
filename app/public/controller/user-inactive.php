<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\User\DeactivateUser;
use App\Infrastructure\Persistence\MysqliUserRepository;

global $link;
include ('../layouts/config.php');
require_once '../layouts/session.php';
require_once '../layouts/permissions.php';
require_once '../layouts/activity_logger.php';

if (isset($_GET['id_User'])){
    $id = (int) $_GET['id_User'];
    require_permission('update', 'User', $id);

    $useCase = new DeactivateUser(new MysqliUserRepository($link));

    try {
        $useCase->handle($id);
        log_activity_ctx($link, 'DEACTIVATE', [
            'entidad' => 'User',
            'entidad_id' => $id,
            'descripcion' => "Desactivó usuario ID $id",
            'datos' => ['id_User' => $id],
        ]);
        header("Location: ../dash-users-list.php");
    } catch (\mysqli_sql_exception $e) {
        log_activity_ctx($link, 'DEACTIVATE', [
            'entidad' => 'User',
            'entidad_id' => $id,
            'descripcion' => "No se pudo desactivar el usuario ID $id",
            'datos' => ['id_User' => $id],
            'resultado' => 'error',
        ]);
        echo '<script> alert ("No se pudo dar de baja al usuario")</script>';
    }

    // Cerrar la conexión
    $link->close();

}
