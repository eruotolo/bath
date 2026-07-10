<?php

namespace App\Application\Invoice;

use App\Domain\Invoice\InvoiceRepositoryInterface;

final class ListUnbilledServicesForContract
{
    public function __construct(private readonly InvoiceRepositoryInterface $repository) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function handle(int $customerId, int $contractId): array
    {
        return $this->repository->listUnbilledServicesForContract($customerId, $contractId);
    }
}
