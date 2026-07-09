<?php

namespace App\Application\Customer;

use App\Domain\Customer\Customer;
use App\Domain\Customer\CustomerRepositoryInterface;

final class CreateCustomer
{
    public function __construct(private readonly CustomerRepositoryInterface $repository) {}

    public function handle(array $input): int
    {
        $customer = new Customer(
            id: null,
            rut: $input['rut_Cliente'],
            name: $input['nombre_Cliente'],
            phone: $input['telefono_Cliente'],
            email: $input['email_Cliente'],
            address: $input['direccion_Cliente'],
            region: $input['region_Cliente'],
            city: $input['ciudad_Cliente'],
            commune: $input['comuna_Cliente'],
        );

        return $this->repository->save($customer);
    }
}
