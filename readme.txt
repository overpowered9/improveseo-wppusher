=== Improve SEO ===
Contributors: improveseoteam
Tags: seo, ai content, content generator, bulk posts, openai
Requires at least: 5.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 2.0.12
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Creates a large number of pages/posts and customize them to rank in Google.

== Description ==

Improve SEO generates SEO-focused posts and pages for your WordPress site. Give it a
keyword and it produces a full article — title, body, meta title and meta description —
with an optional AI-generated cover image.

Features:

* Single-post generation with a guided, step-by-step wizard.
* Bulk generation from a keyword list, with scheduled drip-feed publishing.
* AI cover images, generated from the post title or from your own prompt.
* Regenerate content or images, optionally with your own custom instructions.
* Meta title and meta description generated within SEO length limits, and written to
  Yoast, Rank Math or SEOPress when one of those is active.
* FAQ sections emitted as structured blocks with matching FAQPage JSON-LD schema.
* Shortcodes for testimonials, buttons, Google Maps embeds and videos.
* Keyword research and saved keyword lists.

== External services ==

This plugin relies on the ImproveSEO content generation service to create posts, images
and keyword suggestions. Using the plugin's generation features requires an account.

Service: ImproveSEO API — https://imporve-seo-admin-server-nzbm.onrender.com

What is sent, and when: when you generate or regenerate content, the plugin sends your
seed keyword, chosen title, article length, language, tone, point of view, niche
selections, call to action, any custom instruction you type, and your site's API key and
site code. When you generate an image it sends the post title or your image prompt, the
niche, and your business city if you set one during onboarding. Nothing is sent until you
start a generation.

Terms of service: https://improveseoplugin.com/terms
Privacy policy: https://improveseoplugin.com/privacy

The plugin also loads Google Fonts (Lato, Poppins) on its admin dashboard screen from
https://fonts.googleapis.com, and Google Maps from https://maps.googleapis.com when you
use the Google Maps shortcode, which requires your own Google Maps API key.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/`, or install it through the
   WordPress plugins screen.
2. Activate the plugin through the "Plugins" screen in WordPress.
3. Go to Improve SEO and complete the onboarding to connect your account.

== Frequently Asked Questions ==

= Do I need an account to use this plugin? =

Yes. Content, image and keyword generation run on the ImproveSEO service and require an
API key and site code, which you get by connecting your account during onboarding.

= Does generating content cost credits? =

Yes. Generating an article or an image uses credits from your plan. Regenerating costs
less than a first generation. The plugin shows your remaining balance before you start.

= Where are my generated posts saved? =

As drafts in WordPress, unless you schedule them. You can review and edit any post before
it is published.

== Changelog ==

= 2.0.12 =
* Security: authenticated the plugin's public endpoint and prepared its SQL.
* Security: prepared the remaining direct database queries.
* Security: stopped writing to wp-config.php and to the WordPress root directory.
* Removed unreachable bundled seed data.
* Removed development-only scripts from the distributed plugin.

== Upgrade Notice ==

= 2.0.12 =
Security release. Update is recommended for all users.
