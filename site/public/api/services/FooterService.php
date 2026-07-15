<?php

declare(strict_types=1);

class FooterService
{
    private PDO $db;
    private FooterRepository $repo;
    private UploadService $uploadService;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->repo = new FooterRepository($db);
        $this->uploadService = new UploadService();
    }

    public function get(): array
    {
        $row = $this->repo->find();
        if (!$row) {
            not_found_response('Footer not configured');
        }
        return $this->formatRow($row);
    }

    public function update(array $data): array
    {
        $row = $this->repo->find();
        if (!$row) {
            not_found_response('Footer not found');
        }

        $this->validateFields($data);

        $this->db->beginTransaction();
        try {
            $updateData = $data;
            if (!isset($updateData['logo']) || $updateData['logo'] === '') {
                $updateData['logo'] = $row['logo'];
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

    public function uploadLogo(array $file): array
    {
        $row = $this->repo->find();
        if (!$row) {
            not_found_response('Footer not found');
        }

        $stored = $this->uploadService->store($file, 'settings');

        $this->db->beginTransaction();
        try {
            if (!empty($row['logo'])) {
                $this->uploadService->delete($row['logo']);
            }
            $this->repo->update((int) $row['id'], array_merge($this->formatRow($row), [
                'logo' => $stored['filename'],
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
        $validator->optionalUrl('facebook', $data['facebook'] ?? null);
        $validator->optionalUrl('instagram', $data['instagram'] ?? null);
        $validator->optionalUrl('youtube', $data['youtube'] ?? null);
        $validator->optionalUrl('tiktok', $data['tiktok'] ?? null);
        $validator->optionalUrl('maps_url', $data['maps_url'] ?? null);
        $validator->optionalUrl('website', $data['website'] ?? null);
        $validator->failOrContinue();
    }

    private function formatRow(array $row): array
    {
        return [
            'id' => (int) $row['id'],
            'logo' => $row['logo'] ?? null,
            'description' => $row['description'],
            'address' => $row['address'],
            'phone' => $row['phone'],
            'email' => $row['email'],
            'website' => $row['website'],
            'working_hours' => $row['working_hours'],
            'facebook' => $row['facebook'] ?? null,
            'instagram' => $row['instagram'] ?? null,
            'youtube' => $row['youtube'] ?? null,
            'tiktok' => $row['tiktok'] ?? null,
            'maps_url' => $row['maps_url'],
            'copyright' => $row['copyright'],
            'updated_at' => $row['updated_at'] ?? null,
        ];
    }
}