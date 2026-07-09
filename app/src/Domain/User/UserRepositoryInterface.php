<?php

namespace App\Domain\User;

interface UserRepositoryInterface
{
    public function insert(User $user): int;

    public function update(int $id, ?string $useremail, ?string $username, ?string $name, ?string $lastname, ?string $image): void;

    public function find(int $id): ?User;

    /**
     * Usuarios activos con el nombre de categoría (join a `category`), para dash-users-list.php.
     * @return array<int, array<string, mixed>>
     */
    public function listActiveWithCategory(): array;

    public function updatePassword(int $id, string $passwordHash): void;

    public function deactivate(int $id, string $passwordHash): void;

    public function setCategory(int $id, int $category): void;
}
