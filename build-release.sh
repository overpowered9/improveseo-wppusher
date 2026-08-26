#!/usr/bin/env bash
#
# Build a WordPress.org-ready ZIP of the plugin.
#
# WHY THIS EXISTS
#
# This repository is installed directly by WP Pusher, so the working tree IS the plugin
# directory on a live site. That has two consequences that Plugin Check reports and that
# cannot be fixed by editing PHP:
#
#   1. hidden_files — .gitignore and .gitattributes ship with the plugin. Deleting them from
#      the repository is the wrong fix: .gitignore is what keeps .env and build artefacts out
#      of version control. They should be absent from the DISTRIBUTION, not from the repo.
#
#   2. Mismatched text domain — Plugin Check derives the expected text domain from the plugin
#      FOLDER name. WP Pusher names that folder after the repository, "improveseo-wppusher",
#      while the code and the plugin header both declare "improveseo" — which is correct, and
#      matches the published Plugin URI (wordpress.org/plugins/improveseo/). The mismatch is
#      an artefact of the install method, not a defect in the code.
#
# Both disappear when the plugin is packaged the way wp.org actually receives it: a ZIP whose
# top-level directory is the plugin SLUG, containing no dot-files.
#
# USAGE
#     ./build-release.sh              # writes dist/improveseo.zip
#     ./build-release.sh /tmp/out     # writes /tmp/out/improveseo.zip
#
# Run Plugin Check against the unzipped result, not against the WP Pusher install, when you
# want the number that wp.org will actually see.

set -euo pipefail

SLUG="improveseo"
SRC="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
OUT="${1:-$SRC/dist}"
STAGE="$(mktemp -d)"
trap 'rm -rf "$STAGE"' EXIT

# Keep the version in the ZIP name honest — read it from the plugin header rather than
# hardcoding it here, so the two can never disagree.
VERSION="$(grep -m1 '^Version:' "$SRC/improveseo.php" | sed 's/Version:[[:space:]]*//' | tr -d '\r')"
if [ -z "$VERSION" ]; then
  echo "ERROR: could not read Version from improveseo.php header" >&2
  exit 1
fi

# Stable tag in readme.txt must match the header Version, or wp.org serves the wrong download.
STABLE="$(grep -m1 '^Stable tag:' "$SRC/readme.txt" | sed 's/Stable tag:[[:space:]]*//' | tr -d '\r')"
if [ "$VERSION" != "$STABLE" ]; then
  echo "ERROR: Version ($VERSION) and readme.txt Stable tag ($STABLE) disagree." >&2
  echo "       wp.org would serve the wrong files. Fix one of them and re-run." >&2
  exit 1
fi

echo "Building ${SLUG} ${VERSION}"
mkdir -p "$STAGE/$SLUG" "$OUT"

# rsync rather than cp: the exclude list is the whole point of this script.
#
# '.*' excludes every dot-entry at any depth, rather than naming them one at a time. That was
# a deliberate change after the first run of this script tripped its own safety net on a
# .claude/ directory nobody had thought of — an enumerated list only excludes the dot-files
# that existed the day it was written, and wp.org rejects any of them. Verified first that no
# dot-file in this plugin needs to ship: there is no .htaccess, no .user.ini, nothing.
rsync -a \
  --exclude '.*' \
  --exclude 'node_modules' \
  --exclude 'dist' \
  --exclude 'build-release.sh' \
  --exclude '*.log' \
  --exclude 'test_modal.html' \
  "$SRC/" "$STAGE/$SLUG/"

# Belt and braces: catch any dot-file the list above did not anticipate. A new one appearing
# is exactly the failure this script is meant to prevent, so fail loudly rather than ship it.
LEFTOVER="$(find "$STAGE/$SLUG" -name '.*' -not -name '.' -not -name '..' | head -20)"
if [ -n "$LEFTOVER" ]; then
  echo "ERROR: hidden files still present in the build:" >&2
  echo "$LEFTOVER" >&2
  echo "       Add them to the exclude list in build-release.sh and re-run." >&2
  exit 1
fi

ZIP="$OUT/${SLUG}.zip"
rm -f "$ZIP"
( cd "$STAGE" && zip -rq "$ZIP" "$SLUG" )

echo "Wrote $ZIP"
echo "  top-level directory : $SLUG/   (matches the text domain, so the mismatch is gone)"
echo "  hidden files        : none"
echo "  files               : $(find "$STAGE/$SLUG" -type f | wc -l)"
