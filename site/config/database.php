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
        CREATE TABLE IF NOT EXISTS portfolio (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            slug TEXT UNIQUE NOT NULL,
            category TEXT NOT NULL CHECK(category IN ('achievement','activity')),
            short_description TEXT NOT NULL,
            content TEXT NOT NULL DEFAULT '',
            cover_image_id INTEGER,
            event_date DATE,
            location TEXT,
            youtube_url TEXT,
            featured INTEGER NOT NULL DEFAULT 0,
            published INTEGER NOT NULL DEFAULT 0,
            seo_title TEXT,
            seo_description TEXT,
            og_image_id INTEGER,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (cover_image_id) REFERENCES portfolio_media(id) ON DELETE SET NULL,
            FOREIGN KEY (og_image_id) REFERENCES portfolio_media(id) ON DELETE SET NULL
        );
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS portfolio_media (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            portfolio_id INTEGER NOT NULL,
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
            FOREIGN KEY (portfolio_id) REFERENCES portfolio(id) ON DELETE CASCADE
        );
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS organization_members (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            parent_id INTEGER,
            name TEXT NOT NULL,
            position TEXT NOT NULL,
            photo TEXT,
            biography TEXT,
            display_order INTEGER NOT NULL DEFAULT 0,
            published INTEGER NOT NULL DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (parent_id) REFERENCES organization_members(id) ON DELETE SET NULL
        );
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS hero (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            headline TEXT NOT NULL DEFAULT '',
            subtitle TEXT NOT NULL DEFAULT '',
            description TEXT NOT NULL DEFAULT '',
            background_image TEXT,
            primary_button_text TEXT NOT NULL DEFAULT '',
            primary_button_url TEXT NOT NULL DEFAULT '',
            secondary_button_text TEXT NOT NULL DEFAULT '',
            secondary_button_url TEXT NOT NULL DEFAULT '',
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS footer (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            logo TEXT,
            description TEXT NOT NULL DEFAULT '',
            address TEXT NOT NULL DEFAULT '',
            phone TEXT NOT NULL DEFAULT '',
            email TEXT NOT NULL DEFAULT '',
            website TEXT NOT NULL DEFAULT '',
            working_hours TEXT NOT NULL DEFAULT '',
            facebook TEXT,
            instagram TEXT,
            youtube TEXT,
            tiktok TEXT,
            maps_url TEXT NOT NULL DEFAULT '',
            copyright TEXT NOT NULL DEFAULT '',
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
    ");

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
        'CREATE INDEX IF NOT EXISTS idx_portfolio_slug ON portfolio(slug);',
        'CREATE INDEX IF NOT EXISTS idx_portfolio_category ON portfolio(category);',
        'CREATE INDEX IF NOT EXISTS idx_portfolio_published ON portfolio(published);',
        'CREATE INDEX IF NOT EXISTS idx_portfolio_featured ON portfolio(featured);',
        'CREATE INDEX IF NOT EXISTS idx_portfolio_event_date ON portfolio(event_date);',
        'CREATE INDEX IF NOT EXISTS idx_org_display_order ON organization_members(display_order);',
        'CREATE INDEX IF NOT EXISTS idx_media_portfolio_id ON portfolio_media(portfolio_id);',
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

    $heroExists = $pdo->query('SELECT COUNT(*) as count FROM hero')->fetch();
    if (((int) $heroExists['count']) === 0) {
        return false;
    }

    $footerExists = $pdo->query('SELECT COUNT(*) as count FROM footer')->fetch();
    if (((int) $footerExists['count']) === 0) {
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
    seedHero($pdo);
    seedFooter($pdo);
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

function seedHero(PDO $pdo): void
{
    $count = $pdo->query('SELECT COUNT(*) as count FROM hero')->fetch();
    if (((int) $count['count']) > 0) {
        return;
    }

    $stmt = $pdo->prepare("
        INSERT INTO hero (headline, subtitle, description, background_image,
            primary_button_text, primary_button_url,
            secondary_button_text, secondary_button_url)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        'Sanggar Pelita Budaya',
        'Where Tradition Lives',
        'Preserving and celebrating the richness of Indonesian traditional art through dance, music, and community.',
        null,
        'Explore Our Work',
        '/portfolio',
        'Download Profile',
        '/contact',
    ]);
}

function seedFooter(PDO $pdo): void
{
    $count = $pdo->query('SELECT COUNT(*) as count FROM footer')->fetch();
    if (((int) $count['count']) > 0) {
        return;
    }

    $stmt = $pdo->prepare("
        INSERT INTO footer (logo, description, address, phone, email, website,
            working_hours, facebook, instagram, youtube, tiktok, maps_url, copyright)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        null,
        'Sanggar Pelita Budaya — preserving and celebrating Indonesian cultural heritage through traditional art.',
        '',
        '',
        '',
        '',
        '',
        null,
        null,
        null,
        null,
        '',
        '© {year} Sanggar Pelita Budaya. All rights reserved.',
    ]);
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