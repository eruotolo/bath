<?php

namespace App\Application\User;

use App\Domain\User\UserRepositoryInterface;

final class DeactivateUser
{
    public function __construct(private readonly UserRepositoryInterface $repository) {}

    public function handle(int $id): void
    {
        $randomPassword = bin2hex(random_bytes(16));
        $this->repository->deactivate($id, password_hash($randomPassword, PASSWORD_DEFAULT));
    }
}
