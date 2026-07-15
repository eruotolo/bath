<?php

namespace App\Application\Bathroom;

use App\Domain\Bathroom\BathroomRepositoryInterface;

final class ListAvailableBathrooms
{
    public function __construct(private readonly BathroomRepositoryInterface $repository) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function handle(): array
    {
        return $this->repository->listAvailable();
    }
}
