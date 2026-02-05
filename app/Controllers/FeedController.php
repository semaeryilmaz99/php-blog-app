<?php

namespace App\Controllers;

use App\Repositories\FeedRepository;

class FeedController
{
    private FeedRepository $feed;

    public function __construct()
    {
        // Feed’e özel repository
        $this->feed = new FeedRepository();
    }

    public function index(): void
    {
        // Giriş kontrolü
        if (!isset($_SESSION['user'])) {
            header('Location: /blog-app/public/login');
            exit;
        }

        // Login olan kullanıcı
        $viewerId = (int) $_SESSION['user']['id'];

        // Feed = takip edilen kullanıcıların postları
        $posts = $this->feed->getFeedPosts($viewerId);

        // View
        require __DIR__ . '/../Views/feed/index.php';
    }
}
