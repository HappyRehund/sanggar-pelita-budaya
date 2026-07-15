<?php

declare(strict_types=1);

class FooterRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function find(): ?array
    {
        $stmt = $this->db->query('SELECT * FROM footer ORDER BY id ASC LIMIT 1');
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->db->prepare("
            UPDATE footer SET
                logo = :logo,
                description = :description,
                address = :address,
                phone = :phone,
                email = :email,
                website = :website,
                working_hours = :working_hours,
                facebook = :facebook,
                instagram = :instagram,
                youtube = :youtube,
                tiktok = :tiktok,
                maps_url = :maps_url,
                copyright = :copyright,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ");

        $stmt->bindValue(':logo', $data['logo'] ?? null);
        $stmt->bindValue(':description', $data['description'] ?? '');
        $stmt->bindValue(':address', $data['address'] ?? '');
        $stmt->bindValue(':phone', $data['phone'] ?? '');
        $stmt->bindValue(':email', $data['email'] ?? '');
        $stmt->bindValue(':website', $data['website'] ?? '');
        $stmt->bindValue(':working_hours', $data['working_hours'] ?? '');
        $stmt->bindValue(':facebook', $data['facebook'] ?? null);
        $stmt->bindValue(':instagram', $data['instagram'] ?? null);
        $stmt->bindValue(':youtube', $data['youtube'] ?? null);
        $stmt->bindValue(':tiktok', $data['tiktok'] ?? null);
        $stmt->bindValue(':maps_url', $data['maps_url'] ?? '');
        $stmt->bindValue(':copyright', $data['copyright'] ?? '');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function exists(): bool
    {
        $row = $this->db->query('SELECT COUNT(*) as count FROM footer')->fetch();
        return ((int) $row['count']) > 0;
    }
}