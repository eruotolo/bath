<?php

namespace App\Application\Service;

use App\Domain\Service\ServiceRepositoryInterface;

final class DeactivateService
{
    public function __construct(private readonly ServiceRepositoryInterface $repository) {}

    public function handle(int $id): void
    {
        $this->repository->setInactive($id);
    }
}
