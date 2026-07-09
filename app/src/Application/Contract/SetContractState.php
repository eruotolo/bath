<?php

namespace App\Application\Contract;

use App\Domain\Contract\ContractRepositoryInterface;

final class SetContractState
{
    public function __construct(private readonly ContractRepositoryInterface $repository) {}

    public function handle(int $id, int $state): void
    {
        $this->repository->setState($id, $state);
    }
}
