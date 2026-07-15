<?php

declare(strict_types=1);

class UserRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function findByUsername(string $username): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, username, password_hash, full_name, created_at, updated_at
             FROM users WHERE username = ?'
        );
        $stmt->execute([$username]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, username, full_name, created_at, updated_at
             FROM users WHERE id = ?'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function updatePassword(int $id, string $passwordHash): void
    {
        $stmt = $this->db->prepare(
            "UPDATE users SET password_hash = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?"
        );
        $stmt->execute([$passwordHash, $id]);
    }

    public function count(): int
    {
        $row = $this->db->query('SELECT COUNT(*) as count FROM users')->fetch();
        return (int) $row['count'];
    }

    public function exists(): bool
    {
        return $this->count() > 0;
    }

    public function insert(string $username, string $passwordHash, string $fullName): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (username, password_hash, full_name) VALUES (?, ?, ?)'
        );
        $stmt->execute([$username, $passwordHash, $fullName]);
        return (int) $this->db->lastInsertId();
    }
}