<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Contact\UpdateContact;
use App\Infrastructure\Persistence\MysqliContactRepository;

global $link;
include ('../layouts/config.php');

if (isset($_POST['update'])){
    $id_Contacto = (int) $_POST['idC'];
    $id_Cliente = (int) $_POST['idCC'];

    $useCase = new UpdateContact(new MysqliContactRepository($link));

    try {
        $useCase->handle($id_Contacto, $_POST);
        header("Location: ../dash-customers-item.php?id_Cliente=$id_Cliente&status=success&msg=" . urlencode('Contacto actualizado correctamente'));
    } catch (\mysqli_sql_exception $e) {
        header('Location: ../dash-customers.php?status=error&msg=' . urlencode('No se pudo actualizar el contacto'));
    }
}else{
    header('Location: ../dash-customers.php?status=error&msg=' . urlencode('No se pudo actualizar el contacto'));
}
// Cerrar la conexión
$link->close();
