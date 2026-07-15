<?php

declare(strict_types=1);

function require_auth(): void
{
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        unauthorized_response();
    }
}

function is_authenticated(): bool
{
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function get_current_user_id(): ?int
{
    return $_SESSION['admin_user_id'] ?? null;
}

function get_current_username(): ?string
{
    return $_SESSION['admin_username'] ?? null;
}

function get_current_full_name(): ?string
{
    return $_SESSION['admin_full_name'] ?? null;
}