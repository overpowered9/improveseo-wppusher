# Keyword data in the plugin: Option A (hand-off) and dormant Option B (Content v3)

Branch `feature/keyword-data-v3`, cut from `Nov-03` (00e2dd6), built 2026-11-11/12. Pairs with the server branch of the same name (`imporve-seo-admin-server`, ad46eff; its report is `docs/KEYWORD_DATA_V3.md` there). Nothing was deleted; every change is additive and v2 generation is untouched.

## Contents

- [Phase 0: what the plugin looked like](#phase-0-what-the-plugin-looked-like)
- [What was built, per phase](#what-was-built-per-phase)
- [New settings, options, post meta, columns](#new-settings-options-post-meta-columns)
- [QA results](#qa-results)
- [How to try each feature](#how-to-try-each-feature)
- [What is needed to make it production-ready](#what-is-needed-to-make-it-production-ready)
- [Known limitations and open questions](#known-limitations-and-open-questions)

## Phase 0: what the plugin looked like

| Item | Finding (file:line on Nov-03) |
|---|---|
| "Keyword Finder" | Did not exist. The "Keywords Generator Tool" (`views/features/keyword.php`) asks gpt-4o for up to 30 keyword strings via the browser → `POST /api/v1/generate/generatekeyword`; no volume, difficulty or intent. |
| Saved lists | `wp_improveseo_lists (id, name VARCHAR(50), list, size, created_at)` (`includes/installer.php:277-295`). Save handler `improveseo.php:1534-1629` had **no nonce and no capability check** and inserted raw input. |
| Single wizard | `#seed_keyword` pre-filled only from onboarding (`views/GenerateAIpopup/GenerateAIpopuphtml.php:747-760`). Titles: `/generate/auxiliary`; articles: `/content-v2/article`. The WP post is created later by `improveseo_builder()` (`modules/builder.php:1336`) from the project options. |
| Bulk wizard | A saved list is mandatory; `?keyword_list=<id>` pre-selects one. Posts are created in `saveContentInTaskList()` (`modules/bulk_AI_post_function.php:1677`). |
| Content v3 Title / Planner calls | **None in the plugin**, and the server's `/content-v3/title` and `/planner` routes are admin-only (Bearer). Decided with the project owner (2026-11-11): build Option B dormant, ready to wire in. |
| Server contract | Verified on ad46eff: `/api/v1/generate/keywords/metrics` → `{success, items[], cached_count, cost_usd}`; `/api/v1/content-v3/keyword-data/prefetch` → `{success, status, seed, related_count, reason?}`; Title/Planner accept `use_keyword_data` and `recent_posts`. Matches the contract. |

## What was built, per phase

### Phase 1: API client (a9d512b)

`modules/keyword-data.php` (new, included from `improveseo.php`):
- `improveseo_keyword_data_request()` mirrors `improveseo_call_auxiliary_api()`: stored API key + site code, `X-API-Key` / `X-Site-Code` / `X-Site-Domain`, `wp_remote_post`, `$error` by reference, `$is_not_connected` on 401/403. Server codes map to sentences a site owner can act on (`improveseo_keyword_data_error_message()`); 503 `KEYWORD_DATA_NOT_CONFIGURED` → "Keyword data is not enabled on the ImproveSEO server yet."
- `improveseo_keyword_metrics(array $keywords)`: chunks of 100, dedupes, returns items keyed by the normalised keyword. Every item is cleaned to the contract's fields; missing values stay `null`; `scope` is pinned to `national` (v1).
- `improveseo_keyword_prefetch($keyword)`: never throws; `unavailable` on any problem, logged.
- `docs/content-v3-prompt-drafts.md` and `docs/keyword-data-research.md` added (force-added; `*.md` is git-ignored).

### Phase 2: keyword tool (a821d58)

- After the Keyword Generator produces its list, `assets/js/keyword-metrics.js` makes **one** bulk call (admin-ajax `improveseo_keyword_metrics`, nonce `improveseo_ajax`, `manage_options`) and renders a table: Keyword · Monthly searches (US) · Difficulty · Intent · "Write a post". Sortable by searches and difficulty (unknowns always last). "—" for missing values, never 0. Loading, empty, partial-failure (warning) and error states with Retry; a 401/403 opens the shared connection-guard modal.
- "Create bulk project" for the checked rows: saves them as a list named `<seed> selection YYYY-MM-DD` and opens the Bulk wizard on it. The Save Results success popup also gets a "Create bulk project" button for the new list.
- Saved lists keep metrics: new nullable `metrics LONGTEXT` column (dbDelta on fresh installs; `improveseo_ensure_lists_metrics_column()` self-heals existing sites, same pattern as the bulk meta columns). The save handler now has `check_ajax_referer` + `current_user_can('manage_options')`, sanitises inputs, validates the metrics JSON field by field, caps the name at the column's 50 characters and reports insert failures. `wt-script.js` sends the nonce and the metrics.
- **Not done on purpose:** bumping `improveseo_db_version`. `improveseo_update_db_check()` runs `improveseo_install()` at `plugins_loaded`, which ends in `$wp_rewrite->flush_rules()` before WordPress has created `$wp_rewrite`, so any version change fatals once on every existing site (reproduced locally). The column is added on demand instead.

### Phase 3: hand-off and post meta (4106fed)

- Single wizard: `create_post_single&keyword=<kw>&kw_src=finder[&kw_list=<id>]` pre-fills the seed; `assets/js/keyword-wizard.js` shows one line under it ("US monthly searches: 2,400 · Difficulty: 18 · Intent: commercial") via admin-ajax `improveseo_keyword_summary` (`edit_posts`): from the saved list's metrics when `kw_list` is given, else one lookup (capped at 3 per session). Editing the keyword clears the line and looks the new one up on blur. Hidden when there is no data.
- Bulk wizard: "Search data (US monthly searches) is available for N of M keywords" under the list, from the list's stored metrics (`get_keyword_list_data` and the page's per-list cache now carry `metrics`). No lookup is ever made from the bulk wizard.
- Post meta `_iseo_seed_keyword` and `_iseo_keyword_data` (the snapshot the user saw):
  - single: wizard hidden fields → `#main_form` (`ai_keyword_data`, `ai_keyword_source`) → `do_create_post` (validated, `modules/dashboard.php`) → project options (also preserved on draft publish by `improveseo_preserve_generation_options()`) → both builder paths (`modules/builder.php`, after `improveseo_project_id`);
  - bulk: from the project's list metrics at insert and on regeneration (`improveseo_bulk_task_keyword_data()`).

### Phase 4: Option B, dormant (e03a1fd)

- Setting **Use keyword data in Content v3 (beta)** (`improveseo_v3_keyword_data`, default OFF) in Settings → Content Features, auto-saved like the featured-image toggles (`includes/ajax.php` allow-list).
- When on: `keyword-wizard.js` prefetches the seed being typed (admin-ajax `improveseo_keyword_prefetch` → `/content-v3/keyword-data/prefetch`), debounced 600 ms, at most 3 per wizard session, skipped when the keyword came from the Keyword Generator.
- `improveseo_v3_step_fields('title'|'planner')` returns `use_keyword_data: true` and, for the Planner, `recent_posts` (`improveseo_recent_plugin_posts(20)`: published posts with `_iseo_seed_keyword`, or `improveseo_project_id`, or a bulk task `post_id`). Empty when the setting is off. **This is the one integration point** for the future v3 Title/Planner calls; nothing attaches it to a live request today, so every payload stays byte-identical.
- `window.iseoHandlePlannerResponse(resp, {onKeep, onChange})` / `window.iseoShowOverlapNotice()` show "A recent post may already cover this: <title>. <reason>" with Keep keyword / Change keyword.
- Bulk: logs what a v3 Title request would carry; a failed prefetch never blocks a post.

## New settings, options, post meta, columns

| Kind | Name | Notes |
|---|---|---|
| Setting | `improveseo_v3_keyword_data` | `0`/`1`, default `0`, group `improveseo_feature_settings` |
| Option | `improveseo_lists_metrics_col` | `ready` once the column exists (self-heal marker) |
| Column | `wp_improveseo_lists.metrics` | `LONGTEXT NULL`, JSON map keyword → item |
| Post meta | `_iseo_seed_keyword` | plain text |
| Post meta | `_iseo_keyword_data` | JSON item `{keyword, search_volume, keyword_difficulty, intent, scope, fetched_at}` |
| Project option | `ai_keyword_data`, `ai_keyword_source` | inside the task's base64(json) `options` |
| AJAX actions | `improveseo_keyword_metrics` (manage_options), `improveseo_keyword_summary`, `improveseo_keyword_prefetch` (edit_posts) | all check nonce `improveseo_ajax` |
| Assets | `assets/js/keyword-metrics.js` + `.css` (Keyword Generator page), `assets/js/keyword-wizard.js` (wizard pages) | page-scoped in `includes/assets.php` |

## QA results

Two passes. **Pass 1 (2026-11-11):** local Docker WordPress (`improveseo_wp`, http://localhost:8088) with `WP_DEBUG` + `WP_DEBUG_LOG` on, headless Chrome driven over CDP, the plugin's server calls intercepted by a throwaway mu-plugin (contract-shaped stub, or rewritten to the server branch running locally on port 3005); no DataForSEO credentials yet. **Pass 2 (2026-11-12, real data):** the team lead's DataForSEO credentials in the local server's `.env`, the real site API key (user 1 / website 19 / `609E`, copied from the DB without printing it) in the local WordPress, the plugin pointed at the local server branch, `ISEO_DOMAIN_ENFORCEMENT=off` locally because the WP runs on `localhost:8088`. Screenshots from pass 2 are delivered separately (the `keyword-data-delivery` folder with the Word report), not in the repo.

### Pass 2: real DataForSEO data (2026-11-12)

| # | Scenario | Result |
|---|---|---|
| S1 | Server: `POST /generate/keywords/metrics` with the real key, 3 keywords | Pass. `water heater repair cost` 1,300 / KD 3 / informational; `tankless water heater installation` 40,500 / 0 / transactional; `sump pump replacement` 5,400 / 2 / commercial; `cost_usd` 0.01236. Log: `[dataforseo] endpoint=keyword_overview http=200 status=20000`. **The DataForSEO field names the server report flagged as unverified are confirmed correct** (volume, difficulty and intent all populated). |
| S2 | Server: `POST /content-v3/keyword-data/prefetch` | Pass. `status: ready`, seed item, `related_count: 20`, `related_keywords` endpoint cost 0.0144. Cache tables missing (migration 022 not applied) → `[keyword-data] cache unavailable` logged, call still succeeds. |
| S3 | Server (admin): Title step with `use_keyword_data: true` | Pass. `keyword_data.used = true`, prompt `content_v3_title_keyword_data` (in-code fallback; production Mongo deliberately not seeded from a dev box), `<keyword_data>` block in the user message with the seed + 10 related keywords and real volumes; title "Water heater repair cost in Española: what drives the price", 1 attempt. |
| S4 | Server (admin): Planner step with keyword data + `recent_posts` | Pass. `keyword_data.used = true`, 19 related; `recent_posts` selected post 12 (score 0.571), `overlap = { post_id: 12, reason: … }`, `demand_notes` written; article_type `cost`, size `small`. First attempt returned non-JSON, second succeeded (the 3-attempt rule working). |
| S5 | Server (admin): A/B harness, 2 keywords, `save: false` | Pass. Both keywords ran OFF then ON; totals: DataForSEO $0.054, 78 s, tokens reported; prefetch `ready` for both. |
| 1 | Keyword tool, real metrics (10 keywords) | Pass. Table shows real figures, e.g. `water heater repair near me` 90,500 / KD 7 / commercial; `water heater repair or replace` 90 / 2; sort by volume 90,500 → 90; "2 selected" enables Create bulk project; Save Results stores all 10 items in `metrics`; "Write a post" link gains `kw_list=2`. (The keyword-*ideas* call is the plugin's existing browser → Render request; production rejects it from `localhost:8088` by domain, so for this local run that one call was answered with a fixed list. It is unchanged code.) |
| 3 | Hand-offs | Pass. Single wizard from the link: "US monthly searches: 1,300 · Difficulty: 3 · Intent: informational" from the saved list; typing `tankless water heater installation cost` → one real lookup → "US monthly searches: 27,100 · Difficulty: 12 · Intent: commercial"; bulk wizard: "Search data (US monthly searches) is available for 10 of 10 keywords." |
| 4 | Setting OFF: payloads unchanged | Pass. Title + article generation: the 6 generation requests (`/auxiliary` ×5, `/content-v2/article`) are byte-identical with the setting OFF and ON; the only extra requests with it ON are the 3 prefetch calls made while typing. |
| 5 | Setting ON: real prefetch | Pass. Fast typing → 1 call; 4 distinct keywords → 3 calls (cap); all answered `ready` (`sump pump replacement`, `sump pump replacement cost`, `drain cleaning`). |
| 8 | Post meta | Pass. Single: project 15 built through the real builder → post has `_iseo_seed_keyword = water heater repair cost` and the real snapshot. Bulk: `saveContentInTaskList()` wrote `water heater repair near me` + snapshot (90,500 / 7 / commercial) for the real list and only the seed for a list without data; `improveseo_recent_plugin_posts()` lists them. |
| — | Spend | 27 DataForSEO calls in the whole pass, **$0.36** total (from the server's per-call `cost=` log lines; prefetch ≈ $0.027 per seed, metrics ≈ $0.012–0.013 per batch). |
| — | Lint / tests after the pass | `php -l` clean on every changed PHP file, `node --check` clean, server `npm test` 754/754, `typecheck` clean. |
| — | `debug.log` | No new notice from the changed code. Seen and pre-existing on Nov-03: `Undefined variable $post_author` in `saveContentInTaskList` (`bulk_AI_post_function.php`, the author fallback block) when a project has no assigned author; it falls back to author 1 as before. |

### Merge readiness (checked 2026-11-12, no merge performed)

- Plugin: `origin/Nov-03` is still `00e2dd6`, the commit the branch was cut from → `feature/keyword-data-v3` is a **pure fast-forward** of `Nov-03`; no conflicts possible. Working tree clean.
- Server: `origin/main` is still `571cb62` → `feature/keyword-data-v3` (ad46eff) is a **pure fast-forward** of `main`. Working tree clean.
- After the merge nothing changes at runtime until the Render env carries the DataForSEO variables (see below); with them absent the new routes answer 503 `KEYWORD_DATA_NOT_CONFIGURED` and the plugin shows its "not enabled yet" message, exactly as in pass 1.

### Pass 1: stub and error paths (2026-11-11)

| # | Scenario | Result |
|---|---|---|
| 1 | Keyword tool: metrics table; missing values show "—"; sorting; selection; "Write a post" links | Pass. 10 rows, 3 with no data shown as "—"; sort desc/asc with unknowns last; links carry `keyword`, `kw_src=finder`, and `kw_list` after a save. |
| 1b | Keyword tool: server 503 (not configured), timeout, 401 (real server) | Pass. Error banner with the mapped sentence + Retry; table still lists keywords with "—"; Save Results still works; metrics JSON empty. |
| 2 | Save a list: nonce + capability; metrics persist | Pass. Request carries `nonce`; row 2 stored the JSON map; the handler rejects a missing nonce (`check_ajax_referer`). |
| 3 | Hand-offs prefill | Pass. Single wizard pre-filled with source `keyword_finder`, list 2; summary line "US monthly searches: 1,100 · Difficulty: 7 · Intent: commercial"; success popup shows "Create bulk project"; bulk wizard pre-selects the list and shows "available for 10 of 10 keywords" (hidden for a list without data). |
| 4 | Setting OFF: payloads unchanged | Pass. Generate title + Generate post logged 7 server requests (`/users/status`, `/auxiliary` ×5, `/content-v2/article`); bodies byte-identical with the setting OFF and ON. |
| 5 | Setting ON: prefetch ≤ 3, debounced | Pass. Fast typing produced one call; five distinct keywords produced 3 calls total. |
| 6 | Server unavailable / slow | Pass. With the keyword routes timing out and the setting on, title + article still generated; no console errors; failures logged. |
| 7 | Overlap notice | Pass. Modal with Keep keyword / Change keyword; Change focuses `#seed_keyword`; `overlap: null` shows nothing. |
| 8 | Post meta | Pass. Single: project 15 → post 122 has both meta keys (data from the wizard). Bulk: `saveContentInTaskList()` wrote both keys for a list with metrics and only the seed for a list without; `improveseo_recent_plugin_posts()` returned them plus older project posts. |
| — | Settings toggle | Pass. Saves via the auto-save, "Saved" pill, persists after reload; the featured-image toggles unchanged. |
| — | PHP lint / JS syntax | `php -l` clean on every touched file; `node --check` clean. No phpcs/eslint config exists in the plugin. |
| — | `debug.log` | No new notices or warnings from the changed code. Pre-existing on Nov-03 and still present: `wp_enqueue_script called incorrectly` (custom-plugin-script, `improveseo.php:40-60`), `GenerateAIpopup.php` foreach on false (`$saved_rnos`), `create-post-single.php` `$task`/`$word_ai_email`, `single_AI_post_function.php:32,42`, `dashboard.php` `max_posts`/`cats`, `functions.php:333` `tags`, `builder.php` `tags`/`geoData`, and the browser error `jQuery(...).smartWizard is not a function`. |
| — | Server branch | `npm test`: 754/754 pass; `npm run typecheck` clean. |

## How to try each feature

1. **Keyword tool:** Improve SEO → Keyword Lists & Tool → Generate Keywords for Me → type a seed → Generate Keywords. The "Search data" table fills under the buttons. Click a column header to sort. Tick rows → Create bulk project. Save Results → the popup offers Close / View Keyword Lists / Create bulk project.
2. **Write a post:** click "Write a post" on a row. The single wizard opens with the keyword and the search-data line under it; type another keyword and leave the field to see its data (max 3 lookups per visit).
3. **Bulk:** open the Bulk wizard on a list saved from the tool; the keyword count line is followed by "Search data … available for N of M keywords".
4. **Setting:** Settings → Content Features → "Use keyword data in Content v3 (beta)". Off: nothing changes. On: open the single wizard, type a keyword; the browser console shows `[ImproveSEO] keyword prefetch "…": ready|unavailable`.
5. **Post meta:** after a post is built, `wp postmeta` has `_iseo_seed_keyword` and (when data was shown) `_iseo_keyword_data`.

## What is needed to make it production-ready

Server (Render), on a deploy that includes `feature/keyword-data-v3`:
- `DATAFORSEO_LOGIN` and `DATAFORSEO_PASSWORD` (DataForSEO Labs; the account the team lead provided works, balance $50.57 before QA). Without them the tool shows "Keyword data is not enabled on the ImproveSEO server yet." and the wizards show no line.
- Migration 022 (two tables, `keyword_metrics_cache` and `keyword_related_cache`) applied to Supabase: `npm run --silent migrations:print` in the server repo prints the SQL. Until then every lookup pays DataForSEO (logged `[keyword-data] cache unavailable`).
- `KEYWORD_METRICS_CREDIT_COST` (default `0`, i.e. free) — a product decision.
- `ISEO_V3_KEYWORD_DATA=true` only when Option B should be live for Title/Planner (keep unset until the A/B test passes); `POST /api/v1/content-v3/prompts/seed` once with an admin token to register the step prompts.
- Optional `AB_MODEL_PRICE_INPUT_PER_MTOK` / `AB_MODEL_PRICE_OUTPUT_PER_MTOK` for the harness cost column.

Plugin: nothing beyond the existing API key + site code per site. The beta setting ships OFF.

## Known limitations and open questions

1. **Real DataForSEO data verified on 2026-11-12** (pass 2 above): the item shape matches the server's normaliser; some keywords legitimately return difficulty `0` (shown as 0, not "—"). The DataForSEO API password was shared through chat for this run; rotating it in the DataForSEO dashboard (API Access → Reset password) before production is recommended.
2. **Option B is dormant.** The plugin makes no Content v3 Title/Planner requests, and the server's step routes are admin-only. When those calls are built, spread `improveseo_v3_step_fields('title'|'planner')` into their payloads and call `window.iseoHandlePlannerResponse(resp, {onKeep, onChange})` with the Planner's answer.
3. **National data only (US, `location_code` 2840).** No town-level figures; the table says so.
4. A typed keyword in the single wizard costs one metrics lookup (server-side DataForSEO call unless cached), capped at 3 per visit.
5. The `improveseo_db_version` fatal (see Phase 2) is a pre-existing bug worth fixing separately: `improveseo_install()` should not flush rewrite rules when called from `plugins_loaded`.
6. The pre-existing `smartWizard is not a function` browser error and the PHP warnings listed under QA are untouched.
7. Lists made by hand (Keyword Lists → New List / CSV import) have no metrics; the bulk wizard simply shows no line for them.
