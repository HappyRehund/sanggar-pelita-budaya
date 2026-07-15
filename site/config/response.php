<?php

declare(strict_types=1);

function json_response(mixed $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function success_response(mixed $data = null, string $message = 'Success'): void
{
    json_response([
        'success' => true,
        'message' => $message,
        'data' => $data,
    ]);
}

function error_response(string $message, int $status = 400, array $errors = []): void
{
    $response = [
        'success' => false,
        'message' => $message,
    ];

    if (!empty($errors)) {
        $response['errors'] = $errors;
    }

    json_response($response, $status);
}

function not_found_response(string $message = 'Resource not found'): void
{
    error_response($message, 404);
}

function unauthorized_response(string $message = 'Unauthorized'): void
{
    error_response($message, 401);
}

function forbidden_response(string $message = 'Forbidden'): void
{
    error_response($message, 403);
}

function validation_error_response(array $errors, string $message = 'Validation failed'): void
{
    error_response($message, 422, $errors);
}