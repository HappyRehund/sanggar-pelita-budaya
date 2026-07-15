<?php

declare(strict_types=1);

class OrganizationRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function insert(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO organization_members
                (parent_id, name, position, photo, biography, display_order, published)
            VALUES
                (:parent_id, :name, :position, :photo, :biography, :display_order, :published)
        ");

        $parentId = $data['parent_id'] ?? null;
        $stmt->bindValue(':parent_id', $parentId, $parentId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':name', $data['name']);
        $stmt->bindValue(':position', $data['position']);
        $stmt->bindValue(':photo', $data['photo'] ?? null);
        $stmt->bindValue(':biography', $data['biography'] ?? null);
        $stmt->bindValue(':display_order', $data['display_order'] ?? 0, PDO::PARAM_INT);
        $stmt->bindValue(':published', $data['published'] ?? 1, PDO::PARAM_INT);
        $stmt->execute();
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->db->prepare("
            UPDATE organization_members SET
                parent_id = :parent_id,
                name = :name,
                position = :position,
                photo = :photo,
                biography = :biography,
                display_order = :display_order,
                published = :published,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ");

        $parentId = $data['parent_id'] ?? null;
        $stmt->bindValue(':parent_id', $parentId, $parentId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':name', $data['name']);
        $stmt->bindValue(':position', $data['position']);
        $stmt->bindValue(':photo', $data['photo'] ?? null);
        $stmt->bindValue(':biography', $data['biography'] ?? null);
        $stmt->bindValue(':display_order', $data['display_order'] ?? 0, PDO::PARAM_INT);
        $stmt->bindValue(':published', $data['published'] ?? 1, PDO::PARAM_INT);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM organization_members WHERE id = ?');
        $stmt->execute([$id]);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM organization_members WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findAll(bool $publishedOnly = false): array
    {
        if ($publishedOnly) {
            return $this->db->query(
                "SELECT * FROM organization_members WHERE published = 1 ORDER BY display_order ASC, created_at ASC"
            )->fetchAll();
        }
        return $this->db->query(
            "SELECT * FROM organization_members ORDER BY display_order ASC, created_at ASC"
        )->fetchAll();
    }

    public function count(): int
    {
        $row = $this->db->query('SELECT COUNT(*) as count FROM organization_members')->fetch();
        return (int) $row['count'];
    }

    public function countPublished(): int
    {
        $row = $this->db->query(
            "SELECT COUNT(*) as count FROM organization_members WHERE published = 1"
        )->fetch();
        return (int) $row['count'];
    }

    public function updateDisplayOrder(int $id, int $order): void
    {
        $stmt = $this->db->prepare(
            'UPDATE organization_members SET display_order = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?'
        );
        $stmt->execute([$order, $id]);
    }

    public function findTopLevel(bool $publishedOnly = false): array
    {
        if ($publishedOnly) {
            return $this->db->query(
                "SELECT * FROM organization_members WHERE parent_id IS NULL AND published = 1 ORDER BY display_order ASC"
            )->fetchAll();
        }
        return $this->db->query(
            "SELECT * FROM organization_members WHERE parent_id IS NULL ORDER BY display_order ASC"
        )->fetchAll();
    }

    public function findChildren(int $parentId, bool $publishedOnly = false): array
    {
        if ($publishedOnly) {
            $stmt = $this->db->prepare(
                "SELECT * FROM organization_members WHERE parent_id = ? AND published = 1 ORDER BY display_order ASC"
            );
        } else {
            $stmt = $this->db->prepare(
                "SELECT * FROM organization_members WHERE parent_id = ? ORDER BY display_order ASC"
            );
        }
        $stmt->execute([$parentId]);
        return $stmt->fetchAll();
    }
}