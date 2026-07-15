<?php

declare(strict_types=1);

class CsrfController
{
    public function token(array $params): void
    {
        $token = generate_csrf_token();
        success_response(['csrf_token' => $token]);
    }
}