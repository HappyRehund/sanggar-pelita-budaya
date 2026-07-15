#!/bin/sh
set -e

echo "[entrypoint] Fixing permissions..."
chown -R www-data:www-data /var/www/site/data /var/www/site/public/uploads
chmod -R 775 /var/www/site/data /var/www/site/public/uploads

echo "[entrypoint] Running database bootstrap..."
cd /var/www/site
su -s /bin/sh www-data -c "php seed.php" || echo "[entrypoint] Warning: seed.php failed, continuing..."

echo "[entrypoint] Starting Apache..."
exec "$@"