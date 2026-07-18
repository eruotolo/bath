<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\User\CreateUser;
use App\Infrastructure\Persistence\MysqliUserRepository;

global $link;
include "../layouts/config.php";
require_once '../layouts/session.php';
require_once '../layouts/permissions.php';
require_permission('create', 'User');

if (isset($_POST['crear'])){
    $new_category = (int) ($_POST['category'] ?? 0);
    if ($new_category === 3) {
        require_permission('grant_superadmin');
    } else {
        require_permission('manage_users');
    }

    #file name with a random number so that similar dont get replaced
    $pname = rand(1000,10000)."-".$_FILES["file"]["name"];

    #temporary file name to store file
    $tname = $_FILES["file"]["tmp_name"];

    #upload directory path
    $uploads_dir = '../uploads/users/';

    #TO move the uploaded file to specific location
    move_uploaded_file($tname, $uploads_dir.'/'.$pname);

    $useCase = new CreateUser(new MysqliUserRepository($link));

    try {
        $useCase->handle($_POST, $pname);
        header('Location: ../dash-users-list.php?status=success&msg=' . urlencode('Usuario creado correctamente'));
        exit();
    } catch (\mysqli_sql_exception $e) {
        header('Location: ../dash-users-list.php?status=error&msg=' . urlencode('No se pudo crear el usuario'));
        exit();
    }

}else{
    header('Location: ../dash-users-list.php?status=error&msg=' . urlencode('No se pudo crear el usuario'));
    exit();
}
// Cerrar la conexión
$link->close();
