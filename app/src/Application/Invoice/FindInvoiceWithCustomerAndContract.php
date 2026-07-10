<?php

namespace App\Application\Invoice;

use App\Domain\Invoice\InvoiceRepositoryInterface;

final class FindInvoiceWithCustomerAndContract
{
    public function __construct(private readonly InvoiceRepositoryInterface $repository) {}

    /**
     * @return array<string, mixed>|null
     */
    public function handle(int $id, int $contractId): ?array
    {
        return $this->repository->findWithCustomerAndContract($id, $contractId);
    }
}
