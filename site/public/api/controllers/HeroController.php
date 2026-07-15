<?php

declare(strict_types=1);

class HeroController
{
    private HeroService $service;

    public function __construct()
    {
        $this->service = new HeroService(getPDO());
    }

    public function get(array $params): void
    {
        $hero = $this->service->get();
        success_response($hero, 'Hero');
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
                $hero = $this->service->uploadBackground($file);
                $formInput = $this->normalizeInput($input);
                $hero = $this->service->update($formInput);
            } else {
                $hero = $this->service->update($this->normalizeInput($input));
            }
        } else {
            $input = get_json_input();
            $hero = $this->service->update($this->normalizeInput($input));
        }

        success_response($hero, 'Hero updated');
    }

    private function normalizeInput(array $input): array
    {
        return [
            'headline' => trim($input['headline'] ?? ''),
            'subtitle' => trim($input['subtitle'] ?? ''),
            'description' => trim($input['description'] ?? ''),
            'primary_button_text' => trim($input['primary_button_text'] ?? ''),
            'primary_button_url' => trim($input['primary_button_url'] ?? ''),
            'secondary_button_text' => trim($input['secondary_button_text'] ?? ''),
            'secondary_button_url' => trim($input['secondary_button_url'] ?? ''),
        ];
    }
}