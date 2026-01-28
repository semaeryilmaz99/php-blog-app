<?php

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use Predis\Client;

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$redis = new Client([
    'scheme' => 'tcp',
    'host' => $_ENV['REDIS_HOST'] ?? '127.0.0.1',
    'port' => (int) ($_ENV['REDIS_PORT'] ?? 6380),
]);

echo $redis->ping();
