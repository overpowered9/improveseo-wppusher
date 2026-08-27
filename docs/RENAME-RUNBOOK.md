# Runbook: rename the plugin directory to `improveseo`

**Goal:** take Plugin Check on improveseoplugin.com from **17 errors → 0**.

**Why this and not a code change:** Plugin Check compares the declared text domain against the
plugin **directory name**, and reports dot-files that exist **in that directory**. Neither is a
property of the source code. The same commit scanned in a directory called `improveseo` returns
0 errors; scanned in `improveseo-wppusher` it returns 17. The directory is the bug.

| | Directory | Result |
|---|---|---|
| Now | `wp-content/plugins/improveseo-wppusher/` | 14 × `TextDomainMismatch` + 3 × `hidden_files` |
| After | `wp-content/plugins/improveseo/` | **0 errors** |

Total time: about a minute. No file is deleted at any point.

---

## Before you start

**Take a backup.** UpdraftPlus is already installed — *UpdraftPlus → Backup Now* is enough. This
procedure is data-safe (see "Why deactivation is safe" below), but a rename of a live plugin
directory deserves a restore point regardless.

Note the current state so you can compare afterwards:

* *Tools → Plugin Check* → select **Improve SEO** → *Check it!* → should say **17 errors found**.

---

## The procedure

### 1. Deactivate the plugin

*Plugins → Installed Plugins →* **Improve SEO** → **Deactivate**

Do this explicitly rather than letting the rename do it implicitly. WordPress keys the
`active_plugins` option by folder path, so the moment the directory changes, the plugin
disappears from that list anyway — deactivating first keeps it orderly.

### 2. Rename the directory

*File Manager →* navigate to `wp-content/plugins/` → right-click **improveseo-wppusher** →
**Rename** → `improveseo` → confirm.

Check there is no pre-existing `improveseo` directory before you do this. If one exists, it is a
leftover from an earlier install — rename it to `improveseo-old` first, and delete it only after
step 4 passes.

### 3. Reactivate

*Plugins → Installed Plugins →* **Improve SEO** → **Activate**

It will appear as a fresh entry in the list because its path changed. That is expected.

### 4. Verify

*Tools → Plugin Check* → select **Improve SEO** → *Check it!*

Expected: **Checks complete. 0 errors found.** Leave the *Warning* checkbox unticked, as you
have it now — warnings are a separate matter and do not block anything.

Then confirm the plugin actually works:

* *Improve SEO → Settings* loads, and **Save Changes** saves.
* *Improve SEO → Onboarding* loads.
* The **Channels** post type is still in the sidebar.
* A validator error still reads in English, e.g. submit *Authors → Create* with an empty
  required field and you should get `… is required`, not a blank or a raw token.

Those last two are the meaningful ones: the `Channels` labels and the validator messages come
from the exact lines Plugin Check was flagging, so if they render correctly, the text domain
resolves correctly.

---

## 5. Make it stick — WP Pusher

**This step is not optional.** WP Pusher names the install directory after the repository. While
it tracks a *branch* it copies the tree verbatim, so the next push recreates
`improveseo-wppusher/` and all 17 errors return.

*WP Pusher → Plugins →* **Improve SEO** → switch it from tracking a branch to tracking
**releases** (the label is "Link releases" or "Use release assets" depending on your WP Pusher
version), then cut a release:

```bash
git tag v2.0.13
git push origin v2.0.13
```

`.github/workflows/release.yml` builds `improveseo.zip` — top-level directory `improveseo/`, no
dot-files — and attaches it to the GitHub Release. That is what WP Pusher then deploys.

### If the directory comes back as `improveseo-wppusher`

Then WP Pusher is naming it from the repository rather than from the ZIP. Two fixes, either
works:

* Rename the GitHub repository `improveseo-wppusher` → `improveseo`, or
* Disconnect this plugin from WP Pusher and install the release ZIP through
  *Plugins → Add New → Upload Plugin*. WordPress uses the ZIP's top-level directory, so this
  always lands in `improveseo/`.

---

## Why deactivation is safe

Renaming deactivates the plugin, which fires `register_deactivation_hook` →
`improveseo_uninstall()` (`includes/installer.php:45`). Traced in full:

```php
wp_clear_scheduled_hook('improveseo_parse_tasks_hook');
delete_option('improveseo_scheduled_last_execute_time');
delete_option('improveseo_scheduled_execute_time');
```

* **No `DROP TABLE`, no post or term deletion.** Projects, tasks, generated posts, the API key,
  site code and every setting are untouched.
* There is **no `uninstall.php`** and **no `register_uninstall_hook()`**, so even deleting the
  plugin would not drop data — though this procedure never deletes anything.
* `improveseo_parse_tasks_hook` is **never scheduled** — the only `wp_schedule_event()` for it is
  commented out at `includes/installer.php:127`. Clearing it does nothing.
* Both deleted options **recreate themselves on the next page load** with the same defaults, at
  `includes/ScheduledPosts.php:7-12`.
* The real cron, `cronjob_request_event`, is re-registered by `improveseo_ensure_cron_scheduled()`
  on `init` (`improveseo.php:525`), guarded by `wp_next_scheduled()` so it cannot double-schedule.

**The one real consequence:** resetting `improveseo_scheduled_last_execute_time` restarts the
300-second window in `ScheduledPosts.php`, so scheduled-post processing may be delayed once by up
to five minutes. Nothing is lost.

Nothing else depends on the directory name — verified, not assumed:

| Check | Result |
|---|---|
| Literal `improveseo-wppusher` in any PHP/JS/CSS | **0 occurrences** |
| `plugin_basename()` | not used anywhere |
| `IMPROVESEO_ROOT`, `IMPROVESEO_DIR`, `WT_PATH`, `WT_URL` | all from `plugin_dir_path/url(__FILE__)` — relative, rename-safe |
| Option keys, cron hooks, post types, meta keys, nonce actions | all `improveseo_*`, none folder-derived |

---

## Rollback

If anything looks wrong, reverse it the same way:

1. *Plugins →* **Improve SEO** → **Deactivate**
2. *File Manager →* rename `improveseo` back to `improveseo-wppusher`
3. *Plugins →* **Improve SEO** → **Activate**

You are back to the previous state, 17 errors included. Nothing else needs undoing — no code
change is involved in this procedure.

---

## WP-CLI equivalent

If you have shell access, the whole thing is:

```bash
wp plugin deactivate improveseo-wppusher
mv wp-content/plugins/improveseo-wppusher wp-content/plugins/improveseo
wp plugin activate improveseo

wp plugin list --name=improveseo --field=file     # improveseo/improveseo.php
wp cron event list | grep cronjob_request_event   # re-registered on next init
wp plugin check improveseo --format=table         # 0 errors
```

Rollback:

```bash
wp plugin deactivate improveseo && mv wp-content/plugins/improveseo wp-content/plugins/improveseo-wppusher
wp plugin activate improveseo-wppusher
```
