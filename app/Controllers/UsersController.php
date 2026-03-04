<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Repositories\UserRepository;
use App\Repositories\PostRepository;
use App\Repositories\FollowRepository;

class UsersController extends Controller
{
    private UserRepository  $users;
    private PostRepository  $posts;
    private FollowRepository $follows;

    public function __construct()
    {
        $this->users   = new UserRepository();
        $this->posts   = new PostRepository();
        $this->follows = new FollowRepository();
    }

    public function index(): void
    {
        $this->requireLogin();

        $me         = (int) $_SESSION['user']['id'];
        $otherUsers = $this->users->listOtherUsers($me);

        $this->render('users/index', compact('otherUsers'));
    }

    public function show(): void
    {
        $this->requireLogin();

        $me = (int) $_SESSION['user']['id'];
        $id = (int) ($_GET['id'] ?? 0);

        if ($id <= 0) {
            $this->setErrors(['Geçersiz kullanıcı.']);
            $this->redirect('/users');
            return;
        }

        // Kendi profiline girerse userpage'e yönlendir
        if ($id === $me) {
            $this->redirect('/userpage');
            return;
        }

        $profile = $this->users->findById($id);

        if (!$profile) {
            $this->setErrors(['Kullanıcı bulunamadı.']);
            $this->redirect('/users');
            return;
        }

        $posts          = $this->posts->listByUser($id);
        $isFollowing    = $this->follows->isFollowing($me, $id);
        $followersCount = $this->follows->countFollowers($id);
        $followingCount = $this->follows->countFollowing($id);

        $this->render('users/show', compact(
            'profile', 'posts', 'isFollowing', 'followersCount', 'followingCount'
        ));
    }
}