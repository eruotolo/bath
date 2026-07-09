<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\User\CreateUser;
use App\Infrastructure\Persistence\MysqliUserRepository;

global $link;
include "../layouts/config.php";

if (isset($_POST['crear'])){
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
    } catch (\mysqli_sql_exception $e) {
        header('Location: ../index.php?status=error&msg=' . urlencode('No se pudo crear el usuario'));
    }

}else{
    header('Location: ../index.php?status=error&msg=' . urlencode('No se pudo crear el usuario'));
}
// Cerrar la conexión
$link->close();
