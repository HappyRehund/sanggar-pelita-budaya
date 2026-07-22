<?php

declare(strict_types=1);

class HighlightRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function insert(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO highlights
                (title, slug, category, short_description, cover_media_id,
                 event_date, location, youtube_url, seo_title, seo_description)
            VALUES
                (:title, :slug, :category, :short_description, :cover_media_id,
                 :event_date, :location, :youtube_url, :seo_title, :seo_description)
        ");

        $this->bindHighlightFields($stmt, $data);
        $stmt->execute();
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->db->prepare("
            UPDATE highlights SET
                title = :title,
                slug = :slug,
                category = :category,
                short_description = :short_description,
                cover_media_id = :cover_media_id,
                event_date = :event_date,
                location = :location,
                youtube_url = :youtube_url,
                seo_title = :seo_title,
                seo_description = :seo_description,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ");

        $this->bindHighlightFields($stmt, $data);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM highlights WHERE id = ?');
        $stmt->execute([$id]);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM highlights WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM highlights WHERE slug = ?');
        $stmt->execute([$slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        if ($ignoreId !== null) {
            $stmt = $this->db->prepare('SELECT COUNT(*) as count FROM highlights WHERE slug = ? AND id != ?');
            $stmt->execute([$slug, $ignoreId]);
        } else {
            $stmt = $this->db->prepare('SELECT COUNT(*) as count FROM highlights WHERE slug = ?');
            $stmt->execute([$slug]);
        }
        $row = $stmt->fetch();
        return ((int) $row['count']) > 0;
    }

    public function findRelated(int $highlightId, string $category, int $limit = 4): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM highlights
             WHERE category = ? AND id != ?
             ORDER BY event_date DESC, created_at DESC
             LIMIT ?"
        );
        $stmt->bindValue(1, $category);
        $stmt->bindValue(2, $highlightId, PDO::PARAM_INT);
        $stmt->bindValue(3, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function count(array $filters = []): int
    {
        [$where, $params] = $this->buildWhereClause($filters);
        $sql = "SELECT COUNT(*) as count FROM highlights";
        if ($where !== '') {
            $sql .= ' WHERE ' . $where;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return (int) $row['count'];
    }

    public function find(array $filters, int $page, int $perPage): array
    {
        [$where, $params] = $this->buildWhereClause($filters);

        $sort = $this->buildOrderBy($filters['sort'] ?? 'newest');
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT * FROM highlights";
        if ($where !== '') {
            $sql .= ' WHERE ' . $where;
        }
        $sql .= ' ' . $sort . ' LIMIT ? OFFSET ?';

        $stmt = $this->db->prepare($sql);
        $params[] = $perPage;
        $params[] = $offset;
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countByCategory(string $category): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) as count FROM highlights WHERE category = ?"
        );
        $stmt->execute([$category]);
        $row = $stmt->fetch();
        return (int) $row['count'];
    }

    public function setCoverMedia(int $highlightId, ?int $mediaId): void
    {
        $stmt = $this->db->prepare('UPDATE highlights SET cover_media_id = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
        $stmt->execute([$mediaId, $highlightId]);
    }

    private function bindHighlightFields(PDOStatement $stmt, array $data): void
    {
        $stmt->bindValue(':title', $data['title']);
        $stmt->bindValue(':slug', $data['slug']);
        $stmt->bindValue(':category', $data['category']);
        $stmt->bindValue(':short_description', $data['short_description']);
        $stmt->bindValue(':cover_media_id', $data['cover_media_id'] ?? null, PDO::PARAM_NULL);
        $stmt->bindValue(':event_date', $data['event_date'] ?: null);
        $stmt->bindValue(':location', $data['location'] ?? '');
        $stmt->bindValue(':youtube_url', $data['youtube_url'] ?? '');
        $stmt->bindValue(':seo_title', $data['seo_title'] ?? null);
        $stmt->bindValue(':seo_description', $data['seo_description'] ?? null);
    }

    private function buildWhereClause(array $filters): array
    {
        $where = [];
        $params = [];

        if (!empty($filters['category']) && $filters['category'] !== 'all') {
            $where[] = 'category = ?';
            $params[] = $filters['category'];
        }

        if (!empty($filters['search'])) {
            $term = '%' . $filters['search'] . '%';
            $where[] = '(title LIKE ? OR short_description LIKE ? OR location LIKE ? OR category LIKE ?)';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        return [implode(' AND ', $where), $params];
    }

    private function buildOrderBy(string $sort): string
    {
        return match ($sort) {
            'oldest' => 'ORDER BY event_date ASC, created_at ASC',
            'alphabetical' => 'ORDER BY title ASC',
            default => 'ORDER BY event_date DESC, created_at DESC',
        };
    }
}