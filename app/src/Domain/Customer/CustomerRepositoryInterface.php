<?php

namespace App\Domain\Customer;

interface CustomerRepositoryInterface
{
    public function findById(int $id): ?Customer;

    public function save(Customer $customer): int;

    public function setInactive(int $id): void;

    public function count(): int;

    /**
     * @return Customer[]
     */
    public function listActive(): array;
}
