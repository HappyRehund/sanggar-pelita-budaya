<?php

declare(strict_types=1);

class AuthController
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService(getPDO());
    }

    public function login(array $params): void
    {
        require_csrf();

        $input = get_json_input();
        if (empty($input)) {
            $input = $_POST;
        }

        $username = trim((string)($input['username'] ?? ''));
        $password = (string)($input['password'] ?? '');

        $user = $this->authService->login($username, $password);
        success_response(['user' => $user], 'Login successful');
    }

    public function logout(array $params): void
    {
        require_csrf();
        $this->authService->logout();
        success_response(null, 'Logout successful');
    }

    public function session(array $params): void
    {
        $user = $this->authService->getCurrentUser();
        if ($user === null) {
            unauthorized_response();
        }
        success_response($user, 'Session active');
    }
}