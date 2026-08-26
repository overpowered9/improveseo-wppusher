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

## 2. `hidden_files` — 2 findings — PACKAGING

> Hidden files are not permitted. (`.gitignore`, `.gitattributes`)

They ship because WP Pusher installs the working tree directly. **Do not delete them from the
repository** — `.gitignore` is what keeps `.env` and build output out of version control.

Excluded from the distribution instead: see `.distignore` and `docs/BUILD.md`. The built ZIP
contains no dot-files at all, verified after each build.

---

## 3. `includes/lsolesen/pel/**` — 60 findings — THIRD-PARTY LIBRARY

The bundled PEL EXIF library. Split by kind:

* **55 × `ExceptionNotEscaped`** — interpolated values inside `throw new …Exception(…)` messages.
  Not user-facing output unless an exception goes uncaught.
* **5 × `OutputNotEscaped`** — `Pel.php:271`, `Pel.php:296`, `PelConvert.php:421`. These genuinely
  echo, via the library's own debug/warning helpers.

PEL is genuinely used — 26 references in `includes/functions.php` write GPS EXIF data into
generated images — so it can be neither deleted nor ignored away. Patching it in place is
possible but is undone by the next library update.

**Status: decision pending.** See the options and recommendation in the accompanying analysis.
Nothing in `includes/lsolesen/` has been modified.

---

## Also removed rather than patched

`includes/Carbon/` previously carried 11 findings. It was a ~4,000-line library used for exactly
three calls in `includes/crons.php`, so it was replaced with native date handling
(`strtotime()` / `time()` / a floored absolute difference) and deleted. Equivalence was verified
against the bundled library across nine inputs — including a future timestamp, where
`diffInMinutes()`'s absolute-value behaviour matters, and the MySQL zero date `0000-00-00
00:00:00` — with identical results in every case.
