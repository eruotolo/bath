<?php

namespace App\Application\Service;

use App\Domain\Service\ServiceRepositoryInterface;

final class FindServiceForPrint
{
    public function __construct(private readonly ServiceRepositoryInterface $repository) {}

    /**
     * Igual que findWithContractAndCustomer, pero preserva el comportamiento original
     * (INNER JOIN con servicios_bathrooms): si el servicio no tiene ningún baño asignado,
     * no se puede imprimir el comprobante.
     * @return array{service: array<string, mixed>, bathrooms: array<int, array<string, mixed>>}|null
     */
    public function handle(int $id): ?array
    {
        $service = $this->repository->findWithContractAndCustomer($id);
        if ($service === null) {
            return null;
        }

        $bathrooms = $this->repository->listAssignedBathrooms($id);
        if ($bathrooms === []) {
            return null;
        }

        return ['service' => $service, 'bathrooms' => $bathrooms];
    }
}
