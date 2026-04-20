#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PLUGIN_DIR="$SCRIPT_DIR"
PLUGIN_SLUG="$(basename "$PLUGIN_DIR")"
WPORG_SLUG="logkit"

MAIN_PHP_FILE="$(/usr/bin/grep -RIl --include='*.php' -m 1 '^\s*Plugin Name:' "$PLUGIN_DIR" || true)"
if [[ -z "${MAIN_PHP_FILE}" ]]; then
  MAIN_PHP_FILE="$PLUGIN_DIR/$PLUGIN_SLUG.php"
fi

VERSION="$(grep -E '^Version:' "$MAIN_PHP_FILE" | head -n1 | sed -E 's/^Version:[[:space:]]*//')"
if [[ -z "${VERSION}" ]]; then
  VERSION="dev"
fi

OUT_ZIP="${1:-$PLUGIN_DIR/${WPORG_SLUG}-${VERSION}.zip}"

rm -f "$OUT_ZIP"

STAGING_DIR="$(mktemp -d 2>/dev/null || mktemp -d -t logkit_zip)"
trap 'rm -rf "$STAGING_DIR"' EXIT

mkdir -p "$STAGING_DIR/$WPORG_SLUG"

/usr/bin/rsync -a \
  --exclude='.*' \
  --exclude='*.zip' \
  --exclude='node_modules' \
  --exclude='vendor' \
  --exclude='tests' \
  "$PLUGIN_DIR/" \
  "$STAGING_DIR/$WPORG_SLUG/"

(
  cd "$STAGING_DIR"
  /usr/bin/zip -r "$OUT_ZIP" "$WPORG_SLUG" \
    -x "$WPORG_SLUG/.*" \
       "$WPORG_SLUG/**/.*"
)

echo "Created: $OUT_ZIP"
