# Building the WordPress.org release ZIP

Three of the errors Plugin Check reports against the WP Pusher install cannot be fixed by
editing PHP, because they are artefacts of *how the plugin is installed* rather than defects in
the code:

| Finding | Why it appears |
|---|---|
| `hidden_files` (3) | `.gitignore`, `.gitattributes` and `.distignore` ship because WP Pusher installs the working tree directly. Deleting them from the repository would be the wrong fix — `.gitignore` is what keeps `.env` and build output out of version control, and `.distignore` is the exclude list this build reads. |
| `TextDomainMismatch` (14) | Plugin Check derives the expected text domain from the plugin **folder**, which WP Pusher names after the repository (`improveseo-wppusher`). The code and the header both declare `improveseo`, matching the published `Plugin URI`. |
| `application_detected` | Any `.sh` in the plugin directory is treated as an application file — which is why the build script below lives in this document rather than as a file in the repo. |

All three disappear when the plugin is packaged the way wp.org actually receives it: a ZIP whose
top-level directory is the plugin **slug** and which contains no dot-files or dev tooling.

Demonstrated by running the i18n sniff over the identical files under both assumptions:

```
text_domain=improveseo-wppusher  ->  14 TextDomainMismatch errors
text_domain=improveseo           ->   0 TextDomainMismatch errors
```

The code was never wrong.

## Run Plugin Check against the ZIP, not the WP Pusher install

The WP Pusher install will always report those findings. **Unzip the release and scan that** —
that is the number wp.org will actually see.

## The build script

Save this outside the plugin directory (for example `~/build-improveseo.sh`), `chmod +x` it, and
run it from the plugin root. It is kept here rather than committed as a `.sh` so that it cannot
be flagged as an application file, and so it can never end up inside the ZIP it produces.

```bash
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
# The plugin root is the CURRENT directory, not the script's own location: this script is
# kept outside the plugin (see docs/BUILD.md) so that it never ends up inside the ZIP it
# builds, which means $BASH_SOURCE would point at the wrong tree.
SRC="$(pwd)"
if [ ! -f "$SRC/improveseo.php" ]; then
  echo "ERROR: run this from the plugin root (no improveseo.php in $SRC)" >&2
  exit 1
fi
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
# The exclude list lives in .distignore, not in this script, so that the two can never
# drift apart. '.*' is added on top of it as a catch-all: an enumerated list only excludes
# the dot-files that existed the day it was written, and wp.org rejects any of them. This
# was a deliberate change after the first run tripped the safety net below on a .claude/
# directory nobody had anticipated.
if [ ! -f "$SRC/.distignore" ]; then
  echo "ERROR: .distignore is missing — refusing to build a ZIP with no exclude list" >&2
  exit 1
fi

rsync -a \
  --exclude '.*' \
  --exclude-from="$SRC/.distignore" \
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
```

## What it guarantees

* Top-level directory is `improveseo/`, so the text domain matches and the mismatch is gone.
* No dot-files. The exclusion is `.*` rather than a list of names — an enumerated list only
  excludes what existed the day it was written, and wp.org rejects any of them. This was a
  deliberate change after the first run tripped the script's own safety net on a `.claude/`
  directory nobody had anticipated.
* It **refuses to build** if the header `Version` and the readme `Stable tag` disagree. That
  mismatch makes wp.org serve the wrong download and is invisible until someone reports a bad
  update.
* No `node_modules`, no logs, no scratch files.

## Verifying a build

```bash
unzip -q dist/improveseo.zip -d /tmp/verify
ls /tmp/verify                                          # improveseo
find /tmp/verify/improveseo -name '.*' | wc -l          # 0
grep -m1 '^Version:'    /tmp/verify/improveseo/improveseo.php
grep -m1 '^Stable tag:' /tmp/verify/improveseo/readme.txt
```

## What remains after all this

**Nothing, as far as errors go.** Verified 2026-08-27 against Plugin Check 2.1.0 on WordPress
7.0.2: `wp plugin check improveseo` reports **0 errors** against the unzipped
`dist/improveseo.zip`.

The `includes/lsolesen/pel/` EXIF library used to account for 60 of them. Five were real `echo`
statements and are patched; the other 55 were `ExceptionNotEscaped` on values that are never
output, and carry a scoped, justified `phpcs:disable` in six files. The reasoning, the trace
showing no catch block renders a PEL exception, and the re-apply procedure are in
`PLUGIN-CHECK-NOTES.md` §3. The library itself is genuinely used — `addGpsInfo()` writes GPS EXIF
into generated images — and PHP has no native EXIF *writer*, so it cannot simply be dropped.

Warnings are a separate matter and are **not** addressed by this build: the same run reports
~2,870 of them, dominated by `NonPrefixedVariableFound`, `MissingUnslash`, `InputNotSanitized`
and `NonceVerification`. wp.org blocks on errors, not warnings, but these are worth their own
piece of work.

`includes/Carbon/` used to report 11. It was removed rather than patched: it was a ~4,000-line
library used for exactly three calls in `includes/crons.php`, replaced with native date handling
after verifying identical results across nine inputs.
