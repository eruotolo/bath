<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\User\UpdateUser;
use App\Infrastructure\Persistence\MysqliUserRepository;

global $link;
include "../layouts/config.php";
require_once '../layouts/session.php';
require_once '../layouts/permissions.php';

if(isset($_POST['update'])){
    $id = (int) $_POST['id'];
    require_permission('update', 'User', $id);

    $repository = new MysqliUserRepository($link);

    $target = $repository->find($id);
    if ($target !== null && $target->category === 3) {
        require_permission('grant_superadmin');
    }

    $new_category = (int) ($_POST['category'] ?? 0);
    if ($new_category === 3) {
        require_permission('grant_superadmin');
    } else {
        require_permission('manage_users');
    }

    $imageFilename = null;
    if(isset($_FILES['file']) && $_FILES["file"]["error"] == 0){
        #file name with a random number so that similar dont get replaced
        $pname = rand(1000, 10000) . "-" . $_FILES["file"]["name"];

        #temporary file name to store file
        $tname = $_FILES["file"]["tmp_name"];

        #upload directory path
        $uploads_dir = '../uploads/users/';

        #TO move the uploaded file to specific location
        move_uploaded_file($tname, $uploads_dir . '/' . $pname);

        $imageFilename = $pname;
    }

    $useCase = new UpdateUser($repository);

    try {
        $useCase->handle($id, $_POST, $imageFilename);
        header('Location: ../dash-users-list.php?status=success&msg=' . urlencode('Usuario actualizado correctamente'));
        exit();
    } catch (\mysqli_sql_exception $e) {
        header('Location: ../dash-users-list.php?status=error&msg=' . urlencode('No se pudo actualizar el usuario'));
        exit();
    }
}
