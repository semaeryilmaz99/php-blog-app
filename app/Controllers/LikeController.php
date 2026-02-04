<?php

namespace App\Controllers;

use App\Repositories\LikeRepository;

class LikeController
{
    private LikeRepository $likes;

    public function __construct()
    {
        $this->likes = new LikeRepository();
    }

    /**
     * Kullanıcı giriş yapmadan like atamasın
     */
    private function requireLogin(): void
    {
        if (!isset($_SESSION['user'])) {
            header('Location: /blog-app/public/login');
            exit;
        }
    }

    /**
     * Kullanıcıyı geldiği sayfaya geri gönderir.
     * (Dashboard'dan like'a basınca tekrar dashboard'a dönmesi için)
     */
    private function redirectBack(): void
    {
        $to = $_SERVER['HTTP_REFERER'] ?? '/blog-app/public/dashboard';
        header('Location: ' . $to);
        exit;
    }

    /**
     * POST /likes/toggle
     * - Eğer like varsa: unlike
     * - Eğer yoksa: like
     */
    public function toggle(): void
    {
        $this->requireLogin();

        // Login olan kullanıcı id
        $userId = (int) $_SESSION['user']['id'];

        // Formdan gelen post_id
        $postId = (int) ($_POST['post_id'] ?? 0);

        if ($postId <= 0) {
            $_SESSION['errors'] = ['Geçersiz post.'];
            $this->redirectBack();
        }

        // Toggle kontrolü (DB değişikliği burada yapılır)
        if ($this->likes->isLiked($userId, $postId)) {
            $this->likes->unlike($userId, $postId);
            $_SESSION['flash'] = 'Beğeni kaldırıldı.';
        } else {
            $this->likes->like($userId, $postId);
            $_SESSION['flash'] = 'Beğenildi.';
        }

        // En son geri dön
        $this->redirectBack();
    }
}
