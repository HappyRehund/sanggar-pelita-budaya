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

    // Simplify highlights schema: drop content/featured/published/og_media_id columns.
    // Destructive, guarded by schema-version marker so it only runs once.
    $highlightsSimplifyV1 = $pdo->query("SELECT value FROM schema_meta WHERE key = 'highlights_simplify_v1'")->fetchColumn();
    if ($highlightsSimplifyV1 === false) {
        $pdo->exec('DROP TABLE IF EXISTS highlights;');
        $pdo->exec('DROP TABLE IF EXISTS highlights_media;');
        $pdo->exec('DROP INDEX IF EXISTS idx_highlights_slug;');
        $pdo->exec('DROP INDEX IF EXISTS idx_highlights_category;');
        $pdo->exec('DROP INDEX IF EXISTS idx_highlights_published;');
        $pdo->exec('DROP INDEX IF EXISTS idx_highlights_featured;');
        $pdo->exec('DROP INDEX IF EXISTS idx_highlights_event_date;');
        $pdo->exec('DROP INDEX IF EXISTS idx_media_highlight_id;');
    }

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS highlights (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title_en TEXT NOT NULL,
            title_id TEXT NOT NULL,
            slug TEXT UNIQUE NOT NULL,
            category TEXT NOT NULL CHECK(category IN ('achievement','activity')),
            short_description_en TEXT NOT NULL,
            short_description_id TEXT NOT NULL,
            cover_media_id INTEGER,
            event_date DATE,
            location TEXT,
            youtube_url TEXT,
            seo_title_en TEXT,
            seo_title_id TEXT,
            seo_description_en TEXT,
            seo_description_id TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (cover_media_id) REFERENCES highlights_media(id) ON DELETE SET NULL
        );
    ");

    // Further simplify highlights: a highlight has a single cover image (no gallery).
    // Destructive, guarded by schema-version marker so it only runs once.
    $highlightsSimplifyV2 = $pdo->query("SELECT value FROM schema_meta WHERE key = 'highlights_simplify_v2'")->fetchColumn();
    if ($highlightsSimplifyV2 === false) {
        $pdo->exec('DROP TABLE IF EXISTS highlights_media;');
        $pdo->exec('DROP INDEX IF EXISTS idx_media_highlight_id;');
    }

    // Migrate highlights to bilingual (EN + ID) content columns.
    // Destructive, guarded by schema-version marker so it only runs once.
    $highlightsBilingualV1 = $pdo->query("SELECT value FROM schema_meta WHERE key = 'highlights_bilingual_v1'")->fetchColumn();
    if ($highlightsBilingualV1 === false) {
        $hasLegacyTitle = false;
        $cols = $pdo->query('PRAGMA table_info(highlights)');
        foreach ($cols->fetchAll() as $col) {
            if ($col['name'] === 'title') {
                $hasLegacyTitle = true;
                break;
            }
        }

        if ($hasLegacyTitle) {
            // Add bilingual columns (mirror existing data into both languages).
            $pdo->exec('ALTER TABLE highlights ADD COLUMN title_en TEXT;');
            $pdo->exec('ALTER TABLE highlights ADD COLUMN title_id TEXT;');
            $pdo->exec('ALTER TABLE highlights ADD COLUMN short_description_en TEXT;');
            $pdo->exec('ALTER TABLE highlights ADD COLUMN short_description_id TEXT;');
            $pdo->exec('ALTER TABLE highlights ADD COLUMN seo_title_en TEXT;');
            $pdo->exec('ALTER TABLE highlights ADD COLUMN seo_title_id TEXT;');
            $pdo->exec('ALTER TABLE highlights ADD COLUMN seo_description_en TEXT;');
            $pdo->exec('ALTER TABLE highlights ADD COLUMN seo_description_id TEXT;');

            // Backfill: copy existing single-language values into both EN and ID columns.
            $pdo->exec('UPDATE highlights SET title_en = title, title_id = title WHERE title IS NOT NULL;');
            $pdo->exec('UPDATE highlights SET short_description_en = short_description, short_description_id = short_description WHERE short_description IS NOT NULL;');
            $pdo->exec('UPDATE highlights SET seo_title_en = seo_title, seo_title_id = seo_title;');
            $pdo->exec('UPDATE highlights SET seo_description_en = seo_description, seo_description_id = seo_description;');

            // Drop legacy single-language columns by recreating the table.
            // Disable FK enforcement and use legacy_alter_table=1 so SQLite
            // neither fires FK triggers during the rename nor rewrites FK
            // text on highlights_media to follow the highlights->highlights_old
            // rename (which would leave a dangling ref after highlights_old
            // is dropped).
            $pdo->exec('PRAGMA foreign_keys = OFF;');
            $pdo->exec('PRAGMA legacy_alter_table = 1;');
            try {
                $pdo->exec('DROP TABLE IF EXISTS highlights_old;');
                $pdo->exec('ALTER TABLE highlights RENAME TO highlights_old;');
                $pdo->exec("
                    CREATE TABLE highlights (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        title_en TEXT NOT NULL,
                        title_id TEXT NOT NULL,
                        slug TEXT UNIQUE NOT NULL,
                        category TEXT NOT NULL CHECK(category IN ('achievement','activity')),
                        short_description_en TEXT NOT NULL,
                        short_description_id TEXT NOT NULL,
                        cover_media_id INTEGER,
                        event_date DATE,
                        location TEXT,
                        youtube_url TEXT,
                        seo_title_en TEXT,
                        seo_title_id TEXT,
                        seo_description_en TEXT,
                        seo_description_id TEXT,
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                        FOREIGN KEY (cover_media_id) REFERENCES highlights_media(id) ON DELETE SET NULL
                    );
                ");
                $pdo->exec("
                    INSERT INTO highlights (id, title_en, title_id, slug, category, short_description_en, short_description_id,
                        cover_media_id, event_date, location, youtube_url, seo_title_en, seo_title_id,
                        seo_description_en, seo_description_id, created_at, updated_at)
                    SELECT id, title_en, title_id, slug, category, short_description_en, short_description_id,
                        cover_media_id, event_date, location, youtube_url, seo_title_en, seo_title_id,
                        seo_description_en, seo_description_id, created_at, updated_at
                    FROM highlights_old;
                ");
                $pdo->exec('DROP TABLE highlights_old;');
            } finally {
                $pdo->exec('PRAGMA legacy_alter_table = 0;');
                $pdo->exec('PRAGMA foreign_keys = ON;');
            }
        }
    }

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS highlights_media (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            highlight_id INTEGER NOT NULL,
            type TEXT NOT NULL CHECK(type IN ('cover')),
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

    // Repair broken FK references caused by ALTER TABLE RENAME migrations.
    //
    // History:
    //  - The bilingual migration renamed highlights -> highlights_old and
    //    SQLite rewrote highlights_media's FK trigger to point at
    //    highlights_old (then dropped) -> INSERT into highlights_media failed
    //    with "no such table: main.highlights_old".
    //  - Repair v1 renamed highlights_media -> highlights_media_broken and
    //    recreated highlights_media. But that rename rewrote highlights's
    //    FK text (cover_media_id REFERENCES highlights_media) to point at
    //    highlights_media_broken (then dropped) -> UPDATE highlights failed
    //    with "no such table: main.highlights_media_broken".
    //
    // Root cause: SQLite stores FK clauses as TEXT in sqlite_master and
    // ALTER TABLE RENAME rewrites that text. legacy_alter_table=1 did not
    // reliably prevent it here. The bulletproof fix is to DROP and
    // CREATE both tables fresh (with FKs OFF so enforcement cannot fire
    // mid-rebuild), copying data via temp tables. This guarantees both
    // tables' FK text references the correct, existing counterpart.
    $highlightsFkRepairV2 = $pdo->query("SELECT value FROM schema_meta WHERE key = 'highlights_fk_repair_v2'")->fetchColumn();
    if ($highlightsFkRepairV2 === false) {
        $pdo->exec('PRAGMA foreign_keys = OFF;');
        try {
            // Stage current data into temp tables.
            $pdo->exec('DROP TABLE IF EXISTS _hl_stage;');
            $pdo->exec('DROP TABLE IF EXISTS _hlm_stage;');
            $pdo->exec('ALTER TABLE highlights RENAME TO _hl_stage;');
            $pdo->exec('ALTER TABLE highlights_media RENAME TO _hlm_stage;');

            // Recreate both with correct, mutually-consistent FK text.
            $pdo->exec("
                CREATE TABLE highlights (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    title_en TEXT NOT NULL,
                    title_id TEXT NOT NULL,
                    slug TEXT UNIQUE NOT NULL,
                    category TEXT NOT NULL CHECK(category IN ('achievement','activity')),
                    short_description_en TEXT NOT NULL,
                    short_description_id TEXT NOT NULL,
                    cover_media_id INTEGER,
                    event_date DATE,
                    location TEXT,
                    youtube_url TEXT,
                    seo_title_en TEXT,
                    seo_title_id TEXT,
                    seo_description_en TEXT,
                    seo_description_id TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (cover_media_id) REFERENCES highlights_media(id) ON DELETE SET NULL
                );
            ");
            $pdo->exec("
                CREATE TABLE highlights_media (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    highlight_id INTEGER NOT NULL,
                    type TEXT NOT NULL CHECK(type IN ('cover')),
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

            // Copy data back (order matters: media first so highlights'
            // cover_media_id FK is satisfiable, though FKs are off anyway).
            $hlmCols = $pdo->query('PRAGMA table_info(_hlm_stage)')->fetchAll();
            $hlmHas = static fn(string $c): bool => in_array($c, array_column($hlmCols, 'name'), true);
            $mediaSelect = implode(', ', array_filter(
                ['id', 'highlight_id', 'type', 'filename', 'original_filename', 'mime_type', 'extension',
                 'width', 'height', 'size_bytes', 'alt_text', 'sort_order', 'created_at'],
                $hlmHas
            ));
            $pdo->exec("INSERT INTO highlights_media ($mediaSelect) SELECT $mediaSelect FROM _hlm_stage;");

            $hlCols = $pdo->query('PRAGMA table_info(_hl_stage)')->fetchAll();
            $hlHas = static fn(string $c): bool => in_array($c, array_column($hlCols, 'name'), true);
            $hlSelect = implode(', ', array_filter(
                ['id', 'title_en', 'title_id', 'slug', 'category', 'short_description_en', 'short_description_id',
                 'cover_media_id', 'event_date', 'location', 'youtube_url', 'seo_title_en', 'seo_title_id',
                 'seo_description_en', 'seo_description_id', 'created_at', 'updated_at'],
                $hlHas
            ));
            $pdo->exec("INSERT INTO highlights ($hlSelect) SELECT $hlSelect FROM _hl_stage;");

            $pdo->exec('DROP TABLE _hl_stage;');
            $pdo->exec('DROP TABLE _hlm_stage;');
        } finally {
            $pdo->exec('PRAGMA foreign_keys = ON;');
        }
        $pdo->exec("INSERT INTO schema_meta (key, value) VALUES ('highlights_fk_repair_v2', '1');");
    }

    if ($highlightsSimplifyV1 === false) {
        $pdo->exec("INSERT INTO schema_meta (key, value) VALUES ('highlights_simplify_v1', '1');");
    }

    if ($highlightsSimplifyV2 === false) {
        $pdo->exec("INSERT INTO schema_meta (key, value) VALUES ('highlights_simplify_v2', '1');");
    }

    if ($highlightsBilingualV1 === false) {
        $pdo->exec("INSERT INTO schema_meta (key, value) VALUES ('highlights_bilingual_v1', '1');");
    }

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