# Content v3 — plugin changes (branch `feature/content-v3`)

For the next release's readme.txt changelog. Off until the site's "Use Content v3" setting and the server's switch are both on; with either off nothing changes.

**New**
- Settings → Content Features: "Use Content v3".
- Settings → Business Details: niche (search as you type), closest kind of business for "Other", regulated field (suggested, never set silently), local or nationwide, towns served, how the business refers to itself, business name, what it does, ideal client, what makes it stand out. One-time notice when the business is set to Regulated.
- Single post wizard (Content v3): Target town with a check when the keyword names another town; titles from real search results; Approve runs research and a plan; step 2 shows the article type, the size with its reason (sizes too small for the title are disabled), questions for the owner, and a Keep / Turn off line for regulated topics; the article is checked and, when it still misses checks, a notice offers "Use anyway" or "Regenerate"; the point of view has three options; Witty is hidden for regulated niches; leaving the wizard deletes everything stored for the post, including its generated images.
- Bulk under Content v3: the same chain per post; a Regulated business's posts and posts that still miss checks are saved as drafts with the reason.

**Fixed (also for the current pipeline)**
- Bulk: an error message can no longer be published as a post; an image failure no longer throws away (and re-buys) the article; failed posts stop after 3 attempts and show why.
- Single post: regenerations are priced as regenerations, and the regenerate instruction reaches the server (article and image).
- A second "Generate AI Post" while one is running is ignored (it was charged twice).
- The FAQ schema is found in v3 articles.
- No more PHP warnings on the single and bulk post screens and in the AI popup, no "called incorrectly" notice for the plugin's main script on every request (it is now loaded from the proper hooks, once), and no JavaScript errors on the settings page, the dashboard and the front end.

**Completion and production-readiness pass (14–15 Nov)**
- Only the site's administrators can spend credits: generation, image and Content v3 requests need `manage_options` (filter `improveseo_generation_capability`), and the request nonce is printed only on the plugin's own screens. Keyword list, bulk project and shortcode forms got nonces.
- No error can become a post: the single wizard shows a plain message and keeps Generate available, and Submit refuses an empty article or one starting with "Error:" (also for the current pipeline).
- One charge per click: each Generate sends a request id, so a retry after a timeout gets the finished article instead of buying it again.
- Bulk queue: each task is leased while it runs (two runs can no longer process the same post); a server outage pauses the queue instead of using up attempts (at most 8 times per post); a failing step counts an attempt; the article is saved before the image; a post takes the title its article was written for; the server switching Content v3 off sends the post to the current pipeline.
- Bulk under Content v3: the same three point-of-view options as the single wizard, and a Target town for the whole project (a saved town or "Not about a specific location").
- The plugin keeps the server's last answer about Content v3 for up to a day, so one slow check no longer switches a running wizard or batch to the current pipeline. A Regulated business stays on the current pipeline until its niche has a disclaimer text.
- Single Content v3 posts get the plugin's content wrapper (FAQ schema, FAQ formatting) and are passed through `wp_kses_post` before saving.
- Leave also removes the unsubmitted article from the server; images of wizards closed without Leave or Submit are deleted after 8 days, unless a post uses them.
- The niche list is loaded only while Content v3 is on; with it off the Settings page says why.
- New `uninstall.php`: deleting the plugin removes what Content v3 and its keyword data added (options, transients, post meta, the columns they added), and nothing older.
