<?php

namespace App\Application\Service;

use App\Domain\Service\ServiceRepositoryInterface;

final class RemoveAssignedBathroom
{
    public function __construct(private readonly ServiceRepositoryInterface $repository) {}

    public function handle(int $idRelacion): void
    {
        $this->repository->removeAssignedBathroom($idRelacion);
    }
}
