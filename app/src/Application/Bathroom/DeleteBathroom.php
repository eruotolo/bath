<?php

namespace App\Application\Bathroom;

use App\Domain\Bathroom\BathroomRepositoryInterface;

final class DeleteBathroom
{
    public function __construct(private readonly BathroomRepositoryInterface $repository) {}

    public function handle(int $id): void
    {
        $this->repository->delete($id);
    }
}
