<?php

namespace App\Application\Bathroom;

use App\Domain\Bathroom\BathroomRepositoryInterface;

final class ListBathroomsByContract
{
    public function __construct(private readonly BathroomRepositoryInterface $repository) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function handle(int $idContrato): array
    {
        return $this->repository->listByContract($idContrato);
    }
}
