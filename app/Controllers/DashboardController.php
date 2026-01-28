<?php

namespace App\Controllers;

use App\Repositories\PostRepository;

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

        $userId = (int) $_SESSION['user']['id'];

        // 🔍 Arama kelimesi (GET ?q=...)
        $q = trim($_GET['q'] ?? '');

        // Arama varsa filtreli getir, yoksa tüm postları getir
        if ($q !== '') {
            $posts = $this->posts->searchByUser($userId, $q);
        } else {
            $posts = $this->posts->listByUser($userId);
        }

        // View'da arama inputu dolu kalsın diye $q da kullanılabilir
        require __DIR__ . '/../Views/dashboard/index.php';
    }
}
