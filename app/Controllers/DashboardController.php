<?php

namespace App\Controllers;

use App\Repositories\PostRepository;
use App\Core\RedisClient;

class DashboardController
{
    private PostRepository $posts;

    public function __construct()
    {
        $this->posts = new PostRepository();
    }

    public function index()
    {
        if (!isset($_SESSION['user'])) {
            header('Location: /blog-app/public/login');
            exit;
        }

        // Dashboard artık global ama "ben beğendim mi" için viewerId lazım
        $viewerId = (int) $_SESSION['user']['id'];

        // 🔍 Arama kelimesi (GET ?q=...)
        $q = trim($_GET['q'] ?? '');

        // ✅ Global cache key:
        // - arama yoksa tek key
        // - arama varsa q'ya göre farklı key
        $cacheKey = ($q === '')
            ? "dashboard:global:v1"
            : "dashboard:global:q:" . sha1($q) . ":v1";

        $ttlSeconds = 60;

        $posts = null;

        // 1) Redis'ten okumayı dene
        try {
            $redis = RedisClient::get();
            $cached = $redis->get($cacheKey);

            if ($cached) {
                $decoded = json_decode($cached, true);
                if (is_array($decoded)) {
                    $posts = $decoded; // ✅ cache HIT
                }
            }
        } catch (\Throwable $e) {
            $posts = null; // Redis yoksa DB ile devam
        }

        // 2) Cache MISS: DB'den çek + Redis'e yaz
        if (!is_array($posts)) {
            // ✅ Global postlar + like bilgisi (viewer = login user)
            $posts = $this->posts->listAllWithLikes($viewerId, $q);

            try {
                $redis = $redis ?? RedisClient::get();
                $redis->setex($cacheKey, $ttlSeconds, json_encode($posts, JSON_UNESCAPED_UNICODE));
            } catch (\Throwable $e) {
                // Redis'e yazamasak da sorun değil
            }
        }

        require __DIR__ . '/../Views/dashboard/index.php';
    }
}
