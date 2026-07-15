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

    public function list(bool $publishedOnly = false): array
    {
        $rows = $this->repo->findAll($publishedOnly);
        return array_map('formatOrganizationRow', $rows);
    }

    public function listTree(bool $publishedOnly = false): array
    {
        $rows = $this->repo->findAll($publishedOnly);
        return $this->buildTree($rows);
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

        $this->validateMemberFields($data, $id);

        if (!empty($data['parent_id']) && (int) $data['parent_id'] === $id) {
            validation_error_response(['parent_id' => 'A member cannot be their own parent.']);
        }

        $this->db->beginTransaction();
        try {
            $this->repo->update($id, $data);
            $row = $this->repo->findById($id);
            $this->db->commit();
            return formatOrganizationRow($row);
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
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
        $validator->required('position', $data['position'] ?? null);
        $validator->maxLength('name', $data['name'] ?? '', 255);
        $validator->maxLength('position', $data['position'] ?? '', 255);
        $validator->failOrContinue();
    }

    private function buildTree(array $rows): array
    {
        $byId = [];
        foreach ($rows as $row) {
            $byId[(int) $row['id']] = formatOrganizationRow($row);
            $byId[(int) $row['id']]['children'] = [];
        }

        $roots = [];
        foreach ($byId as $id => &$node) {
            $parentId = $node['parent_id'];
            if ($parentId !== null && isset($byId[$parentId])) {
                $byId[$parentId]['children'][] = &$node;
            } else {
                $roots[] = &$node;
            }
        }
        return $roots;
    }
}