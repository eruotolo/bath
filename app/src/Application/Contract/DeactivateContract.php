<?php

namespace App\Application\Contract;

use App\Domain\Bathroom\BathroomRepositoryInterface;
use App\Domain\Contract\ContractRepositoryInterface;

final class DeactivateContract
{
    public function __construct(
        private readonly ContractRepositoryInterface $contracts,
        private readonly BathroomRepositoryInterface $bathrooms,
    ) {}

    public function handle(int $id): void
    {
        $this->contracts->setState($id, 1);
        $this->bathrooms->unassignAllFromContract($id);
    }
}
