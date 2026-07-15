<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
define('DB_PATH', BASE_PATH . '/data/sanggar.sqlite');

define('APP_ENV', getenv('APP_ENV') ?: 'production');
define('APP_DEBUG', getenv('APP_DEBUG') === '1');

define('CORS_ALLOWED_ORIGINS', [
    'http://localhost:5173',
]);

$directories = [
    BASE_PATH . '/data',
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}