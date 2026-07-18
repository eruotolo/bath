<?php

namespace App\Application\User;

use App\Domain\User\User;
use App\Domain\User\UserRepositoryInterface;

final class CreateUser
{
    public function __construct(private readonly UserRepositoryInterface $repository) {}

    public function handle(array $input, string $imageFilename): int
    {
        $category = isset($input['category']) && in_array((int) $input['category'], [1, 2, 3], true)
            ? (int) $input['category']
            : 2;

        $user = new User(
            id: null,
            useremail: $input['useremail'],
            username: $input['username'],
            passwordHash: password_hash($input['password'], PASSWORD_DEFAULT),
            token: bin2hex(random_bytes(50)),
            name: $input['name'],
            lastname: $input['lastname'],
            image: $imageFilename,
            category: $category,
            state: 1,
        );

        return $this->repository->insert($user);
    }
}
