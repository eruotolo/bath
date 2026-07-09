<?php

namespace App\Application\Contact;

use App\Domain\Contact\Contact;
use App\Domain\Contact\ContactRepositoryInterface;

final class UpdateContact
{
    public function __construct(private readonly ContactRepositoryInterface $repository) {}

    public function handle(int $id, array $input): void
    {
        $contact = new Contact(
            id: $id,
            customerId: (int) $input['idCC'],
            name: $input['nombreC'],
            lastname: $input['apellidoC'],
            rut: $input['rutC'],
            phone: $input['telefonoC'],
            address: $input['direccionC'],
            observation: $input['observacionC'],
        );

        $this->repository->update($contact);
    }
}
