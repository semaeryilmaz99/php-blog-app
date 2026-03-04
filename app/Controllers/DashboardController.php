<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\RedisClient;
use App\Repositories\PostRepository;

class DashboardController extends Controller
{
    private PostRepository $posts;

    public function __construct()
    {
        $this->posts = new PostRepository();
    }

    public function index(): void
    {
        $this->requireLogin();

        $viewerId = (int) $_SESSION['user']['id'];
        $q        = trim($_GET['q'] ?? '');

        // Viewer bazlı cache key — her kullanıcı kendi like durumunu görür
        $cacheKey = ($q === '')
            ? "dashboard:user:{$viewerId}:v1"
            : "dashboard:user:{$viewerId}:q:" . sha1($q) . ":v1";

        $ttlSeconds = 60;
        $posts      = null;
        $redis      = null;

        try {
            $redis  = RedisClient::get();
            $cached = $redis->get($cacheKey);

            if ($cached) {
                $decoded = json_decode($cached, true);
                if (is_array($decoded)) {
                    $posts = $decoded;
                }
            }
        } catch (\Throwable $e) {
            $posts = null;
        }

        if (!is_array($posts)) {
            $posts = $this->posts->listAllWithLikes($viewerId, $q);

            try {
                $redis = $redis ?? RedisClient::get();
                $redis->setex($cacheKey, $ttlSeconds, json_encode($posts, JSON_UNESCAPED_UNICODE));
            } catch (\Throwable $e) {
                // Redis yoksa devam et
            }
        }

        $userRepository = new \App\Repositories\UserRepository();
        $otherUsers     = $userRepository->listOtherUsers($viewerId);  

        $this->render('dashboard/index', compact('posts', 'q', 'otherUsers'));
    }
}