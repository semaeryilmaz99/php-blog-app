<?php

namespace App\Core;

abstract class Controller
{
    /**
     * Giriş kontrolü
     */
    protected function requireLogin(): void
    {
        if (!isset($_SESSION['user'])) {
            $this->redirect('/login');
        }
    }

    /**
     * CSRF token doğrula
     */
    protected function verifyCsrf(): void
    {
        $token = $_POST['csrf_token'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(403);
            exit('403 Forbidden: Geçersiz CSRF token.');
        }
    }

    /**
     * Yönlendirme — BASE_URL otomatik eklenir
     */
    protected function redirect(string $path): void
    {
        header('Location: ' . BASE_URL . $path);
        exit;
    }

    /**
     * Güvenli geri yönlendirme
     */
    protected function redirectBack(string $fallback = '/dashboard'): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        $host    = parse_url($referer, PHP_URL_HOST);
        $myHost  = $_SERVER['HTTP_HOST'];

        $to = ($host === $myHost) ? $referer : BASE_URL . $fallback;
        header('Location: ' . $to);
        exit;
    }

    /**
     * View render
     */
    protected function render(string $view, array $data = []): void
    {
        extract($data);
        require __DIR__ . '/../Views/' . $view . '.php';
    }

    /**
     * Session flash/errors/old yardımcıları
     */
    protected function setFlash(string $message): void
    {
        $_SESSION['flash'] = $message;
    }

    protected function setErrors(array $errors): void
    {
        $_SESSION['errors'] = $errors;
    }

    protected function setOld(array $old): void
    {
        $_SESSION['old'] = $old;
    }
}