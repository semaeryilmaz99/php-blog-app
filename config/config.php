<?php

return [
    'app' => [
        'name'      => $_ENV['APP_NAME'] ?? 'BlogApp',
        'env'       => $_ENV['APP_ENV'] ?? 'local',
        'debug'     => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
        'url'       => rtrim($_ENV['APP_URL'] ?? 'http://localhost', '/'),
        'base_path' => rtrim($_ENV['APP_BASE_PATH'] ?? '/blog-app/public', '/'),
    ],

    'db' => [
        'host'    => $_ENV['DB_HOST'] ?? '127.0.0.1',
        'port'    => (int) ($_ENV['DB_PORT'] ?? 3306),
        'name'    => $_ENV['DB_NAME'] ?? '',
        'user'    => $_ENV['DB_USER'] ?? '',
        'pass'    => $_ENV['DB_PASS'] ?? '',
        'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
    ],

    'redis' => [
        'host'     => $_ENV['REDIS_HOST'] ?? '127.0.0.1',
        'port'     => (int) ($_ENV['REDIS_PORT'] ?? 6379),
        'password' => $_ENV['REDIS_PASSWORD'] ?? null,
        'database' => (int) ($_ENV['REDIS_DB'] ?? 0),
    ],

    'mail' => [
        'host'     => $_ENV['MAIL_HOST'] ?? '',
        'port'     => (int) ($_ENV['MAIL_PORT'] ?? 587),
        'username' => $_ENV['MAIL_USER'] ?? '',
        'password' => $_ENV['MAIL_PASS'] ?? '',
        'from'     => $_ENV['MAIL_FROM'] ?? '',
    ],
];