<?php

namespace App\Repositories;

use App\Core\Database;
use PDO;

class FollowRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * followerId, followingId'yi takip ediyor mu?
     */
    public function isFollowing(int $followerId, int $followingId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT 1
             FROM follows
             WHERE follower_id = :follower AND following_id = :following
             LIMIT 1"
        );

        $stmt->execute([
            'follower' => $followerId,
            'following' => $followingId,
        ]);

        return (bool) $stmt->fetchColumn();
    }

    /**
     * Takip et
     */
    public function follow(int $followerId, int $followingId): void
    {
        $stmt = $this->db->prepare(
            "INSERT IGNORE INTO follows (follower_id, following_id)
             VALUES (:follower, :following)"
        );

        $stmt->execute([
            'follower' => $followerId,
            'following' => $followingId,
        ]);
    }

    /**
     * Takipten çık
     */
    public function unfollow(int $followerId, int $followingId): void
    {
        $stmt = $this->db->prepare(
            "DELETE FROM follows
             WHERE follower_id = :follower AND following_id = :following"
        );

        $stmt->execute([
            'follower' => $followerId,
            'following' => $followingId,
        ]);
    }

    // (Opsiyonel) sayaçlar - sonra userpage'de göstereceğiz
    public function countFollowers(int $userId): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM follows WHERE following_id = :id");
        $stmt->execute(['id' => $userId]);
        return (int) $stmt->fetchColumn();
    }

    public function countFollowing(int $userId): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM follows WHERE follower_id = :id");
        $stmt->execute(['id' => $userId]);
        return (int) $stmt->fetchColumn();
    }
}
