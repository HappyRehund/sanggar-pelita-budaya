<?php

declare(strict_types=1);

function get_csrf_token(array $params): void
{
    $token = generate_csrf_token();
    success_response(['csrf_token' => $token]);
}