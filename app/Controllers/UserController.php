<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Repositories\UserRepository;
use App\Repositories\PostRepository;

class UserController extends Controller
{
    private UserRepository $users;
    private PostRepository $posts;

    public function __construct()
    {
        $this->users = new UserRepository();
        $this->posts = new PostRepository();
    }

    public function show(): void
    {
        $this->requireLogin();

        $userId = (int) $_SESSION['user']['id'];

        $user       = $this->users->findById($userId);
        $posts      = $this->posts->listByUser($userId);
        $otherUsers = $this->users->listOtherUsers($userId);

        $flash  = $_SESSION['flash'] ?? null;
        $errors = $_SESSION['errors'] ?? [];
        $old    = $_SESSION['old'] ?? [];
        unset($_SESSION['flash'], $_SESSION['errors'], $_SESSION['old']);

        $this->render('userpage/index', compact(
            'user', 'posts', 'otherUsers', 'flash', 'errors', 'old'
        ));
    }

    public function updateProfile(): void
    {
        $this->requireLogin();
        $this->verifyCsrf();

        $userId   = (int) $_SESSION['user']['id'];
        $username = trim($_POST['username'] ?? '');
        $bio      = strip_tags(trim($_POST['bio'] ?? ''));

        $errors = [];

        if ($username === '') {
            $errors[] = 'Username boş olamaz.';
        } elseif (mb_strlen($username) < 3 || mb_strlen($username) > 50) {
            $errors[] = 'Username 3 ile 50 karakter arasında olmalı.';
        }

        if ($bio !== '' && mb_strlen($bio) > 1000) {
            $errors[] = 'Bio en fazla 1000 karakter olabilir.';
        }

        if ($errors) {
            $this->setErrors($errors);
            $this->setOld(['username' => $username, 'bio' => $bio]);
            $this->redirect('/userpage');
            return;
        }

        if ($this->users->usernameExistsForOtherUser($userId, $username)) {
            $this->setErrors(['Bu username başka bir kullanıcı tarafından kullanılıyor.']);
            $this->setOld(['username' => $username, 'bio' => $bio]);
            $this->redirect('/userpage');
            return;
        }

        $this->users->updateProfile($userId, $username, $bio);
        $_SESSION['user']['username'] = $username;

        $this->setFlash('Profil güncellendi.');
        $this->redirect('/userpage');
    }

    public function updateAvatar(): void
    {
        $this->requireLogin();
        $this->verifyCsrf();

        $userId = (int) $_SESSION['user']['id'];
        $errors = [];

        if (empty($_FILES['avatar']) || $_FILES['avatar']['error'] === UPLOAD_ERR_NO_FILE) {
            $this->setErrors(['Lütfen bir avatar dosyası seç.']);
            $this->redirect('/userpage');
            return;
        }

        if ($_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            $this->setErrors(['Avatar yüklenirken bir hata oluştu.']);
            $this->redirect('/userpage');
            return;
        }

        if ($_FILES['avatar']['size'] > 2 * 1024 * 1024) {
            $this->setErrors(['Avatar dosyası çok büyük (max 2MB).']);
            $this->redirect('/userpage');
            return;
        }

        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $mime    = mime_content_type($_FILES['avatar']['tmp_name']);

        if (!isset($allowed[$mime])) {
            $this->setErrors(['Sadece JPG, PNG veya WEBP avatar yükleyebilirsin.']);
            $this->redirect('/userpage');
            return;
        }

        // Eski avatarı sil
        $current = $this->users->findById($userId);
        if (!empty($current['avatar_path'])) {
            $oldFile = __DIR__ . '/../../public' .
                       str_replace(BASE_URL, '', $current['avatar_path']);
            if (file_exists($oldFile)) unlink($oldFile);
        }

        $ext      = $allowed[$mime];
        $fileName = bin2hex(random_bytes(16)) . '.' . $ext;
        $destDir  = __DIR__ . '/../../public/uploads/avatars';

        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        if (!move_uploaded_file($_FILES['avatar']['tmp_name'], $destDir . '/' . $fileName)) {
            $this->setErrors(['Avatar kaydedilemedi.']);
            $this->redirect('/userpage');
            return;
        }

        $avatarPath = BASE_URL . '/uploads/avatars/' . $fileName;
        $this->users->updateAvatar($userId, $avatarPath);
        $_SESSION['user']['avatar_path'] = $avatarPath;

        $this->setFlash('Avatar güncellendi.');
        $this->redirect('/userpage');
    }

    public function deletePost(): void
    {
        $this->requireLogin();
        $this->verifyCsrf();

        $userId = (int) $_SESSION['user']['id'];
        $postId = (int) ($_POST['post_id'] ?? 0);

        if ($postId <= 0) {
            $this->setErrors(['Geçersiz post id.']);
            $this->redirect('/userpage');
            return;
        }

        // Görseli sil
        $post = $this->posts->findByIdAndUser($postId, $userId);
        if ($post && !empty($post['image_path'])) {
            $filePath = __DIR__ . '/../../public' .
                        str_replace(BASE_URL, '', $post['image_path']);
            if (file_exists($filePath)) unlink($filePath);
        }

        $deleted = $this->posts->deleteByIdAndUser($postId, $userId);

        if (!$deleted) {
            $this->setErrors(['Post bulunamadı veya yetkin yok.']);
            $this->redirect('/userpage');
            return;
        }

        $this->setFlash('Post silindi.');
        $this->redirect('/userpage');
    }
}