# Plugin dashboard: Help & Support section and how support tickets work

**Scope:** ImproveSEO WordPress plugin, `Improve SEO → Dashboard` (`admin.php?page=improveseo_dashboard`)
**Plugin version:** 2.0.13 · **Branch:** `Oct-06-merge` (first pass `87d5db5`, then a design pass on the Help & Support cards)
**Date:** 13 Oct 2026

---

## 1. Summary

The dashboard's top row had seven cards. Two of them ("New here?" and "Need a hand?") were help links squeezed into a row meant for account status. They now sit in their own **Help & Support** section, below Content Metrics and above Quick Links. That gives the remaining five account cards about 50% more width.

"Need a hand?" is now a full explainer of the support-ticket process, covering:

- how to submit a ticket
- what happens after submitting
- what each status means
- what to include so support can fix the problem faster

Every statement in it was checked against the current CMS and admin-server code; none of it is aspirational. While verifying the copy I found several inconsistencies in the ticket system itself. They are listed in **section 6** for a decision.

---

## 2. What was wrong

| # | Problem | Effect |
|---|---------|--------|
| 1 | 7 cards in one `auto-fit, minmax(160px, 1fr)` grid | About 215px per card at 1920px. Plan buttons had been shrunk to 11.5px so "Cancel Subscription" wouldn't wrap; credit totals were 21px. |
| 2 | Help links mixed into the account-status row | Low-frequency actions competed for space with balance, plan and "create now". |
| 3 | "Need a hand?" said only *"Typical reply within one business day."* | Nothing on where the ticket goes, whether a login is needed, how replies arrive or what to include. |
| 4 | "New here?" pointed at things that don't exist (see §6) | No walkthrough video exists, and the Knowledge Base entries are unlinked labels. |

---

## 3. What changed

### 3.1 Layout

```
BEFORE (connected site)
[Quick Start][Guided Start][New here?][Need a hand?][Plan][Credits Remaining][Credits Used]
[ Content Metrics                 ][ Review Business Details           ]
Quick Links …

AFTER
[Quick Start][Guided Start][Plan][Credits Remaining][Credits Used]
[ Content Metrics                 ][ Review Business Details           ]
Help & Support
[ New here?     [Quick Start Guide]  [How credits work]  [Support center] ]
[ Need a hand?  · Typical reply: 1 business day        [Submit a ticket ↗] ]
[   How it works  ①────②────③────④                                        ]
[   Statuses you'll see  Open · In Progress · Awaiting Reply · Resolved     ]
[   ┌ Include in your ticket ✓✓✓ │ Your site details  Site·Plugin·WP·PHP [Copy] ┐ ]
Quick Links …
```

Both help cards are full width and stacked. An earlier version put them side by side (1 : 2). Next to the taller ticket card, "New here?" had to stretch its three links into tall, mostly empty boxes, so the stacked layout replaced it.

**Card widths in the top row (measured in headless Chrome):**

| Viewport | Before | After |
|---|---|---|
| 1920px | ~215px (7 cards) | **323px** (5 cards) |
| 1366px | wrapped | **212px** (5 in one row) |
| 1280px | wrapped | **195px** (5 in one row) |
| ≤1100px | — | wraps cleanly |

**Disconnected site:** only Quick Start and the plan card are shown. Quick Start takes two-thirds of the row so "Connect now" leads, and the plan buttons are capped at 300px wide so they don't stretch into banners.

### 3.2 Type and spacing in the top row (now that there's room)

| Element | Before | After |
|---|---|---|
| Card padding | 16px | 20px |
| Card title | 15px | 16px |
| Body text | 12.5px | 13px |
| Credit totals | 21px | 26px; scales down to 20px on narrow cards so "5,610 credits" never wraps |
| Plan name | 16px | 18px |
| Plan buttons | 11.5px | 12.5px |
| Captions (expiry, "this cycle", plan date) | 11.5px | 12px |

### 3.3 New "Help & Support" section

Both cards share one header pattern (icon tile, title, one-line summary) and the dashboard's existing colour tokens. Neither has the decorative wave, the hover lift or the corner chevron of the small status tiles above: these are content cards, styled like Content Metrics and Business Details.

**New here?** (full width)
- Summary: "Everything you need to connect this site and publish your first post."
- Three link tiles, side by side on wide screens and stacked on narrow ones. Each opens in a new tab:
  - **Quick Start Guide** → `account.improveseoplugin.com/tutorials`. It's public, so it's tagged *No sign-in*. It covers automatic setup, manual setup and credits.
  - **How credits work** → the same page's `#how-credits-are-used` section: what posts and images cost, and when credits expire. *No sign-in*. The anchor was confirmed to scroll to the section on the live page.
  - **Support center** → `/support`: Knowledge Base, live service status and the user's tickets.
- The card no longer has an "Open tutorials" button (it went to `/support`, which has no tutorials) or the "Watch the 4-minute setup walkthrough" line (no such video exists). See §6.

**Need a hand?** (full width)
- **Header:** the title, a "Typical reply: 1 business day" pill (the existing promise), a one-line summary of what support helps with, and the primary **Submit a ticket ↗** button on the right, where it's seen first. On phones the button goes full width under the summary.
- **How it works:** a four-step timeline. It runs across the card when the card is ≥720px wide and becomes a vertical timeline below that:
  1. **Submit your ticket:** sign in, choose a category and priority, and describe the issue.
  2. **Get your ticket ID:** a confirmation email arrives, and the ticket shows under *Your Tickets*.
  3. **We work on it:** replies arrive by email and in your account; respond from your account.
  4. **Resolved:** we mark it resolved. Still stuck? Open a new ticket quoting its ID.
- **Statuses you'll see:** one line using the CMS's own status colours, so users recognise them on the ticket: `Open` received · `In Progress` we're on it · `Awaiting Reply` we need your input · `Resolved` fixed.
- **Before-you-submit panel** (two columns on wide cards):
  - **Include in your ticket:** the exact error message or a screenshot (up to 3 files, 10 MB each); the page you were on and what you clicked; your site details.
  - **Your site details:** Site, Plugin, WordPress and PHP shown as a labelled grid, read live from WordPress. A **Copy details** button copies them as one line, e.g. `Site: https://example.com | ImproveSEO 2.0.13 | WordPress 6.x | PHP 8.x`.

The Copy button uses the Clipboard API on HTTPS. On plain-HTTP admins, where that API is unavailable, it falls back to `execCommand('copy')`. On success it turns green with a check mark for 2 seconds and announces the copy to screen readers. If both copy methods fail, it selects the details and says "Press Ctrl+C" (⌘C on a Mac). The dashboard is admin-only (`manage_options`), so showing version numbers there is fine.

Layout breakpoints inside the cards are **container queries**, so each part adapts to the width it actually has, whether the WordPress sidebar is open, folded or hidden. All help content (links, steps, statuses, site details) is defined once in PHP arrays and rendered by loops.

---

## 4. How support tickets work today (end to end)

This is the information requested about tickets. Each point is taken from code, with its source.

| Stage | What happens | Source |
|---|---|---|
| Entry | "Submit a ticket" opens `/support?newTicket=1` in the account CMS. The CMS opens the *Create Support Ticket* dialog on load. Requires the user to be signed in. | `user-cms/src/components/SupportTickets.jsx:444-446`, `src/utils/routes.js:58` |
| Form | **Subject** (required, 5–255 chars) · **Category**: General / Billing / Technical Issue / Feature Request · **Priority**: Low / Medium (default) / High / Urgent · **Description** (required, ≥20 chars) · **Attachments**: up to 3 files, 10 MB each (jpg, png, gif, webp, csv, pdf, xlsx, xls, json, zip, txt, log) | `SupportTickets.jsx:126-131, 178-179, 1923-2040` |
| Submit | `POST /api/v1/tickets`. The CMS also sends `system_info` (email, site URL, plugin and WordPress versions), but takes it from the **account profile**, not from the site the user has a problem with. The user sees "Ticket created successfully! Our team will respond soon." | `SupportTickets.jsx:555-589`, `imporve-seo-admin-server/src/routes/tickets.routes.ts:120-152` |
| Confirmation | Email: *"Your support ticket has been created — {ID}"*. Example ID from the server's email preview script: `ISEO-4821`. Body promises a response **within 24 hours** and asks the user to reply in the dashboard, not by email. | `tickets.routes.ts:129-146`, `src/services/email/product-templates.ts:178-215` |
| Statuses | `Open` (new) → `In Progress` (set automatically on the first admin reply, or manually) → `Awaiting Reply` (set by admin; a user reply moves it back to In Progress) → `Resolved` / `Closed` (set by admin) | `src/models/ticket.models.ts:259-275, 365-390`, `SupportTickets.jsx:133-151` |
| Replies | Admin reply (not internal notes) → email *"New reply on your ticket — {ID}"* + CMS notification *"There is a new message regarding your support ticket {ID}."* The CMS refreshes in real time, with polling every 15s for the list and 4s for an open ticket. | `tickets.routes.ts:287-300`, `user-cms/src/utils/notifications.js:208-219`, `SupportTickets.jsx:354-440` |
| Resolved | **No email.** The CMS hides the reply box: "Create a new ticket if you need further help." | `SupportTickets.jsx:1787, 1887-1890` |
| Closed | Email: *"Your ticket has been closed — {ID}"*. The server **rejects** further messages (`400 Cannot add messages to a closed ticket`). | `tickets.routes.ts:265-270, 433-449` |

---

## 5. Files changed

| File | Change |
|---|---|
| `views/dashboard/index.php` | Support cards removed from the top row; disconnected-row modifier class; new Help & Support section (content in PHP arrays: links, steps, statuses, site details from `home_url()`, `IMPROVESEO_VERSION`, `get_bloginfo('version')`, `PHP_VERSION`); Copy-button script; seven new line icons (book, lifebuoy, help, external, clock, check, copy) replacing the unused play/chat ones. |
| `assets/css/style.css` | Top-row grid (`minmax(190px, 1fr)`, 16px gap), larger type, disconnected 2:1 layout, row-order cleanup; Help & Support styles (link tiles, timeline, status legend, before-you-submit panel), with container queries for the step layout, the link tiles, the panel and the credit totals. |
| `docs/DASHBOARD_HELP_SUPPORT.md` | This document. |

There are no changes to the CMS, the admin server, AJAX calls, or the Quick Start, plan or credit logic.

---

## 6. Findings that need a decision

These are inconsistencies in the ticket system and help content, found while checking the new copy against the code. None are caused by this change. The plugin copy was written to match what users can actually do today.

1. **The closed-ticket email gives instructions that fail.** It says *"If this isn't actually fixed, reply on the ticket and it reopens — you don't need to start a new one."* In fact the server returns 400 for any message on a closed ticket, has no reopen logic, and the CMS hides the reply box. A user who follows the email hits a dead end. **Fix one side:** either implement reopen-on-reply, or change the email to "open a new ticket and quote {ID}" (`product-templates.ts`, `ticketClosed`).
2. **Two different reply-time promises.** The plugin and CMS dashboards say "one business day"; the confirmation email says "within 24 hours". Over a weekend these disagree. Pick one.
3. **"Resolved" sends no email; only "Closed" does.** Users with a resolved ticket find out only if they open the CMS.
4. **"Watch the 4-minute setup walkthrough" refers to a video that doesn't exist.** No video is embedded or linked anywhere in the CMS. The plugin no longer uses this line; the CMS's own "New here?" card (`Dashboard.js:573`) still does. Record the video, or reword the CMS card.
5. **Knowledge Base entries are placeholders.** On the CMS Support page, "Getting Started Guide", "API Documentation" and "Integration Tutorials" are labels with no links (`SupportTickets.jsx:167-171`). The plugin therefore doesn't list them.
6. **The CMS's "Open tutorials" button goes to `/support`, not to the tutorials.** This was a client request in the CMS context. The real step-by-step guide is the public `/tutorials` page. The plugin now links the guide and its credits section directly instead of using that button; consider pointing the CMS button at `/tutorials` too.
7. **Priority has no defined effect.** "Urgent" is only counted on the admin stats (`urgent_open`); no faster response is promised or enforced. Either define one, or tell users what priority changes.
8. **`system_info` describes the account, not the site.** For accounts with several websites it can name the wrong site. A cheap follow-up: let `/support?newTicket=1` accept the site/version string as URL parameters, so the plugin can pre-fill it instead of asking users to paste it.

---

## 7. How it was verified

- **PHP lint:** `php -l views/dashboard/index.php` inside the local WordPress container: no syntax errors.
- **Visual checks:** headless Chrome against the local site at 1920 / 1440 / 1366 / 1280 / 1024 / 600 / 390px:
  - no horizontal scroll at any width
  - "Cancel Subscription" and "5,610 credits" stay on one line down to the 195px minimum card width
  - the four steps sit in one row on wide cards and become a vertical timeline on narrow ones
  - the three "New here?" links go side by side once the card is wide enough
  - the before-you-submit panel splits into two columns when there's room
  - on phones the header icon stays beside the title and "Submit a ticket" goes full width
- **Connected state:** the local site isn't connected to an account, and saving fake credentials would make the plugin ping the live admin server. The five-card row was therefore reproduced in the browser by un-hiding the cards and filling in the same values as the production screenshot (Optimize Plan, 5,610 / 477 credits). No requests were sent to the admin server.
- **Public guide:** the live `account.improveseoplugin.com/tutorials` page was loaded once (read-only) to confirm it opens without sign-in, contains "How credits are used", and that `#how-credits-are-used` scrolls to it.
- **Copy button:** tested on both the Clipboard-API path and the plain-HTTP fallback.
  - The fallback put the exact one-line site-details string on the clipboard and cleaned up after itself.
  - The Clipboard-API path shows "Copied" in green with a check icon, announces to screen readers, and resets after 2 seconds.
- **Links:** all four Help & Support links point to the intended account URLs and open in a new tab, with "(opens in a new tab)" for screen readers.
- **Console:** only the pre-existing `jQuery(...).smartWizard is not a function` error, which is present on every branch.

**Not yet done:** a check against a real connected account.
