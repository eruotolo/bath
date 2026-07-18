<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\User\ToggleUserAdmin;
use App\Infrastructure\Persistence\MysqliUserRepository;

global $link;
include ('../layouts/config.php');
require_once '../layouts/session.php';
require_once '../layouts/permissions.php';

if (isset($_GET['id_User'])){
    $id = (int) $_GET['id_User'];
    require_permission('update', 'User', $id);
    $category = (int) $_GET['category'];

    $repository = new MysqliUserRepository($link);

    $target = $repository->find($id);
    if ($target !== null && $target->category === 3) {
        require_permission('grant_superadmin');
    }

    if ($category === 3) {
        require_permission('grant_superadmin');
    }

    $useCase = new ToggleUserAdmin($repository);

    try {
        $useCase->handle($id, $category);
        header("Location: ../dash-users-list.php");
    } catch (\mysqli_sql_exception $e) {
        echo '<script> alert ("No se pudo setear como Admin")</script>';
    }

    // Cerrar la conexión
    $link->close();

}
