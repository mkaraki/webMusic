<?php

require_once(__DIR__ . '/vendor/autoload.php');

function getEnvInfo(string $name, string | null $fallback = null): string | null
{
    return $_ENV[$name] ?? $_SERVER[$name] ?? $fallback ?? null;
}

if (is_file(__DIR__ . '/.env'))
    Dotenv\Dotenv::createImmutable(__DIR__)->load();


DB::$host = getEnvInfo('DB_HOST', '127.0.0.1');
DB::$port = intval(getEnvInfo('DB_PORT', '3306'));
DB::$user = getEnvInfo('DB_USER', 'webmusic');
DB::$password = getEnvInfo('DB_PASS', 'my-secret-pw');
DB::$dbName = getEnvInfo('DB_NAME', 'webmusic');
DB::$encoding = 'utf8';

$cors_origin = getEnvInfo('CORS_ORIGIN', null);
