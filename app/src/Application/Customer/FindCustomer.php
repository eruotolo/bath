<?php

namespace App\Application\Customer;

use App\Domain\Customer\Customer;
use App\Domain\Customer\CustomerRepositoryInterface;

final class FindCustomer
{
    public function __construct(private readonly CustomerRepositoryInterface $repository) {}

    public function handle(int $id): ?Customer
    {
        return $this->repository->findById($id);
    }
}
