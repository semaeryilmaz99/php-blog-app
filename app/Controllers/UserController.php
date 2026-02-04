<?php

namespace App\Controllers;

use App\Repositories\UserRepository;
use App\Repositories\PostRepository;

class UserController
{
    private UserRepository $users;
    private PostRepository $posts;

    public function __construct()
    {
        // User bilgileri (username, email, bio, avatar_path) için
        $this->users = new UserRepository();

        // User library (kullanıcının postları) için
        $this->posts = new PostRepository();
    }

    /**
     * GET /userpage
     * User page'i gösterir (profil + user library)
     */
    public function show(): void
    {
        $this->requireLogin();

        // Session'dan login olan kullanıcı id'sini alıyoruz
        $userId = (int) $_SESSION['user']['id'];

        $otherUsers = $this->users->listOtherUsers($userId);

        // Kullanıcının güncel bilgilerini DB'den çekiyoruz (session eski kalabilir)
        $user = $this->users->findById($userId);

        // Kullanıcının postlarını çekiyoruz (user library alanını dolduracak)
        $posts = $this->posts->listByUser($userId);

        // Flash / errors / old değerlerini session'dan alıp temizliyoruz
        $flash  = $_SESSION['flash'] ?? null;
        $errors = $_SESSION['errors'] ?? [];
        $old    = $_SESSION['old'] ?? [];

        unset($_SESSION['flash'], $_SESSION['errors'], $_SESSION['old']);

        // View dosyasında $user, $posts, $flash, $errors, $old kullanılabilir
        require __DIR__ . '/../Views/userpage/index.php';
    }

    /**
     * POST /userpage/update-profile
     * Username + Bio güncelleme
     */
    public function updateProfile(): void
    {
        $this->requireLogin();

        $userId = (int) $_SESSION['user']['id'];

        // Formdan gelen verileri al
        $username = trim($_POST['username'] ?? '');
        $bio = trim($_POST['bio'] ?? '');

        // Validasyon (basit)
        $errors = [];

        if ($username === '') {
            $errors[] = 'Username boş olamaz.';
        } elseif (mb_strlen($username) < 3 || mb_strlen($username) > 50) {
            $errors[] = 'Username 3 ile 50 karakter arasında olmalı.';
        }

        // Bio opsiyonel ama çok uzamasın
        if ($bio !== '' && mb_strlen($bio) > 1000) {
            $errors[] = 'Bio en fazla 1000 karakter olabilir.';
        }

        // Hata varsa geri dön
        if ($errors) {
            $_SESSION['errors'] = $errors;

            // Kullanıcı formu tekrar görünce değerler kaybolmasın
            $_SESSION['old'] = [
                'username' => $username,
                'bio' => $bio,
            ];

            $this->redirect('/blog-app/public/userpage');
        }

        // DB update (Repository sadece SQL yapar)
        $this->users->updateProfile($userId, $username, $bio);

        // Session içindeki username'i de güncelle (navbar/dashboard gibi yerlerde kullanılıyor)
        $_SESSION['user']['username'] = $username;

        $_SESSION['flash'] = 'Profil güncellendi.';
        $this->redirect('/blog-app/public/userpage');
    }

    /**
     * POST /userpage/update-avatar
     * Avatar upload + DB avatar_path güncelleme
     */
    public function updateAvatar(): void
    {

        $this->requireLogin();

        $userId = (int) $_SESSION['user']['id'];

        // Dosya seçilmiş mi?
        if (empty($_FILES['avatar']) || $_FILES['avatar']['error'] === UPLOAD_ERR_NO_FILE) {
            $_SESSION['errors'] = ['Lütfen bir avatar dosyası seç.'];
            $this->redirect('/blog-app/public/userpage');
        }

        // Upload hatası var mı?
        if ($_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['errors'] = ['Avatar yüklenirken bir hata oluştu.'];
            $this->redirect('/blog-app/public/userpage');
        }

        // Boyut kontrolü (ör: max 2MB)
        $maxBytes = 2 * 1024 * 1024;
        if ((int)$_FILES['avatar']['size'] > $maxBytes) {
            $_SESSION['errors'] = ['Avatar dosyası çok büyük (max 2MB).'];
            $this->redirect('/blog-app/public/userpage');
        }

        $tmpPath = $_FILES['avatar']['tmp_name'];

        // MIME kontrolü (güvenlik)
        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
        ];

        $mime = mime_content_type($tmpPath);
        if (!isset($allowed[$mime])) {
            $_SESSION['errors'] = ['Sadece JPG, PNG veya WEBP avatar yükleyebilirsin.'];
            $this->redirect('/blog-app/public/userpage');
        }

        // Upload klasörü (public altında)
        $destDir = __DIR__ . '/../../public/uploads/avatars';
        if (!is_dir($destDir)) {
            // Klasör yoksa oluştur (Windows'ta izin gerekiyorsa dikkat)
            mkdir($destDir, 0777, true);
        }

        // Güvenli dosya adı üret
        $ext = $allowed[$mime];
        $fileName = bin2hex(random_bytes(16)) . '.' . $ext;

        // Fiziksel hedef yol
        $destPath = $destDir . '/' . $fileName;

        // Dosyayı taşı
        if (!move_uploaded_file($tmpPath, $destPath)) {
            $_SESSION['errors'] = ['Avatar kaydedilemedi.'];
            $this->redirect('/blog-app/public/userpage');
        }

        // Web üzerinden erişilecek path (DB'ye bunu yazacağız)
        $avatarPath = '/blog-app/public/uploads/avatars/' . $fileName;

        // DB update
        $this->users->updateAvatar($userId, $avatarPath);

        // İstersen session'a da koyabilirsin (opsiyonel)
        $_SESSION['user']['avatar_path'] = $avatarPath;

        $_SESSION['flash'] = 'Avatar güncellendi.';
        $this->redirect('/blog-app/public/userpage');
    }

    /**
     * POST /userpage/delete-post
     * User library içindeki postu siler
     */
    public function deletePost(): void
    {
        $this->requireLogin();

        $userId = (int) $_SESSION['user']['id'];
        $postId = (int) ($_POST['post_id'] ?? 0);

        if ($postId <= 0) {
            $_SESSION['errors'] = ['Geçersiz post id.'];
            $this->redirect('/blog-app/public/userpage');
        }

        // Güvenlik: sadece kendi postunu silebilsin
        $this->posts->deleteByIdAndUser($postId, $userId);

        $_SESSION['flash'] = 'Post silindi.';
        $this->redirect('/blog-app/public/userpage');
    }

    /**
     * Login kontrolü: login değilse login sayfasına atar.
     */
    private function requireLogin(): void
    {
        if (!isset($_SESSION['user'])) {
            $this->redirect('/blog-app/public/login');
        }
    }

    /**
     * Redirect helper
     */
    private function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }
}
