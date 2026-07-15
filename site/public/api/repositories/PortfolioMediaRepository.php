<?php

declare(strict_types=1);

class PortfolioMediaRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function insert(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO portfolio_media
                (portfolio_id, type, filename, original_filename, mime_type,
                 extension, width, height, size_bytes, alt_text, sort_order)
            VALUES
                (:portfolio_id, :type, :filename, :original_filename, :mime_type,
                 :extension, :width, :height, :size_bytes, :alt_text, :sort_order)
        ");

        $stmt->bindValue(':portfolio_id', $data['portfolio_id'], PDO::PARAM_INT);
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
        $stmt = $this->db->prepare('SELECT * FROM portfolio_media WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByPortfolioId(int $portfolioId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM portfolio_media WHERE portfolio_id = ? ORDER BY sort_order ASC, created_at ASC"
        );
        $stmt->execute([$portfolioId]);
        return $stmt->fetchAll();
    }

    public function findGalleryByPortfolioId(int $portfolioId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM portfolio_media WHERE portfolio_id = ? AND type = 'gallery' ORDER BY sort_order ASC"
        );
        $stmt->execute([$portfolioId]);
        return $stmt->fetchAll();
    }

    public function findCoverByPortfolioId(int $portfolioId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM portfolio_media WHERE portfolio_id = ? AND type = 'cover' LIMIT 1"
        );
        $stmt->execute([$portfolioId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM portfolio_media WHERE id = ?');
        $stmt->execute([$id]);
    }

    public function deleteByPortfolioId(int $portfolioId): void
    {
        $stmt = $this->db->prepare('DELETE FROM portfolio_media WHERE portfolio_id = ?');
        $stmt->execute([$portfolioId]);
    }

    public function updateSortOrder(int $id, int $sortOrder): void
    {
        $stmt = $this->db->prepare('UPDATE portfolio_media SET sort_order = ? WHERE id = ?');
        $stmt->execute([$sortOrder, $id]);
    }

    public function findRecent(int $limit = 10): array
    {
        $stmt = $this->db->prepare(
            "SELECT pm.id, pm.filename, pm.original_filename, pm.created_at, pm.portfolio_id
             FROM portfolio_media pm
             ORDER BY pm.created_at DESC
             LIMIT ?"
        );
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function nextSortOrder(int $portfolioId): int
    {
        $stmt = $this->db->prepare(
            "SELECT MAX(sort_order) as max_sort FROM portfolio_media WHERE portfolio_id = ?"
        );
        $stmt->execute([$portfolioId]);
        $row = $stmt->fetch();
        return ((int) ($row['max_sort'] ?? -1)) + 1;
    }
}