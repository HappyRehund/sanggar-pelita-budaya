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
$portfolioController = new PortfolioController();
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

/* ---- Portfolio (public + admin) --------------------------------- */
route('GET',    '/api/portfolio',              [$portfolioController, 'list']);
route('GET',    '/api/portfolio/featured',     [$portfolioController, 'featured']);
route('GET',    '/api/portfolio/gallery',       [$portfolioController, 'galleryImages']);
route('GET',    '/api/portfolio/slug/{slug}',   [$portfolioController, 'getBySlug']);
route('GET',    '/api/portfolio/{id}',         [$portfolioController, 'getById']);
route('POST',   '/api/portfolio',              [$portfolioController, 'create']);
route('PUT',    '/api/portfolio/{id}',         [$portfolioController, 'update']);
route('DELETE', '/api/portfolio/{id}',         [$portfolioController, 'delete']);

/* ---- Portfolio Media --------------------------------------------- */
route('PUT',    '/api/portfolio/media/reorder',   [$portfolioController, 'reorderMedia']);
route('GET',    '/api/portfolio/{id}/media',      [$portfolioController, 'listMedia']);
route('POST',   '/api/portfolio/{id}/media',      [$portfolioController, 'uploadMedia']);
route('DELETE', '/api/portfolio/media/{id}',      [$portfolioController, 'deleteMedia']);

/* ---- Organization (public GETs + admin CRUD) --------------------- */
route('GET',    '/api/organization',              [$organizationController, 'list']);
route('GET',    '/api/organization/tree',         [$organizationController, 'tree']);
route('PUT',    '/api/organization/reorder',      [$organizationController, 'reorder']);
route('GET',    '/api/organization/{id}',        [$organizationController, 'getById']);
route('POST',   '/api/organization',              [$organizationController, 'create']);
route('PUT',    '/api/organization/{id}',        [$organizationController, 'update']);
route('DELETE', '/api/organization/{id}',        [$organizationController, 'delete']);
route('POST',   '/api/organization/{id}/photo',  [$organizationController, 'uploadPhoto']);

/* ---- Settings (public GET + admin PUT) -------------------------- */
route('GET',    '/api/settings',                  [$settingsController, 'get']);
route('PUT',    '/api/settings',                  [$settingsController, 'update']);

/* ---- Dashboard (admin only) ------------------------------------- */
route('GET',    '/api/dashboard',                 [$dashboardController, 'index']);