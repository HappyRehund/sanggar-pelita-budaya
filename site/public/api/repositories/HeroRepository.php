<?php

declare(strict_types=1);

class HeroRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function find(): ?array
    {
        $stmt = $this->db->query('SELECT * FROM hero ORDER BY id ASC LIMIT 1');
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->db->prepare("
            UPDATE hero SET
                headline = :headline,
                subtitle = :subtitle,
                description = :description,
                background_image = :background_image,
                primary_button_text = :primary_button_text,
                primary_button_url = :primary_button_url,
                secondary_button_text = :secondary_button_text,
                secondary_button_url = :secondary_button_url,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ");

        $stmt->bindValue(':headline', $data['headline'] ?? '');
        $stmt->bindValue(':subtitle', $data['subtitle'] ?? '');
        $stmt->bindValue(':description', $data['description'] ?? '');
        $stmt->bindValue(':background_image', $data['background_image'] ?? null);
        $stmt->bindValue(':primary_button_text', $data['primary_button_text'] ?? '');
        $stmt->bindValue(':primary_button_url', $data['primary_button_url'] ?? '');
        $stmt->bindValue(':secondary_button_text', $data['secondary_button_text'] ?? '');
        $stmt->bindValue(':secondary_button_url', $data['secondary_button_url'] ?? '');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function exists(): bool
    {
        $row = $this->db->query('SELECT COUNT(*) as count FROM hero')->fetch();
        return ((int) $row['count']) > 0;
    }
}