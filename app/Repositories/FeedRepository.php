<?php

namespace App\Repositories;

use App\Core\Database;
use PDO;

class FeedRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Login olan kullanıcının takip ettiği kullanıcıların postlarını getirir
     */
    public function getFeedPosts(int $viewerId): array
    {
        $sql = "
        SELECT 
            p.id,
            p.user_id,
            p.title,
            p.content,
            p.image_path,
            p.created_at,
            COUNT(pl.user_id) AS like_count,
            MAX(CASE WHEN pl.user_id = ? THEN 1 ELSE 0 END) AS is_liked
        FROM posts p
        INNER JOIN follows f 
            ON f.following_id = p.user_id
           AND f.follower_id = ?
        LEFT JOIN post_likes pl
            ON pl.post_id = p.id
        GROUP BY p.id
        ORDER BY p.created_at DESC
    ";

        $stmt = $this->db->prepare($sql);

        // Burada ? placeholder sayısı = 2, verdiğimiz array elemanı = 2 (tam eşleşir)
        $stmt->execute([
            $viewerId, // is_liked için
            $viewerId, // follows join için
        ]);

        return $stmt->fetchAll();
    }
}
