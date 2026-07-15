<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
define('DB_PATH', BASE_PATH . '/data/sanggar.sqlite');

define('APP_ENV', getenv('APP_ENV') ?: 'production');
define('APP_DEBUG', getenv('APP_DEBUG') === '1');

define('CORS_ALLOWED_ORIGINS', [
    'http://localhost:5173',
]);

define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024);
define('UPLOAD_ALLOWED_MIME', ['image/jpeg', 'image/png', 'image/webp']);
define('UPLOAD_ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'webp']);
define('UPLOAD_BASE_PATH', BASE_PATH . '/public/uploads');
define('UPLOAD_PUBLIC_PATH', '/uploads');

define('UPLOAD_FOLDERS', [
    'hero' => 'hero',
    'portfolio' => 'portfolio',
    'organization' => 'organization',
    'settings' => 'settings',
    'documents' => 'documents',
]);

define('PORTFOLIO_PER_PAGE', 12);
define('FEATURED_PORTFOLIO_LIMIT', 6);
define('RELATED_PORTFOLIO_LIMIT', 4);
define('GALLERY_IMAGE_LIMIT', 10);

$directories = [
    BASE_PATH . '/data',
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

foreach (UPLOAD_FOLDERS as $folder) {
    $path = UPLOAD_BASE_PATH . '/' . $folder;
    if (!is_dir($path)) {
        mkdir($path, 0755, true);
    }
}

if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}