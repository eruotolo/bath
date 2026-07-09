<?php

namespace App\Application\Bathroom;

use App\Domain\Bathroom\Bathroom;
use App\Domain\Bathroom\BathroomRepositoryInterface;

final class FindBathroom
{
    public function __construct(private readonly BathroomRepositoryInterface $repository) {}

    public function handle(int $id): ?Bathroom
    {
        return $this->repository->find($id);
    }
}
