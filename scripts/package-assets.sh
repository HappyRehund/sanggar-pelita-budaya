#!/usr/bin/env bash
# Package site/public/assets/ into a ZIP for UI-only deployments
# to Rumahweb cPanel.
#
# Bundles the Vite-hashed JS/CSS/images from site/public/assets/.
# Optionally includes index.html via the --with-index flag.
#
# The ZIP strips the site/public/ prefix so files extract directly
# into public_html/ (e.g. assets/index-CvLuP4BC.js, index.html).
#
# Usage:
#   ./scripts/package-assets.sh                          # -> deployment/deploy-assets.zip
#   ./scripts/package-assets.sh --with-index             # include index.html
#   ./scripts/package-assets.sh custom-name.zip          # override output name
#   ./scripts/package-assets.sh --with-index custom.zip  # both
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
SITE_PUBLIC="$PROJECT_ROOT/site/public"
DEPLOY_DIR="$PROJECT_ROOT/deployment"

WITH_INDEX=false
ZIP_NAME="deploy-assets.zip"

for arg in "$@"; do
  case "$arg" in
    --with-index)
      WITH_INDEX=true
      ;;
    --*)
      echo "ERROR: unknown flag '$arg'" >&2
      echo "Usage: $0 [--with-index] [output-name.zip]" >&2
      exit 1
      ;;
    *)
      ZIP_NAME="$arg"
      ;;
  esac
done

ZIP_PATH="$DEPLOY_DIR/$ZIP_NAME"

echo "==> Pre-flight checks"
if [ ! -d "$SITE_PUBLIC/assets" ]; then
  echo "ERROR: site/public/assets/ not found." >&2
  echo "Run: cd frontend && pnpm build && pnpm run deploy:sync" >&2
  exit 1
fi
echo "    site/public/assets/ OK"

if [ "$WITH_INDEX" = true ]; then
  if [ ! -f "$SITE_PUBLIC/index.html" ]; then
    echo "ERROR: site/public/index.html not found." >&2
    exit 1
  fi
  echo "    site/public/index.html OK (will be included)"
else
  echo "    index.html excluded (use --with-index to include it)"
fi

if [ ! -d "$DEPLOY_DIR" ]; then
  mkdir -p "$DEPLOY_DIR"
fi

echo "==> Create assets ZIP"
rm -f "$ZIP_PATH"
cd "$SITE_PUBLIC"

if [ "$WITH_INDEX" = true ]; then
  zip -r "$ZIP_PATH" assets index.html
else
  zip -r "$ZIP_PATH" assets
fi

echo
echo "==> ZIP contents"
unzip -l "$ZIP_PATH" | sort
echo
FILE_COUNT="$(unzip -l "$ZIP_PATH" | grep -cE "^\s+[0-9]" || true)"
echo "Files in ZIP: $FILE_COUNT"
echo "Archive size: $(du -h "$ZIP_PATH" | cut -f1)"

echo
echo "==> Sanity checks"
LISTING="$(unzip -l "$ZIP_PATH")"

if ! grep -qE "assets/[^/]+\.js" <<< "$LISTING"; then
  echo "  FAIL: no JS bundle in ZIP" >&2
  exit 1
fi
echo "  ok: JS bundle present"

if ! grep -qE "assets/[^/]+\.css" <<< "$LISTING"; then
  echo "  WARN: no CSS in ZIP — was the build CSS-only or split differently?" >&2
else
  echo "  ok: CSS present"
fi

if [ "$WITH_INDEX" = true ]; then
  if ! grep -qE "^\s.*index\.html$" <<< "$LISTING"; then
    echo "  FAIL: index.html requested but missing from ZIP" >&2
    exit 1
  fi
  echo "  ok: index.html present"
fi

echo
echo "Done: $ZIP_PATH"
echo "Next: upload to cPanel public_html/, delete old assets/, extract ZIP."