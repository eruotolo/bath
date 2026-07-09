<?php

namespace App\Application\Customer;

use App\Domain\Customer\CustomerRepositoryInterface;

final class ListCustomers
{
    public function __construct(private readonly CustomerRepositoryInterface $repository) {}

    /**
     * @return array{total:int, items:\App\Domain\Customer\Customer[]}
     */
    public function handle(): array
    {
        return [
            'total' => $this->repository->count(),
            'items' => $this->repository->listActive(),
        ];
    }
}
