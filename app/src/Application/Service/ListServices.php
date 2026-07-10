<?php

namespace App\Application\Service;

use App\Domain\Service\ServiceRepositoryInterface;

final class ListServices
{
    public function __construct(private readonly ServiceRepositoryInterface $repository) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function handle(): array
    {
        return $this->repository->listActiveWithDetails();
    }
}
