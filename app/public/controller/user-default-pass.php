<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\User\ResetUserPassword;
use App\Infrastructure\Persistence\MysqliUserRepository;

global $link;
include ('../layouts/config.php');
require_once '../layouts/session.php';
require_once '../layouts/permissions.php';

// RESETEAR PASSWORD A UN VALOR TEMPORAL ALEATORIO
if (isset($_GET['id_User'])){
    $id = (int) $_GET['id_User'];
    require_permission('update', 'User', $id);

    $useCase = new ResetUserPassword(new MysqliUserRepository($link));

    try {
        $temporaryPassword = $useCase->handle($id);
        header('Location: ../dash-users-list.php?status=success&msg=' . urlencode("Password reseteado. Nueva contraseña temporal: $temporaryPassword — comunicásela al usuario."));
    } catch (\mysqli_sql_exception $e) {
        header('Location: ../index.php?status=error&msg=' . urlencode('No se pudo resetear el password del usuario'));
    }

    // Cerrar la conexión
    $link->close();

}
