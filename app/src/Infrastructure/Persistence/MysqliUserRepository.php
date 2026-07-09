<?php

namespace App\Infrastructure\Persistence;

use App\Domain\User\User;
use App\Domain\User\UserRepositoryInterface;
use mysqli;

final class MysqliUserRepository implements UserRepositoryInterface
{
    public function __construct(private readonly mysqli $connection) {}

    public function insert(User $user): int
    {
        $useremail = $user->useremail;
        $username = $user->username;
        $passwordHash = $user->passwordHash;
        $token = $user->token;
        $name = $user->name;
        $lastname = $user->lastname;
        $image = $user->image;
        $category = $user->category;
        $state = $user->state;

        $stmt = $this->connection->prepare(
            'INSERT INTO users (useremail, username, password, token, name, lastname, image, category, state) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->bind_param('sssssssii', $useremail, $username, $passwordHash, $token, $name, $lastname, $image, $category, $state);
        $stmt->execute();

        return $stmt->insert_id;
    }

    public function update(int $id, ?string $useremail, ?string $username, ?string $name, ?string $lastname, ?string $image): void
    {
        if ($image !== null) {
            $stmt = $this->connection->prepare(
                'UPDATE users SET useremail = ?, username = ?, name = ?, lastname = ?, image = ? WHERE id = ?'
            );
            $stmt->bind_param('sssssi', $useremail, $username, $name, $lastname, $image, $id);
        } else {
            $stmt = $this->connection->prepare(
                'UPDATE users SET useremail = ?, username = ?, name = ?, lastname = ? WHERE id = ?'
            );
            $stmt->bind_param('ssssi', $useremail, $username, $name, $lastname, $id);
        }
        $stmt->execute();
    }

    public function find(int $id): ?User
    {
        $stmt = $this->connection->prepare(
            'SELECT id, useremail, username, password, token, name, lastname, image, category, state FROM users WHERE id = ?'
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        if (!$row) {
            return null;
        }

        return new User(
            id: (int) $row['id'],
            useremail: $row['useremail'],
            username: $row['username'],
            passwordHash: $row['password'],
            token: $row['token'],
            name: $row['name'],
            lastname: $row['lastname'],
            image: $row['image'],
            category: (int) $row['category'],
            state: (int) $row['state'],
        );
    }

    public function listActiveWithCategory(): array
    {
        $result = $this->connection->query(
            'SELECT U.id, U.username, U.name, U.lastname, U.useremail, U.image, U.category, C.name_category
             FROM users U JOIN category C ON U.category = C.id_category
             WHERE U.state = 1 ORDER BY U.name ASC'
        );

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function updatePassword(int $id, string $passwordHash): void
    {
        $stmt = $this->connection->prepare('UPDATE users SET password = ? WHERE id = ?');
        $stmt->bind_param('si', $passwordHash, $id);
        $stmt->execute();
    }

    public function deactivate(int $id, string $passwordHash): void
    {
        $stmt = $this->connection->prepare('UPDATE users SET password = ?, state = 0 WHERE id = ?');
        $stmt->bind_param('si', $passwordHash, $id);
        $stmt->execute();
    }

    public function setCategory(int $id, int $category): void
    {
        $stmt = $this->connection->prepare('UPDATE users SET category = ? WHERE id = ?');
        $stmt->bind_param('ii', $category, $id);
        $stmt->execute();
    }
}
