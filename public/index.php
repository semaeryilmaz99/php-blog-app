<?php

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Controllers\AuthController;
use App\Controllers\PostController;
use App\Controllers\DashboardController;
use App\Controllers\UserController;

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

session_start();

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base = '/blog-app/public'; // Projenin public yolu (sende bu şekilde)
$path = str_starts_with($path, $base) ? substr($path, strlen($base)) : $path;
$path = $path === '' ? '/' : $path;

$method = $_SERVER['REQUEST_METHOD'];

$auth = new AuthController();

// ROOT: /blog-app/public/ açılınca nereye gitsin?
if ($method === 'GET' && $path === '/') {
    header('Location: /blog-app/public/login');
    exit;
}

if ($method === 'GET' && $path === '/signup') {
    $auth->showSignup();
    exit;
}

if ($method === 'POST' && $path === '/signup') {
    $auth->signup();
    exit;
}

if ($method === 'GET' && $path === '/login') {
    $auth->showLogin();
    exit;
}

if ($method === 'POST' && $path === '/login') {
    $auth->login();
    exit;
}

// CREATE POST

$post = new PostController();

// create post sayfası
if ($method === 'GET' && $path === '/posts/create') {
    $post->create();
    exit;
}

// post kaydetme
if ($method === 'POST' && $path === '/posts/store') {
    $post->store();
    exit;
}

// Edit sayfası
if ($method === 'GET' && $path === '/posts/edit') {
    $post->edit();
    exit;
}

// Update
if ($method === 'POST' && $path === '/posts/update') {
    $post->update();
    exit;
}

// Delete (POST)
if ($method === 'POST' && $path === '/posts/delete') {
    $post->delete();
    exit;
}


// DASHBOARD 

$dashboard = new DashboardController();

if ($method === 'GET' && $path === '/dashboard') {
    $dashboard->index();
    exit;
}

// USERPAGE ROUTES

$user = new UserController();
if ($method === 'GET' && $path === '/userpage') {
    $user->show();
    exit;
}

if ($method === 'POST' && $path === '/userpage/update-profile') {
    $user->updateProfile();
    exit;
}

if ($method === 'POST' && $path === '/userpage/update-avatar') {
    $user->updateAvatar();
    exit();
}

if ($method === 'POST' && $path === '/userpage/delete-post') {
    $user->deletePost();
    exit;
}

// LIKE ROUTES
$like = new \App\Controllers\LikeController();
if ($method === 'POST' && $path === '/likes/toggle') {
    $like->toggle();
    exit;
}

// FOLLOW ROUTES
$follow = new \App\Controllers\FollowController();
if ($method === 'POST' && $path === '/follows/toggle') {
    $follow->toggle();
    exit;
}

// USERS (list + show)
$usersController = new \App\Controllers\UsersController();

if ($method === 'GET' && $path === '/users') {
    $usersController->index();
    exit;
}

if ($method === 'GET' && $path === '/users/show') {
    $usersController->show();
    exit;
}

$follow = new \App\Controllers\FollowController();
if ($method === 'POST' && $path === '/follows/toggle') {
    $follow->toggle();
    exit;
}

// Logout
if ($method === 'POST' && $path === '/logout') {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();

    header('Location: /blog-app/public/login');
    exit;
}

http_response_code(404);
echo "404 - Not Found";
