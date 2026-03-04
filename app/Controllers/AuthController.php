<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\RedisClient;
use App\Repositories\UserRepository;
use Respect\Validation\Validator as v;

class AuthController extends Controller
{
    private UserRepository $users;

    public function __construct()
    {
        $this->users = new UserRepository();
    }

    public function showSignup(): void
    {
        require __DIR__ . '/../Views/auth/signup.php';
    }

    public function signup(): void
    {
        $this->verifyCsrf();

        $username      = trim($_POST['username'] ?? '');
        $email         = trim($_POST['email'] ?? '');
        $password      = $_POST['password'] ?? '';
        $passwordAgain = $_POST['password_again'] ?? '';

        $errors = [];

        if (!v::alnum()->noWhitespace()->length(3, 20)->validate($username)) {
            $errors[] = "Username 3-20 karakter olmalı, boşluk içermemeli ve sadece harf/rakam olmalı.";
        }
        if (!v::email()->validate($email)) {
            $errors[] = "Email formatı geçersiz.";
        }
        if (!v::stringType()->length(8, null)->validate($password)) {
            $errors[] = "Şifre en az 8 karakter olmalı.";
        }
        if ($password !== $passwordAgain) {
            $errors[] = "Şifreler aynı değil.";
        }

        if (!$errors) {
            if ($this->users->existsByUsername($username)) {
                $errors[] = "Bu username zaten kullanılıyor.";
            }
            if ($this->users->existsByEmail($email)) {
                $errors[] = "Bu email zaten kullanılıyor.";
            }
        }

        if ($errors) {
            $this->setErrors($errors);
            $this->setOld(['username' => $username, 'email' => $email]);
            $this->redirect('/signup');
            return;
        }

        $hash   = password_hash($password, PASSWORD_DEFAULT);
        $userId = $this->users->create([
            'username'      => $username,
            'email'         => $email,
            'password_hash' => $hash,
        ]);

        if (!$userId) {
            $this->setErrors(['Kayıt sırasında bir hata oluştu.']);
            $this->redirect('/signup');
            return;
        }

        $this->setFlash('Kayıt başarılı. Giriş yapabilirsin.');
        $this->redirect('/login');
    }

    public function showLogin(): void
    {
        require __DIR__ . '/../Views/auth/login.php';
    }

    public function login(): void
    {
        $this->verifyCsrf();

        $ip            = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $maxAttempts   = 5;
        $windowSeconds = 60;

        $redis = RedisClient::get();
        $key   = "login:attempts:ip:$ip";
        $attempts = (int) ($redis->get($key) ?? 0);

        if ($attempts >= $maxAttempts) {
            $this->setErrors(["Çok fazla deneme yaptın. Lütfen {$windowSeconds} saniye sonra tekrar dene."]);
            $this->setOld(['identity' => trim($_POST['identity'] ?? '')]);
            $this->redirect('/login');
            return;
        }

        $identity = trim($_POST['identity'] ?? '');
        $password = $_POST['password'] ?? '';
        $errors   = [];

        if ($identity === '') $errors[] = "Email veya username boş olamaz.";
        if ($password === '') $errors[] = "Şifre boş olamaz.";

        if ($errors) {
            $this->incrementAttempts($redis, $key, $windowSeconds);
            $this->setErrors($errors);
            $this->setOld(['identity' => $identity]);
            $this->redirect('/login');
            return;
        }

        $user = $this->users->findByEmailOrUsername($identity);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $this->incrementAttempts($redis, $key, $windowSeconds);
            $this->setErrors(["Email/username veya şifre hatalı."]);
            $this->setOld(['identity' => $identity]);
            $this->redirect('/login');
            return;
        }

        $redis->del([$key]);
        session_regenerate_id(true);

        $_SESSION['user'] = [
            'id'          => (int) $user['id'],
            'username'    => $user['username'],
            'email'       => $user['email'],
            'role'        => $user['role'],
            'avatar_path' => $user['avatar_path'] ?? null,
        ];

        $this->redirect('/dashboard');
    }

    private function incrementAttempts($redis, string $key, int $window): void
    {
        $count = (int) $redis->incr($key);
        if ($count === 1) {
            $redis->expire($key, $window);
        }
    }
}