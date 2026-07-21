<?php

declare(strict_types=1);

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

/* ---- Controllers (autoloader resolves class files) --------------- */
$authController = new AuthController();
$csrfController = new CsrfController();
$highlightController = new HighlightController();
$organizationController = new OrganizationController();
$settingsController = new SettingsController();
$dashboardController = new DashboardController();

/* ---- Health & info (no auth) ------------------------------------- */
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

/* ---- CSRF -------------------------------------------------------- */
route('GET', '/api/csrf-token', [$csrfController, 'token']);

/* ---- Auth (spec-aligned paths) ----------------------------------- */
route('POST', '/api/login', [$authController, 'login']);
route('POST', '/api/logout', [$authController, 'logout']);
route('GET', '/api/session', [$authController, 'session']);

/* Legacy compat aliases (old /api/auth/* paths redirect to new) ---- */
route('POST', '/api/auth/login', [$authController, 'login']);
route('POST', '/api/auth/logout', [$authController, 'logout']);
route('GET', '/api/auth/me', [$authController, 'session']);

/* ---- Highlights (public + admin) --------------------------------- */
route('GET',    '/api/highlights',              [$highlightController, 'list']);
route('GET',    '/api/highlights/featured',     [$highlightController, 'featured']);
route('GET',    '/api/highlights/gallery',      [$highlightController, 'galleryImages']);
route('GET',    '/api/highlights/slug/{slug}',  [$highlightController, 'getBySlug']);
route('GET',    '/api/highlights/{id}',         [$highlightController, 'getById']);
route('POST',   '/api/highlights',              [$highlightController, 'create']);
route('PUT',    '/api/highlights/{id}',         [$highlightController, 'update']);
route('DELETE', '/api/highlights/{id}',         [$highlightController, 'delete']);

/* ---- Highlights Media -------------------------------------------- */
route('PUT',    '/api/highlights/media/reorder',   [$highlightController, 'reorderMedia']);
route('GET',    '/api/highlights/{id}/media',      [$highlightController, 'listMedia']);
route('POST',   '/api/highlights/{id}/media',      [$highlightController, 'uploadMedia']);
route('DELETE', '/api/highlights/media/{id}',      [$highlightController, 'deleteMedia']);

/* ---- Organization (public GETs + admin CRUD) --------------------- */
route('GET',    '/api/organization',              [$organizationController, 'list']);
route('GET',    '/api/organization/featured',     [$organizationController, 'featured']);
route('PUT',    '/api/organization/reorder',      [$organizationController, 'reorder']);
route('GET',    '/api/organization/{id}',         [$organizationController, 'getById']);
route('POST',   '/api/organization',              [$organizationController, 'create']);
route('PUT',    '/api/organization/{id}',         [$organizationController, 'update']);
route('DELETE', '/api/organization/{id}',         [$organizationController, 'delete']);
route('POST',   '/api/organization/{id}/photo',  [$organizationController, 'uploadPhoto']);

/* ---- Settings (public GET + admin PUT) -------------------------- */
route('GET',    '/api/settings',                  [$settingsController, 'get']);
route('PUT',    '/api/settings',                  [$settingsController, 'update']);

/* ---- Dashboard (admin only) ------------------------------------- */
route('GET',    '/api/dashboard',                 [$dashboardController, 'index']);