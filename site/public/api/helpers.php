<?php

declare(strict_types=1);

/**
 * Autoloader for backend classes.
 * Maps class name to file in controllers/, services/, repositories/, middleware/.
 */
function autoload_backend_class(string $class): void
{
    $directories = ['controllers', 'services', 'repositories', 'middleware'];
    $suffixes = ['Controller', 'Service', 'Repository', 'Middleware'];

    foreach ($directories as $dir) {
        foreach ($suffixes as $suffix) {
            if (str_ends_with($class, $suffix)) {
                $file = __DIR__ . '/' . $dir . '/' . $class . '.php';
                if (file_exists($file)) {
                    require_once $file;
                    return;
                }
            }
        }
    }
}

spl_autoload_register('autoload_backend_class');

require_once __DIR__ . '/helpers/formatters.php';

/**
 * Parse the JSON body of the current request into an array.
 * Returns an empty array if body is missing or invalid.
 */
function get_json_input(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || $raw === '') {
        return [];
    }

    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

/**
 * Retrieve an uploaded file from $_FILES or parsed multipart input.
 * Returns null if the field is not present or has an upload error.
 */
function get_uploaded_file(string $field): ?array
{
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $content_type = $_SERVER['CONTENT_TYPE'] ?? '';

    if (str_contains($content_type, 'multipart/form-data') && $method !== 'POST') {
        $parsed = parse_multipart_input();
        $files = $parsed['files'];
        return $files[$field] ?? null;
    }

    if (!isset($_FILES[$field])) {
        return null;
    }

    $file = $_FILES[$field];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    return $file;
}

function parse_request_body(): array
{
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $content_type = $_SERVER['CONTENT_TYPE'] ?? '';

    if (in_array($method, ['POST', 'PUT', 'PATCH'], true)) {
        if (str_contains($content_type, 'multipart/form-data')) {
            if ($method === 'POST' && !empty($_POST)) {
                return [
                    'post' => $_POST,
                    'files' => $_FILES,
                ];
            }
            return parse_multipart_input();
        }

        if (str_contains($content_type, 'application/x-www-form-urlencoded')) {
            if ($method === 'POST') {
                return ['post' => $_POST, 'files' => []];
            }
            $raw = file_get_contents('php://input');
            parse_str($raw, $parsed);
            return ['post' => $parsed, 'files' => []];
        }

        if (str_contains($content_type, 'application/json')) {
            $raw = file_get_contents('php://input');
            $json = json_decode($raw, true);
            return ['post' => is_array($json) ? $json : [], 'files' => []];
        }
    }

    return ['post' => $_POST, 'files' => $_FILES];
}

function parse_multipart_input(): array
{
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $post = [];
    $files = [];

    if (str_contains($method, 'POST') && !empty($_POST)) {
        return ['post' => $_POST, 'files' => $_FILES];
    }

    $raw = file_get_contents('php://input');
    if ($raw === false || $raw === '') {
        return ['post' => [], 'files' => []];
    }

    $content_type = $_SERVER['CONTENT_TYPE'] ?? '';
    $boundary = '';
    if (preg_match('/boundary=(?:"([^"]+)"|([^;]+))/i', $content_type, $matches)) {
        $boundary = $matches[1] ?: $matches[2];
    }

    if ($boundary === '') {
        return ['post' => [], 'files' => []];
    }

    $parts = explode('--' . $boundary, $raw);
    array_shift($parts);
    array_pop($parts);

    $tmp_dir = sys_get_temp_dir();

    foreach ($parts as $part) {
        $part = ltrim($part, "\r\n");
        if ($part === '' || $part === "--\r\n" || $part === "--") continue;

        list($raw_headers, $body) = explode("\r\n\r\n", $part, 2);
        $body = preg_replace('/\r\n--$/', '', $body);

        $headers = [];
        foreach (explode("\r\n", $raw_headers) as $line) {
            if (str_contains($line, ':')) {
                list($k, $v) = explode(':', $line, 2);
                $headers[strtolower(trim($k))] = trim($v);
            }
        }

        $disposition = $headers['content-disposition'] ?? '';
        $name = '';
        $filename = '';
        if (preg_match('/name="([^"]+)"/', $disposition, $m)) $name = $m[1];
        if (preg_match('/filename="([^"]+)"/', $disposition, $m)) $filename = $m[1];

        if ($filename !== '') {
            $tmp_path = tempnam($tmp_dir, 'phpup_');
            file_put_contents($tmp_path, $body);

            $ctype = $headers['content-type'] ?? 'application/octet-stream';
            $files[$name] = [
                'name' => $filename,
                'type' => $ctype,
                'tmp_name' => $tmp_path,
                'error' => UPLOAD_ERR_OK,
                'size' => strlen($body),
            ];
        } else {
            $post[$name] = substr($body, 0, -2);
        }
    }

    return ['post' => $post, 'files' => $files];
}