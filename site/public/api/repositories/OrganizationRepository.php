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
                (name, position, photo, biography, display_order, featured_slot, published)
            VALUES
                (:name, :position, :photo, :biography, :display_order, :featured_slot, :published)
        ");

        $featuredSlot = $data['featured_slot'] ?? null;
        $stmt->bindValue(':name', $data['name']);
        $stmt->bindValue(':position', $data['position']);
        $stmt->bindValue(':photo', $data['photo'] ?? null);
        $stmt->bindValue(':biography', $data['biography'] ?? null);
        $stmt->bindValue(':display_order', $data['display_order'] ?? 0, PDO::PARAM_INT);
        $stmt->bindValue(':featured_slot', $featuredSlot, $featuredSlot === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':published', $data['published'] ?? 1, PDO::PARAM_INT);
        $stmt->execute();
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->db->prepare("
            UPDATE organization_members SET
                name = :name,
                position = :position,
                photo = :photo,
                biography = :biography,
                display_order = :display_order,
                featured_slot = :featured_slot,
                published = :published,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ");

        $featuredSlot = $data['featured_slot'] ?? null;
        $stmt->bindValue(':name', $data['name']);
        $stmt->bindValue(':position', $data['position']);
        $stmt->bindValue(':photo', $data['photo'] ?? null);
        $stmt->bindValue(':biography', $data['biography'] ?? null);
        $stmt->bindValue(':display_order', $data['display_order'] ?? 0, PDO::PARAM_INT);
        $stmt->bindValue(':featured_slot', $featuredSlot, $featuredSlot === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
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

    public function findFeatured(bool $publishedOnly = false): array
    {
        $sql = "SELECT * FROM organization_members WHERE featured_slot IS NOT NULL";
        if ($publishedOnly) {
            $sql .= " AND published = 1";
        }
        $sql .= " ORDER BY featured_slot ASC LIMIT 4";
        return $this->db->query($sql)->fetchAll();
    }

    public function clearSlot(int $slot, ?int $exceptId = null): void
    {
        if ($exceptId !== null) {
            $stmt = $this->db->prepare(
                'UPDATE organization_members SET featured_slot = NULL, updated_at = CURRENT_TIMESTAMP WHERE featured_slot = ? AND id != ?'
            );
            $stmt->execute([$slot, $exceptId]);
        } else {
            $stmt = $this->db->prepare(
                'UPDATE organization_members SET featured_slot = NULL, updated_at = CURRENT_TIMESTAMP WHERE featured_slot = ?'
            );
            $stmt->execute([$slot]);
        }
    }
}