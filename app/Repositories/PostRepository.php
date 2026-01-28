<?php

namespace App\Repositories;

use App\Core\Database;
use PDO;

class PostRepository
{
    private PDO $db;

    public function __construct()
    {
        // DB bağlantısını alıyoruz
        $this->db = Database::getConnection();
    }

    /**
     * Yeni post oluşturur ve yeni post id'sini döndürür.
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO posts (user_id, title, content, image_path)
             VALUES (:user_id, :title, :content, :image_path)"
        );

        $stmt->execute([
            'user_id' => $data['user_id'],
            'title' => $data['title'],
            'content' => $data['content'],
            'image_path' => $data['image_path'], // null olabilir
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Sadece o kullanıcıya ait postu getirir (güvenlik için user_id ile filtreliyoruz)
     */
    public function findByIdAndUser(int $postId, int $userId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT id, title, content, image_path, created_at
         FROM posts
         WHERE id = :id AND user_id = :user_id
         LIMIT 1"
        );

        $stmt->execute([
            'id' => $postId,
            'user_id' => $userId,
        ]);

        $post = $stmt->fetch();
        return $post ?: null;
    }

    /**
     * Post günceller (yine user_id kontrolü var)
     */
    public function updateByIdAndUser(int $postId, int $userId, array $data): void
    {
        $stmt = $this->db->prepare(
            "UPDATE posts
         SET title = :title,
             content = :content,
             image_path = :image_path
         WHERE id = :id AND user_id = :user_id"
        );

        $stmt->execute([
            'title' => $data['title'],
            'content' => $data['content'],
            'image_path' => $data['image_path'], // null olabilir
            'id' => $postId,
            'user_id' => $userId,
        ]);
    }

    /**
     * Post siler (user_id ile güvenli)
     */
    public function deleteByIdAndUser(int $postId, int $userId): void
    {
        $stmt = $this->db->prepare(
            "DELETE FROM posts WHERE id = :id AND user_id = :user_id"
        );

        $stmt->execute([
            'id' => $postId,
            'user_id' => $userId,
        ]);
    }


    /**
     * Kullanıcının postlarını en yeni en üstte listeler.
     */
    public function listByUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, title, content, image_path, created_at
         FROM posts
         WHERE user_id = :user_id
         ORDER BY created_at DESC"
        );

        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function searchByUser(int $userId, string $q): array
    {
        $like = '%' . $q . '%';

        $stmt = $this->db->prepare(
            "SELECT id, user_id, title, content, image_path, created_at
         FROM posts
         WHERE user_id = :user_id
           AND (title LIKE :q1 OR content LIKE :q2)
         ORDER BY created_at DESC"
        );

        $stmt->execute([
            'user_id' => $userId,
            'q1' => $like,
            'q2' => $like,
        ]);

        return $stmt->fetchAll();
    }
}
