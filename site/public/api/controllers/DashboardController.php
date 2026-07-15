<?php

declare(strict_types=1);

class DashboardController
{
    private DashboardService $service;

    public function __construct()
    {
        $this->service = new DashboardService(getPDO());
    }

    public function index(array $params): void
    {
        require_auth();
        $data = $this->service->getData();
        success_response($data, 'Dashboard data');
    }
}