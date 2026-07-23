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
                (title_en, title_id, slug, category, short_description_en, short_description_id,
                 cover_media_id, event_date, location, youtube_url,
                 seo_title_en, seo_title_id, seo_description_en, seo_description_id)
            VALUES
                (:title_en, :title_id, :slug, :category, :short_description_en, :short_description_id,
                 :cover_media_id, :event_date, :location, :youtube_url,
                 :seo_title_en, :seo_title_id, :seo_description_en, :seo_description_id)
        ");

        $this->bindHighlightFields($stmt, $data);
        $stmt->execute();
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->db->prepare("
            UPDATE highlights SET
                title_en = :title_en,
                title_id = :title_id,
                slug = :slug,
                category = :category,
                short_description_en = :short_description_en,
                short_description_id = :short_description_id,
                cover_media_id = :cover_media_id,
                event_date = :event_date,
                location = :location,
                youtube_url = :youtube_url,
                seo_title_en = :seo_title_en,
                seo_title_id = :seo_title_id,
                seo_description_en = :seo_description_en,
                seo_description_id = :seo_description_id,
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
        $stmt->bindValue(':title_en', $data['title_en']);
        $stmt->bindValue(':title_id', $data['title_id']);
        $stmt->bindValue(':slug', $data['slug']);
        $stmt->bindValue(':category', $data['category']);
        $stmt->bindValue(':short_description_en', $data['short_description_en']);
        $stmt->bindValue(':short_description_id', $data['short_description_id']);
        $coverMediaId = $data['cover_media_id'] ?? null;
        $stmt->bindValue(':cover_media_id', $coverMediaId, $coverMediaId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':event_date', $data['event_date'] ?: null);
        $stmt->bindValue(':location', $data['location'] ?? '');
        $stmt->bindValue(':youtube_url', $data['youtube_url'] ?? '');
        $stmt->bindValue(':seo_title_en', $data['seo_title_en'] ?? null);
        $stmt->bindValue(':seo_title_id', $data['seo_title_id'] ?? null);
        $stmt->bindValue(':seo_description_en', $data['seo_description_en'] ?? null);
        $stmt->bindValue(':seo_description_id', $data['seo_description_id'] ?? null);
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
            $where[] = '(title_en LIKE ? OR title_id LIKE ? OR short_description_en LIKE ? OR short_description_id LIKE ? OR location LIKE ? OR category LIKE ?)';
            $params[] = $term;
            $params[] = $term;
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
            'alphabetical' => 'ORDER BY title_en ASC',
            default => 'ORDER BY event_date DESC, created_at DESC',
        };
    }
}