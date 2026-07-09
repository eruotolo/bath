<?php

namespace App\Application\User;

use App\Domain\User\User;
use App\Domain\User\UserRepositoryInterface;

final class FindUser
{
    public function __construct(private readonly UserRepositoryInterface $repository) {}

    public function handle(int $id): ?User
    {
        return $this->repository->find($id);
    }
}
