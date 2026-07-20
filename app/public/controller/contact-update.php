<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Contact\UpdateContact;
use App\Infrastructure\Persistence\MysqliContactRepository;

global $link;
include ('../layouts/config.php');
require_once '../layouts/session.php';
require_once '../layouts/permissions.php';
require_once '../layouts/activity_logger.php';

if (isset($_POST['update'])){
    $id_Contacto = (int) $_POST['idC'];
    $id_Cliente = (int) $_POST['idCC'];
    require_permission('update', 'Customer', $id_Contacto);

    $useCase = new UpdateContact(new MysqliContactRepository($link));

    try {
        $useCase->handle($id_Contacto, $_POST);
        log_activity_ctx($link, 'UPDATE', [
            'entidad' => 'Contact',
            'entidad_id' => $id_Contacto,
            'descripcion' => "Actualizó contacto id $id_Contacto (cliente id $id_Cliente)",
            'datos' => $_POST,
        ]);
        header("Location: ../dash-customers-item.php?id_Cliente=$id_Cliente&status=success&msg=" . urlencode('Contacto actualizado correctamente'));
    } catch (\mysqli_sql_exception $e) {
        log_activity_ctx($link, 'UPDATE', [
            'entidad' => 'Contact',
            'entidad_id' => $id_Contacto,
            'descripcion' => "Error al actualizar contacto id $id_Contacto (cliente id $id_Cliente)",
            'datos' => $_POST,
            'resultado' => 'error',
        ]);
        header('Location: ../dash-customers.php?status=error&msg=' . urlencode('No se pudo actualizar el contacto'));
    }
}else{
    header('Location: ../dash-customers.php?status=error&msg=' . urlencode('No se pudo actualizar el contacto'));
}
// Cerrar la conexión
$link->close();
