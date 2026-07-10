<?php

namespace App\Application\Invoice;

use App\Domain\Invoice\InvoiceRepositoryInterface;

final class RemoveAssignedService
{
    public function __construct(private readonly InvoiceRepositoryInterface $repository) {}

    public function handle(int $idRelacion): void
    {
        $this->repository->removeAssignedService($idRelacion);
    }
}
