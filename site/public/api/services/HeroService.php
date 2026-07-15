<?php

declare(strict_types=1);

class HeroService
{
    private PDO $db;
    private HeroRepository $repo;
    private UploadService $uploadService;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->repo = new HeroRepository($db);
        $this->uploadService = new UploadService();
    }

    public function get(): array
    {
        $row = $this->repo->find();
        if (!$row) {
            not_found_response('Hero not configured');
        }
        return $this->formatRow($row);
    }

    public function update(array $data): array
    {
        $row = $this->repo->find();
        if (!$row) {
            not_found_response('Hero not found');
        }

        $this->validateFields($data);

        $this->db->beginTransaction();
        try {
            $updateData = $data;
            if (!isset($updateData['background_image']) || $updateData['background_image'] === '') {
                $updateData['background_image'] = $row['background_image'];
            }
            $this->repo->update((int) $row['id'], $updateData);
            $updated = $this->repo->find();
            $this->db->commit();
            return $this->formatRow($updated);
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function uploadBackground(array $file): array
    {
        $row = $this->repo->find();
        if (!$row) {
            not_found_response('Hero not found');
        }

        $stored = $this->uploadService->store($file, 'hero');

        $this->db->beginTransaction();
        try {
            if (!empty($row['background_image'])) {
                $this->uploadService->delete($row['background_image']);
            }
            $this->repo->update((int) $row['id'], array_merge($this->formatRow($row), [
                'background_image' => $stored['filename'],
            ]));
            $updated = $this->repo->find();
            $this->db->commit();
            return $this->formatRow($updated);
        } catch (Throwable $e) {
            $this->db->rollBack();
            $this->uploadService->delete($stored['filename']);
            throw $e;
        }
    }

    private function validateFields(array $data): void
    {
        $validator = new ValidationService();
        $validator->maxLength('headline', $data['headline'] ?? '', 500);
        $validator->maxLength('subtitle', $data['subtitle'] ?? '', 255);
        $validator->failOrContinue();
    }

    private function formatRow(array $row): array
    {
        return [
            'id' => (int) $row['id'],
            'headline' => $row['headline'],
            'subtitle' => $row['subtitle'],
            'description' => $row['description'],
            'background_image' => $row['background_image'] ?? null,
            'primary_button_text' => $row['primary_button_text'],
            'primary_button_url' => $row['primary_button_url'],
            'secondary_button_text' => $row['secondary_button_text'],
            'secondary_button_url' => $row['secondary_button_url'],
            'updated_at' => $row['updated_at'] ?? null,
        ];
    }
}