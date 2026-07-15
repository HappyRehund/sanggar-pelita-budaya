<?php

declare(strict_types=1);

function login_handler(array $params): void
{
    require_csrf();

    $raw = file_get_contents('php://input');
    $json = null;
    if ($raw !== '' && $raw !== false) {
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            $json = $decoded;
        }
    }

    $username = trim((string)($_POST['username'] ?? ($json['username'] ?? '')));
    $password = (string)($_POST['password'] ?? ($json['password'] ?? ''));

    if ($username === '' || $password === '') {
        error_response('Username and password are required', 400);
    }

    $pdo = getPDO();
    $stmt = $pdo->prepare('SELECT id, username, password, fullname FROM users WHERE username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        error_response('Username or password is incorrect', 401);
    }

    session_regenerate_id(true);
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_user_id'] = (int) $user['id'];
    $_SESSION['admin_username'] = $user['username'];
    $_SESSION['admin_fullname'] = $user['fullname'];

    success_response([
        'user' => [
            'id' => (int) $user['id'],
            'username' => $user['username'],
            'fullname' => $user['fullname'],
        ],
    ], 'Login successful');
}

function logout_handler(array $params): void
{
    require_csrf();

    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();

    success_response(null, 'Logout successful');
}

function me_handler(array $params): void
{
    if (!is_authenticated()) {
        unauthorized_response();
    }

    success_response([
        'id' => get_current_user_id(),
        'username' => get_current_username(),
        'fullname' => $_SESSION['admin_fullname'] ?? '',
    ]);
}