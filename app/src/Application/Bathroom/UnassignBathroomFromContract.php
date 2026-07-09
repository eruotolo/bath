<?php

namespace App\Application\Bathroom;

use App\Domain\Bathroom\BathroomRepositoryInterface;

final class UnassignBathroomFromContract
{
    public function __construct(private readonly BathroomRepositoryInterface $repository) {}

    public function handle(int $idRelacion, int $idBath, int $idContrato): void
    {
        $this->repository->unassignFromContract($idRelacion, $idBath);

        if ($this->repository->countAssignedToContract($idContrato) === 0) {
            $this->repository->closeContract($idContrato);
        }
    }
}
