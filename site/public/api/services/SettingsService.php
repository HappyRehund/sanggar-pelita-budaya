<?php

declare(strict_types=1);

class SettingsService
{
    private PDO $db;
    private SettingsRepository $repo;
    private UploadService $uploadService;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->repo = new SettingsRepository($db);
        $this->uploadService = new UploadService();
    }

    public function get(): array
    {
        $row = $this->repo->find();
        if (!$row) {
            not_found_response('Settings not configured');
        }
        return $this->formatRow($row);
    }

    public function update(array $data): array
    {
        $row = $this->repo->find();
        if (!$row) {
            not_found_response('Settings not found');
        }

        $this->validateFields($data);

        $this->db->beginTransaction();
        try {
            $updateData = $data;
            foreach (['logo', 'favicon', 'default_og_image'] as $field) {
                if (!isset($updateData[$field]) || $updateData[$field] === '') {
                    $updateData[$field] = $row[$field];
                }
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

    public function uploadImage(string $field, array $file): array
    {
        $row = $this->repo->find();
        if (!$row) {
            not_found_response('Settings not found');
        }

        $allowedFields = ['logo', 'favicon', 'default_og_image'];
        if (!in_array($field, $allowedFields, true)) {
            validation_error_response(['field' => 'Invalid image field.']);
        }

        $stored = $this->uploadService->store($file, 'settings');

        $this->db->beginTransaction();
        try {
            if (!empty($row[$field])) {
                $this->uploadService->delete($row[$field]);
            }
            $updateData = $this->formatRow($row);
            $updateData[$field] = $stored['filename'];
            $this->repo->update((int) $row['id'], $updateData);
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
        $validator->required('site_name', $data['site_name'] ?? null);
        $validator->inEnum(
            'default_language',
            $data['default_language'] ?? '',
            ['en', 'id']
        );
        $validator->failOrContinue();
    }

    private function formatRow(array $row): array
    {
        return [
            'id' => (int) $row['id'],
            'site_name' => $row['site_name'],
            'site_description' => $row['site_description'],
            'logo' => $row['logo'] ?? null,
            'favicon' => $row['favicon'] ?? null,
            'default_language' => $row['default_language'],
            'default_og_image' => $row['default_og_image'] ?? null,
            'maintenance_mode' => (int) $row['maintenance_mode'] === 1,
            'updated_at' => $row['updated_at'] ?? null,
        ];
    }
}