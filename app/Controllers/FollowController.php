<?php

namespace App\Controllers;

use App\Repositories\FollowRepository;

class FollowController
{
    private FollowRepository $follows;

    public function __construct()
    {
        $this->follows = new FollowRepository();
    }

    private function requireLogin(): void
    {
        if (!isset($_SESSION['user'])) {
            header('Location: /blog-app/public/login');
            exit;
        }
    }

    private function redirectBack(): void
    {
        $to = $_SERVER['HTTP_REFERER'] ?? '/blog-app/public/userpage';
        header('Location: ' . $to);
        exit;
    }

    /**
     * POST /follows/toggle
     * - Eğer takip ediyorsam: unfollow
     * - Etmiyorsam: follow
     */
    public function toggle(): void
    {
        $this->requireLogin();

        $me = (int) $_SESSION['user']['id'];
        $targetUserId = (int) ($_POST['user_id'] ?? 0);

        if ($targetUserId <= 0) {
            $_SESSION['errors'] = ['Geçersiz kullanıcı.'];
            $this->redirectBack();
        }

        // Kendini takip etmeyi engelle (mantıklı kural)
        if ($targetUserId === $me) {
            $_SESSION['errors'] = ['Kendini takip edemezsin.'];
            $this->redirectBack();
        }

        if ($this->follows->isFollowing($me, $targetUserId)) {
            $this->follows->unfollow($me, $targetUserId);
            $_SESSION['flash'] = 'Takipten çıkarıldı.';
        } else {
            $this->follows->follow($me, $targetUserId);
            $_SESSION['flash'] = 'Takip edildi.';
        }

        $this->redirectBack();
    }
}
