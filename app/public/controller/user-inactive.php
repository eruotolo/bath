<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\User\DeactivateUser;
use App\Infrastructure\Persistence\MysqliUserRepository;

global $link;
include ('../layouts/config.php');
require_once '../layouts/session.php';
require_once '../layouts/permissions.php';

if (isset($_GET['id_User'])){
    $id = (int) $_GET['id_User'];
    require_permission('update', 'User', $id);

    $useCase = new DeactivateUser(new MysqliUserRepository($link));

    try {
        $useCase->handle($id);
        header("Location: ../dash-users-list.php");
    } catch (\mysqli_sql_exception $e) {
        echo '<script> alert ("No se pudo dar de baja al usuario")</script>';
    }

    // Cerrar la conexión
    $link->close();

}
