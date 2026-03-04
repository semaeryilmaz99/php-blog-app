<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Repositories\PostRepository;

class PostController extends Controller
{
    private PostRepository $posts;

    public function __construct()
    {
        $this->posts = new PostRepository();
    }

    public function create(): void
    {
        $this->requireLogin();
        $this->render('posts/create');
    }

    public function store(): void
    {
        $this->requireLogin();
        $this->verifyCsrf();

        $userId  = (int) $_SESSION['user']['id'];
        $title   = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');

        $errors = [];
        if ($title === '')   $errors[] = 'Başlık boş olamaz.';
        if ($content === '') $errors[] = 'Metin boş olamaz.';

        $media = $this->handleMediaUpload('media', $errors);

        if ($errors) {
            $this->setErrors($errors);
            $this->setOld(['title' => $title, 'content' => $content]);
            $this->redirect('/posts/create');
            return;
        }

        $slug   = $this->slugify($title) . '-' . bin2hex(random_bytes(3));
        $postId = $this->posts->create([
            'user_id'    => $userId,
            'title'      => $title,
            'slug'       => $slug,
            'content'    => $content,
            'image_path' => $media['path'] ?? null,
            'media_type' => $media['media_type'] ?? null,
        ]);

        if (!$postId) {
            $this->setErrors(['Post kaydedilemedi.']);
            $this->redirect('/posts/create');
            return;
        }

        $this->invalidateDashboardCache($userId);
        $this->setFlash('Post eklendi.');
        $this->redirect('/dashboard');
    }

    public function edit(): void
    {
        $this->requireLogin();

        $userId = (int) $_SESSION['user']['id'];
        $postId = (int) ($_GET['id'] ?? 0);

        if ($postId <= 0) {
            $this->redirect('/dashboard');
            return;
        }

        $post = $this->posts->findByIdAndUser($postId, $userId);

        if (!$post) {
            $this->setErrors(['Post bulunamadı veya yetkin yok.']);
            $this->redirect('/dashboard');
            return;
        }

        $mode = 'edit';
        $this->render('posts/create', compact('post', 'mode'));
    }

    public function update(): void
    {
        $this->requireLogin();
        $this->verifyCsrf();

        $userId = (int) $_SESSION['user']['id'];
        $postId = (int) ($_GET['id'] ?? 0);

        if ($postId <= 0) {
            $this->redirect('/dashboard');
            return;
        }

        $existing = $this->posts->findByIdAndUser($postId, $userId);

        if (!$existing) {
            $this->setErrors(['Post bulunamadı veya yetkin yok.']);
            $this->redirect('/dashboard');
            return;
        }

        $title   = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');

        $errors = [];
        if ($title === '')   $errors[] = 'Başlık boş olamaz.';
        if ($content === '') $errors[] = 'Metin boş olamaz.';

        // Yeni resim yüklendiyse işle, yoksa eskiyi koru
        $imagePath = $existing['image_path'];
        $newMedia = $this->handleMediaUpload('media', $errors);

        if ($newMedia !== null) {
        $this->deleteFile($imagePath);
        $imagePath  = $newMedia['path'];
        $mediaType  = $newMedia['media_type'];
        } else {
        $mediaType = $existing['media_type'] ?? null;
        }

        if ($errors) {
            $this->setErrors($errors);
            $this->setOld(['title' => $title, 'content' => $content]);
            $this->redirect('/posts/edit?id=' . $postId);
            return;
        }

        $updated = $this->posts->updateByIdAndUser($postId, $userId, [
            'title'      => $title,
            'content'    => $content,
            'image_path' => $imagePath,
            'media_type' => $mediaType,
        ]);

        if (!$updated) {
            $this->setErrors(['Post güncellenemedi.']);
            $this->redirect('/posts/edit?id=' . $postId);
            return;
        }

        $this->invalidateDashboardCache($userId);
        $this->setFlash('Post güncellendi.');
        $this->redirect('/dashboard');
    }

    public function delete(): void
    {
        $this->requireLogin();
        $this->verifyCsrf();

        $userId = (int) $_SESSION['user']['id'];
        $postId = (int) ($_POST['id'] ?? 0);

        if ($postId <= 0) {
            $this->setErrors(['Geçersiz post.']);
            $this->redirect('/dashboard');
            return;
        }

        $post = $this->posts->findById($postId);

        if (!$post) {
            $this->setErrors(['Post bulunamadı.']);
            $this->redirect('/dashboard');
            return;
        }

        if ((int) $post['user_id'] !== $userId) {
            $this->setErrors(['Bu postu silme yetkin yok.']);
            $this->redirect('/dashboard');
            return;
        }

        // Görseli sil
        $this->deleteFile($post['image_path'] ?? null);

        $this->posts->deleteById($postId);
        $this->invalidateDashboardCache($userId);

        $this->setFlash('Post silindi.');
        $this->redirect('/dashboard');
    }

    // ─── Private helpers ──────────────────────────────────────

    private function handleMediaUpload(string $field, array &$errors): ?array
{
    if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Dosya yüklenirken hata oluştu.';
        return null;
    }

    $tmp  = $_FILES[$field]['tmp_name'];
    $mime = mime_content_type($tmp);

    $allowedImages = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $allowedVideos = ['video/mp4' => 'mp4'];
    $allowed       = array_merge($allowedImages, $allowedVideos);

    if (!isset($allowed[$mime])) {
        $errors[] = 'Sadece JPG, PNG, WEBP veya MP4 yükleyebilirsin.';
        return null;
    }

    $isVideo  = isset($allowedVideos[$mime]);
    $maxBytes = $isVideo ? 25 * 1024 * 1024 : 2 * 1024 * 1024;

    if ($_FILES[$field]['size'] > $maxBytes) {
        $errors[] = $isVideo
            ? 'Video 25MB\'dan büyük olamaz.'
            : 'Resim 2MB\'dan büyük olamaz.';
        return null;
    }

    $ext     = $allowed[$mime];
    $subDir  = $isVideo ? 'videos' : 'posts';
    $destDir = __DIR__ . '/../../public/uploads/' . $subDir;

    if (!is_dir($destDir)) {
        mkdir($destDir, 0755, true);
    }

    $fileName = bin2hex(random_bytes(16)) . '.' . $ext;

    if (!move_uploaded_file($tmp, $destDir . '/' . $fileName)) {
        $errors[] = 'Dosya kaydedilemedi.';
        return null;
    }

    return [
        'path'       => BASE_URL . '/uploads/' . $subDir . '/' . $fileName,
        'media_type' => $isVideo ? 'video' : 'image',
    ];
}

    /**
     * Fiziksel dosyayı sil
     */
    private function deleteFile(?string $path): void
    {
        if (empty($path)) return;

        $filePath = __DIR__ . '/../../public' .
                    str_replace(BASE_URL, '', $path);

        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    /**
     * Dashboard cache'ini temizle
     */
    private function invalidateDashboardCache(int $userId): void
    {
        try {
            $redis = \App\Core\RedisClient::get();
            $keys  = $redis->keys("dashboard:user:{$userId}:*");
            if ($keys) {
                $redis->del($keys);
            }
        } catch (\Throwable $e) {
            // Redis yoksa devam et
        }
    }

    /**
     * Türkçe karakter destekli slug
     */
    private function slugify(string $text): string
    {
        $tr   = ['ş', 'ğ', 'ı', 'ö', 'ü', 'ç', 'Ş', 'Ğ', 'İ', 'Ö', 'Ü', 'Ç'];
        $en   = ['s', 'g', 'i', 'o', 'u', 'c', 's', 'g', 'i', 'o', 'u', 'c'];
        $text = str_replace($tr, $en, $text);
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        $text = preg_replace('/[\s-]+/', '-', $text);
        return trim($text, '-');
    }
}