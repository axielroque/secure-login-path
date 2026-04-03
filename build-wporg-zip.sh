#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PLUGIN_DIR="$SCRIPT_DIR"
PLUGIN_SLUG="$(basename "$PLUGIN_DIR")"
PARENT_DIR="$(dirname "$PLUGIN_DIR")"

VERSION="$(grep -E '^Version:' "$PLUGIN_DIR/$PLUGIN_SLUG.php" | head -n1 | sed -E 's/^Version:[[:space:]]*//')"
if [[ -z "${VERSION}" ]]; then
  VERSION="dev"
fi

OUT_ZIP="${1:-$PLUGIN_DIR/${PLUGIN_SLUG}-${VERSION}.zip}"

rm -f "$OUT_ZIP"

(
  cd "$PARENT_DIR"
  /usr/bin/zip -r "$OUT_ZIP" "$PLUGIN_SLUG" \
    -x "$PLUGIN_SLUG/.*" \
       "$PLUGIN_SLUG/**/.*" \
       "*/node_modules/*" \
       "*/vendor/*" \
       "*/tests/*"
)

echo "Created: $OUT_ZIP"
