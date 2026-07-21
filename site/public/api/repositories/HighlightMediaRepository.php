<?php

declare(strict_types=1);

class HighlightMediaRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function insert(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO highlights_media
                (highlight_id, type, filename, original_filename, mime_type,
                 extension, width, height, size_bytes, alt_text, sort_order)
            VALUES
                (:highlight_id, :type, :filename, :original_filename, :mime_type,
                 :extension, :width, :height, :size_bytes, :alt_text, :sort_order)
        ");

        $stmt->bindValue(':highlight_id', $data['highlight_id'], PDO::PARAM_INT);
        $stmt->bindValue(':type', $data['type']);
        $stmt->bindValue(':filename', $data['filename']);
        $stmt->bindValue(':original_filename', $data['original_filename']);
        $stmt->bindValue(':mime_type', $data['mime_type']);
        $stmt->bindValue(':extension', $data['extension']);
        $stmt->bindValue(':width', $data['width'] ?? null, PDO::PARAM_NULL);
        $stmt->bindValue(':height', $data['height'] ?? null, PDO::PARAM_NULL);
        $stmt->bindValue(':size_bytes', $data['size_bytes'], PDO::PARAM_INT);
        $stmt->bindValue(':alt_text', $data['alt_text'] ?? null);
        $stmt->bindValue(':sort_order', $data['sort_order'] ?? 0, PDO::PARAM_INT);
        $stmt->execute();
        return (int) $this->db->lastInsertId();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM highlights_media WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByHighlightId(int $highlightId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM highlights_media WHERE highlight_id = ? ORDER BY sort_order ASC, created_at ASC"
        );
        $stmt->execute([$highlightId]);
        return $stmt->fetchAll();
    }

    public function findGalleryByHighlightId(int $highlightId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM highlights_media WHERE highlight_id = ? AND type = 'gallery' ORDER BY sort_order ASC"
        );
        $stmt->execute([$highlightId]);
        return $stmt->fetchAll();
    }

    public function findCoverByHighlightId(int $highlightId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM highlights_media WHERE highlight_id = ? AND type = 'cover' LIMIT 1"
        );
        $stmt->execute([$highlightId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM highlights_media WHERE id = ?');
        $stmt->execute([$id]);
    }

    public function deleteByHighlightId(int $highlightId): void
    {
        $stmt = $this->db->prepare('DELETE FROM highlights_media WHERE highlight_id = ?');
        $stmt->execute([$highlightId]);
    }

    public function updateSortOrder(int $id, int $sortOrder): void
    {
        $stmt = $this->db->prepare('UPDATE highlights_media SET sort_order = ? WHERE id = ?');
        $stmt->execute([$sortOrder, $id]);
    }

    public function findRecent(int $limit = 10): array
    {
        $stmt = $this->db->prepare(
            "SELECT hm.id, hm.filename, hm.original_filename, hm.created_at, hm.highlight_id
             FROM highlights_media hm
             ORDER BY hm.created_at DESC
             LIMIT ?"
        );
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function nextSortOrder(int $highlightId): int
    {
        $stmt = $this->db->prepare(
            "SELECT MAX(sort_order) as max_sort FROM highlights_media WHERE highlight_id = ?"
        );
        $stmt->execute([$highlightId]);
        $row = $stmt->fetch();
        return ((int) ($row['max_sort'] ?? -1)) + 1;
    }
}