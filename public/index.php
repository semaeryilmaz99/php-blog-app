<?php

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Controllers\AuthController;
use App\Controllers\PostController;
use App\Controllers\DashboardController;
use App\Controllers\UserController;
use App\Controllers\FeedController;
use App\Controllers\LikeController;
use App\Controllers\FollowController;
use App\Controllers\UsersController;

// .env yükle
$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

// Config yükle ve sabitleri tanımla
$config = require __DIR__ . '/../config/config.php';

define('BASE_URL', $config['app']['base_path']);
define('APP_DEBUG', $config['app']['debug']);

// Debug modu
if (APP_DEBUG) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// Session başlat
session_start();

// CSRF token üret (session başlangıcında bir kez)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Path çözümle
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base = BASE_URL;
$path = str_starts_with($path, $base) ? substr($path, strlen($base)) : $path;
$path = ($path === '' || $path === false) ? '/' : $path;

$method = $_SERVER['REQUEST_METHOD'];

// ─── ROTALAR ────────────────────────────────────────────

// ROOT
if ($method === 'GET' && $path === '/') {
    header('Location: ' . BASE_URL . '/login');
    exit;
}

// AUTH
if ($method === 'GET'  && $path === '/signup') { (new AuthController())->showSignup(); exit; }
if ($method === 'POST' && $path === '/signup') { (new AuthController())->signup();     exit; }
if ($method === 'GET'  && $path === '/login')  { (new AuthController())->showLogin();  exit; }
if ($method === 'POST' && $path === '/login')  { (new AuthController())->login();      exit; }

// LOGOUT
if ($method === 'POST' && $path === '/logout') {
    session_regenerate_id(true);
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(), '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }
    session_destroy();
    header('Location: ' . BASE_URL . '/login');
    exit;
}

// POSTS
if ($method === 'GET'  && $path === '/posts/create')  { (new PostController())->create(); exit; }
if ($method === 'POST' && $path === '/posts/store')   { (new PostController())->store();  exit; }
if ($method === 'GET'  && $path === '/posts/edit')    { (new PostController())->edit();   exit; }
if ($method === 'POST' && $path === '/posts/update')  { (new PostController())->update(); exit; }
if ($method === 'POST' && $path === '/posts/delete')  { (new PostController())->delete(); exit; }

// DASHBOARD
if ($method === 'GET' && $path === '/dashboard') { (new DashboardController())->index(); exit; }

// FEED
if ($method === 'GET' && $path === '/feed') { (new FeedController())->index(); exit; }

// USERPAGE
if ($method === 'GET'  && $path === '/userpage')                { (new UserController())->show();          exit; }
if ($method === 'POST' && $path === '/userpage/update-profile') { (new UserController())->updateProfile(); exit; }
if ($method === 'POST' && $path === '/userpage/update-avatar')  { (new UserController())->updateAvatar();  exit; }
if ($method === 'POST' && $path === '/userpage/delete-post')    { (new UserController())->deletePost();    exit; }

// LIKES
if ($method === 'POST' && $path === '/likes/toggle') { (new LikeController())->toggle(); exit; }

// FOLLOWS
if ($method === 'POST' && $path === '/follows/toggle') { (new FollowController())->toggle(); exit; }

// USERS
if ($method === 'GET' && $path === '/users')      { (new UsersController())->index(); exit; }
if ($method === 'GET' && $path === '/users/show') { (new UsersController())->show();  exit; }

// 404
http_response_code(404);
require __DIR__ . '/../app/Views/errors/404.php';