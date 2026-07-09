<?php

namespace App\Application\Bathroom;

use App\Domain\Bathroom\BathroomRepositoryInterface;

final class AssignBathroomToContract
{
    public function __construct(private readonly BathroomRepositoryInterface $repository) {}

    public function handle(int $idContrato, int $idBath): void
    {
        $this->repository->assignToContract($idContrato, $idBath);
    }
}
