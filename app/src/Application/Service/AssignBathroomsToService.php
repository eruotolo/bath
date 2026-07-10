<?php

namespace App\Application\Service;

use App\Domain\Service\ServiceRepositoryInterface;

final class AssignBathroomsToService
{
    public function __construct(private readonly ServiceRepositoryInterface $repository) {}

    /**
     * @param int[] $bathIds
     */
    public function handle(int $idServicio, array $bathIds): void
    {
        $this->repository->assignBathrooms($idServicio, $bathIds);
    }
}
