<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/response.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/routes.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

if (in_array($_SERVER['REQUEST_METHOD'] ?? '', ['PUT', 'PATCH', 'DELETE'], true)) {
    $content_type = $_SERVER['CONTENT_TYPE'] ?? '';
    if (str_contains($content_type, 'multipart/form-data')) {
        $parsed = parse_request_body();
        if (empty($_POST) && !empty($parsed['post'])) {
            $_POST = $parsed['post'];
        }
    }
}

bootstrapDatabase();

if (APP_ENV === 'development') {
    apply_cors();
    header('Access-Control-Allow-Credentials: true');
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];
$path = $_SERVER['PATH_INFO'] ?? '/';

$path = trim($path, '/');
$path = '/api/' . $path;
$path = rtrim($path, '/') ?: '/api';

$matched = match_route($method, $path);

if ($matched === null) {
    not_found_response('Endpoint not found');
}

[$handler, $params] = $matched;

try {
    call_user_func($handler, $params);
} catch (Throwable $e) {
    if (APP_DEBUG) {
        error_response($e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine(), 500);
    } else {
        error_response('Internal server error', 500);
    }
}