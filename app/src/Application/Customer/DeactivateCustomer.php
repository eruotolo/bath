<?php

namespace App\Application\Customer;

use App\Domain\Customer\CustomerRepositoryInterface;

final class DeactivateCustomer
{
    public function __construct(private readonly CustomerRepositoryInterface $repository) {}

    public function handle(int $id): void
    {
        $this->repository->setInactive($id);
    }
}
