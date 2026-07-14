<?php

namespace App\Application\Bathroom;

use App\Domain\Bathroom\BathroomRepositoryInterface;

final class ListBathroomsWithAssignment
{
    public function __construct(private readonly BathroomRepositoryInterface $repository) {}

    /**
     * @return array{total:int, items:array<int, array<string, mixed>>}
     */
    public function handle(): array
    {
        return [
            'total' => $this->repository->count(),
            'items' => $this->repository->listAllWithAssignment(),
        ];
    }
}
