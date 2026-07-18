<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\User\ChangeOwnPassword;
use App\Infrastructure\Persistence\MysqliUserRepository;

global $link;
include "../layouts/config.php";

session_start();
require_once '../layouts/permissions.php';

if (isset($_POST['update'])){
    $requestedId = (int) $_POST['id'];
    $sessionUserId = (int) ($_SESSION['id'] ?? 0);
    require_permission('update', 'User', $requestedId);

    $useCase = new ChangeOwnPassword(new MysqliUserRepository($link));

    try {
        $ok = $useCase->handle($sessionUserId, $requestedId, $_POST['password']);

        if ($ok) {
            header('Location:../logout.php');
        } else {
            header('Location: ../dash-users-profile.php?status=error&msg=' . urlencode('No se pudo actualizar el password'));
        }
    } catch (\mysqli_sql_exception $e) {
        header('Location: ../dash-users-profile.php?status=error&msg=' . urlencode('No se pudo actualizar el password'));
    }
}else{
    echo '<script> alert ("No se pudo actualizar el password")</script>';
}
