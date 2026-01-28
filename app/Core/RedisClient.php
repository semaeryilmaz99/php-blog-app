<?php

namespace App\Core;

use Predis\Client;

final class RedisClient
{
    private static ?Client $client = null;

    public static function get(): Client
    {
        if (self::$client) {
            return self::$client;
        }

        $config = require __DIR__ . '/../../config/config.php';
        $r = $config['redis'];

        self::$client = new Client([
            'scheme' => 'tcp',
            'host' => $r['host'],
            'port' => (int) $r['port'],
            'password' => ($r['password'] && $r['password'] !== 'null') ? $r['password'] : null,
        ]);

        return self::$client;
    }
}
