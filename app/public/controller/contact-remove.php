<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Contact\DeleteContact;
use App\Infrastructure\Persistence\MysqliContactRepository;

global $link;
require '../layouts/config.php';
require_once '../layouts/session.php';
require_once '../layouts/permissions.php';
require_once '../layouts/activity_logger.php';

// Obtención del identificador único de la fila a eliminar
$id_Contacto = (int) $_GET['id_Contacto'];
$id_Cliente = (int) $_GET['id_Cliente'];
require_permission('delete', 'Customer', $id_Contacto);

$useCase = new DeleteContact(new MysqliContactRepository($link));

try {
    $useCase->handle($id_Contacto);
    log_activity_ctx($link, 'DELETE', [
        'entidad' => 'Contact',
        'entidad_id' => $id_Contacto,
        'descripcion' => "Eliminó contacto id $id_Contacto (cliente id $id_Cliente)",
    ]);
    header("Location: ../dash-customers-item.php?id_Cliente=$id_Cliente");
} catch (\mysqli_sql_exception $e) {
    log_activity_ctx($link, 'DELETE', [
        'entidad' => 'Contact',
        'entidad_id' => $id_Contacto,
        'descripcion' => "Error al eliminar contacto id $id_Contacto (cliente id $id_Cliente)",
        'resultado' => 'error',
    ]);
    header("Location: ../index.php");
}

// Cerrar la conexión
$link->close();
