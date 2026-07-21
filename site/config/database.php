<?php

declare(strict_types=1);

require_once __DIR__ . '/app.php';

function getPDO(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        try {
            $pdo = new PDO('sqlite:' . DB_PATH);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            $pdo->exec('PRAGMA journal_mode = WAL;');
            $pdo->exec('PRAGMA busy_timeout = 5000;');
            $pdo->exec('PRAGMA synchronous = NORMAL;');
            $pdo->exec('PRAGMA cache_size = -8000;');
            $pdo->exec('PRAGMA foreign_keys = ON;');
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Database connection failed',
            ]);
            exit;
        }
    }

    return $pdo;
}

function initSchema(): void
{
    $pdo = getPDO();

    // Migrate legacy users table (created by starter scaffold) to new shape
    migrateLegacyUsersTable($pdo);

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS schema_meta (
            key TEXT PRIMARY KEY,
            value TEXT NOT NULL
        );
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            password_hash TEXT NOT NULL,
            full_name TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS highlights (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            slug TEXT UNIQUE NOT NULL,
            category TEXT NOT NULL CHECK(category IN ('achievement','activity')),
            short_description TEXT NOT NULL,
            content TEXT NOT NULL DEFAULT '',
            cover_media_id INTEGER,
            event_date DATE,
            location TEXT,
            youtube_url TEXT,
            featured INTEGER NOT NULL DEFAULT 0,
            published INTEGER NOT NULL DEFAULT 0,
            seo_title TEXT,
            seo_description TEXT,
            og_media_id INTEGER,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (cover_media_id) REFERENCES highlights_media(id) ON DELETE SET NULL,
            FOREIGN KEY (og_media_id) REFERENCES highlights_media(id) ON DELETE SET NULL
        );
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS highlights_media (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            highlight_id INTEGER NOT NULL,
            type TEXT NOT NULL CHECK(type IN ('cover','gallery','og')),
            filename TEXT NOT NULL,
            original_filename TEXT NOT NULL,
            mime_type TEXT NOT NULL,
            extension TEXT NOT NULL,
            width INTEGER,
            height INTEGER,
            size_bytes INTEGER NOT NULL,
            alt_text TEXT,
            sort_order INTEGER NOT NULL DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (highlight_id) REFERENCES highlights(id) ON DELETE CASCADE
        );
    ");

    // Recreate organization_members without the `published` column (no longer needed in admin).
    // Safe to wipe: no data to preserve. Guarded by schema-version marker so it only runs once.
    $orgSchemaV4 = $pdo->query("SELECT value FROM schema_meta WHERE key = 'organization_v4'")->fetchColumn();
    if ($orgSchemaV4 === false) {
        $pdo->exec('DROP TABLE IF EXISTS organization_members;');
        $pdo->exec('DROP INDEX IF EXISTS idx_org_display_order;');
        $pdo->exec('DROP INDEX IF EXISTS idx_org_featured_slot;');
    }

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS organization_members (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            position_en TEXT NOT NULL,
            position_id TEXT NOT NULL,
            photo TEXT,
            biography_en TEXT,
            biography_id TEXT,
            display_order INTEGER NOT NULL DEFAULT 0,
            featured_slot INTEGER,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
    ");

    if ($orgSchemaV4 === false) {
        $pdo->exec("INSERT INTO schema_meta (key, value) VALUES ('organization_v4', '1');");
    }

    // Drop legacy hero/footer tables (content now static frontend-only)
    $pdo->exec("DROP TABLE IF EXISTS hero");
    $pdo->exec("DROP TABLE IF EXISTS footer");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS settings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            site_name TEXT NOT NULL DEFAULT 'Sanggar Pelita Budaya',
            site_description TEXT NOT NULL DEFAULT '',
            logo TEXT,
            favicon TEXT,
            default_language TEXT NOT NULL DEFAULT 'en',
            default_og_image TEXT,
            maintenance_mode INTEGER NOT NULL DEFAULT 0,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
    ");

    createIndexes($pdo);
}

function createIndexes(PDO $pdo): void
{
    $indexes = [
        'CREATE INDEX IF NOT EXISTS idx_highlights_slug ON highlights(slug);',
        'CREATE INDEX IF NOT EXISTS idx_highlights_category ON highlights(category);',
        'CREATE INDEX IF NOT EXISTS idx_highlights_published ON highlights(published);',
        'CREATE INDEX IF NOT EXISTS idx_highlights_featured ON highlights(featured);',
        'CREATE INDEX IF NOT EXISTS idx_highlights_event_date ON highlights(event_date);',
        'CREATE INDEX IF NOT EXISTS idx_org_display_order ON organization_members(display_order);',
        'CREATE UNIQUE INDEX IF NOT EXISTS idx_org_featured_slot ON organization_members(featured_slot) WHERE featured_slot IS NOT NULL;',
        'CREATE INDEX IF NOT EXISTS idx_media_highlight_id ON highlights_media(highlight_id);',
    ];

    foreach ($indexes as $sql) {
        $pdo->exec($sql);
    }
}

function migrateLegacyUsersTable(PDO $pdo): void
{
    $table = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'");
    if ($table->fetch() === false) {
        return;
    }

    $cols = $pdo->query('PRAGMA table_info(users)');
    $hasPasswordHash = false;
    $hasFullName = false;
    $hasUpdatedAt = false;

    foreach ($cols->fetchAll() as $col) {
        if ($col['name'] === 'password_hash') $hasPasswordHash = true;
        if ($col['name'] === 'full_name') $hasFullName = true;
        if ($col['name'] === 'updated_at') $hasUpdatedAt = true;
    }

    if (!$hasPasswordHash) {
        $pdo->exec('ALTER TABLE users ADD COLUMN password_hash TEXT;');

        $rows = $pdo->query('SELECT id, password FROM users')->fetchAll();
        $stmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
        foreach ($rows as $row) {
            $stmt->execute([$row['password'], $row['id']]);
        }

        // SQLite cannot DROP COLUMN easily; leave legacy password column (harmless)
    }

    if (!$hasFullName) {
        $pdo->exec('ALTER TABLE users ADD COLUMN full_name TEXT;');
        $pdo->exec("UPDATE users SET full_name = fullname WHERE full_name IS NULL;");
    }

    if (!$hasUpdatedAt) {
        $pdo->exec('ALTER TABLE users ADD COLUMN updated_at DATETIME DEFAULT CURRENT_TIMESTAMP;');
    }
}

function isSeeded(): bool
{
    $pdo = getPDO();

    $adminExists = $pdo->query("SELECT COUNT(*) as count FROM users WHERE username = 'admin'")->fetch();
    if (((int) $adminExists['count']) === 0) {
        return false;
    }

    $settingsExists = $pdo->query('SELECT COUNT(*) as count FROM settings')->fetch();
    if (((int) $settingsExists['count']) === 0) {
        return false;
    }

    return true;
}

function seedDatabase(): void
{
    $pdo = getPDO();

    seedAdmin($pdo);
    seedSettings($pdo);
}

function seedAdmin(PDO $pdo): void
{
    $count = $pdo->query("SELECT COUNT(*) as count FROM users WHERE username = 'admin'")->fetch();
    if (((int) $count['count']) > 0) {
        return;
    }

    $hash = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare(
        'INSERT INTO users (username, password_hash, full_name) VALUES (?, ?, ?)'
    );
    $stmt->execute(['admin', $hash, 'Administrator Sanggar']);
}

function seedSettings(PDO $pdo): void
{
    $count = $pdo->query('SELECT COUNT(*) as count FROM settings')->fetch();
    if (((int) $count['count']) > 0) {
        return;
    }

    $stmt = $pdo->prepare("
        INSERT INTO settings (site_name, site_description, logo, favicon,
            default_language, default_og_image, maintenance_mode)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        'Sanggar Pelita Budaya',
        'Preserving and celebrating Indonesian cultural heritage through traditional art, dance, and community.',
        null,
        null,
        'en',
        null,
        0,
    ]);
}

function bootstrapDatabase(): void
{
    initSchema();
    if (!isSeeded()) {
        seedDatabase();
    }
}