<?php

namespace App\Controllers;

use App\Repositories\PostRepository;

class PostController
{
    private PostRepository $posts;

    public function __construct()
    {
        $this->posts = new PostRepository();
    }

    /**
     * Create post sayfasını gösterir.
     */
    public function create()
    {
        // login olmadan post oluşturulmasın
        if (!isset($_SESSION['user'])) {
            header('Location: /blog-app/public/login');
            exit;
        }

        require __DIR__ . '/../Views/posts/create.php';
    }

    /**
     * Formdan gelen veriyi alır, validasyon yapar, resmi yükler, DB'ye kaydeder.
     * Sonunda dashboard'a yönlendirir.
     */
    public function store()
    {
        if (!isset($_SESSION['user'])) {
            header('Location: /blog-app/public/login');
            exit;
        }

        $userId = (int) $_SESSION['user']['id'];

        // 1) Form verilerini al
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');

        // 2) Basit validasyon
        $errors = [];
        if ($title === '') $errors[] = 'Başlık boş olamaz.';
        if ($content === '') $errors[] = 'Metin boş olamaz.';

        // 3) Resim upload (opsiyonel)
        $imagePath = null;

        // Formda <input type="file" name="image"> olmalı
        if (!empty($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {

            // Upload hatası var mı?
            if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                $errors[] = 'Resim yüklenirken hata oluştu.';
            } else {
                // Güvenlik: sadece belirli MIME tiplerine izin ver
                $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

                $tmp = $_FILES['image']['tmp_name'];
                $mime = mime_content_type($tmp);

                if (!isset($allowed[$mime])) {
                    $errors[] = 'Sadece JPG, PNG veya WEBP yükleyebilirsin.';
                } else {
                    // Dosya adını güvenli üret (random)
                    $ext = $allowed[$mime];
                    $fileName = bin2hex(random_bytes(16)) . '.' . $ext;

                    // Kaydedilecek yol (public içine)
                    $destDir = __DIR__ . '/../../public/uploads/posts';
                    if (!is_dir($destDir)) {
                        // klasör yoksa oluştur (Windows'ta izin gerekebilir)
                        mkdir($destDir, 0777, true);
                    }

                    $destPath = $destDir . '/' . $fileName;

                    // Dosyayı public/uploads/posts içine taşı
                    if (!move_uploaded_file($tmp, $destPath)) {
                        $errors[] = 'Resim kaydedilemedi (move_uploaded_file başarısız).';
                    } else {
                        // DB'de saklayacağımız path (web üzerinden erişilecek)
                        $imagePath = '/blog-app/public/uploads/posts/' . $fileName;
                    }
                }
            }
        }

        // 4) Hata varsa geri dön (old values sakla)
        if ($errors) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = [
                'title' => $title,
                'content' => $content,
            ];
            header('Location: /blog-app/public/posts/create');
            exit;
        }

        // 5) Slug üret (basit + çakışma önlemek için random ek)
        $slug = $this->slugify($title) . '-' . bin2hex(random_bytes(3));

        // 6) DB'ye kaydet
        $this->posts->create([
            'user_id' => $userId,
            'title' => $title,
            'slug' => $slug,
            'content' => $content,
            'image_path' => $imagePath,
        ]);

        // 7) Başarılı: flash mesaj + dashboard'a yönlendir
        $_SESSION['flash'] = 'Post eklendi.';
        header('Location: /blog-app/public/dashboard');
        exit;
    }

    /**
     * Edit sayfası: var olan postu forma doldurur
     */
    public function edit()
    {
        if (!isset($_SESSION['user'])) {
            header('Location: /blog-app/public/login');
            exit;
        }

        $userId = (int) $_SESSION['user']['id'];
        $postId = (int) ($_GET['id'] ?? 0);

        if ($postId <= 0) {
            header('Location: /blog-app/public/dashboard');
            exit;
        }

        // Post gerçekten bu kullanıcıya mı ait?
        $post = $this->posts->findByIdAndUser($postId, $userId);

        if (!$post) {
            $_SESSION['errors'] = ['Post bulunamadı veya yetkin yok.'];
            header('Location: /blog-app/public/dashboard');
            exit;
        }

        // Formda action URL’yi view’a göndermek için (create ile aynı view’u kullanacağız)
        $mode = 'edit';

        require __DIR__ . '/../Views/posts/create.php';
    }

    /**
     * Edit formundan gelen veriyi günceller
     */
    public function update()
    {
        if (!isset($_SESSION['user'])) {
            header('Location: /blog-app/public/login');
            exit;
        }

        $userId = (int) $_SESSION['user']['id'];
        $postId = (int) ($_GET['id'] ?? 0);

        if ($postId <= 0) {
            header('Location: /blog-app/public/dashboard');
            exit;
        }

        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');

        $errors = [];
        if ($title === '') $errors[] = 'Başlık boş olamaz.';
        if ($content === '') $errors[] = 'Metin boş olamaz.';

        // Mevcut postu çekiyoruz (hem yetki kontrolü hem eski resmi korumak için)
        $existing = $this->posts->findByIdAndUser($postId, $userId);
        if (!$existing) {
            $_SESSION['errors'] = ['Post bulunamadı veya yetkin yok.'];
            header('Location: /blog-app/public/dashboard');
            exit;
        }

        // Resim: edit’te yeni resim seçilmezse eski kalsın
        $imagePath = $existing['image_path'];

        // Yeni resim geldiyse upload et
        if (!empty($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                $errors[] = 'Resim yüklenirken hata oluştu.';
            } else {
                $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
                $tmp = $_FILES['image']['tmp_name'];
                $mime = mime_content_type($tmp);

                if (!isset($allowed[$mime])) {
                    $errors[] = 'Sadece JPG, PNG veya WEBP yükleyebilirsin.';
                } else {
                    $ext = $allowed[$mime];
                    $fileName = bin2hex(random_bytes(16)) . '.' . $ext;

                    $destDir = __DIR__ . '/../../public/uploads/posts';
                    if (!is_dir($destDir)) {
                        mkdir($destDir, 0777, true);
                    }

                    $destPath = $destDir . '/' . $fileName;

                    if (!move_uploaded_file($tmp, $destPath)) {
                        $errors[] = 'Resim kaydedilemedi.';
                    } else {
                        $imagePath = '/blog-app/public/uploads/posts/' . $fileName;
                    }
                }
            }
        }

        if ($errors) {
            $_SESSION['errors'] = $errors;

            // create.php view’u eski değerleri $old ile basıyordu, edit’te de aynı mantık:
            $_SESSION['old'] = [
                'title' => $title,
                'content' => $content,
            ];

            header('Location: /blog-app/public/posts/edit?id=' . $postId);
            exit;
        }

        // DB update
        $this->posts->updateByIdAndUser($postId, $userId, [
            'title' => $title,
            'content' => $content,
            'image_path' => $imagePath,
        ]);

        $_SESSION['flash'] = 'Post güncellendi.';
        header('Location: /blog-app/public/dashboard');
        exit;
    }
    /**
     * Post silme işlemi
     */
    public function delete()
    {
        // 1) Login kontrolü
        if (!isset($_SESSION['user'])) {
            header('Location: /blog-app/public/login');
            exit;
        }

        $userId = (int) $_SESSION['user']['id'];
        $postId = (int) ($_POST['id'] ?? 0);

        // 2) Geçersiz istek
        if ($postId <= 0) {
            $_SESSION['errors'] = ['Geçersiz post.'];
            header('Location: /blog-app/public/dashboard');
            exit;
        }

        // 3) Postu DB'den çek (var mı + sahibi kim?)
        $post = $this->posts->findById($postId);

        if (!$post) {
            $_SESSION['errors'] = ['Post bulunamadı.'];
            header('Location: /blog-app/public/dashboard');
            exit;
        }

        // 4) Yetki kontrolü (asıl güvenlik)
        if ((int)$post['user_id'] !== $userId) {
            $_SESSION['errors'] = ['Bu postu silme yetkin yok.'];
            header('Location: /blog-app/public/dashboard');
            exit;
        }

        // 5) Silme işlemi (artık güvenli)
        $this->posts->deleteById($postId);

        // ✅ Cache invalidate: dashboard listesi bayatladı
        try {
            $redis = \App\Core\RedisClient::get();
            $redis->del("dashboard:global:v1");
        } catch (\Throwable $e) {
            // Redis yoksa sorun değil
        }

        $_SESSION['flash'] = 'Post silindi.';
        header('Location: /blog-app/public/dashboard');
        exit;
    }




    /**
     * URL için slug üretir.
     * Not: TR karakter desteğini sonra iyileştiririz.
     */
    private function slugify(string $text): string
    {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        $text = preg_replace('/[\s-]+/', '-', $text);
        return trim($text, '-');
    }
}
