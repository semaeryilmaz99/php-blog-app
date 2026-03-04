<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Repositories\FollowRepository;

class FollowController extends Controller
{
    private FollowRepository $follows;

    public function __construct()
    {
        $this->follows = new FollowRepository();
    }

    public function toggle(): void
    {
        $this->requireLogin();
        $this->verifyCsrf();

        $me           = (int) $_SESSION['user']['id'];
        $targetUserId = (int) ($_POST['user_id'] ?? 0);

        if ($targetUserId <= 0) {
            $this->setErrors(['Geçersiz kullanıcı.']);
            $this->redirectBack('/dashboard');
            return;
        }

        if ($targetUserId === $me) {
            $this->setErrors(['Kendini takip edemezsin.']);
            $this->redirectBack('/dashboard');
            return;
        }

        if ($this->follows->isFollowing($me, $targetUserId)) {
            $this->follows->unfollow($me, $targetUserId);
            $this->setFlash('Takipten çıkarıldı.');
        } else {
            $this->follows->follow($me, $targetUserId);
            $this->setFlash('Takip edildi.');
        }

        $this->redirectBack('/dashboard');
    }
}