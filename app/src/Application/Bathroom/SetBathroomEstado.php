<?php

namespace App\Application\Bathroom;

use App\Domain\Bathroom\BathroomRepositoryInterface;

final class SetBathroomEstado
{
    public function __construct(private readonly BathroomRepositoryInterface $repository) {}

    public function handle(int $id, int $estado): void
    {
        $this->repository->setEstado($id, $estado);
    }
}
