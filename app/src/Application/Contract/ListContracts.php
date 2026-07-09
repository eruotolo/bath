<?php

namespace App\Application\Contract;

use App\Domain\Contract\ContractRepositoryInterface;

final class ListContracts
{
    public function __construct(private readonly ContractRepositoryInterface $repository) {}

    /**
     * @return array{total:int, items:array<int, array<string, mixed>>}
     */
    public function handle(?int $state): array
    {
        return [
            'total' => $this->repository->count(),
            'items' => $this->repository->listWithCustomerName($state),
        ];
    }
}
