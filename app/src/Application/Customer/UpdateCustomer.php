<?php

namespace App\Application\Customer;

use App\Domain\Customer\Customer;
use App\Domain\Customer\CustomerRepositoryInterface;

final class UpdateCustomer
{
    public function __construct(private readonly CustomerRepositoryInterface $repository) {}

    /**
     * Reactiva al cliente (estado_Cliente = 1) — comportamiento heredado del código original,
     * único mecanismo existente para reactivar un cliente inactivo.
     */
    public function handle(int $id, array $input): void
    {
        $customer = new Customer(
            id: $id,
            rut: $input['rutCliente'],
            name: $input['nombreCliente'],
            phone: $input['telefonoCliente'],
            email: $input['emailCliente'],
            address: $input['direccionCliente'],
            region: $input['regionCliente'],
            city: $input['ciudadCliente'],
            commune: $input['comunaCliente'],
            active: true,
        );

        $this->repository->save($customer);
    }
}
