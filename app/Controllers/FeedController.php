<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Repositories\FeedRepository;

class FeedController extends Controller
{
    private FeedRepository $feed;

    public function __construct()
    {
        $this->feed = new FeedRepository();
    }

    public function index(): void
    {
        $this->requireLogin();

        $viewerId = (int) $_SESSION['user']['id'];
        $posts    = $this->feed->getFeedPosts($viewerId);

        $userRepository = new \App\Repositories\UserRepository();
        $otherUsers     = $userRepository->listOtherUsers($viewerId);

    $this->render('feed/index', compact('posts', 'otherUsers'));
    }
}