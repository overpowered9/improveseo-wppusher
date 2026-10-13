# Plugin dashboard: Help & Support section and how support tickets work

**Scope:** ImproveSEO WordPress plugin, `Improve SEO → Dashboard` (`admin.php?page=improveseo_dashboard`)
**Plugin version:** 2.0.13 · **Branch:** `Oct-06-merge` (uncommitted working-tree change)
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
[ New here?        ][ Need a hand?  (how it works · what to send · CTA) ]
Quick Links …
```

**Card widths in the top row (measured in headless Chrome):**

| Viewport | Before | After |
|---|---|---|
| 1920px | ~215px (7 cards) | **323px** (5 cards) |
| 1366px | wrapped | **212px** (5 in one row) |
| 1280px | wrapped | **195px** (5 in one row) |
| ≤1100px | — | wraps cleanly; Help & Support stacks to one column |

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

**New here?** (narrow card)
- Keeps the CMS's existing wording and its "Open tutorials" destination (`/support`, a client choice).
- Adds two direct links to things that really exist:
  - **Quick Start Guide:** `account.improveseoplugin.com/tutorials`, which is public, so no sign-in is needed. It covers automatic setup, manual setup and "How credits are used".
  - **Support page:** `/support`, with the Knowledge Base, live service status and the user's tickets.

**Need a hand?** (wide card)
- A pill, "Typical reply within 1 business day" (the existing promise, unchanged).
- A one-line description of what it's for: setup, credits/billing, or content that didn't generate.
- **How it works:** a four-step timeline. The status names use the same colours as the CMS, so users recognise them on the ticket:
  1. **Open a ticket.** It opens in the ImproveSEO account (sign in if asked). Add a subject, category, priority and what happened.
  2. **Get your ticket ID.** A confirmation email arrives, and the ticket shows under *Your Tickets* as `Open`.
  3. **We work on it.** The ticket moves to `In Progress`. Each reply arrives by email and as an account notification; answer from the account, not by replying to the email. `Awaiting Reply` means support needs something from the user.
  4. **Resolved.** The ticket is marked `Resolved`. Still stuck? Open a new ticket and quote the ticket ID.
- **Help us fix it faster:**
  - the exact error message or a screenshot (up to 3 files, 10 MB each)
  - the page the user was on and what they clicked
  - **the site's details**, pre-filled from WordPress, e.g. `Site: https://example.com | ImproveSEO 2.0.13 | WordPress 6.x | PHP 8.x`, with a **Copy** button
- **Submit a ticket →**, which opens `account.improveseoplugin.com/support?newTicket=1` in a new tab.

The Copy button uses the Clipboard API on HTTPS. On plain-HTTP admins, where that API is unavailable, it falls back to `execCommand('copy')`. If both fail, it selects the text and says "Press Ctrl+C". It announces success to screen readers. The dashboard is admin-only (`manage_options`), so showing version numbers there is fine.

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
| `views/dashboard/index.php` | Support cards removed from the top row; disconnected-row modifier class; new Help & Support section; site-details string (`home_url()`, `IMPROVESEO_VERSION`, `get_bloginfo('version')`, `PHP_VERSION`); Copy-button script. |
| `assets/css/style.css` | Top-row grid (`minmax(190px, 1fr)`, 16px gap), larger type, disconnected 2:1 layout, row-order cleanup, styles for the Help & Support section (container queries for the two-column ticket layout and the credit totals). |
| `docs/DASHBOARD_HELP_SUPPORT.md` | This document. |

There are no changes to the CMS, the admin server, AJAX calls, or the Quick Start, plan or credit logic.

---

## 6. Findings that need a decision

These are inconsistencies in the ticket system and help content, found while checking the new copy against the code. None are caused by this change. The plugin copy was written to match what users can actually do today.

1. **The closed-ticket email gives instructions that fail.** It says *"If this isn't actually fixed, reply on the ticket and it reopens — you don't need to start a new one."* In fact the server returns 400 for any message on a closed ticket, has no reopen logic, and the CMS hides the reply box. A user who follows the email hits a dead end. **Fix one side:** either implement reopen-on-reply, or change the email to "open a new ticket and quote {ID}" (`product-templates.ts`, `ticketClosed`).
2. **Two different reply-time promises.** The plugin and CMS dashboards say "one business day"; the confirmation email says "within 24 hours". Over a weekend these disagree. Pick one.
3. **"Resolved" sends no email; only "Closed" does.** Users with a resolved ticket find out only if they open the CMS.
4. **"Watch the 4-minute setup walkthrough" refers to a video that doesn't exist.** No video is embedded or linked anywhere in the CMS. The plugin and CMS (`Dashboard.js:573`) both still use this line; it was left word-for-word pending a decision (record the video, or reword).
5. **Knowledge Base entries are placeholders.** On the CMS Support page, "Getting Started Guide", "API Documentation" and "Integration Tutorials" are labels with no links (`SupportTickets.jsx:167-171`). The plugin therefore doesn't list them.
6. **"Open tutorials" goes to `/support`, not to the tutorials.** This was a client request in the CMS context. The real step-by-step guide is the public `/tutorials` page. The plugin now links that guide directly; consider pointing the button there too.
7. **Priority has no defined effect.** "Urgent" is only counted on the admin stats (`urgent_open`); no faster response is promised or enforced. Either define one, or tell users what priority changes.
8. **`system_info` describes the account, not the site.** For accounts with several websites it can name the wrong site. A cheap follow-up: let `/support?newTicket=1` accept the site/version string as URL parameters, so the plugin can pre-fill it instead of asking users to paste it.

---

## 7. How it was verified

- **PHP lint:** `php -l views/dashboard/index.php` inside the local WordPress container: no syntax errors.
- **Visual checks:** headless Chrome against the local site at 1920 / 1366 / 1280 / 1024 / 600px:
  - no horizontal scroll at any width
  - "Cancel Subscription" and "5,610 credits" stay on one line down to the 195px minimum card width
  - the ticket card switches between two columns and one column on its own width
  - the Help & Support pair stacks at ≤1100px
- **Connected state:** the local site isn't connected to an account, and saving fake credentials would make the plugin ping the live admin server. The five-card row was therefore reproduced in the browser by un-hiding the cards and filling in the same values as the production screenshot (Optimize Plan, 5,610 / 477 credits). No requests were sent to production.
- **Copy button:** tested on both the Clipboard-API path and the plain-HTTP fallback. The fallback put the exact site-details string on the clipboard and cleaned up after itself. Label and screen-reader announcement were confirmed.
- **Links:** all six Help & Support links point to the intended account URLs and open in a new tab.
- **Console:** only the pre-existing `jQuery(...).smartWizard is not a function` error, which is present on every branch.

**Not yet done:** a check against a real connected account, and a commit/push (left for review).
