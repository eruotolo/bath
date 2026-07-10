<?php

namespace App\Application\Invoice;

use App\Domain\Invoice\Invoice;
use App\Domain\Invoice\InvoiceRepositoryInterface;

final class FindInvoice
{
    public function __construct(private readonly InvoiceRepositoryInterface $repository) {}

    public function handle(int $id): ?Invoice
    {
        return $this->repository->find($id);
    }
}
