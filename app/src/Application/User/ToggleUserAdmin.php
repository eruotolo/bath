<?php

namespace App\Application\User;

use App\Domain\User\UserRepositoryInterface;

final class ToggleUserAdmin
{
    public function __construct(private readonly UserRepositoryInterface $repository) {}

    public function handle(int $id, int $currentCategory): void
    {
        $newCategory = $currentCategory === 1 ? 2 : 1;
        $this->repository->setCategory($id, $newCategory);
    }
}
