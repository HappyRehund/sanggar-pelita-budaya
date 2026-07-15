<?php

declare(strict_types=1);

class FooterController
{
    private FooterService $service;

    public function __construct()
    {
        $this->service = new FooterService(getPDO());
    }

    public function get(array $params): void
    {
        $footer = $this->service->get();
        success_response($footer, 'Footer');
    }

    public function update(array $params): void
    {
        require_auth();
        require_csrf();

        $content_type = $_SERVER['CONTENT_TYPE'] ?? '';

        if (str_contains($content_type, 'multipart/form-data')) {
            $parsed = parse_request_body();
            $input = $parsed['post'];
            $file = $parsed['files']['file'] ?? null;
            if ($file !== null) {
                $this->service->uploadLogo($file);
            }
            $footer = $this->service->update($this->normalizeInput($input));
        } else {
            $input = get_json_input();
            $footer = $this->service->update($this->normalizeInput($input));
        }

        success_response($footer, 'Footer updated');
    }

    private function normalizeInput(array $input): array
    {
        return [
            'description' => trim($input['description'] ?? ''),
            'address' => trim($input['address'] ?? ''),
            'phone' => trim($input['phone'] ?? ''),
            'email' => trim($input['email'] ?? ''),
            'website' => trim($input['website'] ?? ''),
            'working_hours' => trim($input['working_hours'] ?? ''),
            'facebook' => trim($input['facebook'] ?? '') ?: null,
            'instagram' => trim($input['instagram'] ?? '') ?: null,
            'youtube' => trim($input['youtube'] ?? '') ?: null,
            'tiktok' => trim($input['tiktok'] ?? '') ?: null,
            'maps_url' => trim($input['maps_url'] ?? ''),
            'copyright' => trim($input['copyright'] ?? ''),
        ];
    }
}