# Content v3: what still needs the team lead

Status on 14 November 2026, after the completion and production-readiness pass. This page lists everything in Content v3 that waits on the team lead: requirements that depend on documents we don't have, model-facing wording the code adds that isn't in the design doc, values the doc leaves open, and conflicts in the doc with the resolution the code uses. Each item says where it lives in the code, so it can be changed without searching.

Paths: P = plugin (`improveseo-wppusher`), S = server (`imporve-seo-admin-server`). The design doc is `docs/content-v3-prompt-drafts.md` (identical in both repos).

## 1. The team lead's documents

The completion brief points to the team lead's documents in `docs/lead/`. That folder doesn't exist. The three links provided (two Claude Docs pages and one shared chat) can't be opened from the development machine, and they can't be exported here, so the work went ahead without them.

To add them later, export each document as Markdown, Word or PDF (or copy its text into a file), put it in `docs/lead/` in the plugin repo, and ask for the items below to be built from it.

## 2. Requirements that depend on those documents

| ID | What is missing | What runs today | Where |
|---|---|---|---|
| W1 | Keyword check rules (the "Single wizard: input fields" document) | Only the title checks of W3: not empty, at most 300 characters, keyword sanity | S `src/services/content-v3/planner-fallback.ts` (`checkApprovedTitle`) |
| G4 | Disclaimer text for each Regulated niche | The setting `content_v3_disclaimers` ships empty, so no disclaimer is inserted. A Regulated business stays on v2 until its niche has a text (decision A3, 14 Nov). | S `src/services/niche-profile/niche-profile.default.ts`; P `modules/content-v3.php` (`improveseo_content_v3_business_allowed`) |
| V10 | Humanizer v1.2 style thresholds | Not checked | S `src/services/writer/validator.ts` |
| Doc C size table | Items per size for numbered titles | `<max_list_items>` 15; at most 15 items per numbered title; minimum words per item from the design doc (60 / 40 / 35) | S `src/routes/content-v3-wizard.handler.ts` (`MAX_LIST_ITEMS`), `src/services/content-v3/planner-fallback.ts` |
| Prompt #11 Cleanup and audit | Not drafted | No cleanup pass | none |
| Prompt #12 Repair | Not drafted | Interim U2: one more Writer run with the same inputs (targets rescaled when the draft was too short, decision A1) | S `src/services/writer/article.ts` |
| Prompt #13 Meta | Not drafted | Interim U4: the existing v3 meta wording on the final article, with length enforcement; version label `content_v3_meta_interim@code#…` | S `src/services/writer/meta.ts` |
| Prompt #14 Image | Not drafted | Interim U5: the existing image route, unchanged | P `modules/single_and_bulk_AI_post_function.php` |
| Business details from the website | Not drafted | Interim U6: manual Business details fields in Settings | P `views/settings/index.php` |

## 3. Wording the code sends to the models that isn't in the design doc

Every prompt and text piece that *is* in the design doc is sent byte for byte; tests re-read the doc and compare. The lines below are code-built. Each one either fills a case the doc's single example doesn't show, or was added after the 14 November 10-keyword run to stop a measured failure. **The team lead should approve or replace each one.**

### In `<blueprint>` (S `src/services/blueprint/blueprint.ts`)

| Line | Text | Why it exists |
|---|---|---|
| 568 | `SUMMARY: a quick answer, directly after the introduction: one paragraph of 40 to 60 words. Its first sentence answers the title. This is the only summary: no key takeaways list.` | The doc's example only shows the takeaways form. The last sentence was added because, with the type piece sent whole ("Takeaways carry …"), the Writer added a takeaways list after every quick answer in the 10-keyword run. |
| 573 | `… The first point answers the title. This is the only summary: no separate quick answer.` (added to the doc's takeaways line) | In 2 of 10 runs the Writer added a quick answer after the takeaways. |
| 601–602 | `About N words.` and `About a, b and c words, in order.` | The doc's example only shows the anchor pattern ("About 540 words for the main section, about 270 for each of the others."). |
| 616 | `BODY: the N <items>, at least M words each.` | Numbered titles: the doc says their item count replaces the section count, but gives no BODY line. |
| 628 | `ORDER OF THE LAST TWO SECTIONS: the closing, then the FAQ.` | The doc's example only shows "FAQ, then the closing." |
| 630 | `FAQ: no FAQ section in this article.` | The Planner can return no FAQ questions; see conflict C7. |

### In `<niche_profile>` (S `src/services/niche-profile/niche-profile.ts`)

| Line | Text | Why it exists |
|---|---|---|
| 257 | `Next step (nationwide): soft = …; medium = …. Firmest allowed: …` | The doc's table has a nationwide next step per profile; its example shows only the local line. |
| 258 | `Next step (nationwide): none is usual for this kind of business. Firmest allowed: …` | Profiles whose nationwide next step is empty in the table. |
| 197 | `Local detail matters somewhat here.` / `Local detail matters little here.` | The example shows only "a lot"; the table has three levels. |
| 270 | `none listed for this niche` (in place of the watchlist) | A niche with no watchlist. |

### Data formats inside tags (S `src/services/content-v3-steps/xml.ts`)

The doc names the tags (`<search_data>`, `<research>`, `<keyword_data>`) but not their inner layout. The layout is code-defined in `searchDataBlock` (line 61) and `researchBlock` (line 79), and for keyword data in S `src/services/keyword-data/keyword-data-block.ts`.

## 4. Values the doc leaves open (working values)

| Item | Value used | Where to change it |
|---|---|---|
| Word-target calibration (decision A1) | Small ×1.0, Medium ×1.3, Large ×1.6 on the numbers in the BODY line | env `CONTENT_V3_TARGET_CALIBRATION_JSON` |
| Second Writer run | Skipped when the only error is "regulated sources" and research gave fewer than 3 sources (decision A2) | S `src/services/writer/article.ts` (`skipSecondRun`) |
| Section count | A draw within the size's range, capped at body questions + optional elements, never below the minimum (C12) | S `blueprint.ts` |
| 40% spread rule | Applied once the site has 5 earlier posts of the type; only to dimensions with 3 or more options (C8) | S `blueprint.ts` (`SPREAD_OPTIONS`) |
| Numbered grouping | Items as H3s under 2–3 grouping H2s from 8 items | S `blueprint.ts` |
| Per-niche provider and help phrases | e.g. HVAC → "call a licensed HVAC technician" | registry setting `content_v3_niche_profiles` |
| Perplexity model and effort (R9) | effort `low`, no model set | env `PERPLEXITY_EFFORT`, `PERPLEXITY_MODEL` (Zaid to choose) |
| Towns DataForSEO doesn't list (S3) | Alias list, then coordinates, then one country call | env `SEARCH_DATA_TOWN_ALIASES_JSON` (Zaid to confirm) |
| Free-step daily allowance | title 60 + 5 per article, town check 100 + 10, approve 30 + 3 | env `CONTENT_V3_FREE_STEP_LIMITS_JSON` |
| Cost ceiling (warning only) | Small $0.25, Medium $0.35, Large $0.50 per article | env `CONTENT_V3_COST_CEILING_USD_JSON` |
| Request time limits | article 480 s, approve 360 s, title 200 s | env `CONTENT_V3_*_DEADLINE_MS` |

## 5. Conflicts in the design doc, and what the code does

| # | Conflict | What the code does |
|---|---|---|
| C7 | No FAQ questions: the doc says "fewer if fewer exist", but the core's STRUCTURE always has an FAQ | No FAQ section when the Planner has none, with the line in section 3 |
| C8 | The 40% limit can never pass for a two-option dimension | Limit applied to dimensions with 3+ options; only the over-used dimension is redrawn |
| C9 | History: "last 20 posts" vs "last 20 posts of the same type" | Same type, one row per post (a regeneration replaces it; Leave removes it) |
| C10 | W9 "through a migration" vs "never delete" and keeping v2 intact | Stored point-of-view values are mapped when read, not rewritten, so v2 still gets its own values after a rollback |
| C11 | Regulated businesses on v2 (decision A3) vs G3 (every post of a Regulated business starts Regulated) | G3 applies once the niche has a disclaimer text; until then the business runs v2 |
| C12 | Section count "a random draw within the range" vs too few questions | Draw within [minimum, min(maximum, questions + optional elements)], as in the doc's own example |
| C13 | Cost guide without a price: the header says the piece is sent "instead", but its text ("Replace the price range with …") only makes sense next to the Cost guide piece | Both are sent, the no-figure piece after the Cost guide piece |
