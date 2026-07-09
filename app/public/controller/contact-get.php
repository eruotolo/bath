<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Contact\FindContact;
use App\Infrastructure\Persistence\MysqliContactRepository;

include ('../layouts/config.php');
global $link;

$id_Contacto = (int) $_POST['id_Contacto'];

$useCase = new FindContact(new MysqliContactRepository($link));
$contact = $useCase->handle($id_Contacto);

if ($contact !== null) {
    echo json_encode([
        'id_Contacto' => $contact->id,
        'id_Cliente' => $contact->customerId,
        'nombre_Contacto' => $contact->name,
        'apellido_Contacto' => $contact->lastname,
        'rut_Contacto' => $contact->rut,
        'telefono_Contacto' => $contact->phone,
        'direccion_Contacto' => $contact->address,
        'observacion_Contacto' => $contact->observation,
    ]);
}else {
    echo "No se encontraron datos para este usuario";
}
