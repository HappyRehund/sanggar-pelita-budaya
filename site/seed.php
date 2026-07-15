<?php

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';

echo "[seed] Starting database bootstrap...\n";

try {
    initSchema();
    echo "[seed] Schema initialized.\n";

    if (isSeeded()) {
        echo "[seed] Database already seeded. Skipping.\n";
    } else {
        seedDatabase();
        echo "[seed] Database seeded successfully.\n";
        echo "[seed]   - 1 admin user (admin/admin123)\n";
    }
} catch (Throwable $e) {
    echo "[seed] ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

echo "[seed] Done.\n";