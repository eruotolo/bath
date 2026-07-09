<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Contact\CreateContact;
use App\Infrastructure\Persistence\MysqliContactRepository;

session_start();
include ('../layouts/config.php');
global $link;

if (isset($_POST['crear'])){
    $id_Cliente = (int) $_POST['id_Cliente'];

    $useCase = new CreateContact(new MysqliContactRepository($link));

    try {
        $useCase->handle($_POST);
        header("Location: ../dash-customers-item.php?id_Cliente=$id_Cliente&status=success&msg=" . urlencode('Contacto creado correctamente'));
    } catch (\mysqli_sql_exception $e) {
        header('Location: ../dash-customers.php?status=error&msg=' . urlencode('No se pudo crear el contacto'));
    }

}else{
    header('Location: ../dash-customers.php?status=error&msg=' . urlencode('No se pudo crear el contacto'));
}
// Cerrar la conexión
$link->close();
