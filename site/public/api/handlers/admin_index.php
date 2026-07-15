<?php

declare(strict_types=1);

function admin_index_handler(array $params): void
{
    require_auth();

    success_response([
        'message' => 'this is admin',
    ]);
}