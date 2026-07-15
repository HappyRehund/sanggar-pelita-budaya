<?php

declare(strict_types=1);

require_once __DIR__ . '/handlers/csrf.php';
require_once __DIR__ . '/handlers/auth.php';
require_once __DIR__ . '/handlers/admin_index.php';
require_once __DIR__ . '/middleware/auth.php';
require_once __DIR__ . '/middleware/csrf.php';

$routes = [];

function route(string $method, string $pattern, callable $handler): void
{
    global $routes;
    $routes[] = [
        'method' => $method,
        'pattern' => $pattern,
        'handler' => $handler,
    ];
}

function match_route(string $method, string $path): ?array
{
    global $routes;

    foreach ($routes as $route) {
        if ($route['method'] !== $method && $route['method'] !== 'ANY') {
            continue;
        }

        $pattern = $route['pattern'];
        $regex = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';

        if (preg_match($regex, $path, $matches)) {
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            return [$route['handler'], $params];
        }
    }

    return null;
}

function apply_cors(): void
{
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

    if (in_array($origin, CORS_ALLOWED_ORIGINS, true)) {
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token, Authorization');
        header('Access-Control-Max-Age: 86400');
    }
}

route('GET', '/api/health', function () {
    $db = getPDO();
    $stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table'");
    $tables = array_column($stmt->fetchAll(), 'name');

    success_response([
        'status' => 'ok',
        'time' => date('c'),
        'env' => APP_ENV,
        'tables' => $tables,
    ]);
});

route('GET', '/api/info', function () {
    success_response([
        'app' => 'Sanggar Pelita Budaya API',
        'version' => '1.0.0',
        'php' => PHP_VERSION,
    ]);
});

route('GET', '/api/csrf-token', 'get_csrf_token');

route('POST', '/api/auth/login', 'login_handler');
route('POST', '/api/auth/logout', 'logout_handler');
route('GET', '/api/auth/me', 'me_handler');

route('GET', '/api/admin', 'admin_index_handler');