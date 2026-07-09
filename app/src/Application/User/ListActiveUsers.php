<?php

namespace App\Application\User;

use App\Domain\User\UserRepositoryInterface;

final class ListActiveUsers
{
    public function __construct(private readonly UserRepositoryInterface $repository) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function handle(): array
    {
        return $this->repository->listActiveWithCategory();
    }
}
