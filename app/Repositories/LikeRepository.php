<?php

namespace App\Repositories;

use App\Core\Database;
use PDO;

class LikeRepository
{
    private PDO $db;

    public function __construct()
    {
        // Database::getConnection() senin PDO bağlantını döndürüyordu
        $this->db = Database::getConnection();
    }

    /**
     * Bu kullanıcı bu postu beğenmiş mi?
     * true/false döner.
     */
    public function isLiked(int $userId, int $postId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT 1
             FROM post_likes
             WHERE user_id = :user_id AND post_id = :post_id
             LIMIT 1"
        );

        $stmt->execute([
            'user_id' => $userId,
            'post_id' => $postId,
        ]);

        // fetchColumn() 1 dönerse true, yoksa false
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Like ekler.
     * UNIQUE(user_id, post_id) sayesinde aynı like 2 kez eklenmez.
     * INSERT IGNORE: aynı kayıt varsa hata vermez, sessizce geçer.
     */
    public function like(int $userId, int $postId): void
    {
        $stmt = $this->db->prepare(
            "INSERT IGNORE INTO post_likes (user_id, post_id)
             VALUES (:user_id, :post_id)"
        );

        $stmt->execute([
            'user_id' => $userId,
            'post_id' => $postId,
        ]);
    }

    /**
     * Like kaldırır.
     */
    public function unlike(int $userId, int $postId): void
    {
        $stmt = $this->db->prepare(
            "DELETE FROM post_likes
             WHERE user_id = :user_id AND post_id = :post_id"
        );

        $stmt->execute([
            'user_id' => $userId,
            'post_id' => $postId,
        ]);
    }

    /**
     * (Opsiyonel) Like sayısı - sonra UI'da göstermek için.
     */
    public function countLikes(int $postId): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM post_likes WHERE post_id = :post_id"
        );

        $stmt->execute(['post_id' => $postId]);

        return (int) $stmt->fetchColumn();
    }
}
