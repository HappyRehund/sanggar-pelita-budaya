#!/usr/bin/env bash
# Package site/ into a Rumahweb cPanel deployment ZIP.
#
# Only ships site/. Excludes persistent server state (SQLite DB + its
# journal/wal/shm sidecars, admin-uploaded images) and PHP editor backups.
# Keeps .htaccess files and .gitkeep placeholders so the folder layout
# survives extraction on the server.
#
# Usage:
#   ./scripts/package-deploy.sh                    # -> deploy-sanggar-pelita.zip
#   ./scripts/package-deploy.sh custom-name.zip    # override output name
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
SITE_DIR="$PROJECT_ROOT/site"
ZIP_NAME="${1:-deploy-sanggar-pelita.zip}"
ZIP_PATH="$PROJECT_ROOT/$ZIP_NAME"

echo "==> Pre-flight checks"
if [ ! -d "$SITE_DIR" ]; then
  echo "ERROR: site/ not found at $SITE_DIR" >&2
  exit 1
fi

for f in \
  ".htaccess" \
  "seed.php" \
  "config/app.php" \
  "config/database.php" \
  "public/.htaccess" \
  "public/index.html" \
  "public/api/index.php" \
  "public/api/routes.php" \
  "public/api/helpers.php"; do
  if [ ! -f "$SITE_DIR/$f" ]; then
    echo "ERROR: site/$f missing — site/ tree is incomplete." >&2
    exit 1
  fi
done

if [ ! -d "$SITE_DIR/public/assets" ]; then
  echo "ERROR: site/public/assets/ missing." >&2
  echo "Run: cd frontend && pnpm build && pnpm run deploy:sync" >&2
  exit 1
fi
if [ ! -f "$SITE_DIR/public/uploads/.htaccess" ]; then
  echo "ERROR: site/public/uploads/.htaccess missing." >&2
  exit 1
fi

for f in "config/.htaccess" "data/.htaccess"; do
  if [ ! -f "$SITE_DIR/$f" ]; then
    echo "  WARN: site/$f missing — SQLite/config would be exposed on the server." >&2
    echo "        Create it per DEPLOYMENT.md before deploying." >&2
  fi
done

echo "    site/ tree OK"

echo "==> ensure .gitkeep placeholders in persistent folders"
touch "$SITE_DIR/data/.gitkeep"
touch "$SITE_DIR/public/uploads/.gitkeep"

echo "==> create deployment ZIP (excluding server state + PHP backups)"
rm -f "$ZIP_PATH"
cd "$PROJECT_ROOT"

zip -r "$ZIP_NAME" site \
  -x "site/data/sanggar.sqlite" \
     "site/data/sanggar.sqlite-journal" \
     "site/data/sanggar.sqlite-wal" \
     "site/data/sanggar.sqlite-shm" \
     "site/public/uploads/*" \
     "site/.gitignore" \
     "site/*.php.bak" \
     "site/*.php~"

echo
echo "==> verify ZIP contents"
LISTING="$(unzip -l "$ZIP_PATH")"
echo "$LISTING" | sort
echo
FILE_COUNT="$(echo "$LISTING" | grep -c "site/")"
echo "Files in ZIP: $FILE_COUNT"
echo "Archive size: $(du -h "$ZIP_PATH" | cut -f1)"
echo
echo "==> Sanity checks"

if grep -Eq "site/data/sanggar\.sqlite" <<< "$LISTING"; then
  echo "  FAIL: SQLite DB leaked into ZIP" >&2
  exit 1
fi
echo "  ok: no SQLite DB or sidecars"

if grep -Eq "site/public/uploads/[^/]+\.(jpg|jpeg|png|webp)" <<< "$LISTING"; then
  echo "  FAIL: uploaded images leaked into ZIP" >&2
  exit 1
fi
echo "  ok: no uploaded images"

if grep -Eq "\.php(bak|~)" <<< "$LISTING"; then
  echo "  FAIL: PHP backup files leaked into ZIP" >&2
  exit 1
fi
echo "  ok: no PHP backup files"

if ! grep -q "site/public/uploads/\.gitkeep" <<< "$LISTING"; then
  echo "  WARN: uploads/.gitkeep missing — dir may not survive extraction" >&2
else
  echo "  ok: uploads/.gitkeep present"
fi

if ! grep -q "site/public/uploads/\.htaccess" <<< "$LISTING"; then
  echo "  WARN: uploads/.htaccess missing" >&2
else
  echo "  ok: uploads/.htaccess present"
fi

echo
echo "Done: $ZIP_PATH"
echo "Next: upload to cPanel and extract per DEPLOYMENT.md."