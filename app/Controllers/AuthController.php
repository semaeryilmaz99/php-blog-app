<?php

namespace App\Controllers;

use App\Core\RedisClient;
use App\Repositories\UserRepository;
use Respect\Validation\Validator as v;

class AuthController
{
    private UserRepository $users;

    public function __construct()
    {
        // Kullanıcı işlemlerini yapan repository'yi hazırla.
        $this->users = new UserRepository();
    }

    public function showSignup()
    {
        // Kayıt (signup) formunu göster.
        require __DIR__ . '/../Views/auth/signup.php';
    }

    public function signup()
    {
        // Formdan gelen alanları oku ve temel normalize et.
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordAgain = $_POST['password_again'] ?? '';

        // Validation hatalarını biriktir.
        $errors = [];

        // Validation
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

        // DB unique kontrolleri (validation geçtiyse)
        if (!$errors) {
            // Kullanıcı adı çakışması kontrolü.
            if ($this->users->existsByUsername($username)) {
                $errors[] = "Bu username zaten kullanılıyor.";
            }
            // Email çakışması kontrolü.
            if ($this->users->existsByEmail($email)) {
                $errors[] = "Bu email zaten kullanılıyor.";
            }
        }

        // Hata varsa geri dön
        if ($errors) {
            // Formu yeniden doldurabilmek için hataları ve eski değerleri session'a koy.
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = ['username' => $username, 'email' => $email];
            $this->redirect('/blog-app/public/signup');
        }

        // Password hash
        // Parolayı güvenli şekilde hash'le.
        $hash = password_hash($password, PASSWORD_DEFAULT);

        // Create user
        // Yeni kullanıcı kaydını oluştur.
        $userId = $this->users->create([
            'username' => $username,
            'email' => $email,
            'password_hash' => $hash,
        ]);

        // İstersen otomatik login de yaparız; şimdilik login'e yönlendirelim
        // Başarılı kayıt mesajı ve login sayfasına yönlendirme.
        $_SESSION['flash'] = "Kayıt başarılı. Giriş yapabilirsin.";
        $this->redirect('/blog-app/public/login');
    }

    public function showLogin()
    {
        // Giriş (login) formunu göster.
        require __DIR__ . '/../Views/auth/login.php';
    }

    public function login()
    {
        // --- Redis Rate Limit (IP bazlı) ---
        // IP bazlı deneme sayacı ile brute-force'u sınırla.
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        $maxAttempts = 5;      // 60 saniyede max 5 deneme
        $windowSeconds = 60;

        // Redis üzerinden sayacı oku.
        $redis = RedisClient::get();
        $key = "login:attempts:ip:$ip";

        $attempts = (int) ($redis->get($key) ?? 0);

        if ($attempts >= $maxAttempts) {
            // Limit aşıldıysa kullanıcıyı beklet.
            $_SESSION['errors'] = [
                "Çok fazla deneme yaptın. Lütfen {$windowSeconds} saniye sonra tekrar dene."
            ];
            $_SESSION['old'] = ['identity' => trim($_POST['identity'] ?? '')];
            $this->redirect('/blog-app/public/login');
        }
        // --- /Redis Rate Limit ---

        $identity = trim($_POST['identity'] ?? '');
        $password = $_POST['password'] ?? '';

        // Login formu için basit boşluk kontrolleri.
        $errors = [];

        if ($identity === '') {
            $errors[] = "Email veya username boş olamaz.";
        }

        if ($password === '') {
            $errors[] = "Şifre boş olamaz.";
        }

        if ($errors) {
            // Başarısız deneme say
            $attemptsNow = (int) $redis->incr($key);
            if ($attemptsNow === 1) {
                $redis->expire($key, $windowSeconds);
            }

            // Hataları ve eski girdiyi saklayıp login'e dön.
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = ['identity' => $identity];
            $this->redirect('/blog-app/public/login');
        }

        // Kullanıcıyı email veya username ile bul.
        $user = $this->users->findByEmailOrUsername($identity);

        // Tek tip hata mesajı (güvenlik)
        if (!$user || !password_verify($password, $user['password_hash'])) {
            // Başarısız deneme say
            $attemptsNow = (int) $redis->incr($key);
            if ($attemptsNow === 1) {
                $redis->expire($key, $windowSeconds);
            }

            // Kullanıcıya detay vermeden tek tip hata dön.
            $_SESSION['errors'] = ["Email/username veya şifre hatalı."];
            $_SESSION['old'] = ['identity' => $identity];
            $this->redirect('/blog-app/public/login');
        }

        // Başarılı login: rate-limit sayacını sıfırla
        // Başarılı girişte IP sayacını temizle.
        $redis->del([$key]);

        // Login başarılı: session güvenliği
        // Session fixation'a karşı yeni session id üret.
        session_regenerate_id(true);

        // Giriş yapan kullanıcı bilgisini session'a koy.
        $_SESSION['user'] = [
            'id' => (int) $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'role' => $user['role'],
            'avatar_path' => $user['avatar_path'] ?? null,
        ];

        $this->redirect('/blog-app/public/dashboard');
    }



    private function redirect(string $to): void
    {
        // Basit redirect helper.
        header("Location: {$to}");
        exit;
    }
}
