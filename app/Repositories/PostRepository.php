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

    public function listByUserWithLikes(int $userId, int $viewerId): array
    {
        // userId: postların sahibi
        // viewerId: dashboardu görüntüleyen user (genelde aynı ama ileride farklı olabilir)

        $stmt = $this->db->prepare(
            "SELECT 
            p.*,

            -- Bu postun toplam like sayısı
            (SELECT COUNT(*) FROM post_likes pl WHERE pl.post_id = p.id) AS like_count,

            -- Bu viewer bu postu beğenmiş mi? (1/0)
            EXISTS(
                SELECT 1 FROM post_likes pl2 
                WHERE pl2.post_id = p.id AND pl2.user_id = :viewer_id
            ) AS is_liked

         FROM posts p
         WHERE p.user_id = :user_id
         ORDER BY p.created_at DESC"
        );

        $stmt->execute([
            'user_id' => $userId,
            'viewer_id' => $viewerId,
        ]);

        return $stmt->fetchAll();
    }

    public function listAll(string $q = ''): array
    {
        $hasQuery = trim($q) !== '';
        $like = '%' . $q . '%';

        $sql = "
        SELECT
            p.id, p.user_id, p.title, p.content, p.image_path, p.created_at,
            u.username, u.avatar_path
        FROM posts p
        JOIN users u ON u.id = p.user_id
        WHERE 1=1
    ";

        if ($hasQuery) {
            $sql .= " AND (p.title LIKE :q1 OR p.content LIKE :q2)";
        }

        $sql .= " ORDER BY p.created_at DESC";

        $stmt = $this->db->prepare($sql);

        $params = [];
        if ($hasQuery) {
            $params['q1'] = $like;
            $params['q2'] = $like;
        }

        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function listAllWithLikes(int $viewerId, string $q = ''): array
    {
        $hasQuery = trim($q) !== '';
        $like = '%' . $q . '%';

        $sql = "
        SELECT
            p.id, p.user_id, p.title, p.content, p.image_path, p.created_at,
            u.username, u.avatar_path,

            (SELECT COUNT(*) FROM post_likes pl WHERE pl.post_id = p.id) AS like_count,

            EXISTS(
                SELECT 1 FROM post_likes pl2
                WHERE pl2.post_id = p.id AND pl2.user_id = :viewer_id
            ) AS is_liked

        FROM posts p
        JOIN users u ON u.id = p.user_id
        WHERE 1=1
    ";

        if ($hasQuery) {
            $sql .= " AND (p.title LIKE :q1 OR p.content LIKE :q2)";
        }

        $sql .= " ORDER BY p.created_at DESC";

        $stmt = $this->db->prepare($sql);

        $params = ['viewer_id' => $viewerId];

        if ($hasQuery) {
            $params['q1'] = $like;
            $params['q2'] = $like;
        }

        $stmt->execute($params);

        return $stmt->fetchAll();
    }
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT id, user_id FROM posts WHERE id = :id LIMIT 1"
        );
        $stmt->execute(['id' => $id]);
        $post = $stmt->fetch();
        return $post ?: null;
    }

    public function deleteById(int $id): void
    {
        $stmt = $this->db->prepare(
            "DELETE FROM posts WHERE id = :id"
        );
        $stmt->execute(['id' => $id]);
    }
}
