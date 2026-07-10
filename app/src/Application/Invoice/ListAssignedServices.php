<?php

namespace App\Application\Invoice;

use App\Domain\Invoice\InvoiceRepositoryInterface;

final class ListAssignedServices
{
    public function __construct(private readonly InvoiceRepositoryInterface $repository) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function handle(int $idFactura): array
    {
        return $this->repository->listAssignedServices($idFactura);
    }
}
