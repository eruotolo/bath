<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\User\ToggleUserAdmin;
use App\Infrastructure\Persistence\MysqliUserRepository;

global $link;
include ('../layouts/config.php');
require_once '../layouts/session.php';
require_once '../layouts/permissions.php';
require_once '../layouts/activity_logger.php';

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
    $new_category = $category === 1 ? 2 : 1;
    $category_names = [1 => 'Administrador', 2 => 'Usuario', 3 => 'SuperAdministrador'];
    $old_category_name = $category_names[$category] ?? 'desconocida';
    $new_category_name = $category_names[$new_category] ?? 'desconocida';
    $username = $target?->username ?? "ID $id";
    $log_data = [
        'id_User' => $id,
        'categoria_anterior' => $category,
        'categoria_nueva' => $new_category,
    ];

    try {
        $useCase->handle($id, $category);
        log_activity_ctx($link, 'ROLE_CHANGE', [
            'entidad' => 'User',
            'entidad_id' => $id,
            'descripcion' => "Cambio de rol de usuario $username de categoría $old_category_name a categoría $new_category_name",
            'datos' => $log_data,
        ]);
        header("Location: ../dash-users-list.php");
    } catch (\mysqli_sql_exception $e) {
        log_activity_ctx($link, 'ROLE_CHANGE', [
            'entidad' => 'User',
            'entidad_id' => $id,
            'descripcion' => "No se pudo cambiar el rol de usuario $username de categoría $old_category_name a categoría $new_category_name",
            'datos' => $log_data,
            'resultado' => 'error',
        ]);
        echo '<script> alert ("No se pudo setear como Admin")</script>';
    }

    // Cerrar la conexión
    $link->close();

}
