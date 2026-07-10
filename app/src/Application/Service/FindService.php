<?php

namespace App\Application\Service;

use App\Domain\Service\Service;
use App\Domain\Service\ServiceRepositoryInterface;

final class FindService
{
    public function __construct(private readonly ServiceRepositoryInterface $repository) {}

    public function handle(int $id): ?Service
    {
        return $this->repository->find($id);
    }
}
