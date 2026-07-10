<?php

namespace App\Application\Service;

use App\Domain\Service\ServiceRepositoryInterface;

final class ListAssignedBathrooms
{
    public function __construct(private readonly ServiceRepositoryInterface $repository) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function handle(int $idServicio): array
    {
        return $this->repository->listAssignedBathrooms($idServicio);
    }
}
