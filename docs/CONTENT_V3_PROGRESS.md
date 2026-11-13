# Content v3: progress

Kept up to date at the end of every phase (brief rule 11), so a fresh session can resume from here.

- **Branches:** `feature/content-v3` in the plugin (from `feature/keyword-data-v3` 3ff3729), the server (from `feature/keyword-data-v3` a8b6f4b) and the admin panel (from `origin/main` 1ecf4e0). Committed per phase, **never pushed**.
- **Design doc:** `docs/content-v3-prompt-drafts.md` (this repo). The server keeps an identical copy in `docs/` (same md5), which the prompt equality tests read.
- **Brief:** "Content v3: production implementation brief" (team lead, received 14 Nov 2026). Phase 0 audit and plan approved 14 Nov.

## Status by phase

| # | Phase | Status | Commits |
|---|---|---|---|
| 0 | Audit and plan | Done, approved 14 Nov | (plan only) |
| 1 | Foundations: flags, prompt registry, model-call wrapper, cost logging, storage, errors, env list, doc-equality harness | **Done** | see `git log` on each branch |
| 2 | Search data lookup (DataForSEO SERP) | **Done** | server |
| 3 | Title + wizard step 1 | **Done** | server + plugin |
| 4 | Research (Perplexity) | Next. Blocked on `PERPLEXITY_API_KEY` for the live run; built with mocks | |
| 5 | Planner + step 2 + regulated topics | | |
| 6 | Niche profile + proof level | | |
| 7 | Blueprint + admin tester panel | | |
| 8 | Writer draft 2 + post-processing + validator + paid v3 route | | |
| 9 | Interim approaches U1–U6 | | |
| 10 | Bulk parity + bulk and bug fixes | | |
| 11 | Keyword data integration | | |
| 12 | Remaining wizard items | | |
| 13 | E2E, 10-keyword run, docs, production gate | | |

## What phase 1 built

**Server** (`imporve-seo-admin-server`):
- `src/config/content-v3.config.ts`: `CONTENT_V3_ENABLED` (literal "true" only), attempts, per-step timeouts, session retention, all env-configurable.
- `src/services/model-call.ts`: the one wrapper for every v3 model call. Hard timeout per attempt (the HTTP request is aborted), up to 3 attempts, step-supplied parse and validate, one `[model-call]` log line per attempt (step, model, prompt version with text hash, input, output, cache read and cache write tokens, cost, latency, attempt, outcome; never content), and a per-article cost ledger (`[content-v3] cost …`). Title and Planner now run through it.
- `src/services/model-pricing.ts`: per-model price table (Anthropic list prices, 14 Nov), `MODEL_PRICES_JSON` override; unknown model logs `cost_usd=null`.
- Prompt registry: `PromptVersion.label`; `getPromptWithVersion`; activation made safe (activate first, then deactivate the others, and only within the same template); `backupPreV3` (once per template, inactive copy labelled "pre-v3"); `POST /content-v3/prompts/seed?mode=replace` (doc-sourced prompts only, backup first, nothing deleted); `POST /content-v3/prompts/backup-pre-v3`; `GET /content-v3/prompts/check` (active DB text vs doc text). The version route now accepts `label` and the top-level `notes` the admin panel sent (it used to drop the version name).
- Migration 023 (`content_v3_sessions`, `content_v3_posts`) and `src/services/content-v3/store.ts` (fails open, scoped by user + website + session key, 512 KB cap, lazy hourly purge).
- `src/services/content-v3/user-errors.ts`: plain messages for every v3 error (OPS6).
- Plugin-facing routes: `GET /content-v3/status`, `POST /content-v3/session/clear`.
- `src/services/content-v3/doc-blocks.ts` + `doc-equality.test.ts`: extract each code block from the doc by section heading and compare byte for byte. Title and Planner V1 pass.
- `.env.example` lists every variable the server reads.

**Plugin:** setting "Use Content v3" (`improveseo_content_v3`, default off, Settings → Content Features, auto-saved); `modules/content-v3.php` (server status check cached 5 min / 1 min after a failure, `improveseo_content_v3_active()`, the Leave AJAX `improveseo_content_v3_clear_session` with nonce + `edit_posts`).

**Admin panel:** the version name is sent as `label` and `metadata.notes`; labels show as chips ("pre-v3" highlighted).

**Tests:** server 788/788, typecheck clean, build clean; plugin `php -l` clean, smoke test in the local WordPress with `E_ALL` shows no notices; admin `npm run build` clean.

## Requirements

Statuses: DONE / PARTIAL / WRONG / MISSING / BLOCKED. Evidence for the starting state is in the Phase 0 audit (`~/.claude/plans/pasted-content-id-1113-you-are-typed-scott.md`, section 2). Only rows that changed since Phase 0 carry new evidence here.

### W — wizard
| ID | Status | Note |
|---|---|---|
| W1 | BLOCKED | Keyword check rules are in the missing "Single wizard: input fields" doc. |
| W2 | DONE (generated title) | Generate title → `improveseo_v3_generate_title` → server `/content-v3/wizard/title` (search data → Title). Typed title: lookup on Approve, phase 5. |
| W3 | MISSING | Phase 5. |
| W4 | MISSING | Phase 8. |
| W5 | DONE | Target town select for a local business: saved towns (Business details), "A new town…", "Not about a specific location"; required before Generate. Preselected only when the site has exactly one saved town. |
| W6 | DONE | `/wizard/town-check` (free) on keyword/town change and with the title; one line under the town. |
| W7 | MISSING | Phase 5. |
| W8 | MISSING | Phase 5. |
| W9 | WRONG | `point_of_view` never sent; options wrong. Phase 6/12. |
| W10 | PARTIAL | Server `POST /content-v3/session/clear` + plugin AJAX exist (phase 1); modal wiring phase 12. |
| W11 | PARTIAL | Phase 8. |
| W12 | DONE | Regenerate sends the session's earlier titles for this keyword; search data reused from the session (E2E: `session hit`, no second DataForSEO charge). |
| W13 | PARTIAL | Phase 12. |
| W14 | PARTIAL | Session store exists (phase 1); reuse phase 12. |
| W15 | DONE | Only `title` returned; form/items/main_concern/page_kind logged (`[content-v3] title …`) and stored in the session. |

### S — search data lookup (phase 2, server `src/services/search-data/`)
| ID | Status | Note |
|---|---|---|
| S1 | DONE | Organic live/advanced, depth 10, `people_also_ask_click_depth` 1, language from `<language>` (`languageCodeFor`). |
| S2 | DONE | Nationwide or "Not about a specific location": one country call. |
| S3 | DONE (working value) | Town resolved on DataForSEO's locations list; unlisted towns → `SEARCH_DATA_TOWN_ALIASES_JSON` (nearest listed city), else coordinates if given, else one country call, logged. Zaid to confirm the choice. |
| S4 | DONE | `SEARCH_DATA_THIN_THRESHOLD` (default 3). |
| S5 | DONE | Country call with the town (and its "in"/state) stripped from the keyword. |
| S6 | DONE | Local first, dedupe, caps 10/8/8, scoped items; block records location, national_fallback and counts. |
| S7 | DONE | `meta.local_domains` = LOCAL ranking domains only (for R4). |
| S8 | DONE | No state-level call. |
| S9 | DONE | Stored in the wizard session (`sessionSearchData`), reused on Regenerate title; one `[search-data]` log line with both calls' counts. |
| S10 | DONE (working value) | Town in keyword detected on the locations list; one-word towns only after in/near/around or before a state. |
| S11 | DONE | Shared DataForSEO client (`assertAllowedPath`: Labs + exactly the two SERP paths), cost per call in `[dataforseo]` lines. |
| S12 | DONE | Existing `searchDataBlock` fed by the lookup; real run output matches the doc's shape. |

Real run 14 Nov ("water heater repair Española", Española NM): town call thin → country call → merged t10/q8/r5, $0.0043, 5.3 s; field names confirmed.

### T — Title
| ID | Status | Note |
|---|---|---|
| T1 | DONE | Byte-equal to the doc by section heading (`doc-equality.test.ts`). Production DB must be seeded with `?mode=replace` and `GET /prompts/check` must say `all_equal`. |
| T2 | DONE | Writing model. |
| T3 | DONE (working value) | `<max_list_items>` 15 (doc C size table missing; 15 is the doc's cap) and `<previous_titles>` from the session. |
| T4 | DONE | |
| T5 | PARTIAL | Sent when Business details has a proof level (`improveseo_business_proof_level`, phase 6 adds the field). |
| T6 | DONE | Server: 3 attempts through model-call.ts; after 3 failures the route answers an empty title + plain message and the wizard leaves the box empty. |
| T7 | DONE | Parsed and validated (`normalizeTitleOutput`/`validateTitleOutput`). |
| T8 | DONE (finding) | Today's title prompt is hard-coded server-side (`generation.routes.ts`, aux type `title`); v3 Title is template `content_v3_title`. |
| T9 | WRONG | Phase 11 (V2 must be V1 verbatim + one appended block). |

### R — Research
| R1–R9 | MISSING | Phase 4. Needs `PERPLEXITY_API_KEY`, model and effort level. |
| R10 | PARTIAL | Phase 4. |

### P — Planner
| ID | Status | Note |
|---|---|---|
| P1 | PARTIAL | System prompt byte-equal to the doc; now through model-call.ts. Replacing `content_v3_intelligence` in the Writer chain: phase 8. |
| P2 | DONE | |
| P3 | PARTIAL | Phase 5. |
| P4 | WRONG | Phase 5. |
| P5 | PARTIAL | Phase 5. |
| P6 | DONE (fields) / WRONG (prompt) | Phase 11. |

### G, B, N, WR, V, U, X, BULK, BUG, K
Unchanged from Phase 0 except:
- **WR3** PARTIAL: the backup and replace mechanism exists (phase 1); the new Writer text lands in phase 8.
- **WR9** PARTIAL: the wrapper sends the system block with `cache_control`, and the log line records `cache_read` per call, so cached tokens on the second article will be visible in the logs.
- **K1, K2, K4** DONE; **K3** DONE (step level); **K5** WRONG (phase 11).
- **BUG4** DONE.

### OPS
| ID | Status | Note |
|---|---|---|
| OPS1 | PARTIAL | `CONTENT_V3_ENABLED` + plugin setting + `GET /status` exist; the v3 chain they switch on is built in phases 2–8. |
| OPS2 | PARTIAL | Labels, pre-v3 backup, replace-mode seeding, check route, safe activation, admin name fix. The text pieces (blueprint, niche, rules blocks) join the registry in their phases. |
| OPS3 | PARTIAL | Per-call log + cost, per-article ledger. Runbook comparison phase 13. |
| OPS4 | PARTIAL | v3 model calls time out and retry ≤ 3; credits once on success arrives with the paid route (phase 8). |
| OPS5 | PARTIAL | Session store + migration 023 + Leave route; wired per phase. |
| OPS6 | PARTIAL | `user-errors.ts` for every v3 route; v2 routes unchanged (rule 3). |
| OPS7 | PARTIAL | `.env.example` complete; CHANGELOG and runbook phase 13. |

## Decisions (approved with the plan, 14 Nov)

- Branch `feature/content-v3`, stacked on `feature/keyword-data-v3` (the brief names it; keeps keyword data shippable on its own).
- Conflicts: C1 Writer rules order of the core (D:374); C2 Planner FAQ 1–5 only when search data or research exists; C3 v3 TOC as `<nav class="iseo-toc">` after the summary; C4 v3 skips em-dash stripping and `<hr>`; C5 link up to 4 sources, regulated with fewer than 3 fails validation; C6 one appended KEYWORD DATA block (the only new wording, shown before commit).
- Interims: U1 skip the old humanizer on v3; U2/U3 one Writer re-run, then "Use anyway / Regenerate" (single) or draft + report (bulk); U4 existing v3 meta logic on the final article; U5 image prompts unchanged; U6 no setup scan, manual Business details fields.
- Billing: v3 charges what v2 charges for the chosen size, reserve and refund on failure; Title, Research and Planner free.
- Storage: `content_v3_sessions` per wizard session (7-day purge, deleted on Leave), `content_v3_posts` per article.

## Blockers and open items

| Item | Blocks | Owner |
|---|---|---|
| "Single wizard: input fields" doc | W1, storage detail, field list | Team lead |
| "Humanizer v1.2" doc | V10 | Team lead |
| "doc C" size table | exact `max_list_items` / `minimum_size` rules (interim: 15 cap, Small) | Team lead |
| Disclaimer texts per regulated niche | G4 | Team lead |
| `PERPLEXITY_API_KEY`, model, effort level | R live run | Team lead / Zaid |
| Apply migration 023 (and 022) in Supabase | sessions + history in production | Team lead |
| Run `POST /content-v3/prompts/seed?mode=replace` after deploy; `GET /prompts/check` must say `all_equal` | gate item 2 | Team lead |

## Phase 3 notes

- Business details gains "Who does the business serve?" (`improveseo_business_reach`, local/nationwide; unset = derived from business type) and "Towns you serve" (`improveseo_business_towns`, one per line).
- The wizard's "Select Title Type" stays as it is. Under v3, "Question-Style" generates through the same v3 Title prompt (the prompt chooses the form itself; no new prompt text is allowed to force it). "Exact Keyword as Title" generates nothing; the lookup runs on Approve (phase 5). **For the team lead:** keep or hide "Question-Style" under v3?
- Local E2E 14 Nov (real DataForSEO + Claude, local server, v3 on): title 11 s; Regenerate reused the session search data and read 1,533 cached prompt tokens; "Not about a specific location" names no town; no console errors. With "Use Content v3" off the wizard loads no v3 script and no town field.
- Known, pre-existing, outside v3 code (to fix before the gate, phase 13): `wp_enqueue_script` notice for the `custom-plugin-script` handle, undefined `$task` / `$word_ai_email` in `views/posting/create-post-single.php` and `form.php`, `foreach` on false in `modules/GenerateAIpopup.php:398-504`.

## Next

Phase 4: Research (Perplexity Agent API), R1–R10. Built and tested with a mocked provider; the live run waits for `PERPLEXITY_API_KEY`, model and effort level.
