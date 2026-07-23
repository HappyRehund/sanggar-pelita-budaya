<?php

declare(strict_types=1);

class OrganizationService
{
    private PDO $db;
    private OrganizationRepository $repo;
    private UploadService $uploadService;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->repo = new OrganizationRepository($db);
        $this->uploadService = new UploadService();
    }

    public function list(): array
    {
        $rows = $this->repo->findAll();
        return array_map('formatOrganizationRow', $rows);
    }

    public function listFeatured(): array
    {
        $rows = $this->repo->findFeatured();
        return array_map('formatOrganizationRow', $rows);
    }

    public function getById(int $id): array
    {
        $row = $this->repo->findById($id);
        if (!$row) {
            not_found_response('Organization member not found');
        }
        return formatOrganizationRow($row);
    }

    public function create(array $data): array
    {
        $this->validateMemberFields($data, null);

        $this->db->beginTransaction();
        try {
            $newSlot = $data['featured_slot'] ?? null;
            if ($newSlot !== null) {
                $this->repo->clearSlot((int) $newSlot);
            }
            $id = $this->repo->insert($data);
            $row = $this->repo->findById($id);
            $this->db->commit();
            return formatOrganizationRow($row);
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function update(int $id, array $data): array
    {
        $existing = $this->repo->findById($id);
        if (!$existing) {
            not_found_response('Organization member not found');
        }

        // PATCH: merge provided keys over the existing row so untouched
        // fields (notably photo) are preserved. PUT: full replace via
        // normalizeInput (which still omits photo, so merge keeps it too).
        $merged = $this->mergeMemberData($existing, $data);

        $this->validateMemberFields($merged, $id);

        $this->db->beginTransaction();
        try {
            // If assigning a featured slot, clear it from any other member first (unique constraint).
            $newSlot = $merged['featured_slot'] ?? null;
            if ($newSlot !== null) {
                $this->repo->clearSlot((int) $newSlot, $id);
            }
            $this->repo->update($id, $merged);
            $row = $this->repo->findById($id);
            $this->db->commit();
            return formatOrganizationRow($row);
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Merge a (possibly partial) update payload over the existing DB row.
     * Only keys present in $patch override $existing. This guarantees that
     * fields the client never sent (e.g. `photo` during a text-only edit)
     * are preserved rather than being nulled out.
     */
    private function mergeMemberData(array $existing, array $patch): array
    {
        $defaults = [
            'name' => $existing['name'] ?? '',
            'position_en' => $existing['position_en'] ?? '',
            'position_id' => $existing['position_id'] ?? '',
            'photo' => $existing['photo'] ?? null,
            'biography_en' => $existing['biography_en'] ?? null,
            'biography_id' => $existing['biography_id'] ?? null,
            'display_order' => (int) ($existing['display_order'] ?? 0),
            'featured_slot' => $existing['featured_slot'] !== null ? (int) $existing['featured_slot'] : null,
        ];

        foreach ($patch as $key => $value) {
            if (array_key_exists($key, $defaults)) {
                $defaults[$key] = $value;
            }
        }

        return $defaults;
    }

    public function delete(int $id): void
    {
        $existing = $this->repo->findById($id);
        if (!$existing) {
            not_found_response('Organization member not found');
        }

        $this->db->beginTransaction();
        try {
            if (!empty($existing['photo'])) {
                $this->uploadService->delete($existing['photo']);
            }
            $this->repo->delete($id);
            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function reorder(array $orderMap): void
    {
        $this->db->beginTransaction();
        try {
            foreach ($orderMap as $memberId => $order) {
                $this->repo->updateDisplayOrder((int) $memberId, (int) $order);
            }
            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function uploadPhoto(int $memberId, array $file): array
    {
        $member = $this->repo->findById($memberId);
        if (!$member) {
            not_found_response('Organization member not found');
        }

        $stored = $this->uploadService->store($file, 'organization');

        $this->db->beginTransaction();
        try {
            if (!empty($member['photo'])) {
                $this->uploadService->delete($member['photo']);
            }
            $this->repo->update($memberId, array_merge(formatOrganizationRow($member), [
                'photo' => $stored['filename'],
            ]));
            $row = $this->repo->findById($memberId);
            $this->db->commit();
            return formatOrganizationRow($row);
        } catch (Throwable $e) {
            $this->db->rollBack();
            $this->uploadService->delete($stored['filename']);
            throw $e;
        }
    }

    private function validateMemberFields(array $data, ?int $ignoreId): void
    {
        $validator = new ValidationService();
        $validator->required('name', $data['name'] ?? null);
        $validator->required('position_en', $data['position_en'] ?? null);
        $validator->required('position_id', $data['position_id'] ?? null);
        $validator->maxLength('name', $data['name'] ?? '', 255);
        $validator->maxLength('position_en', $data['position_en'] ?? '', 255);
        $validator->maxLength('position_id', $data['position_id'] ?? '', 255);

        $slot = $data['featured_slot'] ?? null;
        if ($slot !== null && $slot !== '' && $slot !== 0) {
            $slotInt = (int) $slot;
            if ($slotInt < 1 || $slotInt > 4) {
                $validator->addError('featured_slot', 'Featured slot must be between 1 and 4.');
            }
        }

        $validator->failOrContinue();
    }
}