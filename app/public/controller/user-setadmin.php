<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\User\ToggleUserAdmin;
use App\Infrastructure\Persistence\MysqliUserRepository;

global $link;
include ('../layouts/config.php');

if (isset($_GET['id_User'])){
    $id = (int) $_GET['id_User'];
    $category = (int) $_GET['category'];

    $useCase = new ToggleUserAdmin(new MysqliUserRepository($link));

    try {
        $useCase->handle($id, $category);
        header("Location: ../dash-users-list.php");
    } catch (\mysqli_sql_exception $e) {
        echo '<script> alert ("No se pudo setear como Admin")</script>';
    }

    // Cerrar la conexión
    $link->close();

}
