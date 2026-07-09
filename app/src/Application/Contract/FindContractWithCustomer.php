<?php

namespace App\Application\Contract;

use App\Domain\Contract\ContractRepositoryInterface;

final class FindContractWithCustomer
{
    public function __construct(private readonly ContractRepositoryInterface $repository) {}

    /**
     * @return array<string, mixed>|null
     */
    public function handle(int $id): ?array
    {
        return $this->repository->findWithCustomerName($id);
    }
}
