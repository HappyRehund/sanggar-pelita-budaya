<?php

declare(strict_types=1);

class AuthService
{
    private UserRepository $users;

    public function __construct(PDO $db)
    {
        $this->users = new UserRepository($db);
    }

    public function login(string $username, string $password): array
    {
        $validator = new ValidationService();
        $validator->required('username', $username)->required('password', $password);
        $validator->failOrContinue();

        $user = $this->users->findByUsername($username);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            error_response('Username or password is incorrect', 401);
        }

        session_regenerate_id(true);
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user_id'] = (int) $user['id'];
        $_SESSION['admin_username'] = $user['username'];
        $_SESSION['admin_full_name'] = $user['full_name'];

        return [
            'id' => (int) $user['id'],
            'username' => $user['username'],
            'full_name' => $user['full_name'],
        ];
    }

    public function logout(): void
    {
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
    }

    public function getCurrentUser(): ?array
    {
        if (!is_authenticated()) {
            return null;
        }

        $id = get_current_user_id();
        if ($id === null) {
            return null;
        }

        $user = $this->users->findById($id);
        if (!$user) {
            return null;
        }

        return [
            'id' => (int) $user['id'],
            'username' => $user['username'],
            'full_name' => $user['full_name'],
        ];
    }
}