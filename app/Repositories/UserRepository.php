<?php

namespace App\Repositories;

use App\Core\Database;
use PDO;

class UserRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findByEmailOrUsername(string $value): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM users 
         WHERE email = :email OR username = :username
         LIMIT 1"
        );

        $stmt->execute([
            'email' => $value,
            'username' => $value,
        ]);

        $user = $stmt->fetch();

        return $user ?: null;
    }


    public function existsByEmail(string $email): bool
    {
        $stmt = $this->db->prepare(
            "SELECT id FROM users WHERE email = :email LIMIT 1"
        );
        $stmt->execute(['email' => $email]);

        return (bool) $stmt->fetch();
    }

    public function existsByUsername(string $username): bool
    {
        $stmt = $this->db->prepare(
            "SELECT id FROM users WHERE username = :username LIMIT 1"
        );
        $stmt->execute(['username' => $username]);

        return (bool) $stmt->fetch();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO users (username, email, password_hash)
             VALUES (:username, :email, :password_hash)"
        );

        $stmt->execute([
            'username'      => $data['username'],
            'email'         => $data['email'],
            'password_hash' => $data['password_hash'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT id, username, email, bio, avatar_path
             FROM users
             WHERE id = :id
             LIMIT 1"
        );

        $stmt->execute(['id' => $id]);

        $user = $stmt->fetch();
        return $user ?: null;
    }

    // Userpage için username ve bio güncelleme
    public function updateProfile(int $id, string $username, string $bio): void
    {
        $stmt = $this->db->prepare(
            "UPDATE users
             SET username = :username, bio = :bio
             WHERE id = :id"
        );

        $stmt->execute([
            'username' => $username,
            'bio' => $bio === '' ? null : $bio,
            'id' => $id,
        ]);
    }

    // Userpage için avatar yolunu gğncelleme

    public function updateAvatar(int $id, string $avatarPath): void
    {
        $stmt = $this->db->prepare(
            "UPDATE users
            SET avatar_path = :avatar_path
            WHERE id = :id"
        );

        $stmt->execute([
            'avatar_path' => $avatarPath,
            'id' => $id,
        ]);
    }

    // Güncelleme sırasında email veya username'in başka user tarafından kullanılmadığını kontrol etme

    public function existsByEmailExceptId(string $email, int $exceptId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT id FROM users WHERE email = :email AND id != :id LIMIT 1"
        );
        $stmt->execute(['email' => $email, 'id' => $exceptId]);

        return (bool) $stmt->fetch();
    }

    public function existsByUsernameExceptId(string $username, int $exceptId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT id FROM users WHERE username = :username AND id != :id LIMIT 1"
        );
        $stmt->execute(['username' => $username, 'id' => $exceptId]);

        return (bool) $stmt->fetch();
    }

    public function listOtherUsers(int $meId, int $limit = 20): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, username, avatar_path
         FROM users
         WHERE id <> :me
         ORDER BY id DESC
         LIMIT {$limit}"
        );

        $stmt->execute(['me' => $meId]);
        return $stmt->fetchAll();
    }

    public function usernameExistsForOtherUser(int $userId, string $username): bool
    {
        $stmt = $this->db->prepare(
            "SELECT id FROM users 
         WHERE username = :username AND id != :id
         LIMIT 1"
        );

        $stmt->execute([
            'username' => $username,
            'id' => $userId,
        ]);

        return (bool) $stmt->fetch();
    }
}
