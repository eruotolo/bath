<?php

namespace App\Application\Bathroom;

use App\Domain\Bathroom\BathroomRepositoryInterface;

final class SetBathroomAssigned
{
    public function __construct(private readonly BathroomRepositoryInterface $repository) {}

    public function handle(int $id, int $asignado): void
    {
        $this->repository->setAsignado($id, $asignado);
    }
}
