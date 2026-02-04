<?php

namespace App\Controllers;

use App\Repositories\UserRepository;
use App\Repositories\PostRepository;
use App\Repositories\FollowRepository;

class UsersController
{
    private UserRepository $users;
    private PostRepository $posts;
    private FollowRepository $follows;

    public function __construct()
    {
        $this->users = new UserRepository();
        $this->posts = new PostRepository();
        $this->follows = new FollowRepository();
    }

    private function requireLogin(): void
    {
        if (!isset($_SESSION['user'])) {
            header('Location: /blog-app/public/login');
            exit;
        }
    }

    // GET /users  -> diğer kullanıcıları listeler
    public function index(): void
    {
        $this->requireLogin();

        $me = (int) $_SESSION['user']['id'];

        // Diğer kullanıcılar
        $otherUsers = $this->users->listOtherUsers($me);

        // View: users/index.php
        require __DIR__ . '/../Views/users/index.php';
    }

    // GET /users/show?id=3  -> seçilen kullanıcının profili
    public function show(): void
    {
        $this->requireLogin();

        $me = (int) $_SESSION['user']['id'];
        $id = (int) ($_GET['id'] ?? 0);

        if ($id <= 0) {
            $_SESSION['errors'] = ['Geçersiz kullanıcı.'];
            header('Location: /blog-app/public/users');
            exit;
        }

        $profile = $this->users->findById($id);

        if (!$profile) {
            $_SESSION['errors'] = ['Kullanıcı bulunamadı.'];
            header('Location: /blog-app/public/users');
            exit;
        }

        // Profilin postları
        $posts = $this->posts->listByUser($id);

        // Follow bilgisi
        $isFollowing = ($id !== $me) ? $this->follows->isFollowing($me, $id) : false;

        // Sayaçlar (opsiyonel ama güzel)
        $followersCount = $this->follows->countFollowers($id);
        $followingCount = $this->follows->countFollowing($id);

        require __DIR__ . '/../Views/users/show.php';
    }
}
