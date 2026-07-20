<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\User\ChangeOwnPassword;
use App\Infrastructure\Persistence\MysqliUserRepository;

global $link;
include "../layouts/config.php";

session_start();
require_once '../layouts/permissions.php';
require_once '../layouts/activity_logger.php';

if (isset($_POST['update'])){
    $requestedId = (int) $_POST['id'];
    $sessionUserId = (int) ($_SESSION['id'] ?? 0);
    require_permission('update', 'User', $requestedId);

    $useCase = new ChangeOwnPassword(new MysqliUserRepository($link));
    $log_data = [
        'requested_id_User' => $requestedId,
        'session_user_id' => $sessionUserId,
    ];

    try {
        $ok = $useCase->handle($sessionUserId, $requestedId, $_POST['password']);

        if ($ok) {
            log_activity_ctx($link, 'PASSWORD_RESET', [
                'entidad' => 'User',
                'entidad_id' => $requestedId,
                'descripcion' => "Reseteo de password propio para usuario ID $requestedId",
                'datos' => $log_data,
            ]);
            header('Location:../logout.php');
        } else {
            log_activity_ctx($link, 'PASSWORD_RESET', [
                'entidad' => 'User',
                'entidad_id' => $requestedId,
                'descripcion' => "No se pudo resetear el password propio para usuario ID $requestedId",
                'datos' => $log_data,
                'resultado' => 'error',
            ]);
            header('Location: ../dash-users-profile.php?status=error&msg=' . urlencode('No se pudo actualizar el password'));
        }
    } catch (\mysqli_sql_exception $e) {
        log_activity_ctx($link, 'PASSWORD_RESET', [
            'entidad' => 'User',
            'entidad_id' => $requestedId,
            'descripcion' => "No se pudo resetear el password propio para usuario ID $requestedId",
            'datos' => $log_data,
            'resultado' => 'error',
        ]);
        header('Location: ../dash-users-profile.php?status=error&msg=' . urlencode('No se pudo actualizar el password'));
    }
}else{
    echo '<script> alert ("No se pudo actualizar el password")</script>';
}
