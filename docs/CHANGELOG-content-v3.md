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
