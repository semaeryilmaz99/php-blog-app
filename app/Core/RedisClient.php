<?php

namespace App\Core;

use Predis\Client;

class RedisClient
{
    private static ?Client $client = null;

    public static function get(): Client
    {
        if (self::$client === null) {
            $host = $_ENV['REDIS_HOST'] ?? '127.0.0.1';
            $port = (int) ($_ENV['REDIS_PORT'] ?? 6379);
            $password = $_ENV['REDIS_PASSWORD'] ?? null;

            $config = [
                'scheme' => 'tcp',
                'host' => $host,
                'port' => $port,
            ];

            if (!empty($password)) {
                $config['password'] = $password;
            }

            self::$client = new Client($config);
        }

        return self::$client;
    }
}
