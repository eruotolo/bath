<?php

namespace App\Application\Contract;

use App\Domain\Contract\ContractRepositoryInterface;

final class ListContractsExpiringSoon
{
    public function __construct(private readonly ContractRepositoryInterface $repository) {}

    /**
     * @return array<int, \App\Domain\Contract\Contract>
     */
    public function handle(int $days = 7): array
    {
        return $this->repository->findExpiringSoon($days);
    }
}
