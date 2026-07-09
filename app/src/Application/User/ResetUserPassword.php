<?php

namespace App\Application\User;

use App\Domain\User\UserRepositoryInterface;

final class ResetUserPassword
{
    public function __construct(private readonly UserRepositoryInterface $repository) {}

    /**
     * @return string la contraseña temporal en texto plano, para comunicarle al usuario
     */
    public function handle(int $id): string
    {
        $temporaryPassword = bin2hex(random_bytes(8));
        $this->repository->updatePassword($id, password_hash($temporaryPassword, PASSWORD_DEFAULT));

        return $temporaryPassword;
    }
}
