<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Contact\DeleteContact;
use App\Infrastructure\Persistence\MysqliContactRepository;

global $link;
require '../layouts/config.php';
require_once '../layouts/session.php';
require_once '../layouts/permissions.php';

// Obtención del identificador único de la fila a eliminar
$id_Contacto = (int) $_GET['id_Contacto'];
$id_Cliente = (int) $_GET['id_Cliente'];
require_permission('delete', 'Customer', $id_Contacto);

$useCase = new DeleteContact(new MysqliContactRepository($link));

try {
    $useCase->handle($id_Contacto);
    header("Location: ../dash-customers-item.php?id_Cliente=$id_Cliente");
} catch (\mysqli_sql_exception $e) {
    header("Location: ../index.php");
}

// Cerrar la conexión
$link->close();
