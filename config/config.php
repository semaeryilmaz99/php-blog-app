<?php

return [
    'app' => [
        'name'   => $_ENV['APP_NAME'] ?? 'BlogApp',
        'env'    => $_ENV['APP_ENV'] ?? 'local',
        'debug'  => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
        'url'    => $_ENV['APP_URL'] ?? 'http://localhost',
    ],

    'db' => [
        'host'    => $_ENV['DB_HOST'] ?? '127.0.0.1',
        'name'    => $_ENV['DB_NAME'] ?? '',
        'user'    => $_ENV['DB_USER'] ?? '',
        'pass'    => $_ENV['DB_PASS'] ?? '',
        'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
    ],

    'redis' => [
        'host'     => $_ENV['REDIS_HOST'] ?? '127.0.0.1',
        'port'     => $_ENV['REDIS_PORT'] ?? 6379,
        'password' => $_ENV['REDIS_PASSWORD'] ?? null,
    ]
];
