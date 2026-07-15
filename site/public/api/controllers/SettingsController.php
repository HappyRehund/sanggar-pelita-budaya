<?php

declare(strict_types=1);

class SettingsController
{
    private SettingsService $service;

    public function __construct()
    {
        $this->service = new SettingsService(getPDO());
    }

    public function get(array $params): void
    {
        $settings = $this->service->get();
        success_response($settings, 'Settings');
    }

    public function update(array $params): void
    {
        require_auth();
        require_csrf();

        $content_type = $_SERVER['CONTENT_TYPE'] ?? '';

        if (str_contains($content_type, 'multipart/form-data')) {
            $parsed = parse_request_body();
            $input = $parsed['post'];
            $files = $parsed['files'];

            foreach (['logo', 'favicon', 'default_og_image'] as $field) {
                if (isset($files[$field])) {
                    $this->service->uploadImage($field, $files[$field]);
                }
            }
            $settings = $this->service->update($this->normalizeInput($input));
        } else {
            $input = get_json_input();
            $settings = $this->service->update($this->normalizeInput($input));
        }

        success_response($settings, 'Settings updated');
    }

    private function normalizeInput(array $input): array
    {
        return [
            'site_name' => trim($input['site_name'] ?? ''),
            'site_description' => trim($input['site_description'] ?? ''),
            'default_language' => $input['default_language'] ?? 'en',
            'maintenance_mode' => !empty($input['maintenance_mode']) ? 1 : 0,
        ];
    }
}