# Plugin Check — accepted findings

Every Plugin Check finding in first-party code has been fixed. What remains falls into three
groups that are **not** code defects. This file exists so that a reviewer, or any of us in six
months, does not "fix" them and make things worse.

Last verified against the tree at the commit that added this file.

---

## 1. `WordPress.WP.I18n.TextDomainMismatch` — 14 findings — FALSE POSITIVE

> Mismatched text domain. Expected `improveseo-wppusher` but got `improveseo`.

### Do not mass-replace `improveseo` with `improveseo-wppusher`

That would break every translation the moment the plugin is installed under its real slug, and
would have to be reverted before submission. The code is correct as written.

### Why it fires

Plugin Check derives the *expected* text domain from the **plugin folder name**. This repository
is installed by WP Pusher, which names the folder after the repository — `improveseo-wppusher`.
The plugin's actual slug is `improveseo`.

### Evidence that `improveseo` is canonical

| Source | Value |
|---|---|
| `Text Domain:` header, `improveseo.php` | `improveseo` |
| `Plugin URI:` header | `https://wordpress.org/plugins/improveseo/` |
| Text domain used in code | `improveseo` — all 31 i18n calls, no exceptions |

The string `improve-seo` also appears six times in the codebase. **None of them is a text
domain** — five are the `improve-seo` *category slug* (`includes/filters.php`,
`views/GenerateAIpopup/GenerateAIpopuphtml.php`) and one is a default export filename
(`modules/import-export.php`). They are unrelated and should be left alone.

### Proof

Running the i18n sniff over the identical files under both assumptions:

```
text_domain=improveseo-wppusher  ->  14 TextDomainMismatch errors
text_domain=improveseo           ->   0 TextDomainMismatch errors
```

### The 14 sites

| File | Line |
|---|---|
| `improveseo.php` | 513, 1461 |
| `includes/posttypes.php` | 45, 46 |
| `includes/crons.php` | 15 |
| `includes/ImproveSEO/Validator/IfNot.php` | 16 |
| `includes/ImproveSEO/Validator/Numeric.php` | 15 |
| `includes/ImproveSEO/Validator/PostType.php` | 14 |
| `includes/ImproveSEO/Validator/Required.php` | 15 |
| `includes/ImproveSEO/Validator/RequiredIf.php` | 15 |
| `includes/ImproveSEO/Validator/Unique.php` | 30 |
| `views/settings/index.php` | 62 |
| `views/onboarding/index.php` | 18 |
| `views/authors/create.php` | 53 |

### What makes them disappear

Two independent levers — **either one alone is sufficient**:

1. **At packaging time (already solved).** Build the release with the procedure in
   `docs/BUILD.md`. It produces a ZIP whose top-level directory is `improveseo/`, so the folder
   matches the declared domain. This is what wp.org receives, and it needs no repository change.
2. **At WP Pusher level (optional).** WP Pusher names the install directory after the GitHub
   repository. Renaming the repo `improveseo-wppusher` → `improveseo` would make the *installed*
   folder match too, so live scans stop reporting it. Note this changes the install path, so
   existing sites would likely need a reinstall rather than an update — worth doing only if you
   want the live scan clean, not for submission.

> **Run Plugin Check against the built ZIP, not the WP Pusher install.** The install will always
> report these; the ZIP does not.

### One real gap, unrelated to the mismatch

Internationalisation is **not functional** in this plugin, and fixing the domain does not change
that:

* `load_plugin_textdomain()` is never called.
* There is no `languages/` directory.
* There are no `.pot`, `.po` or `.mo` files.

So the ~31 translatable strings resolve to their English source regardless. If translations are
ever wanted, that is a small feature (loader + `languages/` + generated `.pot`), not a lint fix.

---

## 2. `hidden_files` — 3 findings — PACKAGING

> Hidden files are not permitted. (`.gitignore`, `.gitattributes`, `.distignore`)

They ship because WP Pusher installs the working tree directly. **Do not delete them from the
repository** — `.gitignore` is what keeps `.env` and build output out of version control, and
`.distignore` is the exclude list that keeps all three out of the ZIP.

Excluded from the distribution instead: see `.distignore` and `docs/BUILD.md`. The built ZIP
contains no dot-files at all, verified after each build.

### Audit, 2026-08-27

`.claude/settings.local.json` was **tracked in git**. `.gitignore` lists `/.claude`, but the file
had been committed before that rule existed, and ignore rules never apply to already-tracked
files — so WP Pusher deployed it to the live site. Untracked with `git rm --cached`; the file
stays on disk for local use. The scan did not report it because the deployed copy predates it.

`.distignore` was also extended with forward guards for paths that do not exist in the tree today
(`composer.json`, `composer.lock`, `phpcs.xml*`, `.phpcs.xml*`, `tests`, `.editorconfig`,
`*.xlsx`). An enumerated exclude list only covers what existed the day it was written; the build
script's `--exclude '.*'` catch-all and its hard-fail safety net cover the rest.

No other dev-only file ships. `PLUGIN-CHECK-NOTES.md`, `README.md`, `docs/`, `package-lock.json`
(an orphan — there is no `package.json`) and `test_modal.html` are all in `.distignore`;
`docker-compose.yml` is untracked, so WP Pusher never sees it.

---

## 3. `includes/lsolesen/pel/**` — 60 findings — THIRD-PARTY LIBRARY

The bundled PEL EXIF library (copyright 2004–2007, i.e. PEL ~0.9.x). Split by kind:

* **5 × `OutputNotEscaped`** — statements that genuinely `echo`. **Patched.** See below.
* **55 × `ExceptionNotEscaped`** — values passed into `throw new Pel*Exception(…)`.
  **Suppressed with a scoped, justified annotation.** See below.

### PEL cannot be removed, and cannot be replaced with core functions

`addGpsInfo()` (`includes/functions.php:1467`) writes GPS EXIF into generated images for the
local-SEO feature. Three live call sites: `modules/builder.php:1016`, `modules/builder.php:3066`,
`includes/crons.php:145`.

The usage is **write-only** — 9 `addEntry()` calls, zero `getEntry()`/`getValue()`, ending in
`file_put_contents($output, $jpeg->getBytes())`. That matters, because **PHP has no EXIF writer**:
`exif_read_data()` exists, but there is no `exif_write_*` counterpart, and
`wp_read_image_metadata()` is read-only. So "drop PEL and use native functions" is not available —
dropping PEL means dropping image geotagging.

### The 5 `OutputNotEscaped` — patched

| File | Line | Before | After |
|---|---|---|---|
| `Pel.php` | 271 | `vprintf($str . "\n", $args)` | `echo esc_html( vsprintf( … ) )` |
| `Pel.php` | 296 | `vprintf('Warning: ' . …)` | `echo esc_html( vsprintf( … ) )` |
| `PelConvert.php` | 421 | `printf('%02X ', ord(…))` | `echo esc_html( sprintf( … ) )` |
| `PelConvert.php` | 424 | `print("\n")` | `echo "\n"` |
| `PelConvert.php` | 427 | `print("\n")` | `echo "\n"` |

Five lines changed, nothing else. These are the **only** output statements in the entire PEL tree.

Both code paths are unreachable as shipped, so the patch is a hardening measure rather than a live
fix:

* `Pel::debug()` / `Pel::warning()` are gated on `private static $debug = false`, and
  `Pel::setDebug()` is never called anywhere in the plugin.
* `PelConvert::bytesToDump()` has **zero** call sites outside the library.

Verified by running the patched and unpatched classes side by side: `bytesToDump()` output is
byte-identical across all 256 byte values at six buffer lengths; `debug()`/`warning()` are
identical both with the flag off (silent) and on (plain-text messages). Output diverges only when a
message contains HTML, which is exactly the case being fixed. `addGpsInfo()` was then exercised
end-to-end — a generated JPEG came back a valid image with GPS EXIF that `exif_read_data()` reads
back to the written coordinates.

### The 55 `ExceptionNotEscaped` — suppressed with a scoped annotation

Six files carry a file-level `phpcs:disable` for this one sniff, with a matching `phpcs:enable`
at the end. Two annotation lines and four lines of explanation per file; **no library logic,
message format, or indentation is changed.** Every other sniff still runs over these files.

| File | Findings |
|---|---|
| `PelIfd.php` | 29 |
| `PelDataWindow.php` | 8 |
| `PelEntryTime.php` | 8 |
| `PelTiff.php` | 6 |
| `PelJpeg.php` | 3 |
| `PelExif.php` | 1 |

#### Why not `esc_html()` at the throw sites

Because it is a **behaviour change, not a no-op**. Most of the 55 are not `sprintf` format
arguments — they are typed constructor arguments:

```php
throw new PelUnexpectedFormatException($this->type, $tag, $format, PelFormat::ASCII);
throw new PelWrongComponentCountException($this->type, $tag, $components, 20);
throw new PelJpegInvalidMarkerException($marker, $i);
```

Those constructors consume the arguments internally through `%d` / `%02X` specifiers and use
`$tag` as an array key in `PelTag::getName()`. `esc_html()` returns a string, so wrapping them
coerces every `int` and class constant to `string` and changes what the constructor receives.
Escaping `PelFormat::ASCII` is meaningless. So the sniff is firing on values that are not output
at all, and "fixing" it would be the riskiest change in the file.

#### Why there is nothing to escape at the point of output

Traced, because that is the question the sniff is a proxy for:

* The only PEL entry point in first-party code is `addGpsInfo()` (`includes/functions.php:1467`),
  called from `modules/builder.php:1016`, `modules/builder.php:3066` and `includes/crons.php:145`.
* **No `catch` block anywhere in the plugin handles a PEL exception.** The `catch ( \Throwable $e )`
  blocks at `modules/builder.php:1394` and `:3443` wrap the featured-image code — a different
  block — and route to `error_log()`, not to output.
* No `catch` in the plugin echoes, prints, or passes a caught message into `wp_die()`,
  `add_settings_error()` or an admin notice.
* The interpolated values are library-internal integers, byte offsets, class constants and
  `gettype()` results — never user input.

There is therefore no point of output to escape.

#### Verified

Confirmed with PHPCS 3.x + WPCS 3.4.1, and with Plugin Check 2.1.0 itself:

```
--ignore-annotations  ->  55 ExceptionNotEscaped   (matches the original scan exactly)
default (annotations) ->   0 ExceptionNotEscaped
wp plugin check       ->   0 errors against dist/improveseo.zip
```

`OutputNotEscaped` stays at 0 in both modes — the annotation is scoped to `ExceptionNotEscaped`
and does not mask the five patched `echo` statements above.

### Re-applying after a library update

`docs/pel-escaping.patch` holds the whole PEL delta — the 5-line escaping diff **and** the
annotations in the six files above — and applies with `git apply` against pristine upstream files.
Re-apply it after any PEL upgrade, then confirm the escaping sniff reports 0 `OutputNotEscaped`
and 0 `ExceptionNotEscaped` in `includes/lsolesen/pel/`.

If a PEL upgrade adds a new file that throws with interpolated values, it needs the same two
annotation lines. The check that catches it:

```bash
phpcs --standard=WordPress --sniffs=WordPress.Security.EscapeOutput -s \
      --report=csv includes/lsolesen/pel | tail -n +2 | wc -l   # expect 0
```

> **Worth doing separately:** the bundled copy is ~18 years old, while upstream `lsolesen/pel` is
> still maintained and has PHP 8-compatible releases. Upgrading is a real improvement, but it is a
> library swap that needs `addGpsInfo()` retested — deliberately kept out of this change.

---

## Also removed rather than patched

`includes/Carbon/` previously carried 11 findings. It was a ~4,000-line library used for exactly
three calls in `includes/crons.php`, so it was replaced with native date handling
(`strtotime()` / `time()` / a floored absolute difference) and deleted. Equivalence was verified
against the bundled library across nine inputs — including a future timestamp, where
`diffInMinutes()`'s absolute-value behaviour matters, and the MySQL zero date `0000-00-00
00:00:00` — with identical results in every case.
