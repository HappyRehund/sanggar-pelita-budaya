<?php

declare(strict_types=1);

class SettingsRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function find(): ?array
    {
        $stmt = $this->db->query('SELECT * FROM settings ORDER BY id ASC LIMIT 1');
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->db->prepare("
            UPDATE settings SET
                site_name = :site_name,
                site_description = :site_description,
                logo = :logo,
                favicon = :favicon,
                default_language = :default_language,
                default_og_image = :default_og_image,
                maintenance_mode = :maintenance_mode,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ");

        $stmt->bindValue(':site_name', $data['site_name'] ?? '');
        $stmt->bindValue(':site_description', $data['site_description'] ?? '');
        $stmt->bindValue(':logo', $data['logo'] ?? null);
        $stmt->bindValue(':favicon', $data['favicon'] ?? null);
        $stmt->bindValue(':default_language', $data['default_language'] ?? 'en');
        $stmt->bindValue(':default_og_image', $data['default_og_image'] ?? null);
        $stmt->bindValue(':maintenance_mode', $data['maintenance_mode'] ?? 0, PDO::PARAM_INT);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function exists(): bool
    {
        $row = $this->db->query('SELECT COUNT(*) as count FROM settings')->fetch();
        return ((int) $row['count']) > 0;
    }
}