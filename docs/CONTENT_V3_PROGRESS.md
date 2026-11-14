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
| 4 | Research (Perplexity Agent API) | **Done (mocked)**; live run blocked on `PERPLEXITY_API_KEY` | server |
| 5 | Approve → Research → Planner, step 2, regulated topics | **Done** | server + plugin |
| 6 | Niche profile + proof level | **Done** (G4 texts blocked) | server + plugin |
| 7 | Blueprint + admin tester panel | **Done** | server + admin |
| 8 | Writer draft 2 + post-processing + validator + paid v3 route | **Done** (V10 blocked) | server + plugin |
| 9 | Interim approaches U1–U6 | **Done** (no new code: U1–U4 in 8, U6 in 6, U5 unchanged by design) | — |
| 10 | Bulk parity + bulk and bug fixes | **Done** | server + plugin |
| 11 | Keyword data integration | **Done** (C6 wording approved and committed 14 Nov) | server |
| 12 | Remaining wizard items | **Done** | plugin |
| 13 | E2E, 10-keyword run, docs, production gate | **Done**; gate items 1, 2 and 8 need the team lead (see "Production gate") | server + plugin + admin |

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

## Requirements (final status, 14 Nov)

Statuses: DONE / PARTIAL / WRONG / MISSING / BLOCKED. The starting state and its evidence are in the Phase 0 audit (`~/.claude/plans/pasted-content-id-1113-you-are-typed-scott.md`, section 2). Paths: P = plugin, S = server, A = admin panel. "Working value" = the doc leaves the number or rule open; the value used is listed so the team lead can confirm or change it.

**Count:** 132 IDs. 126 DONE (several with working values; R1 and R9 with the live run and the model choice still open), 3 PARTIAL (BULK4, OPS3, OPS6), 3 BLOCKED (W1, G4, V10).

### W — wizard
| ID | Status | Evidence / note |
|---|---|---|
| W1 | BLOCKED | Keyword check rules are in the missing "Single wizard: input fields" doc. Only the working title checks of W3 exist. |
| W2 | DONE | Generate title → AJAX `improveseo_v3_generate_title` (P `modules/content-v3.php`) → S `/content-v3/wizard/title`: search data → Title. A typed title gets the lookup on Approve. |
| W3 | DONE (working title-check rules) | Approve → S `/wizard/approve`: title check → Research → Planner. Title checks until the wizard doc exists: blocking = empty, markup, over 150 characters; notes = over 70 characters, no keyword word. |
| W4 | DONE | Generate AI Post → S `POST /content-v3/article`: blueprint + niche profile → Writer → validator (one repair run, U2) → post-processing → meta (U4). v2 fallback when the server says v3 is off. |
| W5 | DONE | Target town select: saved towns, "A new town…", "Not about a specific location" (one country lookup). |
| W6 | DONE | S `/wizard/town-check` (free) → one line under the town. |
| W7 | DONE | Step 2 panel: article type, size with size_reason, owner questions with answer fields, regulated line with Keep / Turn off. |
| W8 | DONE | Sizes below `minimum_size` disabled; a larger choice sets `larger_than_suggested` (blueprint step 3m). |
| W9 | DONE | Business details "How the business refers to itself" (We / our, I / my, Business name only); step 2 prefilled and changeable; sent with every v3 article (P `modules/content-v3.php:919`, bulk `:1242`; S `content-v3-article.handler.ts:45`). Old values ("Speaking to the reader", "Auto", the v2 person options) map to the Business details value when read (`improveseo_v3_map_point_of_view()`, P `modules/content-v3.php:812`), so no stored value is rewritten. |
| W10 | DONE | v3 leave modal wording (Stay / Leave); Leave deletes the server session and the image files generated for this post; tab close or reload does the same by beacon. Browser-verified. |
| W11 | DONE | The chosen size is priced exactly like v2 (reserve, refund only when generation fails; no refund for a shorter article); the validator checks the word range. |
| W12 | DONE | Regenerate title sends the session's earlier titles; search data reused (`session hit`, no second DataForSEO charge). |
| W13 | DONE | Regenerate Content keeps the questions, draws new picks (live: fingerprint 385ac963 vs fbc4f3a4); pins stay. |
| W14 | DONE | Regenerate and Change Image reuse the session; Change Image forwards the regenerate instruction. |
| W15 | DONE | Only the title is returned; form / items / main_concern / page_kind logged and stored in the session. |

### S — search data lookup (S `src/services/search-data/`)
| ID | Status | Evidence / note |
|---|---|---|
| S1 | DONE | Organic live/advanced, depth 10, `people_also_ask_click_depth` 1, language from `<language>`. |
| S2 | DONE | Nationwide or "Not about a specific location": one country call. |
| S3 | DONE (working value) | Town resolved on DataForSEO's locations list; unlisted → `SEARCH_DATA_TOWN_ALIASES_JSON` (nearest listed city), else coordinates, else one country call (logged). Zaid to confirm. |
| S4 | DONE | `SEARCH_DATA_THIN_THRESHOLD` (default 3). |
| S5 | DONE | Country call with the town stripped from the keyword. |
| S6 | DONE | Local first, dedupe, caps 10 / 8 / 8, scoped items; block records location, national_fallback, counts. |
| S7 | DONE | Only LOCAL ranking domains reach the research filter (`meta.local_domains`). |
| S8 | DONE | No state-level call. |
| S9 | DONE | Stored in the session, reused on Regenerate title; one `[search-data]` line with both calls' counts. |
| S10 | DONE (working value) | Town in keyword detected on the locations list; one-word towns only after in / near / around or before a state. |
| S11 | DONE | Shared DataForSEO client (Labs + exactly the two SERP paths), cost per call logged. |
| S12 | DONE | `searchDataBlock`; real output matches the doc's shape. |

### T — Title
| ID | Status | Evidence / note |
|---|---|---|
| T1 | DONE | Byte-equal to the doc (`doc-equality.test.ts`). Production: run the replace step (gate item 2). |
| T2 | DONE | Writing model. |
| T3 | DONE (working value) | `<max_list_items>` 15 (doc C size table missing) and `<previous_titles>` from the session. |
| T4 | DONE | No article type sent. |
| T5 | DONE | `<proof_level>` from Business details (Standard unless the user chose Regulated). |
| T6 | DONE | 3 attempts (over 70 characters, bad JSON or an error = failed attempt); then an empty title box and a plain message. |
| T7 | DONE | Output parsed and validated. |
| T8 | DONE (finding) | Today's title prompt is hard-coded (S `generation.routes.ts`, aux type `title`); v3 Title is template `content_v3_title`. |
| T9 | DONE | Keyword-data variant = V1 byte for byte + one appended KEYWORD DATA block (S `prompts.ts`; C6 wording approved and committed 14 Nov, 44afb1f). |

### R — Research (S `src/services/research/`)
| ID | Status | Evidence / note |
|---|---|---|
| R1 | DONE (mocked) | Agent API `POST /v1/agent` (instructions / input / web_search / json_schema / effort), checked against Perplexity's docs 14 Nov. **Live run blocked on `PERPLEXITY_API_KEY`.** |
| R2 | DONE | Instructions byte-equal to the doc; input = the doc's data block; schema = the doc's shape. |
| R3 | DONE | `user_location { city, region, country }` for a Target town. |
| R4 | DONE | Denylist of LOCAL ranking domains, max 20, configurable skip list. |
| R5 | DONE | Source numbers mapped to `search_results`; unmatched items dropped and counted. |
| R6 | DONE | Caps 15 / 10 / 6. |
| R7 | DONE | 3 attempts; then the run continues without research (also when there is no key). |
| R8 | DONE | Stored in the session for Regenerate / Change Image. |
| R9 | DONE (config) / BLOCKED (decision) | `PERPLEXITY_EFFORT` (default low), `PERPLEXITY_MODEL`; cost logged. Zaid to choose. |
| R10 | DONE | S1… tokens, scope and conflict marks, `<local_conditions>` inside `<research>`. |

### P — Planner
| ID | Status | Evidence / note |
|---|---|---|
| P1 | DONE | Byte-equal; low-cost model (`claude-sonnet-5`); runs on Approve; the v3 Writer chain no longer runs `content_v3_intelligence`. |
| P2 | DONE | `<size_options>` and `<minimum_size>` (working rule until doc C). |
| P3 | DONE | JSON, size ≥ minimum, 1–5 FAQ when real questions exist (C2; since the 10-keyword run: People Also Ask or research questions), no FAQ repeating a body question, ≤ 2 owner questions; 3 attempts. |
| P4 | DONE | Fallback plan after 3 failures; the article can still be generated. |
| P5 | DONE | `/wizard/approve` returns only what step 2 shows; the rest stays for the Writer. |
| P6 | DONE | demand_notes / overlap validated only in the keyword-data variant; the variant = V1 + the KEYWORD DATA block + the approved C6 sentence (44afb1f). |

### G — proof level and regulated topics
| ID | Status | Evidence / note |
|---|---|---|
| G1 | DONE | Business details proof level; the niche default only suggested, never set silently (no setup scan: U6). |
| G2 | DONE | Regulated topics list = the doc's table with exclusions (test), editable; list OR Planner; single wizard Keep / Turn off; bulk applies automatically. |
| G3 | DONE | Business Regulated: every post starts Regulated, bulk drafts, one-time notice. Post Regulated: Regulated piece and the 3-source check (C5). The disclaimer part is G4. |
| G4 | BLOCKED | Texts not written. Setting `content_v3_disclaimers` exists and ships empty; nothing is inserted while it is empty. |
| G5 | DONE | Witty hidden for a regulated niche or business. |
| G6 | DONE | Final proof level stored per post (server history, `_iseo_proof_level` post meta). |

### B — blueprint (S `src/services/blueprint/`)
| ID | Status | Evidence / note |
|---|---|---|
| B1 | DONE | Every piece verbatim (test re-parses the doc); one editable record `content_v3_blueprint_directives`. |
| B2 | DONE | All inputs the doc lists. |
| B3 | DONE | Steps 1–7: pins, draws a–m, history checks, fixed assembly order, `<blueprint>` wrap, picks saved. |
| B4 | DONE | No repeat of the last 10; opening / closing not in the previous 2 of the type; 40% spread over the last 20 (from 5 earlier posts, working value); 5 redraws then accept + log; bulk batches remember via the history. |
| B5 | DONE (working values) | Middle aim minus fixed parts, pattern shares, nearest 10, one fewer under 120. Working values: front/back-heavy 1.4↔0.7; "one fewer" stops at the range minimum; 3m aims a quarter into the range; **the section count never exceeds the Planner's body questions** (never below the range minimum), added after the 10-keyword run. |
| B6 | DONE (working value) | Numbered: cap 15, minimum words per kind, no optional elements, no extra list, summary table only on Medium / Large; grouping from 8 items (working value). |
| B7 | DONE | Nationwide never "local only"; local weight 2. |
| B8 | DONE | No-figure Cost guide piece when no currency amount is available. |
| B9 | DONE | `<recent_headings>` from `content_v3_posts` (same type, last 20). |
| B10 | DONE | TOC on Auto: none on Small, on for Medium / Large. |
| B11 | DONE | Firmness from readiness, capped by the niche's firmest; soft when missing. |
| B12 | DONE | Fingerprint stored per post (`content_v3_posts`). |

### N — niche profile
| ID | Status | Evidence / note |
|---|---|---|
| N1 | DONE | Profiles, mapping, watchlists, default proof levels: one editable setting; values = the doc tables (test). Per-niche provider / help phrases are working values. |
| N2 | DONE | Standard and Regulated pieces verbatim, editable. |
| N3 | DONE | Type adjustment lines only on a matching type + niche row. |
| N4 | DONE | `<niche_profile>` as in the doc's example, nationwide next step. |
| N5 | DONE | Firmest closing caps step 3k. |

### WR — Writer draft 2
| ID | Status | Evidence / note |
|---|---|---|
| WR1 | DONE | Core + six rules blocks verbatim, each in its tag, in the core's order (C1). |
| WR2 | DONE | User message in the doc's tags and order; empty tags left out; `<searcher_profile>` with six children. |
| WR3 | DONE (mechanism) | Replace mode backs up every old value as `pre-v3`. Production still holds the pre-v3 texts in the three shared templates; the server uses the doc text from code until the replace step runs (gate item 2). |
| WR4 | DONE | `===WRITER_JSON===`, strict parser; a bad format is a failed attempt. |
| WR5 | DONE | `data-iseo` quick-answer / takeaways / body / faq / closing; TOC, disclaimer slot, FAQ extraction and schema use them. |
| WR6 | DONE | No meta in the Writer JSON; FAQ read from the HTML. |
| WR7 | DONE | First use of up to 4 sources linked; other and unknown tokens removed (unknown logged); spacing and punctuation cleaned. |
| WR8 | DONE | TOC by code (`<nav class="iseo-toc">`, C3); disclaimer slot empty until G4. |
| WR9 | DONE | System prompt byte-identical per article; 10-keyword run: `cache_read=8399` on every Writer call. |
| WR10 | DONE | HTML allowlist sanitizer. |

### V — validator
| ID | Status | Evidence / note |
|---|---|---|
| V1–V9, V11 | DONE | S `src/services/writer/validator.ts`; report stored on `user_generations.metadata` and `content_v3_posts.validator`. V1 accepts the closest natural wording (contractions, -ing / -ed), added in the 10-keyword run. |
| V10 | BLOCKED | Humanizer v1.2 doc missing. |

### U — interim approaches (approved with the plan, 14 Nov)
| ID | Status | Evidence / note |
|---|---|---|
| U1 | DONE | The old humanizer does not run on v3. |
| U2 | DONE | One Writer re-run with the same inputs. |
| U3 | DONE | Single: "Please check this article", Use anyway / Regenerate. Bulk: draft with the report. |
| U4 | DONE | The existing v3 meta logic on the final article, with length enforcement. |
| U5 | DONE | "AI Image From Title" and "Custom Prompt" unchanged. |
| U6 | DONE | No setup scan; manual Business details fields (niche search-as-you-type, closest profile for "Other …", proof level, point of view, towns, reach). |

### X — admin tester panel (A `src/pages/ContentV3Tester.tsx`)
| ID | Status | Evidence / note |
|---|---|---|
| X1–X7 | DONE | Renames, removed pins, new dimensions; pins sent as `blueprint_pins` and used by `/article/test`; preview without a model call. Admin-only. |

### BULK
| ID | Status | Evidence / note |
|---|---|---|
| BULK1 | DONE | Same chain per task, no wizard questions, one task per cron tick. |
| BULK2 | DONE | Regulated suggestions apply automatically; a Regulated business's posts and posts that still fail checks are drafts with the reason. |
| BULK3 | DONE | Batch memory through the post history. |
| BULK4 | PARTIAL | Bulk v3 Title and Planner requests carry the Option B opt-in (P `modules/content-v3.php:1195`, `:1226`), the same server path as the wizard, which was verified live. Not yet run live in bulk with keyword data on. |
| BULK5 | DONE | Title → article → image; an image failure keeps the paid article. |
| BULK6 | DONE | `attempts` / `last_error` columns, 3 attempts then Failed. |
| BULK7 | DONE | Any "Error:" output is an error; a 402 can never be published. |

### BUG
| ID | Status | Evidence / note |
|---|---|---|
| BUG1 | DONE | `target_key` and the regenerate instruction sent (v2 and v3). |
| BUG2 | DONE | FAQ found by `data-iseo="faq"`. |
| BUG3 | DONE | The writing model is recorded (v2 and v3). |
| BUG4 | DONE | Nonce + `manage_options`. |

### K — keyword data
| ID | Status | Evidence / note |
|---|---|---|
| K1 | DONE | Both endpoints match the contract. |
| K2 | DONE | Tool and hand-off. |
| K3 | DONE | Flag off = byte-identical payloads (step tests); the wizard sends the opt-in (live `keyword_data=used`). |
| K4 | DONE | `writer-isolation.test.ts`. |
| K5 | DONE | = T9 / P6 (C6 committed, 44afb1f). |

### OPS
| ID | Status | Evidence / note |
|---|---|---|
| OPS1 | DONE | `CONTENT_V3_ENABLED` AND "Use Content v3"; v2 otherwise. |
| OPS2 | DONE | Every v3 prompt and text piece versioned with labels; `pre-v3` backups; replace / check routes; atomic activation; the admin panel keeps the version name. Runs record the prompt version. |
| OPS3 | PARTIAL | `[model-call]` per call and `[content-v3] cost` per article (search data, title, research, planner, writer, meta); compared with DEPLOY_RUNBOOK's medium figure (see Phase 13). **The image is not in the per-article line:** it goes through the unchanged v2 image route (U5, OpenAI `gpt-image-2.5-flare`), whose price is not in the price table. |
| OPS4 | DONE (v3) | Timeouts on every v3 model and provider call, ≤ 3 attempts, credits reserved once and refunded on failure, a second request for a running session refused. |
| OPS5 | DONE | Session store (migration 023), Leave deletes, 7-day purge. `/api/v1/generations` (admin tester history) required no auth; it now requires an admin token. |
| OPS6 | PARTIAL | Plain messages on every v3 route (`user-errors.ts`). The v2 routes still return raw provider errors (unchanged by rule 3). |
| OPS7 | DONE | CHANGELOG, `.env.example`, runbook (S `docs/CONTENT_V3.md`), this table. |

## Decisions (approved with the plan, 14 Nov)

- Branch `feature/content-v3`, stacked on `feature/keyword-data-v3` (the brief names it; keeps keyword data shippable on its own).
- Conflicts: C1 Writer rules order of the core (D:374); C2 Planner FAQ 1–5 only when search data or research exists; C3 v3 TOC as `<nav class="iseo-toc">` after the summary; C4 v3 skips em-dash stripping and `<hr>`; C5 link up to 4 sources, regulated with fewer than 3 fails validation; C6 one appended KEYWORD DATA block (the only new wording; approved and committed 14 Nov).
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
- Known, pre-existing, outside v3 code (to fix before the gate, phase 13): `wp_enqueue_script` notice for the `custom-plugin-script` handle, undefined `$task` / `$word_ai_email` in `views/posting/create-post-single.php` and `form.php`, `foreach` on false in `modules/GenerateAIpopup.php:398-504`. **Fixed in phase 13** (with the same warnings in `create-post-bulk.php`).

## Phase 5 notes

- `<minimum_size>`: smallest size whose body budget (doc targets 900/1,800/3,000 minus fixed 380/450/540) holds the title's items at 60 words each; Small otherwise. Working rule until doc C.
- Fallback plan's FAQ candidates are the search data's People Also Ask questions (real questions, local first); body questions empty.
- The regulated line does not mention a disclaimer while the disclaimer texts (G4) do not exist.
- E2E 14 Nov: approve 33 s (Planner 27 s on claude-sonnet-5 with adaptive thinking, ~2.8k output tokens, $0.035); research skipped (no key) and the step 2 note says so.

## Phase 6 notes

- **For the team lead:** per-niche "provider" and "professional help" phrases are working values (e.g. HVAC → "call a licensed HVAC technician"); the doc gives them per profile, with Plumbing as its only niche example. Editable in the `content_v3_niche_profiles` setting.
- Proof level sent with every v3 request: Standard unless the user chose Regulated (unset = Standard, so the post-level check runs).
- E2E 14 Nov: picker finds "chiro" → Wellness/Chiropractic/Massage, typo "plumbr" → Plumbing; Dental shows "Suggested: Regulated" without changing the field; saving Regulated shows the one-time notice; wizard hides Witty and shows the three point-of-view options; approve logs `niche=dental proof=regulated regulated=business`.
- Pre-existing console error on the settings page: `jQuery(...).smartWizard is not a function` (custom-plugin-script.js:2706). **Fixed in phase 13.**

## Phase 7 notes

- Real preview 14 Nov (Medium chosen over a suggested Small) first gave 3 body sections, below Medium's 4–6: the bottom-of-range aim plus a 3:1 back-heavy split starved a section under 120 words and the "one fewer" rule kept going. Fixed (see working values above).
- The tester panel's pins had no effect on `/article/test` until phase 8 moved it to the new chain; they do now.
- The scratchpad was wiped mid-session; QA scripts now live in `content-v3-delivery/qa/` (outside the repos).

## Phase 8 notes

- Live tester run (Medium, standard, plumbing, Española): passed first try, 1,388 words, writer $0.139.
- Live wizard run (Small; the test site was still Regulated/dental from phase 6): the repair run fixed a summary-form error; the article then needed review because a Regulated post needs 3 cited sources and there is no research without the Perplexity key. **Every Regulated article will need review until research is live.** It used the owner's answer ($180–$450). Cost $0.26 total. 6 credits charged on the connected test account (local server uses the production DB).
- Writing-quality notes for the 10-keyword review (prompt text, not code): without research the Writer still wrote one unsourced local claim ("New Mexico water tends to be hard") and one announcement ("Below we walk through…"); it flagged the unsourced lifespan claim itself in `claims_to_check`.

## Phase 10 notes

- Bulk tests in the local WordPress (mocked server answers, 18 checks, all pass): v3 402 → Failed at once with "Not enough credits…", nothing stored as content, no image bought; v2 402 (≥ 100 characters) → Failed, never published; image failure → article kept, second run reuses it (article bought once); 3 failures → Pending, Pending, Failed; project finishes with Failed counted; draft post gets `_iseo_proof_level` and `_iseo_v3_generation_id`; FAQ normalised.
- Real v3 bulk task (no mocks): 97 s, Done, $0.21, draft post with meta. The repair run was triggered by "at most 1 in 3 body headings may be questions": with 2 body sections (Small) that allows none. Literal reading of the doc; flag for the 10-keyword review.
- **Found and fixed during QA:** landing on wizard step 4 starts generation automatically; a click on "Generate AI Post" while it ran sent a second request that WordPress queued behind the first, so the server charged a regeneration (also true for v2). The wizard now ignores a second start while one is running. And the v3 review notice regenerated on any dismissal; now only an explicit "Regenerate" opens the regenerate dialog. Both verified in the browser with mocked answers.
- **QA side effects on the connected test account (production DB):** 16 credits charged (6 + 4 accidental duplicate + 6 bulk) and 5 "bulk project completed" emails sent by the local server for the test projects. The QA router now answers bulk notifications itself.

## Phase 13 notes

**10-keyword runs** (`npm run v3:ten-keywords`, server; keywords in `docs/content-v3-test-keywords.json`, a working list). Full chain with the same code as `/article/test`; no credits, nothing published. Results in the server's `data/v3-runs/` (git-ignored).

| Pass | Code | Passed checks | Second Writer run | Failed checks | Cost |
|---|---|---|---|---|---|
| 1 (01:34 UTC) | first full version | 3 / 10 | 8 | second summary 6, too short 2, regulated sources 2, keyword in first 100 words 1 | $1.76 (no Title/Planner cost yet) |
| 2 (10:42 UTC) | summary line, 3m aim, keyword wording | 3 / 10 | 7 | too short 5, regulated sources 3 | $1.43 (no Title/Planner cost yet) |
| 3 (11:06 UTC) | final, keyword data off | 6 / 10 | 7 | too short 2 (Large 1,720; Medium 1,132), regulated sources 2 | $1.96 |
| 4 (11:27 UTC) | final, keyword data on | 5 / 10 | 5 | too short 3 · regulated sources 2 | $1.95 + keyword data lookups $0.41 |

The review sheet (passes 3 and 4 side by side, with findings and the doc's "What to check in testing" lists): https://claude.ai/artifact/9w7a5qAYrPMWTy3ffpioU2 (private until shared). Final report (brief section 9): https://claude.ai/artifact/XVy4QVYPzD4y7ZdkL6wNRg (private until shared). Local copies in `content-v3-delivery/review/` (outside the repos); local copy `data/v3-runs/2026-11-14T11-06-03-426Z/review.html` (server).

**Fixed because of the runs** (server, committed): one-summary sentence in the code-built SUMMARY line (both forms; **wording to confirm**); section count capped at the Planner's body questions; 3m aims a quarter into the range; keyword check accepts natural wording; the Planner's 1-FAQ minimum only with real questions (C2).

**For the team lead (prompt text and numbers, not code):**
- Length: the Writer writes about 95% of the body word targets on Small, about 70% on Medium, about 55% on Large. Large articles stay under 2,400 words; about one Medium first draft in three is under 1,200. Options: raise the aims, give the second run the word count (changes U2), or narrow the ranges.
- Second Writer runs: 7 of 10. They fixed a short draft, an off-length quick answer and a missed keyword, never a regulated post (no sources without research). Skipping the second run when "regulated sources" is the only failure saves its cost (changes U2).
- Cost per article (pass 3, all steps, no research, no image): Small $0.14 / $0.18 with the second run; Medium $0.13 / $0.21–0.25; Large $0.29 (second run). DEPLOY_RUNBOOK assumes $0.129 for a medium article and v3 charges v2's credits (runbook note added).
- "At most 1 in 3 body headings may be questions" allows none on a Small article with 2 sections (literal reading).
- Keyword data: was used by the Title and the Planner on 7 of 10 keywords. On the other 3, DataForSEO's keyword endpoints answered HTTP 500 (after one retry; the town keyword also had no data) and the steps ran their normal prompts, as designed. Where it was used, the Planner returned demand_notes as one sentence (6 of 10) and overlap null (this run has no earlier posts to overlap). Pass rates were similar with and without it (6 and 5 of 10); the difference is one Medium article that came out short. Without migration 022's cache each article paid its keyword lookups again: $0.41 for the 10 articles.

**Other phase 13 work:**
- Plugin: the pre-existing PHP warnings (single and bulk post screens, AI popup), the "called incorrectly" notice for `custom-plugin-script` (enqueued while plugin files loaded, and twice) and the JS errors (SmartWizard on settings and dashboard, jQuery UI Sortable on the front end) are gone. Local WordPress with WP_DEBUG on, five screens: 103 new debug-log lines before, 0 after; 4 console errors before, 0 after; the script still loads once on each screen it loaded on; the v3 wizard still loads its parts.
- Server: `/api/v1/generations` (the admin tester's saved generations) was open to anyone; it now requires an admin token (Phase 0 finding, OPS5). The 10-keyword script runs with keyword data on as well and builds one sheet for both runs.
- Admin panel: the explicit `any`s the v3 tester commits added are typed; the branch adds no lint problems.
- Lint state: the server's `npm run lint` matches no files (a JavaScript-only ESLint config in a TypeScript repo, the same on `main`), so the typecheck is the server's static check. The admin panel's lint is not clean repo-wide (on `main` too); the branch adds none.
- Local QA server (port 3005) stopped; the local WordPress is back to v3 off, no API key, no QA mu-plugins, WP_DEBUG as before.
- **QA side effects this session:** none on accounts (the 10-keyword runs charge no credits). API spend for the four passes about $7 (Anthropic + DataForSEO). Earlier phases: 16 credits on the connected test account and 5 Mailgun emails (phase 10 notes).

## Commits (branch `feature/content-v3`, none pushed)

| Repo | Commits (oldest first) |
|---|---|
| Plugin | f7cfa2a (1) · f4ae39a (2, doc) · 80c14e0 (3) · 2761ff7 (4, doc) · 9d7502e (5) · b3922d3 (6) · d213ec2 (7, doc) · f678b93 (8) · 618f3ca (10) · 179b9ae (12) · 354a50b (13) · this commit (13, docs) |
| Server | 25ba719 (1) · 025a7e2 (2) · b257c17 (3) · e1b6b68 (4) · 7ee69ca (5) · 0a28a6c (6) · 2bdf08f (7) · c54873d (8) · b954c4d (10) · fecf648, de653d3, 7cb6253, 4fa439f, c23fe4b, docs commit (13) · 44afb1f (11, C6) |
| Admin panel | 4f2b122 (1) · 047a6ae (7) · 3e88dd5 (13) |

## Production gate (brief section 8)

| # | Gate item | Status |
|---|---|---|
| 1 | Every requirement DONE, or BLOCKED with sign-off | **Needs you:** written sign-off for 3 BLOCKED (W1, G4, V10) and 3 PARTIAL (BULK4, OPS3, OPS6). |
| 2 | Every stored prompt matches the doc; equality tests pass | Tests pass. **Production still holds the pre-v3 texts** in three templates until `POST /content-v3/prompts/seed?mode=replace` runs (writes to the production prompt DB, backing up first: needs your go-ahead), then `GET /prompts/check` must say `all_equal`. |
| 3 | All tests pass; linters and type-checks clean; no PHP notices, no console errors | Server 937/937 and typecheck clean; plugin `php -l` clean and 0 notices / 0 console errors on the main screens with WP_DEBUG; admin build clean. Lint: see "Lint state" above (pre-existing). |
| 4 | Flag off: v2 unchanged; flag on: single and bulk work | With either switch off, v2 runs, changed only by the approved fixes that apply to v2 too (BUG1, BUG3, BULK5–7, the double-start guard, the PHP warnings). Evidence: server tests (every v3 route answers `v3_disabled` when off; the v2 route tests pass); local WordPress with "Use Content v3" off shows the v2 wizard and loads no v3 script (re-checked 14 Nov). The plugin has no automated test suite, so the plugin side is browser-checked, not unit-tested. Flag on: single (phase 8) and bulk (phase 10) end to end. |
| 5 | Timeouts, capped retries, clean fallback; no error text published; credits once | DONE (OPS4, BULK7, refund on failure, double start refused). |
| 6 | Per-step cost and tokens logged; cached tokens > 0 from the second article | DONE: `cache_read=8399` on every Writer call in the 10-keyword runs. |
| 7 | 10-keyword run completed, review sheet handed over | DONE: https://claude.ai/artifact/9w7a5qAYrPMWTy3ffpioU2. |
| 8 | Disclaimer texts and U1–U6 decided and in place | U1–U6 approved 14 Nov and in place. **Disclaimer texts not written (G4).** |
| 9 | Docs updated | DONE: CHANGELOG (server `CHANGELOG.md`, plugin `docs/CHANGELOG-content-v3.md`), `.env.example`, runbook (server `docs/CONTENT_V3.md`), this file. |
| 10 | Rollout and rollback plan | DONE: server `docs/CONTENT_V3.md` (staging → one site → all; rollback = unset `CONTENT_V3_ENABLED`, no data loss). |

## C6 (approved 14 Nov)

The keyword-data variants are V1 byte for byte followed by one block (server 44afb1f). Title: the existing KEYWORD DATA rules, unchanged. Planner: the existing "7. KEYWORD DATA AND RECENT POSTS" rules, unchanged, plus the one new sentence:

> When this section applies, add two fields to the JSON above: "demand_notes": "one sentence, or null", and "overlap": {"post_id": the post_id from recent_posts, "reason": "one sentence"} or null.

Evidence: the 14 Nov live check named the overlapping post (501); the keyword-data 10-keyword run returned one-sentence demand_notes and a null overlap with no earlier posts. The code-built "This is the only summary" sentence in the blueprint's SUMMARY line was kept (14 Nov).

## Next

Content v3 is complete on the branches; nothing is pushed. Before cutover, the items in "Production gate" that need the brief's author or the team lead:
- Sign-off for the BLOCKED and PARTIAL rows (W1, G4, V10; BULK4, OPS3, OPS6).
- Disclaimer texts (G4, gate item 8) and the missing docs (wizard input fields, Humanizer v1.2, doc C) — team lead.
- `PERPLEXITY_API_KEY` (and model / effort: Zaid), migrations 022 and 023, the production prompt replace step.
- The length tuning for Medium and Large, and whether the second Writer run skips a "regulated sources"-only failure (both change U2 or doc numbers).
Then the runbook's rollout: staging, one site, all sites. A fresh session resumes from this file.
