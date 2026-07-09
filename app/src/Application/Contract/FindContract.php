<?php

namespace App\Application\Contract;

use App\Domain\Contract\Contract;
use App\Domain\Contract\ContractRepositoryInterface;

final class FindContract
{
    public function __construct(private readonly ContractRepositoryInterface $repository) {}

    public function handle(int $id): ?Contract
    {
        return $this->repository->find($id);
    }
}
