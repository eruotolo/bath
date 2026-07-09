<?php

namespace App\Application\Contact;

use App\Domain\Contact\Contact;
use App\Domain\Contact\ContactRepositoryInterface;

final class CreateContact
{
    public function __construct(private readonly ContactRepositoryInterface $repository) {}

    public function handle(array $input): int
    {
        $contact = new Contact(
            id: null,
            customerId: (int) $input['id_Cliente'],
            name: $input['nombre_Contacto'],
            lastname: $input['apellido_Contacto'],
            rut: $input['rut_Contacto'],
            phone: $input['telefono_Contacto'],
            address: $input['direccion_Contacto'],
            observation: null,
        );

        return $this->repository->insert($contact);
    }
}
