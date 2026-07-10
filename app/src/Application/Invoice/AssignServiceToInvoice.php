<?php

namespace App\Application\Invoice;

use App\Domain\Invoice\InvoiceRepositoryInterface;

final class AssignServiceToInvoice
{
    public function __construct(private readonly InvoiceRepositoryInterface $repository) {}

    public function handle(int $idFactura, int $idServicio): void
    {
        $this->repository->assignService($idFactura, $idServicio);
    }
}
