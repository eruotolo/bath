<?php

namespace App\Application\User;

use App\Domain\User\UserRepositoryInterface;

final class UpdateUser
{
    public function __construct(private readonly UserRepositoryInterface $repository) {}

    /**
     * Un campo vacío en el form limpia esa columna (NULL) — comportamiento heredado del código original.
     */
    public function handle(int $id, array $input, ?string $imageFilename): void
    {
        $useremail = ($input['useremail'] ?? '') !== '' ? $input['useremail'] : null;
        $username = ($input['username'] ?? '') !== '' ? $input['username'] : null;
        $name = ($input['name'] ?? '') !== '' ? $input['name'] : null;
        $lastname = ($input['lastname'] ?? '') !== '' ? $input['lastname'] : null;

        $this->repository->update($id, $useremail, $username, $name, $lastname, $imageFilename);
    }
}
