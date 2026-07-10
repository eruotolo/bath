<?php

namespace App\Application\Service;

use App\Domain\Service\ServiceRepositoryInterface;

final class FindServiceWithContractAndCustomer
{
    public function __construct(private readonly ServiceRepositoryInterface $repository) {}

    /**
     * @return array<string, mixed>|null
     */
    public function handle(int $id): ?array
    {
        return $this->repository->findWithContractAndCustomer($id);
    }
}
