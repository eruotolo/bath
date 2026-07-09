<?php

namespace App\Domain\User;

final class User
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $useremail,
        public readonly string $username,
        public readonly string $passwordHash,
        public readonly ?string $token,
        public readonly ?string $name,
        public readonly ?string $lastname,
        public readonly ?string $image,
        public readonly int $category,
        public readonly int $state,
    ) {}
}
