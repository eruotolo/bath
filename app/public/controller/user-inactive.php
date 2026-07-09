<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\User\DeactivateUser;
use App\Infrastructure\Persistence\MysqliUserRepository;

global $link;
include ('../layouts/config.php');

if (isset($_GET['id_User'])){
    $id = (int) $_GET['id_User'];

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
