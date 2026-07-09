<?php

namespace App\Application\User;

use App\Domain\User\UserRepositoryInterface;

final class ChangeOwnPassword
{
    public function __construct(private readonly UserRepositoryInterface $repository) {}

    /**
     * @return bool false si el id solicitado no coincide con el usuario logueado (intento de IDOR)
     */
    public function handle(int $sessionUserId, int $requestedId, string $newPassword): bool
    {
        if ($sessionUserId !== $requestedId) {
            return false;
        }

        $this->repository->updatePassword($requestedId, password_hash($newPassword, PASSWORD_DEFAULT));

        return true;
    }
}
