<?php

namespace App\Application\Invoice;

use App\Domain\Invoice\InvoiceRepositoryInterface;

final class ListInvoices
{
    public function __construct(private readonly InvoiceRepositoryInterface $repository) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function handle(): array
    {
        return $this->repository->listActiveWithDetails();
    }
}
