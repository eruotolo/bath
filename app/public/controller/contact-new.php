<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Contact\CreateContact;
use App\Infrastructure\Persistence\MysqliContactRepository;

session_start();
include ('../layouts/config.php');
require_once '../layouts/permissions.php';
require_once '../layouts/activity_logger.php';
global $link;
require_permission('create', 'Customer');

if (isset($_POST['crear'])){
    $id_Cliente = (int) $_POST['id_Cliente'];
    $contact_nombre = $_POST['nombre_Contacto'] ?? '';

    $useCase = new CreateContact(new MysqliContactRepository($link));

    try {
        $useCase->handle($_POST);
        log_activity_ctx($link, 'CREATE', [
            'entidad' => 'Contact',
            'entidad_id' => $id_Cliente,
            'descripcion' => "Creó contacto '$contact_nombre' para cliente id $id_Cliente",
            'datos' => $_POST,
        ]);
        header("Location: ../dash-customers-item.php?id_Cliente=$id_Cliente&status=success&msg=" . urlencode('Contacto creado correctamente'));
    } catch (\mysqli_sql_exception $e) {
        log_activity_ctx($link, 'CREATE', [
            'entidad' => 'Contact',
            'entidad_id' => $id_Cliente,
            'descripcion' => "Error al crear contacto '$contact_nombre' para cliente id $id_Cliente",
            'datos' => $_POST,
            'resultado' => 'error',
        ]);
        header('Location: ../dash-customers.php?status=error&msg=' . urlencode('No se pudo crear el contacto'));
    }

}else{
    header('Location: ../dash-customers.php?status=error&msg=' . urlencode('No se pudo crear el contacto'));
}
// Cerrar la conexión
$link->close();
