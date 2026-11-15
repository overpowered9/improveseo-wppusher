# Content v3: how it was asked, and how it is built

This document describes Content v3 end to end: what was asked for, and how and where each part is built. It covers both repos and the admin panel. It is the reference for reviewing the work, running it, and adding the pieces that wait on the team lead.

- Plugin (WordPress, PHP): `improveseo-wppusher`, branch `feature/content-v3` (paths below start with **P**).
- Server (Express, TypeScript): `imporve-seo-admin-server`, branch `feature/content-v3` (paths start with **S**).
- Admin panel (React): `improvese0-admin-frontend`, branch `feature/content-v3` (paths start with **A**).

Status: built and tested on 14–15 November 2026 (section 14). Nothing is pushed or deployed.

**Verdict.**
- **Deploy the code: GO.** With `CONTENT_V3_ENABLED` unset, users see v2 as today, plus security and reliability fixes that also apply to v2 (section 11).
- **Switch v3 on: GO once the team lead has done section 12, steps 1–4** (migrations, prompt step, Perplexity key). Without the migrations v3 stays off automatically.
- **Open items with owners:**
  - 3 requirements wait on the team lead's documents (W1, G4, V10);
  - the code-built wording needs his sign-off;
  - the Perplexity model choice is Zaid's;
  - credit pricing is yours.

Section 13 maps every requirement to the code: 129 done, 3 blocked.

---

## 1. In one page

**What Content v3 is.** A new way of writing a post. Instead of one long prompt, the article is produced by a chain of focused steps:

1. **Search data** (code, DataForSEO): what ranks for the keyword in the post's town, and what people ask.
2. **Title** (model): one title in the form the search results call for.
3. **Research** (Perplexity): sourced facts, follow-up questions and local conditions.
4. **Planner** (model): the article type, the size, the body and FAQ questions, who the searcher is, and questions for the owner.
5. **Blueprint** (code): a varied structure for this article (opening, summary form, sections and their word targets, format accent, closing), checked against the site's earlier posts so posts don't all look alike.
6. **Niche profile** (code): what the business's niche allows (who the professional is, next steps, pricing, how strict claims must be).
7. **Writer** (model): the article, following the blueprint, the niche profile and six shared rule blocks.
8. **Checks and finishing** (code): source links, a table of contents, the disclaimer slot, an HTML allowlist, then a validator. A failed check gets one more Writer run.
9. **Meta** title and description (model, interim).

**Two switches.** v3 runs only when both are on:
- server env `CONTENT_V3_ENABLED=true`, and
- the site's plugin setting **Use Content v3** (Settings → Content Features).

If either is off, the plugin uses v2 exactly as before. **Rollback** means unsetting `CONTENT_V3_ENABLED`; sites fall back to v2 within 5 minutes and no data is lost.

**Billing.** A v3 article costs exactly what v2 charges for the chosen size, through the same reserve-then-refund path. It is charged once per click: retries replay the finished article instead of buying it again.

---

## 2. Where the requirements come from

| Source | What it defines |
|---|---|
| `docs/content-v3-prompt-drafts.md` (both repos, identical) | The design: every prompt's exact text, the data tags, the run order, what code does around each prompt, and a "What to check in testing" list per section. |
| Brief 1, "Content v3: production implementation brief" | Requirements W, S, T, R, P, G, B, N, WR, V, U, X, BULK, BUG, K and OPS (132 IDs), the engineering rules, testing and the production gate. |
| Brief 2, "Content v3: completion and production-readiness pass" | An independent re-check of every ID, the gaps built, production hardening, an independent review, full testing and a go/no-go report. |
| Decisions recorded in the design doc and during the work | 30 Oct (numbered titles, section counts), 2–3 Nov (proof level, tags), 11 Nov (keyword prefetch is plugin-facing), 14 Nov (billing as v2; the interim approaches U1–U6; the C6 sentence; A1 calibrate word targets; A2 skip the second Writer run when only "regulated sources" fails and research gave fewer than 3 sources; A3 Regulated businesses stay on v2 until their niche has a disclaimer text). |
| The team lead's documents (`docs/lead/`) | Not available. Section 12 lists what depends on them. |

The engineering rules from Brief 1, which the work follows:
- Prompt text is taken verbatim from the design doc; old prompt values are backed up as a version first.
- v2 stays the default and unchanged; v3 runs only behind both switches.
- Nothing is deleted; secrets live only in server env; every new env var is in `.env.example`.
- Every model call goes through one wrapper (timeout, up to 3 attempts, strict JSON, one structured log line).
- The Writer, Title and Planner system prompts are byte-identical across articles, so they are cached.
- WordPress security on every touched handler (nonce, capability, sanitize, escape, prepared SQL).
- One commit per phase, never pushed.

---

## 3. The parts and where they live

### Plugin
| Part | Where |
|---|---|
| The v3 module: switches, server calls, wizard and bulk handlers, Business details, cleanup | P `modules/content-v3.php` |
| Single wizard UI (Steps 1–4) | P `views/GenerateAIpopup/GenerateAIpopuphtml.php`, `assets/js/custom-plugin-script.js`, `assets/js/content-v3-wizard.js` |
| Single post creation (v2 and the v3 hand-off) | P `modules/single_AI_post_function.php` |
| Bulk queue (cron, leases, retries) | P `modules/bulk_AI_post_function.php` |
| Settings: Business details, "Use Content v3", keyword data (beta) | P `views/settings/index.php`, `includes/settings.php`, `assets/js/business-niche-picker.js` |
| Keyword data (Option A tool, Option B prefetch) | P `modules/keyword-data.php`, `assets/js/keyword-wizard.js` |
| Who may spend credits | P `includes/functions.php` (`improveseo_can_generate()`: `manage_options`, filterable) |
| Uninstall cleanup | P `uninstall.php` |

### Server
| Part | Where |
|---|---|
| Routes (status, wizard, article, admin tools) | S `src/routes/content-v3*.ts` |
| Search data (DataForSEO SERP) | S `src/services/search-data/` |
| Title and Planner steps | S `src/services/content-v3-steps/` |
| Research (Perplexity Agent API) | S `src/services/research/` |
| Blueprint | S `src/services/blueprint/` |
| Niche profiles, regulated topics, disclaimers | S `src/services/niche-profile/`, `src/services/content-v3/regulated-topics.ts` |
| Writer, post-processing, validator, meta | S `src/services/writer/` |
| Sessions, post history, run ledger, daily allowance | S `src/services/content-v3/` (`store.ts`, `runs.ts`, `quota.ts`) |
| Model calls, retries, costs | S `src/services/model-call.ts`, `src/services/content-v3-steps/attempts.ts` |
| Prompt registry and seeding | S `src/services/prompt-management.service.ts`, `src/services/prompt-seed.v3.ts` |
| Config | S `src/config/content-v3.config.ts`, `search-data.config.ts`, `research.config.ts`, `keyword-data.config.ts` |
| Migrations | S `src/migrations/022-…`, `023-…`, `024-…` |

### Admin panel
- The v3 tester (every blueprint pin, the full chain without credits, the prompts sent): A `src/pages/ContentV3Tester.tsx`.
- The v3 prompts page (versions, the doc check): A `src/pages/PromptsV3.tsx`.

### Outside services
| Service | Used for | Key |
|---|---|---|
| Anthropic Claude | Title and Writer (writing model, `ANTHROPIC_WRITE_MODEL`); Planner and Meta (lower-cost model, `ANTHROPIC_MODEL`) | `ANTHROPIC_API_KEY` |
| DataForSEO | Google SERP (search data) and Labs (keyword data) | `DATAFORSEO_LOGIN`, `DATAFORSEO_PASSWORD` |
| Perplexity Agent API | Research | `PERPLEXITY_API_KEY` (added by the team lead; without it research is skipped) |
| The existing image route | Featured image (unchanged, interim U5) | as v2 |

### Where data is kept
| Store | What | Lifetime |
|---|---|---|
| Postgres `content_v3_sessions` (migration 023) | One wizard session: search data, titles shown, title step, research, plan, step costs | 7 days, deleted on Submit or Leave |
| Postgres `content_v3_posts` (023) | One row per post: picks, fingerprint, headings, validator report (feeds the blueprint's history checks) | 365 days |
| Postgres `content_v3_runs` (024) | One row per article request: status, credits, step costs, timings, the stored answer for replay | 90 days |
| Postgres `content_v3_quota` (024) | Daily counters per account for the free steps and delivered articles | 30 days |
| Postgres `keyword_metrics_cache`, `keyword_related_cache` (022) | DataForSEO Labs results | 30 days |
| Postgres `user_generations` (existing) | One row per paid article, type `content_v3` | as v2 |
| MongoDB prompt registry (existing) | Every v3 prompt and text piece, with versions and a `pre-v3` backup | permanent |
| WordPress | Settings, the cached server status, wizard image file list, bulk task columns, post meta | until uninstall |

---

## 4. The single wizard, step by step

### 4.1 Before the wizard: Settings

| Asked | Built |
|---|---|
| A business-level proof level, never set to Regulated silently (G1) | Settings → Business Details: niche (search-as-you-type over the server's niche list, synonyms, small typos), profile for "Other …", proof level (the niche's default is only a suggestion), point of view, towns served, reach (local or nationwide), name, description, ideal client, what makes it stand out. P `views/settings/index.php`, `modules/content-v3.php`. Choosing Regulated shows a one-time notice (G3). |
| v3 behind a site switch (OPS1) | "Use Content v3" (default off) and "Use keyword data in Content v3 (beta)" (default off). The niche list loads only while v3 is on (both switches). |
| No setup scan yet (U6, interim) | Manual Business details fields; the setup scan waits for prompt #14. |

The plugin asks the server `GET /content-v3/status` whether v3 is on (cached 5 minutes). If the server can't be reached, the last answer it gave is kept for up to a day, so one slow check can't switch a running wizard or batch to v2 (B-8). A **Regulated business** whose niche has no disclaimer text yet stays on v2 (decision A3).

### 4.2 Step 1: keyword, town and title

| Asked | Built |
|---|---|
| W1 keyword check (rules from the lead's wizard document) | **Waits on the document** (section 12). The title checks of W3 run. |
| W5 a local business picks a Target town: a saved town, a new town, or "Not about a specific location" | Target town select under the keyword; the server refuses approve without a choice (400 with a plain message). |
| W6 a different town in the keyword shows one line | `POST /content-v3/wizard/town-check` (free) → "Your keyword mentions X, but this post is set to Y. Please check the town." |
| W2 Generate title: search data, then the Title prompt | `POST /content-v3/wizard/title`: search data (cached in the session) → Title step. |
| W12 Regenerate title sends the titles already shown and reuses the search data | `<previous_titles>` from the session; the search data comes from the session (`[search-data] session hit`), so DataForSEO is not paid twice. |
| W15 only the title is shown | The title is returned; form, item count, main concern and page kind are logged and kept in the session. |
| T6 over 70 characters, bad JSON or an error is a failed attempt; after 3 the box stays empty | As asked; the user gets "We couldn't write a title this time. Type your own title, or try again." |

**Search data (S1–S12)**, S `src/services/search-data/search-data.service.ts`:
- Google organic, live, advanced, depth 10, People Also Ask click depth 1, language from the wizard.
- Local post: the first call is for the Target town, resolved on DataForSEO's location list (working value for towns it doesn't list: an alias list, then coordinates, then the country).
- Nationwide or "Not about a specific location": one country call.
- The result is thin when it has fewer than 3 People Also Ask questions or related searches (`SEARCH_DATA_THIN_THRESHOLD`). Only then is a second, country call made, with the town removed from the keyword.
- Merging: local first, duplicates removed, caps of 10 titles, 8 questions and 8 related searches; every item is marked local or national.
- Only local ranking domains go to Research's competitor filter. There is never a state-level call.
- The block is built as the design doc shows it (`searchDataBlock`, S `src/services/content-v3-steps/xml.ts`).

**Title (T1–T9)**, S `src/services/content-v3-steps/title.step.ts`:
- The system prompt is the doc text byte for byte (template `content_v3_title`) and runs on the writing model.
- Data tags as listed, empty ones left out. `<max_list_items>` is 15 (lowered in bulk to what the chosen size holds), plus `<previous_titles>`.
- The business proof level is sent; the article type is not.
- With keyword data on (Option B), the variant template `content_v3_title_keyword_data` is the same text plus one appended KEYWORD DATA block, with up to 10 related keywords in `<keyword_data>`.

**Time limit:** the title request answers within 200 seconds (the plugin waits 240).

### 4.3 Approve title

| Asked | Built |
|---|---|
| W3 the title check, then Research, then the Planner | `POST /content-v3/wizard/approve`: title check → search data (from the session, or looked up now for a typed title) → Research → Planner → regulated check. |
| P5 show only what step 2 needs | The answer carries the article type, size and reason, owner questions and the regulated line; the rest stays in the session for the Writer. |

**Title check** (working rules until the lead's wizard document exists): an empty title, markup or more than 150 characters blocks; more than 70 characters, or no word of the keyword, is a note.

**Research (R1–R10)**, S `src/services/research/research.service.ts`:
- Perplexity **Agent API** (`POST /v1/agent`): the instructions verbatim from the doc, the data block as `input`, `web_search` with up to 20 results, and the doc's output schema as `response_format`.
- For a local post, `user_location` is set to city, region and two-letter country (the country from `SEARCH_DATA_COUNTRY_ISO`).
- The competitor denylist (`-domain.com`) is built only from local ranking pages, up to 20, skipping government, education, encyclopedia and news domains (a configurable list).
- Each returned source number is mapped to its search result (URL, date, token S1…). Facts or questions whose source has no match are dropped.
- Caps: 15 facts, 10 questions, 6 local conditions.
- Up to 3 attempts. If all fail, or there is no key, the run continues without research: the Planner works from the search data and the Writer uses no figures. What failed attempts cost is still recorded.
- The result is kept in the session for Regenerate and Change Image. The model and effort are config (`PERPLEXITY_MODEL`, `PERPLEXITY_EFFORT`, default `low`).

**Planner (P1–P6)**, S `src/services/content-v3-steps/planner.step.ts`:
- The system prompt is the doc text byte for byte (template `content_v3_planner`) and runs on the lower-cost model.
- Data as listed, with `<size_options>` and `<minimum_size>`. A numbered title raises the minimum size so every item gets its words.
- Code checks the output: valid JSON; a size not below the minimum; 1 to 5 FAQ questions (when real questions exist); no FAQ question repeating a body question; at most 2 owner questions. Up to 3 attempts.
- After 3 failures, a fallback plan (P4): the article type from the keyword, the minimum size, no owner questions. The article can still be generated.
- Option B: `<keyword_data>` and `<recent_posts>` go in, and the `demand_notes` and `overlap` fields are accepted only in that variant.

**Regulated check (G2)** for a Standard business: either the editable regulated topics list (multi-word terms with "never match on their own" exclusions) or the Planner's `regulated_topic` is enough. The single wizard then shows the line with **Keep / Turn off**; bulk applies it automatically.

**Time limit:** the approve request answers within 360 seconds (the plugin waits 420). Research stops 150 seconds before that, so the Planner keeps its share; a Planner out of time falls back to the default plan.

### 4.4 Step 2: the plan

| Asked | Built |
|---|---|
| W7 show the article type, size with its reason, owner questions with answer fields, and the regulated line | As asked, in the Step 2 panel. |
| W8 a larger size than suggested is allowed, never below the minimum; a larger choice changes blueprint step 3m | Smaller sizes are greyed out in the wizard and refused by the server (400, before any charge). A larger size aims the word targets at the bottom of its range. |
| W9 point of view: "We / our", "I / my", "Business name only", prefilled from Business details, changeable per post | As asked, in both the single and the bulk wizard, with tooltips describing the three options. Old stored values ("Speaking to the reader", "Auto") map to the Business details value when read (C10), so v2 still gets its own values after a rollback. |
| G5 no Witty tone for regulated niches | Hidden in the wizard; a stored "witty" becomes "friendly" for a regulated niche (single and bulk). |

### 4.5 Generate AI Post

`POST /content-v3/article` (plugin auth, paid), S `src/routes/content-v3-article.handler.ts`:

1. **Checks before any charge:**
   - v3 is on;
   - the session still holds the plan;
   - the size is not below the minimum;
   - a Regulated post's niche has a disclaimer text;
   - the per-minute limit (30 per account).
2. **Claim** the request in the run ledger by its `request_id`:
   - a finished request is answered again from the ledger with no charge;
   - a second run of the same session is refused (409).
3. **Reserve** v2's price for the chosen size (a regeneration in the same slot is priced as one).
4. **Generate**, within 480 seconds (the plugin waits 600):
   - Blueprint and niche profile (code), then the Writer;
   - post-processing, then the validator;
   - one more Writer run if the validator found an error;
   - meta, table of contents, disclaimer, a final HTML allowlist pass.
5. **Record:**
   - a `user_generations` row (type `content_v3`: the model, every prompt version, what was billed, the validator report);
   - one post-history row;
   - the per-article cost line.
6. **On any failure** the reserved credits are refunded and the user sees a plain message. If the refund itself fails it is queued and retried every 10 minutes.

**Blueprint (B1–B12)**, S `src/services/blueprint/blueprint.ts`, no model call:
- **Pieces:** every text piece is verbatim from the doc, in one editable record (`content_v3_blueprint_directives`): 5 openings, 4 length patterns, 4 accents, 4 closing moves, 2 closing formats, 3 firmness levels, 4 article types plus "Cost guide, no figure available", and the numbered piece.
- **Steps 1–7 of the doc:**
  - tester pins;
  - draws a–m in order;
  - history checks;
  - assembly in the fixed order (ARTICLE TYPE, NUMBERED, OPENING, SUMMARY, BODY, FORMAT, FAQ, ORDER, CLOSING);
  - the `<blueprint>` wrap;
  - the picks saved per post.
- **History (B4):**
  - no exact repeat of the site's last 10 posts of the same type;
  - no opening or closing move from the previous 2;
  - no pick in more than 40% of the last 20, applied from 5 earlier posts and only to dimensions with 3 or more options (C8); only the over-used dimension is redrawn;
  - after 5 redraws, accept and log.
- **Word targets (B5):** aim at the middle of the range (900 / 1,800 / 3,000), subtract the fixed parts (380 / 450 / 540), split by the length pattern, round to 10, one section fewer while any section is under 120 words. The numbers given to the Writer are calibrated per size (decision A1: ×1.0 / ×1.3 / ×1.6, `CONTENT_V3_TARGET_CALIBRATION_JSON`), because the Writer delivers less than it is asked for on longer articles.
- **Numbered titles (B6):**
  - The Title step's form decides; a typed title needs a number before a known item kind ("7 signs …").
  - At most 15 items, with minimum words per item of 60, 40 or 35 depending on the item kind.
  - No optional elements and no extra list; a summary table only on Medium and Large.
  - Items as H2s below 8, as H3s under 2–3 grouping H2s from 8 (working value).
- **Optional elements:** nationwide posts never get "local only" elements; local posts favour them.
- **Price check (B8):** a currency amount in the research facts or the Business details pricing selects the "no figure available" Cost guide piece when there is none.
- **Table of contents:** on Auto, none on Small, included on Medium and Large.
- **Closing firmness:** from the reader's readiness, capped by the niche.
- **Fingerprint:** stored per post.

**Niche profile (N1–N5)**, S `src/services/niche-profile/`:
- **Settings:** the 8 profiles, the niche mapping, watchlists and default proof levels are one editable setting; adding a niche is one row.
- **Pieces:** the Standard piece (with the niche's watchlist) and the Regulated piece are verbatim and editable; the type adjustment lines are added only when the type and profile match a row.
- **The block:** assembled as in the doc's example (provider, help phrase, next step per firmness, the nationwide next step, pricing, local detail, proof level). The firmest closing caps the blueprint.

**Writer (WR1–WR10)**, S `src/services/writer/`:
- **System prompt:** the core (template `content_v3_system`) followed by the six rule blocks, each in its own tag, in the core's order: no invention, reader first, human style, SEO, local SEO, formatting. All verbatim and byte-identical for every article, so Anthropic caches them.
- **User message:** the doc's tags in the doc's order, empty tags left out, `<searcher_profile>` with its six children, `<research>` with tokens S1…, `<recent_headings>` from the site's last 20 posts of the type (h2s and body h3s, never FAQ questions).
- **Output:** the HTML, a line `===WRITER_JSON===`, then `{facts_used, claims_to_check, items_delivered}`. The parser is strict: text before the article, a missing key or `{}` is a failed attempt.
- **Sections:** carry `data-iseo` (quick-answer, takeaways, body, faq, closing). The table of contents, the disclaimer position, FAQ extraction and the FAQ schema all use them.

**Post-processing**, S `src/services/writer/postprocess.ts`:
- **HTML allowlist:** p, h2, h3, ul, ol, li, table, thead, tbody, tr, th, td, strong, em, a. Any link the model wrote is removed unless it is the call to action.
- **Source tokens:** the first use of up to 4 sources becomes a link on the source's domain. Lists like `[S1, S2]` are handled, other and unknown tokens are removed (unknown ones logged), and spacing is cleaned.
- **Table of contents** (`<nav class="iseo-toc">`) and the **disclaimer** go before the FAQ and the closing, or before the closing when there is no FAQ. No disclaimer is inserted while its text is empty.
- **A final allowlist pass** runs over everything, the TOC and disclaimer included; comments are removed.

**Validator (V1–V11)**, S `src/services/writer/validator.ts`. Errors trigger one more Writer run:
- the keyword (or its closest wording) is in the first 100 words;
- exactly one summary right after the introduction (a quick answer of 40–60 words, or 3–5 takeaways matching the blueprint);
- the FAQ count matches;
- at most 1 in 3 body headings end in "?" (a "questions" list may use questions);
- every h2 has a `data-iseo`;
- a numbered title gets its N items (N to N + 2 body sections as H2s), and the Writer reports N;
- the word count is in the size's range;
- no vague back-references ("as mentioned above", "in the next section", …);
- no fixed-label headings ("FAQ", "What's next", "Conclusion: …");
- a Regulated post links at least 3 sources.

Warnings (repeated heading starts, a heading from a recent post, em dashes, bold, unknown tokens, claims to check) are stored and never block. **V10** (the Humanizer v1.2 style thresholds) waits on that document.

**Repair (U2) and the failure flow (U3):**
- A failed draft gets one more Writer run. If it was too short, the targets are scaled up.
- The second run is skipped when "regulated sources" is the only error and research gave fewer than 3 sources (decision A2).
- If it still fails, the better draft is kept: single shows "Please check this article" with Use anyway / Regenerate; bulk saves a draft with the report.

**Meta (U4, interim):** the existing v3 meta wording runs on the final article with the existing length rules. Its version is a hash of the exact text (`content_v3_meta_interim@code#…`).

### 4.6 After the article: review, regenerate, image

- **Regenerate Content (W13):** new blueprint picks, the same questions; pinned dimensions stay. It reuses the stored search data, research and plan (W14), and is priced as a regeneration.
- **Change Image (W14, U5):** the existing "AI Image From Title" and "Custom Prompt", unchanged.

### 4.7 Submit

- **Saving the content:** passed through `wp_kses_post` and wrapped in the plugin's content wrapper, so the FAQ is normalised and gets FAQPage schema. The FAQ is found by `data-iseo="faq"` (BUG2).
- **Post meta:** the final proof level and the generation id (G6).
- **Refusing bad content:** Submit refuses content that is empty or starts with "Error:", so an error can never become a post (v2 too).
- **Session:** ended on the server; image files are kept.

### 4.8 Leave

- **Before Submit (W10):** a modal ("all data for this post will be lost") with Stay / Leave. Closing or reloading the tab does the same by beacon.
- **What Leave deletes:**
  - the server session;
  - the image files generated for this post (only images, only inside uploads);
  - the unsubmitted post's history row;
  - the article text of its generation rows. The row and its billing stay.
- **Abandoned wizards:** if the tab is closed with neither Leave nor Submit, a daily cleanup deletes that wizard's images after 8 days, unless a post, an attachment or any post meta uses them.

---

## 5. Bulk generation

| Asked | Built |
|---|---|
| BULK1 the same chain per task, no wizard questions | One task per cron tick: Title (with `<max_list_items>` lowered to what the size holds) → Approve (research, planner) → article → image. Owner questions are skipped. |
| BULK2 Regulated applies automatically; a Regulated business saves drafts | As asked; posts that still fail checks are also saved as drafts with the report. |
| BULK3 blueprint batch memory | The batch's earlier posts count in the history. |
| BULK4 keyword data with Option B when the flag is on | The title and planner requests carry the opt-in, the same server path as the wizard. |
| BULK5 no double charge when the image fails | The article is saved to the task before the image call; an image failure keeps the paid article. |
| BULK6 retries stop, with a cap and backoff | 3 attempts, with `next_attempt_at` backoff, then Failed with the reason. |
| BULK7 an error can never be published | Empty content or an error is a failed attempt, never a post. |
| (W5 for bulk) | A local business picks the project's Target town in the bulk wizard: a saved town or "Not about a specific location" (default: the first saved town). |

**Robustness, from the completion pass and the independent review:**
- **Outage or failure.** "Retry later" (no attempt counted, the queue pauses 1 minute doubling up to 30) only when:
  - the server could not be reached;
  - a gateway error came back with no v3 answer (a deploy, a cold start);
  - the server restarted mid-article;
  - a limit was hit.

  At most 8 times per post. A step that failed is a counted attempt, so a post that fails every time ends Failed. An earlier run still finishing delays only its own post.
- **Keys unique per site.** A bulk post's session key includes the site code (task ids restart at 1 on every site), so two sites of one account never share regeneration pricing or the one-running-run rule.
- **The title travels with the article.** A fresh title is saved to the task at once, and the post takes the title the server wrote its article for.
- **Lease and heartbeat.** A task is claimed with a lease (30 minutes), the lease is refreshed between steps, and every write checks it. A stuck task is reclaimed only after its lease expires, and that counts an attempt.
- **Outages.** A network error or server outage doesn't count an attempt: the queue pauses with backoff (1 minute doubling up to 30) and resumes on its own.
- **v3 switched off mid-run.** The server's "v3 off" sends the task back to v2.
- **Exactly one charge.** Each task attempt sends a `request_id`, so a run that died after paying replays instead of paying again.

---

## 6. Money, safety and reliability

| Topic | How it is built |
|---|---|
| **Credits charged once** | Reserve before writing, refund on any failure. The `content_v3_runs` ledger (migration 024) claims each `request_id` before any charge, replays a finished one, and allows one running run per session across all server instances (rows move only while running). Failed refunds are retried every 10 minutes. A refund whose outcome is unknown is reported for a person and never retried, so it is never paid twice. |
| **A process that dies** | On shutdown (every deploy, and the rollback itself) articles in progress are failed at once and refunded within the 25-second drain. Runs a dead process left behind are swept a minute after boot and every 10 minutes (charged ones refunded). This housekeeping runs whether v3 is on or off. |
| **Time limits** | Article 480 s, approve 360 s, title 200 s, each below the plugin's wait (600 / 420 / 240 s). Every attempt is cut to the time left; past the limit no new attempt starts. |
| **Retries** | Up to 3 attempts per step; exponential backoff with jitter (`CONTENT_V3_RETRY_BASE_MS`, default 1 s); `Retry-After` honoured; a request the provider refused (a 4xx other than 408, 409 or 429) is not retried. |
| **Free-step allowance** | Title, town check and approve are free but cost us money, so each has a daily allowance per account that grows with the articles delivered that day: title 60 + 5 per article, town check 100 + 10, approve 30 + 3 (`CONTENT_V3_FREE_STEP_LIMITS_JSON`). A refusal is not counted. Plus 30 requests a minute per account on the wizard steps and on the article. |
| **Switches** | Every v3 route answers `409 v3_disabled` while `CONTENT_V3_ENABLED` is off, or while migrations 023 and 024 are missing (checked once a minute), and the plugin falls back to v2. Keyword prefetch spends nothing unless v3 and keyword data are both on. |
| **Who may spend credits** | Generation and image AJAX need `manage_options` (filter `improveseo_generation_capability`), plus a nonce printed only on the plugin's own screens. List, bulk and shortcode forms got nonces. |
| **HTML** | Server: allowlist, model links removed, parsed URLs, a final pass. Plugin: `wp_kses_post` before saving. |
| **Errors** | Users only see plain messages (`user-errors.ts`); provider error text never reaches a user or a log. |
| **Personal data in logs** | The request log keeps only the field names of v3 bodies (owner answers, business text and the call-to-action phone stay out); `cta_phone` is also redacted everywhere. |

---

## 7. Data, migrations and cleanup

- **Migrations** (S `src/migrations/`), applied once in Supabase:
  - 022: keyword data caches;
  - 023: sessions and post history;
  - 024: run ledger, daily counters and `schema_migrations`.
  
  All are idempotent (`IF NOT EXISTS`), enable row level security with no policies (only the server touches them), record themselves in `schema_migrations`, and export `DOWN_SQL`. `npm run --silent migrations:print` prints them for pasting into Supabase; `-- --down` prints the reversals.
- **JSON stored as objects.** The installed database driver (postgres.js 3.4.7) JSON-encodes parameters typed json/jsonb, so the code sends JSON as text and casts it (`::text::jsonb`). Without this, a wizard session would have been lost after its second write. This was checked against a throwaway Postgres 16 (migrations up twice, down, up; session merge; replay; refunds; history; caches: 30 checks).
- **Code first, migrations after.** It is safe to deploy the code before the migrations: every store fails open (the wizard keeps working; the ledger falls back to the in-process lock; the daily allowance is not enforced), and each logs that once.
- **Retention.** Sessions 7 days; post history 365 days; runs 90 days; daily counters 30 days. All are purged automatically.
- **Uninstall** (P `uninstall.php`, new). Removes only what v3 and its keyword data added:
  - options, transients and the wizard file lists;
  - the per-project bulk towns;
  - four post meta keys;
  - the 7 bulk task columns and the keyword list metrics column;
  - the cleanup hook.

  Nothing older is touched. This was tested on copies of the tables under a throwaway prefix.

---

## 8. Logs and support

- **One line per model call:**
  ```
  [model-call] step=… model=… prompt=<template>@<version>#<hash> attempt=n/3 outcome=… in=… out=… cache_read=… cost_usd=… ms=… run=<session key>
  ```
- **One cost line per article:**
  ```
  [content-v3] cost run=… search_data=… title=… research=… planner=… writer=… meta=… total=…
  ```
  It is followed by `[content-v3] latency run=… route=article ms=…`. When an article cost more than its size's ceiling (default $0.25 / $0.35 / $0.50, `CONTENT_V3_COST_CEILING_USD_JSON`), `[content-v3] cost over ceiling …` is logged; billing is unchanged.
- **Finding one run:** every line of a run carries `run=<session key>`, including the search data, DataForSEO and keyword data lines and the plugin's own v3 lines (bulk tasks use `iseo_bulk_t<task id>`). Search the logs for `run=<key>`.
- **Admin trail:** `GET /api/v1/content-v3/admin/trail?run=<key>` returns the run's ledger rows (status, credits, step costs, timings, errors), generation rows (model, prompt versions, billing, validator verdict) and post history row. No article text.
- **Prompt versions.** Every article records the version of each text it was built from:
  - the Writer system prompt;
  - the niche profile block (with its type adjustment lines);
  - the blueprint pieces;
  - the interim meta prompt.

---

## 9. Prompt registry

- Every v3 prompt and text piece is a template in the MongoDB registry, with versions selectable in the admin panel's v3 prompts page.
- `POST /content-v3/prompts/seed?mode=replace` (admin) makes each doc-sourced prompt the active version, backing up the old value as a version labelled `pre-v3` first. `GET /content-v3/prompts/check` compares every active text with the doc (`all_equal` when they match).
- Until the replace step runs on production, the server uses the doc text held in code, so production behaves correctly either way.
- Templates:
  - `content_v3_title`, `content_v3_title_keyword_data`, `content_v3_planner`, `content_v3_planner_keyword_data`;
  - `content_v3_research`;
  - `content_v3_system` (Writer core), `content_v3_rules_no_invention`, `content_v3_business_context` (reader first), `content_v3_rules_human_style`, `content_v3_seo_rules`, `content_v3_rules_local_seo`, `content_v3_rules_formatting`;
  - `content_v3_blueprint_directives`;
  - `content_v3_niche_standard_piece`, `content_v3_niche_regulated_piece`;
  - the settings `content_v3_niche_profiles`, `content_v3_regulated_topics` and `content_v3_disclaimers`.

---

## 10. Configuration

Server env (every one is in `.env.example`; all optional except where noted):

| Variable | Default | What it does |
|---|---|---|
| `CONTENT_V3_ENABLED` | off | The master switch (`true` turns v3 on) |
| `PERPLEXITY_API_KEY` | none | Research; without it research is skipped |
| `PERPLEXITY_EFFORT`, `PERPLEXITY_MODEL` | `low`, none | Research model choice (Zaid) |
| `DATAFORSEO_LOGIN`, `DATAFORSEO_PASSWORD` | existing | Search data and keyword data |
| `ISEO_V3_KEYWORD_DATA` | off | Option B (keyword data in Title and Planner) |
| `CONTENT_V3_MAX_ATTEMPTS` | 3 | Attempts per step |
| `CONTENT_V3_TIMEOUT_*_MS` | 30 / 60 / 120 / 90 / 300 / 60 s | Per-attempt limit for search data, title, research, planner, writer, meta |
| `CONTENT_V3_ARTICLE_DEADLINE_MS`, `_APPROVE_`, `_TITLE_` | 480 / 360 / 200 s | Whole-request limits |
| `CONTENT_V3_RETRY_BASE_MS` | 1000 | First retry wait |
| `CONTENT_V3_TARGET_CALIBRATION_JSON` | 1.0 / 1.3 / 1.6 | Word-target calibration (A1) |
| `CONTENT_V3_FREE_STEP_LIMITS_JSON` | see section 6 | Daily allowance for the free steps |
| `CONTENT_V3_COST_CEILING_USD_JSON` | 0.25 / 0.35 / 0.50 | Cost warning per article |
| `CONTENT_V3_SESSION_TTL_DAYS` | 7 | Session lifetime |
| `SEARCH_DATA_THIN_THRESHOLD`, `SEARCH_DATA_COUNTRY_*`, `SEARCH_DATA_TOWN_ALIASES_JSON` | 3, US | Search data |
| `CONTENT_V3_STORE` | Postgres | `memory` is for local development only, never on Render |

The plugin settings are listed in section 4.1. The editable registry settings are listed in section 9.

---

## 11. What was changed outside v3, and why it is safe

These fixes apply to v2 as well. They are security or data-safety fixes allowed by the brief:
- **Who may generate.** The generation and image AJAX handlers need `manage_options`. Every plugin screen already required it.
- **Nonces.** The AJAX nonce is printed only on the plugin's screens. Lists, bulk projects and shortcodes got nonces.
- **Submit.** Refuses empty content or content starting with "Error:".
- **Regenerate.** Sends `target_key` and the regenerate instruction (BUG1).
- **Model name.** The v2 row records the writing model (BUG3).
- **Generations history.** `/api/v1/generations` (the admin tester's history) needs an admin token.
- **v2 bulk retries.** Only "could not connect" and 502/503/504 pause the queue, at most 8 times per post; any other error is a counted attempt (it was any 5xx, forever). v2 bulk is otherwise called exactly as before, with no new billing behaviour.
- **Image log line.** The image route logs `[image] run=… model in out cost_usd ms` (log only).
- **Request log.** Keeps only field names for v3 bodies; `cta_phone` is redacted.
- **Prompt reader.** The shared prompt reader returns the newest active version while one is being activated.

---

## 12. What the team lead adds on the server side, and what waits on him

v3 is complete without the items below. Each one is added later without code changes, unless marked.

### Before switching v3 on
1. **Apply migrations 022, 023, 024** in Supabase. Paste the output of `npm run --silent migrations:print` in the SQL editor. They are idempotent.
2. **Deploy the server** with `CONTENT_V3_ENABLED` unset (v3 off). Nothing changes for users.
3. **Run the prompt replace step** on the deployed server: `POST /api/v1/content-v3/prompts/seed?mode=replace` (admin). Then `GET /api/v1/content-v3/prompts/check` must say `all_equal`.
4. **Add `PERPLEXITY_API_KEY`** on Render, with Zaid's `PERPLEXITY_MODEL` / `PERPLEXITY_EFFORT` if not the defaults. Without it, articles are written without research: no sourced figures, and Regulated posts can't reach 3 source links, so they show the review notice.
5. **Deploy the plugin** (fast-forward `Nov-03`; sites update through WP Pusher).
6. **Turn v3 on in stages:** set `CONTENT_V3_ENABLED=true` on staging, then switch on "Use Content v3" on one test site, then one real site, then all sites.

### Waiting on his documents or decisions

| Item | What happens today | What he provides |
|---|---|---|
| W1 keyword check rules | Only the title checks run | The "Single wizard: input fields" document |
| G4 disclaimer texts | Regulated businesses stay on v2; a post marked Regulated on a Standard site gets no disclaimer | One text per regulated niche, in the `content_v3_disclaimers` setting (admin panel, no deploy) |
| V10 style thresholds | Not checked | The Humanizer v1.2 document (code change) |
| Doc C size table | Working values (15 items maximum) | The table |
| Prompts #11 Cleanup, #12 Repair, #13 Meta, #14 Image and business details from the website | Interims U1–U6 | The prompt texts (code change to wire each one) |
| Wording the code adds | Listed with file and line in `docs/CONTENT_V3_LEAD_REQUIREMENTS.md` | Approve or replace each line |
| Pricing | v3 costs us more per article than the credit price implies (measured 14 Nov) | A decision on credit prices (no change made) |
| Image cost in the logs | `[image] … cost_usd=null` | The image model's price in `MODEL_PRICES_JSON` on Render (from OpenAI's price page) |

---

## 13. Requirement map

Every ID from Brief 1, what was asked, how it is built, and its status. "Working value" means the design doc leaves the number or rule open; the value used is listed in `docs/CONTENT_V3_LEAD_REQUIREMENTS.md`.

Count: 3 BLOCKED, 129 DONE (of 132).

### W: Wizard and run order

| ID | Asked (Brief 1, verbatim) | Built | Status |
|---|---|---|---|
| W1 | Keyword typed: the keyword check (code) runs. Its rules come from the lead's wizard doc; mark BLOCKED if they are not available. | Waits on the lead's "Single wizard: input fields" document. The working title checks of W3 run meanwhile. | BLOCKED |
| W2 | Generate title: the search data lookup runs, then the Title prompt. If the user typed their own title, the lookup runs on Approve title instead. | P `modules/content-v3.php` (AJAX `improveseo_v3_generate_title`) → S `POST /content-v3/wizard/title`: search data, then Title. A typed title gets the lookup on Approve. | DONE |
| W3 | Approve title: the title check (code), then Research, then Planner. | S `POST /content-v3/wizard/approve`: title check (`planner-fallback.ts`), Research, Planner, regulated check. Check rules until W1's document exists: empty, markup or over 150 characters blocks; over 70 characters or no keyword word is a note. | DONE (working check rules) |
| W4 | Generate AI Post: blueprint and niche profile (code), then the Writer, then the validator and the post-processing steps. | S `POST /content-v3/article`: blueprint + niche profile, Writer, post-processing, validator, one repair run, meta. v2 is used when the server says v3 is off. | DONE |
| W5 | A local business must choose a Target town in step 1: a saved town, a new town, or "Not about a specific location". The last option gives one country-level lookup. | Single: Target town select (saved towns, a new town, "Not about a specific location"); the server refuses approve without a choice for a local business. Bulk: a project-level Target town. "Not about a specific location" gives one country lookup. | DONE |
| W6 | If the seed keyword names a different town than the one chosen, the wizard shows one line asking the user to check. | S `POST /content-v3/wizard/town-check` (free) → one line under the town. | DONE |
| W7 | Step 2 shows: article type, post size with size_reason, the owner questions (from the Planner) with answer fields, and the regulated line with Keep / Turn off when it applies. | Step 2 panel: article type, size with size_reason, owner questions with answer fields, regulated line with Keep / Turn off. | DONE |
| W8 | The user can choose a larger size than suggested, never below minimum_size. The larger choice changes blueprint step 3m. | Sizes below `minimum_size` are greyed out and refused by the server before any charge; a larger size sets `larger_than_suggested` (blueprint step 3m). | DONE |
| W9 | Point of view dropdown: label "How the business refers to itself" (or keep "Point of view" with a help line). Options: "We / our", "I / my", "Business name only". Remove "Speaking to the reader" and "Auto (AI decides)". Prefilled from Business details and changeable per post. Existing "Speaking to the reader" and "Auto" values map to the Business details value through a migration. | Single and bulk wizards: "We / our", "I / my", "Business name only", prefilled from Business details, with a help line. Old values map to the Business details value when read (`improveseo_v3_map_point_of_view`), so v2 keeps its values after a rollback; a v3 value reaching v2 is mapped back (`improveseo_v2_point_of_view`). | DONE (read-time mapping, C10) |
| W10 | Leaving the wizard before Submit shows a modal ("all data for this post will be lost") with Stay or Leave. Leave deletes everything stored for the post. | Leave modal (Stay / Leave); Leave, tab close or reload deletes the session, the post's images, its history row and its article text (billing kept). Abandoned wizards' images are deleted by a daily cleanup unless used. | DONE |
| W11 | Credits charge the chosen size. There is no refund for a shorter article. | The chosen size is priced exactly as v2 (reserve, refund only when generation fails); no refund for a shorter article. | DONE |
| W12 | Regenerate title sends <previous_titles> and reuses the cached search data. | `<previous_titles>` from the session; search data reused from the session (no second DataForSEO charge). | DONE |
| W13 | Regenerate Content draws new blueprint picks but keeps the same body and FAQ questions. Pinned dimensions stay pinned. | Regenerate Content keeps the questions and draws new picks; pins stay. | DONE |
| W14 | Regenerate and Change Image reuse the stored search data, research and plan. | Regenerate and Change Image reuse the session's search data, research and plan. | DONE |
| W15 | Only the title is shown to the user. form, items, main_concern and page_kind are logged. | Only the title is returned; form / items / main_concern / page_kind are logged and stored in the session. | DONE |

### S: Search data

| ID | Asked (Brief 1, verbatim) | Built | Status |
|---|---|---|---|
| S1 | Google organic, Live mode, advanced results, depth 10, people_also_ask_click_depth 1, language from <language>. | S `search-data.service.ts`: organic, live, advanced, depth 10, PAA click depth 1, language from `<language>`. | DONE |
| S2 | Nationwide business, or a post marked "Not about a specific location": one call with location = the country. | One country call for nationwide or "Not about a specific location". | DONE |
| S3 | Local post: first call with location = the post's Target town. Resolve towns DataForSEO does not list using the working value (coordinates or the nearest listed city), kept configurable. | Town resolved on DataForSEO's location list; unlisted → `SEARCH_DATA_TOWN_ALIASES_JSON` (nearest listed city), then coordinates, then the country (logged). | DONE (working value) |
| S4 | Thin check: fewer than 3 People Also Ask questions or fewer than 3 related searches. Make the threshold a config value. | `SEARCH_DATA_THIN_THRESHOLD` (default 3). | DONE |
| S5 | Second call only when thin: same settings, location = the country, and the town removed from the keyword if it appears there. | Country call only when thin, with the town removed from the keyword. | DONE |
| S6 | Merge: local items first, then national; duplicates removed; caps of 10 titles, 8 questions and 8 related searches. Every item carries scope="local" or scope="national". The block records the location used, national_fallback, and the counts. | Local first, duplicates removed, caps 10 / 8 / 8, every item scoped; the block records the location used, national_fallback and the counts. | DONE |
| S7 | National items are used for wording and questions only. National domains never enter the Research competitor filter. | Only local ranking domains reach Research's filter (`meta.local_domains`). | DONE |
| S8 | No state-level call. | No state-level call. | DONE |
| S9 | Both results are cached and reused on Regenerate title. Log whether the national call ran, and the counts from both calls. | Stored in the session and reused on Regenerate title; one `[search-data]` line with both calls' counts and `run=`. | DONE |
| S10 | Detect a town in the seed keyword using DataForSEO's location list (working value). | Town in the keyword detected on the location list; one-word towns only after in / near / around or before a state. | DONE (working value) |
| S11 | Reuse the shared DataForSEO client from the keyword data work, with cost logging per call. | The shared DataForSEO client from the keyword data work; cost logged per call, with `run=`. | DONE |
| S12 | Build the <search_data> block exactly as the Title and Planner data sections show it. | `searchDataBlock` (S `content-v3-steps/xml.ts`), the doc's shape. | DONE |

### T: Title (prompt 1)

| ID | Asked (Brief 1, verbatim) | Built | Status |
|---|---|---|---|
| T1 | System prompt verbatim. The data block uses the tags listed, and empty tags are left out. | Template `content_v3_title`, byte-equal to the doc (equality tests); empty tags left out. | DONE |
| T2 | Runs on the writing model. | Writing model (`ANTHROPIC_WRITE_MODEL`). | DONE |
| T3 | New tags: <max_list_items> (from the size table, never more than 15) and <previous_titles>. | `<max_list_items>` 15 (lowered in bulk to what the size holds) and `<previous_titles>`. | DONE (working value) |
| T4 | The provisional article type is NOT sent. | No article type sent. | DONE |
| T5 | The business-level <proof_level> is sent (decided 3 Nov). | `<proof_level>` from Business details. | DONE |
| T6 | Code checks length after each call: over 70 characters is a failed attempt. Unreadable JSON or an error is also a failed attempt. Up to 3 attempts in all; after 3 failures the title box is left empty for the user. | 3 attempts (over 70 characters, bad JSON or an error fails one); then an empty box and a plain message. | DONE |
| T7 | Output JSON: title, form, items, main_concern, page_kind. | Output parsed and validated (title, form, items, main_concern, page_kind). | DONE |
| T8 | The new text goes into today's title prompt slot. Find the setting name and report it. | Today's title prompt is hard-coded in S `generation.routes.ts`; the v3 Title is template `content_v3_title`. | DONE (finding) |
| T9 | Option B integration as described in rule 5 of section 2. | Keyword-data variant `content_v3_title_keyword_data` = the Title text byte for byte plus one appended KEYWORD DATA block (C6 approved 14 Nov). | DONE |

### R: Research (prompt 2)

| ID | Asked (Brief 1, verbatim) | Built | Status |
|---|---|---|---|
| R1 | Built on the Perplexity Agent API, NOT Sonar Chat Completions: the system prompt goes in `instructions`, the data in `input`, search settings on the `web_search` tool, and the schema in `response_format` (json_schema). Check the current Perplexity docs for the exact request shape before coding. | S `research.service.ts`: Agent API `POST /v1/agent` with instructions / input / web_search / json_schema / effort, checked against Perplexity's docs. A live run needs `PERPLEXITY_API_KEY` (section 12). | DONE (live run waits on the key) |
| R2 | Instructions and input verbatim, with the output schema exactly as the doc gives it. | Instructions byte-equal to the doc; input = the doc's data block; schema = the doc's shape. | DONE |
| R3 | When a target town is given, set `user_location` (city, region, two-letter country code). | `user_location { city, region, country }`; the country from `SEARCH_DATA_COUNTRY_ISO`. | DONE |
| R4 | `search_domain_filter` is a denylist (-domain.com) built from the LOCAL ranking pages only, up to 20 domains. Skip government, education, encyclopedia and major news domains; keep that skip list configurable. | Denylist of local ranking domains only, max 20, configurable skip list. | DONE |
| R5 | Map each returned source number to the matching `search_results` entry, adding the source token, URL and date. Drop any fact or question whose source has no match. | Source numbers mapped to `search_results` (token, URL, date); unmatched facts and questions dropped and counted. | DONE |
| R6 | Enforce the caps in code as well: 15 facts, 10 questions, 6 local conditions. | Caps 15 / 10 / 6 in code. | DONE |
| R7 | Up to 3 attempts. If all fail, the run continues without research: the Planner works from the search data, and the Writer gets no facts, so it uses no figures. | 3 attempts; then the run continues without research (also with no key). Failed attempts' cost is recorded. | DONE |
| R8 | Stored for Regenerate and Change Image. | Stored in the session for Regenerate and Change Image. | DONE |
| R9 | The model and effort level are config values (an open question for Zaid). Log the cost. | `PERPLEXITY_EFFORT` (default low), `PERPLEXITY_MODEL`; cost logged. | DONE (config); model choice: Zaid |
| R10 | Number facts S1, S2 and so on when building <research> for the Writer, with scope and conflict marks, and <local_conditions> inside <research>. | S1… tokens with scope and conflict marks; `<local_conditions>` inside `<research>`. | DONE |

### P: Planner (prompt 3)

| ID | Asked (Brief 1, verbatim) | Built | Status |
|---|---|---|---|
| P1 | System prompt verbatim. Runs on the low-cost model. Replaces content_v3_intelligence. | Template `content_v3_planner`, byte-equal; lower-cost model (`ANTHROPIC_MODEL`); replaces `content_v3_intelligence` in the v3 chain. | DONE |
| P2 | Data block as listed, including <size_options> (the three sizes with their word ranges, from the size table) and <minimum_size>. | `<size_options>` and `<minimum_size>` (from the title's item count until doc C's table exists). | DONE (working rule) |
| P3 | Code validates the output: valid JSON; post_size not below minimum_size; 1 to 5 FAQ questions; no FAQ question repeating a body question; at most 2 owner questions. A failure counts as an attempt; up to 3 attempts. | JSON; size ≥ minimum; 1–5 FAQ when real questions exist; no FAQ repeating a body question; ≤ 2 owner questions; 3 attempts. | DONE |
| P4 | After 3 failures: article type falls back to the keyword guess, post size to the minimum or Small, with no owner questions and no searcher profile. The article can still be generated. | Fallback plan after 3 failures (also when out of time); the article can still be generated. | DONE |
| P5 | What is shown to the user and what goes only to the Writer follows the doc. | `/wizard/approve` returns only what step 2 shows; the rest stays in the session for the Writer. | DONE |
| P6 | Option B: <keyword_data> and <recent_posts> in the user message when the flag is on, and the demand_notes and overlap output fields, which the validator accepts only in that variant. | `<keyword_data>` and `<recent_posts>` with the flag on; demand_notes / overlap accepted only in that variant (`content_v3_planner_keyword_data`). | DONE |

### G: Proof level and regulated topics

| ID | Asked (Brief 1, verbatim) | Built | Status |
|---|---|---|---|
| G1 | Business-level proof level: suggested by the setup scan with its evidence, confirmed by the user, editable in Business details. It is never set to Regulated silently. | Business details proof level; the niche default is only a suggestion, never set silently. | DONE (no setup scan: U6) |
| G2 | Post-level check for a Standard business: either the regulated topics list (an editable setting of multi-word terms plus the "never match on their own" exclusions, English only in v1) or the Planner's regulated_topic is enough. The single wizard then shows Keep / Turn off; in bulk the result applies automatically. | Regulated topics list (the doc's table with exclusions, editable setting `content_v3_regulated_topics`) or the Planner's flag; single wizard Keep / Turn off; bulk applies automatically. | DONE |
| G3 | Business Regulated: every post starts Regulated, bulk saves posts as drafts, and the user sees a one-time notice. Post Regulated: the Regulated piece, a minimum of 3 source links and the disclaimer, for that post only. | Business Regulated: every post starts Regulated, bulk drafts, a one-time notice; until the niche has a disclaimer text the business stays on v2 (decision A3). Post Regulated: the Regulated piece and ≥ 3 source links; the disclaimer part is G4. | DONE (with A3) |
| G4 | The disclaimer is inserted by code, before the last two sections, with one text per regulated niche. The texts are not written yet: make them admin-editable settings and mark G4 BLOCKED until content exists. Never ship an empty disclaimer. | Inserted by code before FAQ + closing (or the closing alone) from the editable setting `content_v3_disclaimers`, which ships empty; nothing is inserted while a text is empty. | BLOCKED (texts) |
| G5 | The Witty tone is not offered for regulated niches. | Witty hidden for a regulated niche; a stored "witty" becomes "friendly" (single and bulk). | DONE |
| G6 | The final proof level is stored per post as <proof_level>. | Final proof level stored per post (`_iseo_proof_level` post meta, server history). | DONE |

### B: Blueprint

| ID | Asked (Brief 1, verbatim) | Built | Status |
|---|---|---|---|
| B1 | One editable record, content_v3_blueprint_directives, holds every text piece verbatim under its own key: 5 openings, 4 length patterns, 4 accents, 4 closing moves, 2 closing formats, 3 firmness levels, the 4 article type pieces plus "Cost guide, no figure available", and the numbered piece. | Every piece verbatim (a test re-reads the doc) in one editable record `content_v3_blueprint_directives`. | DONE |
| B2 | The inputs code needs, as listed under "How code assembles the blueprint". | All the inputs the doc lists. | DONE |
| B3 | The algorithm, steps 1 to 7, implemented exactly: pins; draws a to m in order; history checks; assembly in the fixed order; wrap in <blueprint>; save the picks to the post. | S `blueprint.ts`: pins; draws a–m; history checks; the fixed assembly order; the `<blueprint>` wrap; picks saved. | DONE |
| B4 | History rules: no exact repeat of the site's last 10 posts; no opening or closing move used in the previous 2 posts of the same type; no single pick in more than 40% of the last 20 posts; after 5 redraws, accept and log. Bulk runs remember picks from earlier articles in the same batch. | No repeat of the last 10; opening / closing not in the previous 2; 40% over the last 20 from 5 earlier posts, only for dimensions with 3+ options, redrawing only that dimension; 5 redraws then accept + log; history = same type, one row per post; bulk batches count. | DONE (C8, C9 working values) |
| B5 | Word targets: aim at the middle of the size's range (900 / 1,800 / 3,000), subtract the fixed parts (about 380 / 450 / 540), split by the length pattern shares, round to the nearest 10, and draw one section fewer if any section would fall under 120 words. Step 3m applies when the user chose a larger size. | Middle aim minus fixed parts, pattern shares, nearest 10, one fewer under 120; 3m aims a quarter into the range; sections capped at questions + optional elements (C12); calibrated targets for the Writer (A1). | DONE (working values) |
| B6 | Numbered titles: at most 15 items; minimum words per item of 60 (tips, mistakes, reasons, signs), 40 (steps, stages) or 35 (ideas, examples, questions to ask); no optional elements; no extra list; a summary table only on Medium and Large. | The Title step's form decides; typed titles need a number before a known item kind; cap 15; 60 / 40 / 35 words per item; no optional elements; no extra list; table only on Medium / Large; grouped from 8 items. | DONE (working value) |
| B7 | Nationwide: never a "local only" element. Local: "local only" elements get a higher chance. | Nationwide never "local only"; local weight 2. | DONE |
| B8 | "Price available" detection (a currency amount in research facts or the Business details pricing field) selects the no-figure Cost guide piece when false. | Currency before or after the amount ($, €, £, USD, EUR, GBP, dollars, euros; not "pounds"); with no price the no-figure piece is sent after the Cost guide piece. | DONE (C13) |
| B9 | <recent_headings>: the site's headings for the same article type from its last 20 posts. | `<recent_headings>`: h2s and body h3s of the last 20 posts of the same type (FAQ questions left out). | DONE |
| B10 | Table of contents: inserted by code; on Auto, none on Small and included on Medium and Large. | TOC by code; on Auto none on Small, included on Medium / Large. | DONE |
| B11 | Closing firmness comes from readiness (researching = soft, comparing = medium, ready to hire = direct), capped by the niche profile's firmest allowed value; soft when readiness is missing. | Firmness from readiness, capped by the niche; soft when missing. | DONE |
| B12 | The structure fingerprint is stored per post. | Fingerprint stored per post (`content_v3_posts`). | DONE |

### N: Niche profile

| ID | Asked (Brief 1, verbatim) | Built | Status |
|---|---|---|---|
| N1 | The 8 profiles, the niche mapping, the watchlists and the default proof levels stored as settings; adding a niche is one row. | One editable setting `content_v3_niche_profiles`: profiles, mapping, watchlists, default proof levels; adding a niche is one row. Per-niche provider / help phrases are working values. | DONE (working phrases) |
| N2 | The Standard piece (with the watchlist filled in) and the Regulated piece are stored verbatim as editable text. | Standard and Regulated pieces verbatim, editable. | DONE |
| N3 | The type adjustment lines are added only when the type and niche match a row. | Type adjustment lines only on a matching row; versioned with the niche profile block. | DONE |
| N4 | The <niche_profile> block is assembled as in the doc's example, including the next step per firmness and the nationwide next step. | `<niche_profile>` as in the doc's example, with the nationwide next step. | DONE |
| N5 | The firmest closing value caps blueprint step 3k. | The firmest closing caps blueprint step 3k. | DONE |

### WR: Writer (prompt 4)

| ID | Asked (Brief 1, verbatim) | Built | Status |
|---|---|---|---|
| WR1 | The system prompt is the core verbatim, followed by the six shared rules blocks verbatim, each wrapped in its own tag. Use the order the core lists them in: rules_no_invention, rules_reader_first, rules_human_style, rules_seo, rules_local_seo, rules_formatting. Note: the SEO section's developer note says the SEO blocks sit after the other shared rules, which conflicts with that order. Flag the conflict in Phase 0 and use the core's order unless I say otherwise. | Core + six rules blocks verbatim, each in its tag, in the core's order. | DONE (C1) |
| WR2 | The user message has the exact tags and order listed; empty tags are left out; <searcher_profile> has its six child tags. | The doc's tags and order; empty tags left out; `<searcher_profile>` with six children. | DONE |
| WR3 | The new text replaces content_v3_system. content_v3_seo_rules and content_v3_business_context are replaced by the rules blocks. Old values are backed up as a version. | Replace mode backs up every old value as `pre-v3`; until it runs in production the server uses the doc text from code. | DONE (mechanism; production step: section 12) |
| WR4 | Output format: the article HTML, then a line with exactly ===WRITER_JSON===, then a JSON object {facts_used, claims_to_check, items_delivered}. The parser is strict, and a bad format counts as a failed attempt. | `===WRITER_JSON===`; strict parser: text before the article, `{}` or a missing key is a failed attempt. | DONE |
| WR5 | Section tags: data-iseo values quick-answer, takeaways, body, faq and closing. closing replaces conclusion and whats-next. Update every piece of code that finds sections by tag (TOC, disclaimer position, FAQ extraction, schema markup, any parsing). | `data-iseo` quick-answer / takeaways / body / faq / closing; TOC, disclaimer slot, FAQ extraction and schema use them. | DONE |
| WR6 | Meta title and description are no longer in the Writer JSON; see U4. The FAQ is read from the HTML: each <h3> plus the next <p> inside the faq section. | No meta in the Writer JSON; FAQ read from the HTML. | DONE |
| WR7 | Source tokens: after writing, the first use of up to 4 different sources becomes a link and the remaining tokens are removed. A token with no matching source is removed and logged. Regulated posts need at least 3 links. Check that the token removal leaves no double spaces or broken punctuation. | First use of up to 4 sources linked on DOM text nodes (lists like `[S1, S2]` handled); other and unknown tokens removed (unknown logged); spacing and punctuation cleaned; leftovers logged. | DONE |
| WR8 | Table of contents and disclaimer are inserted by code. | TOC (`<nav class="iseo-toc">`) and disclaimer by code. | DONE |
| WR9 | The system prompt is cached (rule 6 in section 3). | System prompt byte-identical per article; cached tokens on every Writer call after the first (10-keyword run). | DONE |
| WR10 | HTML allowlist from <rules_formatting>: p, h2, h3, ul, ol, li, table, thead, tbody, tr, th, td, strong, em and a. Sanitize anything outside it. | Allowlist sanitizer; model links kept only for the CTA; a final allowlist pass after the TOC and disclaimer; plugin `wp_kses_post`. | DONE |

### V: Validator

| ID | Asked (Brief 1, verbatim) | Built | Status |
|---|---|---|---|
| V1 | The keyword appears in the first 100 words. | The keyword or its closest natural wording (contractions, -ing / -ed) in the first 100 words. | DONE |
| V2 | Exactly one summary section, directly after the introduction. A quick answer is 40 to 60 words; key takeaways have 3 to 5 points, matching the blueprint's count. A heading starting "Key takeaways:" is allowed (decision D21). | Exactly one summary right after the introduction; quick answer 40–60 words; 3–5 takeaways matching the blueprint; "Key takeaways: …" allowed. | DONE |
| V3 | The FAQ count matches the blueprint. | FAQ count matches the blueprint. | DONE |
| V4 | At most 1 in 3 body headings ends in a question mark. | At most 1 in 3 body headings end in "?"; a "questions" list may use questions as its items. | DONE |
| V5 | Every h2 has a data-iseo value. | Every h2 has a known `data-iseo`. | DONE |
| V6 | items_delivered matches the number in the title. | N items (as H2s N to N + 2 body sections: guide + leftover); the Writer must report N. | DONE |
| V7 | The word count is within the size's range. | Word count in the size's range (block boundaries counted as spaces). | DONE |
| V8 | Vague back-references are flagged ("as mentioned above", "see below"). Specific cross-references that name their target are allowed. | A fuller list of vague back-references; named cross-references allowed. | DONE |
| V9 | No fixed-label headings, per the HEADINGS list in the Writer core. | Fixed labels caught with any apostrophe and with extra words; "Key takeaways: <topic>" allowed. | DONE |
| V10 | The style checks from Humanizer v1.2, sections 7 and 11 (tone ranges, rhythm, threes, limits, em dashes, bold). Mark BLOCKED if that doc is missing. | Waits on the Humanizer v1.2 document. | BLOCKED |
| V11 | A validator report is stored per post. What happens on failure depends on U3 (Repair). | Report stored on `user_generations.metadata` and `content_v3_posts.validator`; failure flow = U3. | DONE |

### U: Prompts not yet written (interims)

| ID | Asked (Brief 1, verbatim) | Built | Status |
|---|---|---|---|
| U1 | #11 Cleanup and audit (replaces content_v3_humanize). Check whether the current humanize pass would break data-iseo tags, source tokens or the v3 rules. Propose skipping it for v3 or keeping it as is, until #11 is written. | The old humanize pass does not run on v3. | DONE (interim, approved 14 Nov) |
| U2 | #12 Repair. Propose an interim, for example re-running the Writer once with the same inputs and, if it still fails, saving as a draft with the validator report. No new prompt text. | One more Writer run (targets rescaled when too short; skipped per A2). | DONE (interim) |
| U3 | Validator failure flow, which depends on U2. | Single: "Please check this article" with Use anyway / Regenerate. Bulk: a draft with the report. | DONE (interim) |
| U4 | #13 Meta title and description. The Writer no longer returns meta, so v3 needs a meta step. Find today's meta prompt and propose running it on the v3 article with the smallest possible change, pending #13. | The existing v3 meta wording on the final article, with length enforcement; versioned by hash. | DONE (interim) |
| U5 | #4 Image prompt. Find today's "AI Image From Title" and "Custom Prompt" and propose keeping them as they are. | "AI Image From Title" and "Custom Prompt" unchanged. | DONE (interim) |
| U6 | #14 Business details from website, the setup scan and the onboarding order (niche first, a search-as-you-type niche list with synonyms and typo tolerance, the "Other ..." flows). Report what exists today. These depend on open questions, so propose scope and wait for my decision. | No setup scan; manual Business details fields (niche search-as-you-type, closest profile for "Other …", proof level, point of view, towns, reach). | DONE (interim) |

### X: Admin tester

| ID | Asked (Brief 1, verbatim) | Built | Status |
|---|---|---|---|
| X1 | Rename "Lived scenario" to "Reader's situation" and "Micro anecdote" to "What's at stake". | A `ContentV3Tester.tsx`: "Reader's situation" and "What's at stake". | DONE |
| X2 | Remove the body sections pin. | No body sections pin. | DONE |
| X3 | "Pull quote" becomes "Checklist". | "Checklist" accent. | DONE |
| X4 | FAQ placement becomes "FAQ then closing" / "Closing then FAQ"; "Woven in" goes. | "FAQ then closing" / "Closing then FAQ". | DONE |
| X5 | The closing format becomes "paragraph plus the call to action on its own line" (no bold). | Closing format "paragraph plus the call to action on its own line". | DONE |
| X6 | Point of view Auto now means the Business details setting. | Auto point of view = the tester's Business details point of view (sent with the business profile; "We / our" when none is set). | DONE |
| X7 | New dimensions: article type, length pattern, optional elements (multi-select), summary form, closing format, closing firmness. Pins apply in blueprint step 2. The panel is admin-only. | New pins: article type, length pattern, optional elements (multi-select), summary form, closing format, closing firmness; applied in blueprint step 2; admin-only; preview without a model call. | DONE |

### BULK: Bulk

| ID | Asked (Brief 1, verbatim) | Built | Status |
|---|---|---|---|
| BULK1 | The v3 path runs the same chain as single posts, with no wizard questions; owner questions are skipped. | Same chain per task, no wizard questions (owner questions skipped), one task per cron tick. | DONE |
| BULK2 | Regulated: suggestions apply automatically, and a business-level Regulated site saves posts as drafts. | Regulated suggestions apply automatically; a Regulated business's posts, and posts that still fail checks, are drafts with the reason. | DONE |
| BULK3 | Blueprint batch memory (B4). | Batch memory through the post history. | DONE |
| BULK4 | Keyword data from stored keyword lists, with Option B behaviour when the flag is on. | Bulk Title and Planner requests carry the Option B opt-in, the same server path as the wizard. | DONE |
| BULK5 | Fix: an article is charged twice when the image fails. | The article is saved to the task before the image; an image failure keeps the paid article; a dead run replays its `request_id`. | DONE |
| BULK6 | Fix: retries never stop. Add a cap and a backoff. | 3 attempts with `next_attempt_at` backoff, then Failed; leases and heartbeats; an outage pauses the queue without using attempts. | DONE |
| BULK7 | Fix: a 402 error message can be published as post content. Errors must never be published. | Empty or error content is a failed attempt, never a post. | DONE |

### BUG: Known bugs

| ID | Asked (Brief 1, verbatim) | Built | Status |
|---|---|---|---|
| BUG1 | Regenerate drops target_key and the custom instruction (around single_AI_post_function.php:415-433). | `target_key` and the regenerate instruction sent (v2 and v3). | DONE |
| BUG2 | FAQ detection expects a literal "FAQ" heading (around functions.php:1897), which v3 bans. Switch it to data-iseo="faq". | FAQ found by `data-iseo="faq"`; single v3 posts get the content wrapper and FAQ schema. | DONE |
| BUG3 | The wrong model name is logged (around content-v2.routes.ts:217-222). | The writing model is recorded (v2 and v3). | DONE |
| BUG4 | Saving a keyword list has no nonce or capability check. This may already be fixed by the keyword work; verify. | Nonce + `manage_options` on keyword list create / edit / delete. | DONE |

### K: Keyword data

| ID | Asked (Brief 1, verbatim) | Built | Status |
|---|---|---|---|
| K1 | The metrics endpoint and the prefetch endpoint match the contract. | Metrics and prefetch endpoints match the contract. | DONE |
| K2 | The keyword tool shows metrics, and the hand-off to the single and bulk wizards works. | The keyword tool shows metrics; the hand-off to the wizards works. | DONE |
| K3 | With ISEO_V3_KEYWORD_DATA off, payloads are byte-identical to v3 without keyword data. | Flag off = byte-identical payloads (step tests). | DONE |
| K4 | Keyword data never reaches the Writer (a test asserts it). | Tests on the retired chain and on the live Writer path: no keyword data reaches the Writer; the Writer modules import nothing from keyword data. | DONE |
| K5 | Option B is integrated per rule 5 in section 2. | = T9 / P6 (C6 committed 14 Nov). | DONE |

### OPS: Operations

| ID | Asked (Brief 1, verbatim) | Built | Status |
|---|---|---|---|
| OPS1 | Flags: CONTENT_V3_ENABLED (env) plus the per-site "Use Content v3" setting, default off; v2 remains the fallback. | `CONTENT_V3_ENABLED` AND "Use Content v3"; v2 otherwise; Regulated businesses on v2 until their disclaimer exists (A3). | DONE |
| OPS2 | Prompt registry: every prompt and text piece is versioned; an admin can see and select versions; old content_v3_* values are kept as version "pre-v3". | Every registry prompt and piece versioned with labels; `pre-v3` backups; replace / check routes; atomic activation; code-held texts (interim meta, niche profile, blueprint pieces) versioned by hash on every article. | DONE |
| OPS3 | Per-step logs and a per-article cost summary (search data, research, title, planner, writer, image, meta) compared with DEPLOY_RUNBOOK's figure for a medium article. Update the runbook. | `[model-call]` per call; `[content-v3] cost` per article (search data, title, research incl. failed attempts, planner, writer, meta), latency and cost-ceiling lines. The image runs through the unchanged image route, outside this line. | DONE (image cost: section 12) |
| OPS4 | Timeouts on every external call; idempotent retries; no double charging; credits charged once, on success. | Timeouts and per-request limits; backoff retries; the `content_v3_runs` ledger (request_id replay, one running run per session, refund retries); credits charged once. | DONE |
| OPS5 | Storage: everything stored per post for Regenerate and Change Image; data cleared on wizard Leave (W10); sensible retention for abandoned sessions. | Sessions (023) for Regenerate / Change Image; Leave deletes; retention 7 / 365 / 90 / 30 days; uninstall cleanup. | DONE |
| OPS6 | Error messages to users are plain and actionable, and never show raw provider errors. | Plain messages on every v3 route (`user-errors.ts`); v2 routes unchanged by rule 3. | DONE (v3) |
| OPS7 | Docs: CHANGELOG, env var list, a runbook section for Content v3 (enable, disable, roll back), and the final status of every requirement. | CHANGELOG, `.env.example`, runbook (S `docs/CONTENT_V3.md`), `CONTENT_V3_PROGRESS.md`, this document. | DONE |

---

## 14. How it was verified

Verified on 15 November 2026 against the final commits (server `eab0698`, plugin `3fbf523`, admin `a518a99`).

### Automated checks
| Check | Result |
|---|---|
| Server unit and integration tests (`npm test`) | 1026 of 1026 pass |
| Server typecheck (`tsc --noEmit`), plus unused-code checks on the v3 files | Clean (the 8 unused-code hits are all in older files) |
| Every prompt and text piece against the design doc | Byte-equal (equality tests) |
| Plugin PHP syntax, all 34 files the branch changed | Pass on PHP 7.4.33 and 8.2.33; no PHP 8-only functions |
| Admin panel build and lint | Builds; no new lint problems |
| Migrations and stores on a real, throwaway Postgres 16 | 34 of 34 checks pass: migrations up twice, down, up; session merge; replay; refunds; stale sweep; guards; post history; keyword caches |
| `uninstall.php` on copies of the tables under a throwaway prefix | Removes only what v3 added; older options, meta and columns stay |

### Independent review
- A separate reviewer that had not seen the work checked the code against the design doc and both briefs. It reported 17 findings: 3 high, 4 medium, 10 low.
- All 17 were fixed. The reviewer then re-checked every fix and found five smaller residual risks, which were fixed too:
  - a double refund in a narrow race;
  - shutdown coverage before the charge;
  - the stale-sweep threshold on mixed instances;
  - a database blip in the table check;
  - provider outages reported as failures.

### End-to-end, local WordPress
The real plugin and the real local server ran against the production database, with WP_DEBUG on, driven through the browser. The credits were charged to the test account.

| Scenario | Result |
|---|---|
| Single wizard, Medium, local | Title, Regenerate title (cached search data and prompt), Approve (plan with type, size, 2 owner questions), image, Generate: 1,205 words, all section tags, TOC, FAQ, meta; validator passed after the second, rescaled Writer run. Cost $0.28, 89 s. |
| Submit | Creates the project and publishes the post with the content wrapper, the FAQ, the proof level and the generation id. Found and fixed on the way: an old error in `posting.js` (no location tree on the screen) had stopped Submit from sending the form; it predates v3. |
| Extra clicks on Generate, then Leave | One generation and one charge. Leave asks first, then removes the session, the post row, the article text and the stored answer. |
| Keyword data on | Title and Planner use their keyword-data variants (`keyword_data=used`). |
| Bulk, 5 posts, Small, Target town Santa Fe, as drafts | Two queue runs at once took two different posts (the lease works). All 5 done: $0.11–0.18 each. The regulated-topic post was saved as a draft for review. |
| Bulk: server down | The post waits (no attempt used), the queue pauses for a minute, with a plain message. |
| Bulk: server switches v3 off | `v3_disabled` sends the post back to the queue for v2. |
| Site switch off; Regulated business without a disclaimer | The wizard shows v2, with no console errors. |
| PHP notices and console errors | None from the v3 code paths. An old `tinymce is not defined` error on admin screens was fixed. The legacy project builder (shared by v2 and v3 Submit) still logs older warnings (undefined keys in `builder.php`, `api.php`, `dashboard.php`, `functions.php`); they predate v3 and are left as they were. |

### 10-keyword runs
The ten keywords in `docs/content-v3-test-keywords.json` ran through the full chain twice on 15 November, with keyword data off and then on. This is the same code as the admin tester: no credits, real model and DataForSEO costs, and no research (no Perplexity key yet). The review sheet with both runs side by side, each with its "What to check in testing" list, is at S `data/v3-runs/2026-11-15T10-52-20-207Z/review.html` (git-ignored, local).

| | Keyword data off | Keyword data on |
|---|---|---|
| All checks passed | 7 of 10 | 7 of 10 |
| Failures | 3 × "regulated sources" (the 3 regulated topics: no research without the key), 1 × quick answer length | 2 × "regulated sources", 2 × question headings |
| Keyword data used | not requested | 9 of 10 (one DataForSEO lookup hit the 8 s limit and fell back, as designed) |
| Cost per article, Small | $0.10–0.18 (median $0.16) | $0.09–0.21 (median $0.13) |
| Cost per article, Medium | $0.12–0.22 (median $0.14) | $0.12–0.27 (median $0.21) |
| Cost per article, Large | $0.19 | $0.21 |
| Time per article | 61–134 s | 81–194 s |

- **Length now lands in range.** The Large article had 2,420 words with keyword data off and 2,512 with it on (range 2,400–3,600). Before the calibration (decision A1) the Writer delivered about 55% of its Large targets. Medium articles were 1,254–1,607 words.
- **Regulated topics need the Perplexity key.** Without research there are no sources to link, so these posts fail "regulated sources". They are saved for review, as designed, until the key is added.
- **Prompt caching works.** Every Writer call after the first reads its 8,399-token system prompt from the cache.



---

## 15. Switching on, switching off, rolling back

- **On:** follow section 12, steps 1–6.
- **Off for everyone:** unset `CONTENT_V3_ENABLED` on Render. Within 5 minutes every site uses v2. Nothing is lost; open v3 wizards finish on v2.
- **Off for one site:** switch off "Use Content v3" in its Settings.
- **Undo the migrations** (only if v3 is abandoned): `npm run --silent migrations:print -- --down` prints the reversal SQL, newest first.
- **Undo a prompt:** select the `pre-v3` version in the admin panel's v3 prompts page.
