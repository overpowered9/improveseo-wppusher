=== Improve SEO ===
Contributors: nateg108
Tags: seo, pages, posts, bulk posts, AI content generation
Requires at least: 5.0
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 2.0.12
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Create AI generated SEO optimized content directly inside WordPress.

== Description ==

ImproveSEO is an AI-powered local SEO plugin designed to help small business owners create SEO optimized content directly inside WordPress.

**Features:**

* Single AI post/bulk AI post project creation
* AI-powered content generation
* Keyword generator
* SEO list management
* Scheduled post publishing

== External Services ==

This plugin relies on third-party services. What each is used for, and what data
leaves your site, is listed below.

= ImproveSEO cloud service (required) =

The plugin cannot generate content without this service; all AI generation happens
off-site.

* Data sent: your API key and site code, your site URL, your account email when
  connecting the site, and the generation parameters you supply - seed keyword,
  title, number of words, content language, tone of voice, point of view, call to
  action, details to include, and niche.
* When: only when you connect your site, or when you ask the plugin to generate
  keywords, an article, or an image. Nothing is sent in the background.
* Account dashboard: https://account.improveseoplugin.com/
* API endpoint: https://imporve-seo-admin-server-nzbm.onrender.com
* Terms of Service: https://improveseoplugin.com/terms
* Privacy Policy: https://improveseoplugin.com/privacy

= OpenAI (optional) =

Used only if you enter your own OpenAI API key under Improve SEO > Settings. If that
field is empty the plugin never contacts OpenAI.

* Data sent: your OpenAI API key and the image prompt derived from your post title.
* When: only while generating an image, and only if you have configured a key.
* Terms of Service: https://openai.com/policies/terms-of-use
* Privacy Policy: https://openai.com/policies/privacy-policy

= Google Maps (optional) =

Used only by the Google Maps shortcode, and only if you supply your own Google API
key.

* Data sent: your Google API key and the address you enter into the shortcode, sent
  to the Google Maps JavaScript and Geocoding APIs.
* When: only when a page containing the Google Maps shortcode is rendered.
* Terms of Service: https://cloud.google.com/maps-platform/terms
* Privacy Policy: https://policies.google.com/privacy

= Vimeo (optional) =

Used only by the video shortcode when you embed a Vimeo video. The Vimeo player
script is loaded from Vimeo so the embed can play.

* Data sent: the video ID you embed, plus whatever the visitor's browser sends to
  Vimeo when loading the player.
* When: only on pages where you have embedded a Vimeo video.
* Terms of Service: https://vimeo.com/terms
* Privacy Policy: https://vimeo.com/privacy

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/improveseo/`
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Follow the **Getting Started** guide below to connect your site

== Getting Started ==

To use Improve SEO, you need to connect it with the User CMS dashboard. Follow these steps carefully:

= Step 1: Create a User CMS Account =

1. Go to [https://user-cms-blue.vercel.app/](https://user-cms-blue.vercel.app/)
2. Click **Sign Up**
3. Enter your name, email address, and password
4. Complete the registration process
5. Log in with your new credentials

= Step 2: Get Your API Key =

1. After logging in to User CMS, navigate to your **Dashboard**
2. Locate your **API Key** — it will be displayed on your dashboard or under your account/profile section
3. Copy the API Key and keep it safe — you will need it in Step 4

= Step 3: Get Your Site Code =

1. In the User CMS dashboard, go to the **Sites** or **Add Site** section
2. Enter your website's **URL/domain** (e.g., `https://example.com`)
3. Click **Submit** or **Add Site**
4. The system will generate a unique **Site Code** for your domain
5. Copy the Site Code — you will need it in Step 4

= Step 4: Configure the Plugin in WordPress =

1. In your WordPress admin dashboard, go to **Improve SEO** in the left sidebar
2. Click on **Settings**
3. You will see two fields:
   - **API Key** — Paste the API Key you copied from Step 2
   - **Site Code** — Paste the Site Code you generated in Step 3
4. Click **Save Settings**
5. You should see a success confirmation — your plugin is now connected!

= Step 5: Start Creating Content =

1. Go to **Improve SEO > Create Posts** to create new single AI posts or bulk AI post projects.
2. Follow the steps inside the wizard
3. Use **Improve SEO > Single Post Projects/Bulk Post Projects** to manage your content projects
4. Use **Improve SEO > Keyword Lists** to manage your SEO keyword lists

== Frequently Asked Questions ==

= How do I get my API Key? =

Sign up at [https://user-cms-blue.vercel.app/](https://user-cms-blue.vercel.app/), log in, and find your API Key on the dashboard.

= How do I get my Site Code? =

After logging in to User CMS, add your website URL/domain. A unique Site Code will be generated for your site.

= Where do I enter the API Key and Site Code? =

In your WordPress admin, go to **Improve SEO > Settings** and paste both values in the corresponding fields.

= What if my API Key or Site Code is not working? =

- Make sure there are no extra spaces before or after the key
- Ensure you are using the correct API Key and Site Code for your domain
- Try regenerating the Site Code from User CMS
- Check that your domain in User CMS matches your actual WordPress site URL


= Can I use the AI content generator? =

Yes! Select if you want to create a single AI post or a bulk AI post project, then follow the prompts inside the content creation wizard. Make sure your API Key and Site Code are configured in Settings first.

== Screenshots ==

1. Plugin dashboard overview
2. Settings page — enter API Key and Site Code

== Changelog ==

= 2.0.12 =
* Security: fixed an arbitrary file upload flaw in the image upload handler that could lead to remote code execution. The endpoint is no longer reachable by unauthenticated visitors, now verifies a nonce and the `upload_files` capability, and accepts only server-validated image types. Thanks to Joao Ramos Maciel for responsibly disclosing this issue.
* Security: the AI title generation endpoint is no longer exposed to unauthenticated visitors and now verifies a nonce and user capability.
* Security: restored TLS certificate verification on image downloads.
* Removed a leftover third-party plugin updater that could fetch code from an external server over an unencrypted connection.
* All third-party styles, scripts and fonts are now bundled with the plugin instead of being loaded from external CDNs.
* Removed a large amount of unreachable legacy code, including two files that could never load.
* Added an External Services section to this readme documenting every third-party service the plugin contacts.

= 2.0.11 =
* Latest stable release
* AI content generation support
* Bulk and single post creation
* Custom shortcodes for testimonials, buttons, maps, and videos

== Upgrade Notice ==

= 2.0.12 =
Important security release. Fixes an arbitrary file upload vulnerability that could lead to remote code execution, and removes a third-party plugin updater. All users should update immediately.

= 2.0.11 =
Latest stable version with AI content generation support.