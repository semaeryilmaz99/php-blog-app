<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Repositories\LikeRepository;

class LikeController extends Controller
{
    private LikeRepository $likes;

    public function __construct()
    {
        $this->likes = new LikeRepository();
    }

    public function toggle(): void
    {
        $this->requireLogin();
        $this->verifyCsrf();

        $userId = (int) $_SESSION['user']['id'];
        $postId = (int) ($_POST['post_id'] ?? 0);

        if ($postId <= 0) {
            $this->setErrors(['Geçersiz post.']);
            $this->redirectBack('/dashboard');
            return;
        }

        if ($this->likes->isLiked($userId, $postId)) {
            $this->likes->unlike($userId, $postId);
            $this->setFlash('Beğeni kaldırıldı.');
        } else {
            $this->likes->like($userId, $postId);
            $this->setFlash('Beğenildi.');
        }

        $this->redirectBack('/dashboard');
    }
}