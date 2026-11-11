# Keyword Finder data → Content v3: research report (2026-11-08)

## Context

**What was asked.** Should Keyword Finder data (search volume, keyword difficulty, intent, related keywords) feed the Content v3 chain (Title → Research → Planner → Writer, plus a DataForSEO SERP lookup)? If so, how? The comparison is against the design in the drafts doc.

**What the code says first.**

- **There is no Keyword Finder** of the kind described. The plugin has a *Keyword Generator*: gpt-4o turns one seed into up to 30 keyword strings. It returns no metrics, has no single or bulk mode, takes no town, and charges nothing.
- **The drafts' v3 chain is not built yet.** There is no Title or Planner prompt, no Research call, no SERP lookup, no validator and no `recent_headings`.
- **No code calls DataForSEO**, in either repo, on any branch (all 41 plugin branches and all 3 server branches on GitHub were checked).
- Production runs **v2**. The current "v3" is an admin-only test endpoint that runs intelligence → writer → humanize.

So every option below is new code. The question is really which of these to build, and in what order. Read-only research; nothing was changed.

**Path prefixes**

| Prefix | Location |
|---|---|
| `P:` | Plugin, `/home/muhammad-waleed-hassan/Improve-SEO/improveseo-wppusher` (branch `Nov-03`) |
| `S:` | Server, `/home/muhammad-waleed-hassan/SEO-admin-server/imporve-seo-admin-server-main` (branch `main`, the deploy branch) |
| `D:` | Drafts doc, `/home/muhammad-waleed-hassan/Downloads/content-v3-prompt-drafts.md`. It is **not** in plugin `docs/`, which holds only BUILD.md, DASHBOARD_HELP_SUPPORT.md, RENAME-RUNBOOK.md and pel-escaping.patch. |

**UNVERIFIED** means the claim could not be confirmed in code.

---

## 1. Current-state map

### 1.1 Keyword Finder: today's Keyword Generator

**Where it lives**
- Menu "Keywords Generator Tool" at `P:includes/menus.php:96`, hidden from the sidebar at :158-161.
- Page: `P:features/keyword.php:10-12`.
- View: `P:views/features/keyword.php`: seed input :58, results :62, `generate()` :89-174.
- Entry card: `P:views/lists/create-choice.php:30`.

**Provider and endpoint**
- The browser calls `POST https://imporve-seo-admin-server-nzbm.onrender.com/api/v1/generate/generatekeyword` directly (`P:views/features/keyword.php:122`), with body `{seed_keyword, count: 30}` (:137-140).
- The server route is `S:src/routes/generation.routes.ts:1193-1316`. Its prompt is inline (:1219-1224) and asks for "long-tail keyword variations … mix informational, commercial and question-style search intents". `count` is clamped to 1–50 (:1211).
- Upstream is OpenAI Chat Completions via `chat()` (`S:src/services/openai.ts:9-28`). The model is `OPENAI_MODEL || 'gpt-4o'` (:7). No max_tokens or temperature is set, the timeout is 60 s (:22), and there is no retry.
- **There is no DataForSEO, no Labs and no Google Ads call.**

**Fields returned**
- `{success, data: {generation_id, seed_keyword, keywords[], total_count}}` (`generation.routes.ts:1293-1301`). The plugin uses only `keywords` (`P:views/features/keyword.php:142-143`).
- **No volume, difficulty, intent or CPC.**

**Modes and location**
- No single or bulk mode, and no town input; the server accepts an optional `niche`, which the plugin never sends (:1199).
- A second copy of `generate()` sits at `P:modules/GenerateAIpopup.php:557-640`. It is reachable only through the dead `create_new_project` option (`P:views/posting/ai_multi_post_form.php:275`, a file nothing renders).

**Storage**
- Plugin table `wp_improveseo_lists (id, name VARCHAR(50), list LONGTEXT, size, created_at)` (`P:includes/installer.php:277-295`). Rows are inserted by AJAX `sw_saved_search_results_keyword` (`P:improveseo.php:1432`, handler :1534-1629), which has no nonce or capability check.
- No transients and no post meta.
- Server: a `user_generations` row of type `keywords` with `billed {cost:0}` (`generation.routes.ts:1254-1285`), plus `keyword_daily_usage` (`S:src/migrations/019-keyword-daily-usage.ts:28-36`).

**Credits**
- Free since commit d6cc207 (2026-10-12): the route has only `validateApiAccess` (:1193), and published pricing shows `keywords: 0` (`S:src/services/credit-pricing.ts:75-77`).
- `creditsPerPiece()` still counts 1 keyword credit (`S:src/config/credits.config.ts:194-199`).
- The cap is runs per UTC day: free 10 / basic 20 / starter 50 / pro 50 (`S:src/config/keyword-limits.config.ts:36`). Over the cap the server returns 429 `KEYWORD_DAILY_LIMIT_REACHED` (`generation.routes.ts:1162-1172`). If the check itself fails, the run is allowed (:1139-1156).

### 1.2 Content generation today

**Production: v2.** Title is two OpenAI calls through `/generate/auxiliary`; the article is the v2 Claude pipeline.

| Button | Plugin path | Server call | Model / max_tokens |
|---|---|---|---|
| Generate title (`#reload`, `P:views/GenerateAIpopup/GenerateAIpopuphtml.php:1005-1006`) | `generateAITitle()` → AJAX `getGPTdata` (`P:modules/bulk_AI_post_function.php:2800`). It calls `getAudienceData()` (:2835), sets the 30-day `AudienceData` cookie (:2853), then `generateTitle()` (:2856). | `POST /api/v1/generate/auxiliary` twice: `type:'audience_data'` (`S:src/routes/generation.routes.ts:1447-1449`), then `type:'title'` (:1453-1463, **today's title prompt, hard-coded, with no setting name**). Rate-limited to 120/min (:35-39). | gpt-4o via `chat()`, no max_tokens set |
| Approve title (`#checkbox_need`, :1008-1011) | Enables Next in the browser only | none | n/a |
| Generate AI Post (`#generateapivalue`) | AJAX `getaaldata` (`P:modules/single_AI_post_function.php:15-203`) → `createAIpost2` (:392) | `POST /api/v1/content-v2/article` (:411-412). Credits are reserved before the call and refunded on failure (`S:src/routes/content-v2.routes.ts:142-259`). | see the v2 pipeline below |
| Meta | Plugin auxiliary `meta_title` / `meta_description`, up to 3 re-prompts (`single_AI_post_function.php:220-294`) | `/generate/auxiliary` (`generation.routes.ts:1441-1446`, 1481-1495) | gpt-4o |
| Regenerate | Same v2 call. The payload drops `target_key` and `custom_instruction` (`single_AI_post_function.php:415-433`), so it is priced as a first generation and the instruction is lost. | v2 | v2 |

**v2 pipeline** (`S:src/services/claude-content-generator.service.ts`)
1. Intelligence: `claudeJSON`, max_tokens 1800 (:280).
2. Writer: `claudeText`, max_tokens 6000 / 8000 / 12000, `anthropicWriteModel` (:301-304).
3. Humanize: same budget, `anthropicWriteModel` (:315-320).
4. `recoverMeta`: 900 (:502-505), only when needed.
5. `regenerateMetaField`: 300 (:550-553), up to 3 tries per field.

**Shared wrapper** (`S:src/services/claude.service.ts`)
- `cache_control: ephemeral` goes on every system block (:56-59).
- SDK `maxRetries: 4` (:23). The writer streams (:66-73).
- Models (`S:src/config/config.ts`):
  - `ANTHROPIC_MODEL || 'claude-opus-4-8'` (:13)
  - `ANTHROPIC_WRITE_MODEL || 'claude-opus-4-8'` (:15)
  - The local `S:.env` sets `ANTHROPIC_MODEL=claude-sonnet-5`. **Production values are UNVERIFIED.**
  - `config.ts:10` defaults `openaiModel` to `gpt-4o-mini`, but `openai.ts:7` defaults to `gpt-4o`.

**The content_v3_\* settings are server-side**, in Mongo `PromptTemplate` / `PromptVersion` (`S:src/models/prompt.models.ts:9,50,88-89`). They are defined in `V3_PROMPT_DEFS` (`S:src/services/claude-content-generator-v3.service.ts:381-412`) and seeded by `POST /api/v1/content-v3/prompts/seed` (`S:src/routes/content-v3.routes.ts:208-222`; `S:src/services/prompt-seed.v3.ts:25-79`). Whether they are seeded in production is UNVERIFIED.

| Setting | In-code default | Used by |
|---|---|---|
| `content_v3_intelligence` | `INTEL_SYSTEM` :243-245 (46 words). Its JSON shape includes `lsi_keywords` "8-12 closely related phrases" (:761). | Phase 1, `claudeJSON`, 1800 tokens (:511-515) |
| `content_v3_system` | `GEN_SYSTEM` :247-333 | Writer, `claudeText`, 6000 / 8000 / 12000, write model (:555-562) |
| `content_v3_humanize` | `HUMANIZE_SYSTEM` :335-356 | Phase 4 (:571-587) |
| `content_v3_blueprint_directives` | JSON from `S:src/services/content-blueprint.ts:219-246` | Code blueprint (:525-542) |
| `content_v3_seo_rules` | :358-360: "main keyword … first 100 words. Weave in 3-4 of the audience's real phrases. Do not repeat any phrase more than twice." | Appended to the writer user message (:825) |
| `content_v3_business_context` | :370-377 | Writer user message (:810) |

**v3 call order** (`generateContentV3`, :473-728)
1. Load the 6 prompts (:485-492).
2. Intelligence (:511-515).
3. Blueprint, no AI (:525-542).
4. Writer (:556-562).
5. Humanize (:571-587).
6. `recoverMeta`, max_tokens 900 (:589-593, :873-915).
7. Meta length fixes, 300 each, in parallel (:596-636, :917-946).
8. Post-process and schema (:643-674).

**Where v3 is used**
- The writer's user message passes "LSI phrases to weave in naturally" taken from the model-invented `lsi_keywords` (:791). This is the pattern Option C would bring back.
- v3 is reachable only through `POST /api/v1/content-v3/article/test` (requireAdmin, no credits; `content-v3.routes.ts:161-200`). The file header says "The plugin continues to call /api/v1/content-v2/article throughout" (:7-10).
- The admin frontend tester calls only the v2 test route (`/home/muhammad-waleed-hassan/SEO-admin-frontend/improvese0-admin-frontend/src/services/api.ts:350`).

**Bulk**
- WP-Cron runs every 120 s (`P:improveseo.php:466-478, 520-535`) and processes **one** task per tick (`LIMIT 1`, `P:modules/bulk_AI_post_function.php:665-667`).
- Per post: auxiliary audience data, then auxiliary title (:1806-1830), then the image, then `/content-v2/article` (:899, :2046-2047).

### 1.3 DataForSEO today

**There are no call sites.** No client, no credentials env, and no Live or Standard choice. Both repos were grepped across every branch for `dataforseo`, `serp`, `people_also_ask`, `related_searches` and `search_volume`.
- The only "Google Suggest" references are stale comments (`P:views/GenerateAIpopup/GenerateAIpopuphtml.php:3320, 3664`) and a server comment about old plugins (`generation.routes.ts:1322-1325`).
- The closest pattern to copy is the axios helper in `S:src/services/openai.ts:14-24`: 60 s timeout, no retry.
- `S:src/middleware/rateLimit.middleware.ts:1-17, 59-90` is in-memory and per process.

### 1.4 Handoff today

- **Into the single wizard: none.** `#seed_keyword` (`GenerateAIpopuphtml.php:772-773`) is pre-filled only when `?from=onboarding`, which builds "service in city" from options (:747-760). Nothing reads `$_GET['keyword']`.
- **Into bulk: through a saved list only.** Keyword Lists offers "Create Bulk Project From Keyword List", which links to `create_post_bulk&keyword_list=<id>` (`P:views/lists/index.php:227-232`). The wizard pre-selects that list (`P:modules/GenerateAIpopup.php:214-223`) and loads its keywords (`GenerateAIpopuphtml.php:4575-4656`).
- The generator's save popup offers only Close or View Lists (`P:assets/js/wt-script.js:261-297`), even though the save handler returns the new list id (`P:improveseo.php:1613-1623`).

**Natural places for a handoff**
1. A per-keyword "Write a post" link that opens `create_post_single&keyword=…&keyword_list=<id>`, read in the pre-fill block at :747-760.
2. A "Create bulk project" button in the save popup (`wt-script.js:271-297`).

**What the plugin does not store**
- **No per-post seed keyword.** A grep for `update_post_meta(…seed|keyword…)` finds nothing. The overlap check would need it.

---

## 2. Gap list: what D assumes vs what exists

| D assumes | D ref | Exists? |
|---|---|---|
| Title prompt (#3), JSON output, 3 attempts, 70-char check | D:50-157 | **No.** Today it is a hard-coded gpt-4o string (`generation.routes.ts:1453-1463`), returns plain text, and has no length check (titles are 80-100 characters by its prompt). |
| SERP lookup (DataForSEO Live, advanced, depth 10, PAA click depth 1, thin check, national fallback, merge with scope labels) | D:127-140 | **No.** No DataForSEO client or credentials. |
| People Also Ask / related-search parsing, `scope` labels | D:113-118, 133 | **No.** Field names are an open "Confirm" (D:1217). |
| Town detection in the seed; small-town fallback | D:132-134, 1218-1219 | **No.** |
| Research query on the Perplexity Agent API | D:161-243 | **No.** There is no Perplexity code. The model is an open question (D:1225). |
| Planner (#2) replacing `content_v3_intelligence` | D:247-347 | **No.** The intelligence step exists but is a different contract: it returns invented `lsi_keywords` and `faq_questions` (v3 :752-763). |
| Planner validator (JSON, size floor, FAQ 1-5, no duplicates, ≤2 owner questions, 3 attempts) | D:336-338 | **No.** |
| Writer draft 2 + six rules blocks; `===WRITER_JSON===`; `closing` tag | D:351-499 | **No.** The live writer uses `===META_JSON===` (v3 :217) and `conclusion` / `whats-next` tags (:312-321). |
| Writer validator (items, summary, FAQ count, heading rules, keyword in first 100 words, word count) | D:489, 1031 | **No.** These are prompt text only; there is no code check (`S:src/services/content-postprocess.ts:172-194` only post-processes). |
| Blueprint per D (types, picks, history checks, structure fingerprint) | D:501-649 | **Partial.** Blueprint exists (`content-blueprint.ts:143-197`) but there is no history check or fingerprint. |
| `recent_headings` | D:535, 1240 | **No.** Nothing in either repo. |
| Niche profile, proof level, regulated list | D:813-956 | **No.** |
| Cache search data and research for Regenerate; delete on wizard exit | D:138, 234, 1251 | **No.** Regenerate re-runs everything. State is DOM + cookie (`bulk_AI_post_function.php:2853`); only the blueprint seed is stored (`content-v2.routes.ts:220`). |
| Prompt caching with identical system prompts | D:485 | **The mechanism exists** (`claude.service.ts:56-59`). Today's v2 writer system prompt is about 566 words (~750 tokens), under the 1,024-token minimum for Opus 4.8 / Sonnet 5 (assumption A2), so it probably does **not** cache (UNVERIFIED; check `usage.cache_read_input_tokens`). |
| Token/cost logging | D:138, 147 | **Partial.** Usage is returned per step but never stored (`content-v2.routes.ts:217-222`). The stored model is `ANTHROPIC_MODEL`, not the write model. |
| Paid v3 endpoint + plugin wiring | D:46 | **No** (`content-v3.routes.ts:7-10`). |
| The plugin finds the FAQ in v3 output | D:487 | **Breaks.** `improveseo_normalize_faq_markup` matches a literal FAQ heading (`P:includes/functions.php:1897`), which v3 bans (v3 :237-239). |
| Keyword Finder with volume / difficulty / intent | (premise) | **No.** gpt-4o strings only (§1.1). |
| 10 test keywords | D:133, 900, 1247 | **Not written down** in D or either repo (UNVERIFIED). |

---

## 3. Options compared

Every option assumes the D chain is built first. Before then, the only place keyword data could go is v2 intelligence, and that work would be thrown away.

### Option A: handoff only

**What changes**
- Keyword Finder gains metrics (see the provider section in §7) and stores them.
- The single wizard reads `keyword` and `keyword_list` params and shows "US monthly searches · difficulty · intent" under the seed field.
- Bulk gets a direct "Create bulk project" button.
- No prompt changes.

**Files**
- `P:views/features/keyword.php` (UI)
- `P:improveseo.php:1534-1629` (save metrics)
- `P:includes/installer.php:277-295` (new nullable column `metrics LONGTEXT`, via dbDelta, additive)
- `P:views/GenerateAIpopup/GenerateAIpopuphtml.php:747-760` (pre-fill and display)
- `P:assets/js/wt-script.js:271-297` (bulk button)
- `S:` new metrics endpoint (§7 step 3)

**Contract, Planner, validator:** no change. **Caching:** no effect.

**Fallback:** with no metrics, the wizard shows nothing.

**Regenerate:** not relevant (display only).

**Value:** helps the user pick keywords. Article quality does not change.

### Option B: handoff + Title and Planner enrichment (Writer unchanged)

**Everything in A, plus:**
- **New tag `<keyword_data>`** in the Title and Planner **user** messages. It holds:
  - seed volume, difficulty and intent;
  - related keywords with volume;
  - a `scope="local|national"` label on every item.
  - The Title gets the seed plus the **10** closest variants; the Planner gets the seed plus the **top 30** by volume. Keeping the Title input short limits volume-chasing titles.
- **New optional tag `<recent_posts>`** for the Planner: at most 5 candidate overlaps that code has already found.
- **Planner rules added:** rank body and FAQ questions by substance first and demand second; use intent only as a tie-breaker for article type; judge overlap.
- **New Planner JSON fields:** `demand_notes`, `overlap`.

**Files (new server code; these modules do not exist yet)**
- `S:src/services/dataforseo.service.ts`: shared client, also used by the D SERP lookup.
- `S:src/services/keyword-data.service.ts`: lookup, normalise, apply scope rules, render the tag.
- The D Title and Planner builders: user-message builders in the style of `buildIntelligenceUser` (v3 :739-764).
- New Mongo prompt versions for Title and Planner, created through the existing admin routes (`generation.routes.ts:216-394`). Older versions stay, so rollback is a version switch.
- Plugin: hidden `keyword_list` and `keyword` fields in `#popup_form`; post meta `_improveseo_seed_keyword` and `_improveseo_keyword_data`.
- Bulk: `generateBulkAiContent` (`P:modules/bulk_AI_post_function.php:648`).

**Validator (Planner):**
- Every `demand_notes[].question` exactly matches a `body_questions` or `faq_questions` item.
- Every `demand_notes[].keyword` exactly matches a `<keyword_data>` item.
- `overlap` is non-null only when `<recent_posts>` was sent, and `overlap.recent_title` must match one of them.
- Code, never the model, looks up volume and scope from the matched keyword, so no invented numbers reach the logs or the UI.
- A failure counts as one of D's 3 attempts (D:336).

**Caching:**
- The new rules go in the **system** prompts and are the same for every article; per-article data stays in the user message, so caching is unaffected.
- Side benefit: the Title system prompt is about 756 words (~1,000 tokens, borderline under the 1,024 minimum). About 150 added words push it over, so it starts caching. The Planner (~935 words, ~1,250 tokens) already caches.
- Note: one prompt is shared by every customer, so the cache is warm across sites whenever traffic is steady.

**Fallback:** if the lookup fails or times out (8 s), the tag is left out. Per D's data contract, "A missing tag means that information was not given" (D:59), so the chain runs exactly as D. Each new rule reads "if `<keyword_data>` is given". Bulk never resets a task to Pending for a keyword failure.

**Storage for Regenerate:**
- Wizard: keyword data is stored with search data and research under `target_key` (location still to decide, see §10).
- Regenerate title, Regenerate content and Change image all reuse it with no new call. A new seed keyword means a new lookup.
- On publish it is copied to post meta.
- Bulk: a nullable `keyword_data` column on `improveseo_bulktasksdetails`. Where that table is created is UNVERIFIED; it is not in `P:includes/installer.php`.

**Value:** more evidence for question order and type ties, and real-search wording for `their_words`. It comes in through the Planner, which already filters for quality.

### Option C: B plus a Writer secondary-keyword list

**Rejected; the conflicts are confirmed in D.**
- `<rules_seo>` KEYWORD (D:970-973) limits keywords to `<seed_keyword>` "only where a person would naturally say it" plus "up to 4" `their_words` words, "do not force them in". A 20-30 item list contradicts this.
- D:1024 explains why the old phrase rule was dropped: it "pushed the model toward odd synonyms". A secondary list causes the same thing.
- `<rules_human_style>` PLAIN WORDS (D:1154-1156): "Call the same thing by the same name every time." Keyword variants push the model to rotate names.
- NO PADDING (D:1004-1005) and FOLLOW-UP QUESTIONS (D:990-991): a list encourages sections written to cover keywords rather than questions.
- Today's v2/v3 already do this ("LSI phrases to weave in naturally", v3 :791), and D removes it on purpose.
- Topic coverage already reaches the Writer the right way: Planner body and FAQ questions go into `<blueprint>`, and up to 4 `their_words` words follow `<rules_seo>`.

**Cost if built anyway:** about 400 more Writer input tokens. It would not break caching, since the data goes in the user message.

| | A | B | C |
|---|---|---|---|
| Prompt changes | none | Title + Planner system rules; user tags `<keyword_data>`, `<recent_posts>` | B + Writer list + rules |
| New Planner fields | none | `demand_notes`, `overlap` | same as B |
| Validator | none | 3 exact-match checks | B + keyword-use checks |
| System-prompt caching | n/a | kept (helps the Title prompt) | kept |
| Fails safe | yes | yes (tag omitted) | yes |
| Quality risk | none | Title chasing volume; ranking fixed on national figures (mitigated in §6) | stuffing, synonym rotation |

---

## 4. Recommendation

**Build A now; build B together with the D Title and Planner, behind a flag, and keep it only if the A/B test passes. Reject C.**

**Refinements to B**
1. **Data source in v1 is national only.**
   - Use DataForSEO Labs `related_keywords/live` per article. Labs is **country-only** (assumption A6).
   - Do **not** make a per-article Google Ads town-volume call. At about $0.09 per call it is 5-6× the Labs call, and small towns often come back empty (UNVERIFIED).
   - Show town-level figures only when the Keyword Finder already fetched them in bulk, where one Ads task covers up to 1,000 keywords.
2. **Prefetch** the lookup at D's "Keyword typed: keyword check" step (D:46), so it adds no wait to Generate title. Allow at most 3 lookups per wizard session.
3. **Let `their_words` draw on `<keyword_data>`** (D:283). This is the only way keyword data reaches the Writer, and it stays within `<rules_seo>`'s "up to 4".
4. **Overlap is a hybrid.** Code finds candidates (recent posts' stored seed keywords that appear in this seed's related keywords, or that share title tokens), then the Planner judges whether the intent is the same. Models are unreliable at matching sets of strings; code is exact.
5. **Keyword data never becomes a question source.** The D rule stands: questions come only from People Also Ask, related searches and research (D:273). Whether question-form related keywords should count is an open question (§10).

**Why:** the case (a) cost is about $0.007 per article (§5), and it fails safe. Demand evidence fills the one input the Planner lacks: it can see *which* questions exist but not how much each one matters. The risks (volume-chasing titles, national figures driving local posts) are handled by prompt rules and the scope labels. A/B tests B against D's "What to check" lists plus a demand-coverage metric.

---

## 5. Cost and time

### Pricing assumptions (to verify; none of these are in either repo)

The only cost figure in either repo is "$0.0129 per credit … a medium article at $0.129" (`S:docs/DEPLOY_RUNBOOK.md:52-55`). Credits per article are 6 / 10 / 14 (runbook :24-38); production values are UNVERIFIED.

| ID | Assumption | Source |
|---|---|---|
| A1 | Cheap model = `claude-sonnet-5` (local `S:.env`). Writing model = `claude-opus-4-8` (code default, `config.ts:15`). `OPENAI_MODEL` = gpt-4o (`openai.ts:7`). **Render values are UNVERIFIED.** | code / env |
| A2 | Opus 4.8: $5 in / $25 out per MTok. Sonnet 5: $2 / $10. Cache write 1.25×, read 0.1×. Minimum cacheable prompt 1,024 tokens on both. | claude-api skill reference (cached 2026-10-06), not the repo |
| A3 | gpt-4o: $2.50 / $10 per MTok. The page is marked deprecated. | [OpenAI GPT-4o model page](https://developers.openai.com/api/docs/models/gpt-4o) |
| A4 | SERP Live advanced: $0.002 per call + $0.00015 per PAA click. Assuming 4 clicks: $0.0026 per call, $0.0052 for a thin keyword. | D:140 |
| A5 | Labs Live: $0.012 per task + $0.00012 per item, so `related_keywords` with 30 items ≈ $0.0156. Sources disagree. Whether the response includes the seed's own metrics is UNVERIFIED; if not, add `keyword_overview` (≈ $0.0121). | [Labs Google pricing](https://dataforseo.com/pricing/dataforseo-labs/dataforseo-google-api), [related_keywords docs](https://docs.dataforseo.com/v3/dataforseo_labs-google-related_keywords-live/) |
| A6 | Labs supports **countries only**. Google Ads `search_volume` supports cities: Live $0.09 per task, Standard $0.06, up to 1,000 keywords per task, "up to 7 s" average. | [Labs countries only](https://dataforseo.com/help-center/why-does-dataforseo-labs-api-support-only-countries), [Google Ads pricing](https://dataforseo.com/pricing/keywords-data/google-ads), [City search volume](https://dataforseo.com/help-center/sv-for-city-or-coordinates) |
| A7 | Perplexity web search $2.50 per 1,000 calls, plus model tokens **R** (unknown). | D:234, D:1225 |
| A8 | Tokens are estimated at ~1.3 per word. System prompts by word count: Title 756, Planner 935, Writer core + 6 rules blocks 4,041 (D sections). Article lengths are D's midpoints, 900 / 1,800 / 3,000 words (D:574-577). Writer output ≈ 1,600 / 2,950 / 4,800 tokens. | estimate |

### Per article, medium size (small / large in brackets)

| Flow | Calls | $ per article |
|---|---|---|
| **Current production (v2)** | audience (gpt-4o) $0.0033 · title (gpt-4o) $0.0014 · intelligence (Sonnet 5) $0.0067 · writer (Opus 4.8) $0.083 · humanize (Opus 4.8) $0.084 · meta (gpt-4o, ×2) $0.003 | **≈ $0.18** [S $0.11 / L $0.28]. The runbook anchor is $0.129; the gap comes from the writer and humanize model mix (A1). |
| **D chain, no keyword data** | SERP $0.0026 (thin $0.0052) · Title (Opus) $0.012 · Research $0.0025 + R · Planner (Sonnet) $0.0137 · Writer (Opus) $0.113 uncached / $0.089 with the system prompt cached | **≈ $0.144 + R** [S $0.110 / L $0.191]. **Excludes Cleanup #11, Repair #12 and Meta #13, which are not drafted.** If Cleanup costs about what humanize does today, add ≈ $0.084 [S $0.044 / L $0.14]. |
| **A**, from Keyword Finder (a) | none | **+$0** |
| **A**, typed keyword (b) | none, or one Labs call if the wizard should display metrics | **+$0**, or +$0.016–0.028 |
| **B (a)**, data reused | Title +~500 input tokens (Opus) $0.0025 · Planner +~1,300 input (Sonnet) $0.0026 · +150 output $0.0015 | **+≈$0.007** (~4% of current medium) |
| **B (b)**, fresh lookup (national) | the above + Labs `related_keywords` $0.0156 (+ `keyword_overview` $0.0121 if needed) | **+$0.023 to +$0.035** |
| B (b) + town volume (not recommended in v1) | + Google Ads Live $0.09 | +$0.113 to +$0.125 |
| **C** | B + Writer +~400 input tokens (Opus) $0.002 | (a) +$0.009 · (b) +$0.025–0.037 |

### Bulk, per 100 medium posts

| Flow | $ per 100 | Notes |
|---|---|---|
| Current v2 | ≈ $18 | Serial: 1 post per 120 s cron tick, so at least about 200 min wall time (`P:improveseo.php:473`; `bulk_AI_post_function.php:665-667`) |
| D chain | ≈ $14.4 + 100R (≈ $12.0 + 100R with the writer cache warm); Cleanup/Meta not included | Serial cron keeps the cache warm only if one post takes under 5 min per tick |
| A (a) / (b) | +$0 | — |
| **B (a)**, list metrics already stored | **+$0.66** tokens | The Keyword Finder bulk fetch was paid earlier: 100 × $0.0156 = $1.56 for per-seed related keywords, + one `keyword_overview` batch for the seeds ($0.012 + 100 × $0.00012 = $0.024), + optionally one Ads task for town volumes ($0.06–0.09) |
| **B (b)**, lookup during generation | **+≈$2.25** (+$0.06–0.09 for one town task covering the whole list) | 100 × $0.0156 + $0.024 + $0.66 |
| C | B + $0.20 | — |

### Latency (Generate title)

Assumed latencies, UNVERIFIED: SERP Live advanced with PAA clicks ≈ 3–8 s; Labs Live ≈ 1–3 s; Google Ads Live ≈ 7 s average (A6); Title call ≈ 3–6 s.

| Path | Added wall time |
|---|---|
| (a) from Keyword Finder | 0 s (no call) |
| (b) prefetched on keyword typed (recommended) | 0 s at Generate title |
| (b) in parallel with the SERP lookup (`Promise.allSettled`) | ≈ 0 s, since Labs is usually faster than SERP. A thin keyword's second SERP call is sequential, which gives the lookup even more time. With Ads: 0–4 s. |
| (b) sequential | +1–3 s (Labs) · +~7 s (Ads) · capped at 8 s by the timeout |
| Approve title (Planner) | +~1 s for ~150 more output tokens; no new call |
| Generate AI Post | unchanged; the Writer is not touched |
| Bulk | +1–3 s per post, which doesn't matter at a 120 s tick |

---

## 6. Changes as a diff against the drafts doc

Lines with `+` are additions; `~` marks a changed D line.

**Title, system prompt** (insert after D:67, the scope="national" bullet)
```
+- <keyword_data> gives search demand for <seed_keyword> and close variants. Use it only to choose between wordings that mean the same thing: if a variant with clearly higher volume reads naturally, you may use its wording, keeping the meaning of <seed_keyword>. Volume never chooses the angle. Never put a number from <keyword_data> in the title.
+- In <keyword_data>, scope="national" figures are nationwide demand. Never read them as demand in <target_town>. A missing local figure means it was not measured, not that nobody searches.
```
**Title, FORM** (D:72)
```
~- Question form when the searcher is asking: <seed_keyword> is a question, or most of <people_also_ask> and <related_searches> are questions on this topic, or most of the highest-volume items in <keyword_data> are questions. Phrase it the way people ask it.
```
**Title, data template** (after D:118)
```
+<keyword_data location="United States" source="keyword_finder|lookup" fetched="YYYY-MM-DD">
+  <seed scope="national" volume="..." difficulty="..." intent="informational|commercial|transactional|navigational">...</seed>
+  <seed scope="local" location="Española, New Mexico" volume="...">...</seed>   (only when a town figure above zero exists)
+  <related_keywords count="...">                                                 (Title: up to 10; Planner: up to 30)
+    <item scope="national" volume="..." intent="...">...</item>
+  </related_keywords>
+</keyword_data>
+(Attributes with no value are left out, never sent as 0. Every item carries scope.)
```
**Title, developer notes** (after D:148)
```
+- <keyword_data> comes from the Keyword Finder record when the user arrived from it, otherwise from one Labs lookup prefetched at "keyword typed". It is left out if the lookup fails or takes more than 8 s. The town is stripped from the seed for the lookup, as in the national SERP call (D:134).
```

**Planner, system prompt**
```
D:259 ~ Build every part of the plan from <title>, <search_data>, <research>, <keyword_data> and the business details (...).
after D:267 + If two types fit equally, the seed's intent in <keyword_data> may break the tie: commercial or transactional leans to Cost or Decision guide, informational to Question or Topic guide. The title's promise always comes first. Intent is measured nationwide.
after D:273 + Rank by substance first, then demand. Of two questions with similar substance, the one matching a higher-volume item in <keyword_data> ranks higher. Compare national with national and local with local, never across. A low or missing volume is never a reason to drop a question with substance. Never add a question because of <keyword_data> alone.
+  For each body or FAQ question that matches an item in <keyword_data>, add one demand_notes entry pairing the question's exact text with that item's exact text.
D:283 ~ their_words: up to 8 words or phrases they actually use, taken from the search data, research and <keyword_data>: ...
+7. OVERLAP WITH RECENT POSTS
+Only if <recent_posts> is given. If this article would answer the same main question as one of <recent_posts>, set overlap to that post's title and one line on why. Otherwise overlap is null.
D:304 OUTPUT + "demand_notes": [{"question": "...", "keyword": "..."}], "overlap": {"recent_title": "...", "reason": "..."} or null
```
**Planner, data template** (after D:325)
```
+<keyword_data ...>...</keyword_data>          (up to 30 related keywords, as in the Title)
+<recent_posts>                                 (code sends at most 5 candidates; left out if none)
+  <post date="YYYY-MM-DD" seed_keyword="...">title</post>
+</recent_posts>
```
**Planner, developer notes** (after D:339)
```
+- Validator: every demand_notes.question exactly matches a body or FAQ question; every demand_notes.keyword exactly matches a <keyword_data> item; overlap is only set when <recent_posts> was sent, and recent_title matches one of them. Code adds volume and scope to the log and the UI from the matched item; the model never reports numbers.
+- Shown to the user: an overlap line with Keep / Change keyword. demand_notes are logged only.
+- Candidate overlaps (code): the site's last 20 posts whose stored seed keyword (new post meta `_improveseo_seed_keyword`) appears among this seed's related keywords, or shares at least 60% of its title tokens with this title.
```
**Writer (#10), Blueprint, Rules blocks:** no change. FAQ selection stays "top N of the ranked list" (D:337, 566).

**Data-contract rule for local towns** (new shared note)
```
+Scope rule for keyword figures, mirroring "Never present a national figure as local" (D:1016):
+1. Code sets scope from the location the provider actually used: Labs is always national; a Google Ads call with the town's location code is local.
+2. A local figure is sent only when the town-level call returned a number above zero. Null, zero or missing: leave the local element out. Never copy a national figure into it, and never send volume="0".
+3. A town missing from the provider's location list gets no local call. No nearby-city figure is ever labelled local.
+4. In the UI, label figures "US monthly searches" vs "<Town> monthly searches (Google Ads estimate)".
```

---

## 7. Implementation steps, in order

All changes are additive. No file is deleted; old behaviour stays behind a flag or a prompt version.

0. **Prerequisite (already decided in D, outside this choice):** the DataForSEO SERP lookup, Title route, Research, Planner, Writer draft 2, a paid v3 route and plugin wiring. B depends on Title and Planner existing.
1. **Shared DataForSEO client:** `S:src/services/dataforseo.service.ts`.
   - Basic auth from new env `DATAFORSEO_LOGIN` / `DATAFORSEO_PASSWORD`; follow the axios pattern in `openai.ts:14-24`.
   - Live mode, 8–10 s timeout, 1 retry on 429/5xx/network errors.
   - Log each task's cost and status. One client serves both the SERP lookup and keyword data.
2. **Keyword data service:** `S:src/services/keyword-data.service.ts`.
   - `lookupKeywordData({seed, language, countryCode})` calls `dataforseo_labs/google/related_keywords/live` (limit 30), plus `keyword_overview/live` if the seed's metrics are missing.
   - Normalise the result and apply the scope rule (§6).
   - `renderKeywordDataTag(kd, 'title' | 'planner')`.
   - Unit tests in the existing `*.test.ts` style.
3. **Keyword Finder metrics endpoint** (additive; `/generate/generatekeyword` stays as it is). New `POST /api/v1/keywords/metrics`, `validateApiAccess`, using the same daily allowance logic (`generation.routes.ts:1139-1172`):
   - **Single:** seed → related keywords with metrics.
   - **Bulk:** up to 700 keywords via `keyword_overview`, plus an optional single Google Ads Standard task for town volume.
   - Pricing is an open question (§10).
4. **Plugin Keyword Finder**
   - Show metrics and add an optional town field (`P:views/features/keyword.php`).
   - Save metrics to a new nullable column `metrics LONGTEXT` (`P:includes/installer.php:277-295`, dbDelta) via `P:improveseo.php:1534-1629`. Add a nonce and capability check while touching it; flag this separately.
5. **Handoff (A)**
   - Per-keyword "Write a post" link that opens `create_post_single&keyword=&keyword_list=`.
   - Read those params with `sanitize_text_field` in `GenerateAIpopuphtml.php:747-760`; add hidden fields in `#popup_form` and a metrics line.
   - Save popup gets a "Create bulk project" button (`wt-script.js:271-297`) that opens `create_post_bulk&keyword_list=<id>`, which is already supported (`GenerateAIpopup.php:214-223`).
6. **Server Title and Planner inputs (B)**
   - Accept an optional `keyword_data` object, or the stored record id. Validate it server-side: at most 30 items, numeric fields, stripped text (it comes from the client).
   - Otherwise prefetch: `Promise.allSettled([serpLookup, lookupKeywordData])`, behind env flag `ISEO_V3_KEYWORD_DATA=off|on` (default off).
7. **Prompts (B):** new versions of the Title and Planner prompts with the §6 rules. Create them through the admin routes (`generation.routes.ts:312-394`) and keep the previous versions.
8. **Planner validator (B):** the three exact-match checks; a failure counts as one of the 3 attempts.
9. **Storage**
   - Wizard state under `target_key`: search data, research, keyword data and Planner output (where is a question for Zaid).
   - On publish, post meta `_improveseo_seed_keyword` and `_improveseo_keyword_data`.
   - Bulk: a nullable `keyword_data` column, plus list metrics reuse.
10. **Bulk wiring:** in `generateBulkAiContent` (`bulk_AI_post_function.php:648`) before the title, use list metrics if present, else look them up. On failure, leave the tag out and continue.
11. **Logging and A/B harness:** extend the admin `/content-v3/article/test` (or add a sibling chain-test route) with `keyword_data_mode: off|on` and the diagnostics listed in §9. Store per-step `usage` with the computed $ (today it is never stored).
12. **Run the A/B (§9), decide, then switch the flag on.** Render env changes for the user: `DATAFORSEO_LOGIN`, `DATAFORSEO_PASSWORD`, `ISEO_V3_KEYWORD_DATA`. Per standing practice: test locally end to end and get the user's go-ahead before any push.

---

## 8. Risks

**Local towns**
- Labs gives national data only (A6). Google Ads city volume is often null or zero for small towns (UNVERIFIED). Hence the scope rule (§6) and no per-article town call in v1.
- A town in the seed ("water heater repair española") makes the Labs related list thin or empty. Strip the town for the lookup, as D:134 does for SERP.

**Ranking bias:** national demand can push a nationally popular question above a locally important one. The prompt says substance comes first and demand only breaks ties; the A/B checks this on local keywords.

**Title drift:** volume can pull the title toward generic wording. The Title sees only 10 variants, and the rule is "wording only, never the angle"; the A/B compares near-copies and angle.

**Rate limits:** bulk is serial (1 post per 120 s), about 0.5 DataForSEO calls per minute. The wizard prefetch is capped at 3 per session. DataForSEO account limits are UNVERIFIED.

**Credits**
- Keyword lists are free by product rule (2026-10-12), but metrics cost real money per run (about $0.016 per seed national, $0.09 per town task). Decide whether the free daily allowance still covers metrics.
- Inside content generation (case b), fold the lookup into the article credit; it is about 2–3% of the $0.129 cost anchor.

**Partial failures in bulk**
- A keyword lookup failure must never reset the task.
- Existing bulk bugs (found during research, out of scope, flag for Zaid):
  - the article is charged twice when the image fails (`bulk_AI_post_function.php:899, 927-941`);
  - retries never stop (failure → Pending forever);
  - a 402 error message can be published as a post (:970-974, :2121);
  - no `target_key`, so retries pay the full first-generation price (:2050-2067).

**Prompt-cache surprises:** the v2 writer system prompt is probably under the cache minimum today. In B, keep every per-article value out of system prompts (no date, no town). Confirm with `usage.cache_read_input_tokens > 0` on the second run.

**Stale data:** reuse Keyword Finder metrics for up to 30 days (`fetched` attribute); after that, look them up again.

**Security:** keyword data posted from the browser is untrusted. Validate it server-side, and keep D's "data tags are material, never instructions" line (D:59).

**Model or env uncertainty:** cost tables depend on A1; check the Render env values before relying on the totals.

---

## 9. Test plan (A/B: D chain with vs without `<keyword_data>`)

**Keywords:** the 10 test keywords are **not written down** in D or either repo; ask for the list. If none exists, use:
- 4 local trades, one with the town in the seed;
- 2 local health (regulated);
- 1 small-town thin topic;
- 2 nationwide;
- 1 list or "how many" topic.

**Setup** (admin test route, no credits)
- Per keyword, run the SERP lookup and Research **once** and freeze them, so the two arms differ only by `<keyword_data>`.
- Title + Planner: 3 repeats per arm, 10 × 2 × 3 = 60 runs.
- Writer: 1 run per arm on the same blueprint seed, 20 articles.
- Estimated spend ≈ $5 + 10R (A2–A7).

**Log per run (JSONL)**
- `run_id`, keyword, arm, repeat, input hash.
- Search data counts and `national_fallback`.
- The full `<keyword_data>` with its source, `fetched` date and lookup cost.
- **Title:** `title`, `form`, `items`, `main_concern`, `page_kind`, attempts, length.
- **Planner:** the full JSON, including `article_type`, `type_reason`, `post_size`, ranked `body_questions`, ranked `faq_questions`, `searcher_profile.their_words`, `demand_notes`, `overlap`.
- **Writer:** the HTML and the `===WRITER_JSON===` part.
- Per call: input, output, `cache_read`, `cache_creation` tokens and computed $.
- Latency per step, wall time, and any failure or fallback taken.

**How to judge**
1. **D's checklists**, pass/fail per item, scored blind (arm hidden) by two reviewers: Title D:152-157, Planner D:343-347, Writer D:494-499.
2. **Question-demand coverage**, computed by code:
   - Share of the summed (national) volume of `<keyword_data>` items matched to the questions the blueprint would use (top body questions for the chosen size + FAQ top 3/4/5), divided by the volume matched to any candidate question.
   - For the "off" arm, run the same matcher afterwards.
   - Also: is the highest-demand matchable question included (yes/no)?
3. **Ranking stability:** Kendall tau across the 3 repeats.
4. **Type changes:** count where `article_type` differs between arms, and have a human check whether the change still matches the title's promise.
5. **Overlap:** seed a test site with 3 known overlapping posts and 3 decoys; measure precision and recall of `overlap`.
6. **Local safety:** no local article or UI ever shows a national figure as the town's, and no `volume="0"` is ever sent.
7. **Fallback:** with `DATAFORSEO_PASSWORD` set wrong, the chain completes, the tag is left out, and the run is logged.

**Decision rule (proposed)**
- Adopt B if demand coverage improves on at least 7 of 10 keywords;
- with no increase in D-checklist failures;
- no title becomes a near-copy or loses its angle;
- median added wall time is 1 s or less with prefetch;
- and cost delta is at most $0.01 (case a) / $0.04 (case b).

**End-to-end verification (after implementation)**
- Use the local recipe: server worktree on PORT=3005 with the email flags off; a throwaway mu-plugin rewriting the server host to `172.22.0.1:3005`; driven by the CDP script. The local DB is the production Supabase, so use the admin test route.
- Check:
  - the handoff link pre-fills the seed;
  - Regenerate title and Regenerate content make no second lookup;
  - the second run shows `cache_read_input_tokens > 0` for Title, Planner and Writer;
  - bulk with a metrics-bearing list makes no lookups;
  - a bulk lookup failure does not change the task state.

---

## 10. Open questions for Zaid

**Question**
- Is "Keyword Finder" a planned feature with a spec or branch somewhere? Nothing exists in either repo or on any GitHub branch; today there is only the gpt-4o Keyword Generator. Should it be built on DataForSEO Labs (national) + Google Ads (town)?
- Where should wizard state live: a plugin transient keyed by `target_key`, or a server table? D refers to the "storage section of Single wizard: input fields", which wasn't available.
- Do Keyword Finder metrics stay inside the free daily allowance, given they cost real money per run?
- What are the production values of `ANTHROPIC_MODEL`, `ANTHROPIC_WRITE_MODEL` and `OPENAI_MODEL`?
- Which Perplexity model and effort level (R)? This is already open in D:1225.
- Should question-form related keywords count as a Planner question source alongside People Also Ask, related searches and research?
- Where is the list of 10 test keywords?

**Confirm**
- Labs is country-only; town volume comes only from Google Ads, and small towns often return null.
- `related_keywords/live` returns the seed's own metrics and per-item difficulty and intent (otherwise add `keyword_overview`).
- The DataForSEO prices in A4–A6 and the Live latencies.
- Limits: Title gets 10 related keywords, Planner 30; lookup timeout 8 s; at most 3 prefetches per session; data reused for 30 days.
- The v2 writer system prompt doesn't cache today: is `cache_read_input_tokens` 0 on the v2 test route?

**To build**
- The DataForSEO client (shared with the SERP lookup) and the keyword data service with the tag renderer.
- The Keyword Finder metrics endpoint, UI, and the `metrics` column on `wp_improveseo_lists`.
- Handoff params in the single wizard and the bulk button in the save popup.
- Title and Planner prompt versions with `<keyword_data>` / `<recent_posts>`, and the Planner validator checks.
- Post meta `_improveseo_seed_keyword` / `_improveseo_keyword_data`, and the bulk `keyword_data` column.
- The A/B harness, plus logging per-step usage and $ (usage is never stored today).
- Separate bugs found along the way:
  - Regenerate drops `target_key` / `custom_instruction` (`single_AI_post_function.php:415-433`);
  - the plugin's FAQ detection breaks on v3 headings (`P:includes/functions.php:1897`);
  - bulk double-charges, retries forever, and can publish a 402 message (`bulk_AI_post_function.php:899-974`);
  - `user_generations` logs the wrong model (`content-v2.routes.ts:217-222`).
