=== ImproveSEO ===
Contributors: Nateg108, rahilwazir
Tags: seo, schema, analysis, keyword
Requires at least: 6.0
Tested up to: 6.6.1
Requires PHP: 7.0
Stable tag: 2.0.12
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html


== Description ==

Creates a large number of pages/posts and customize them to rank in Google.

**Requirements**

* PHP 7.0+
* WordPress 6.0+


= Installation =

Upload the ImproveSEO plugin to your wordpress, activate it.

= Third-Party Services =
= YouTube Embedded Videos =

REASON = In the `modules/shortcodes.php` file, we embed YouTube videos using an iframe. The iframe source is set to `http://www.youtube.com`, which is a third-party service.
DOMAIN = http://www.youtube.com
TERMS AND CONDITION = https://www.youtube.com/static?template=terms
PRIVACY AND POLICIES = https://www.youtube.com/howyoutubeworks/policies/community-guidelines/

= Google Maps API =

REASON = In the modules/builder.php and modules/googlemaps.php files, we utilize the Google Maps API to geocode addresses and embed maps.
DOMAIN = https://maps.googleapis.com
TERMS AND CONDITION = https://developers.google.com/maps/terms-20180207

= Google Autocomplete Suggestions =

REASON = In the ImproveSEO/views/features/keyword.php file, we make use of the Google Autocomplete API to fetch search suggestions.
DOMAIN = https://suggestqueries.google.com/complete/search
TERMS AND CONDITION = https://developers.google.com/maps/documentation/javascript/place-autocomplete

= YouTube Video Scraper =

REASON = In the ImproveSEO/assets/js/videoscraper.js file, we scrape YouTube video data using the YouTube Data API.
DOMAIN = https://www.googleapis.com
TERMS AND CONDITION = https://policies.google.com/terms?hl=en-US

= Vimeo Video Embedding =

REASON = In the ImproveSEO/modules/videos.php file, we embed Vimeo videos using the Vimeo Player API.
DOMAIN = https://player.vimeo.com/api/player.js
TERMS AND CONDITION = https://vimeo.com/terms


= Pixabay =

REASON = To show external images
DOMAIN = https://pixabay.com/api/
TERMS AND CONDITION = https://pixabay.com/service/terms/

= Wordai =
REASON = used to rewrite the content
DOMAIN = http://wordai.com
TERMS AND CONDITION = https://wordai.com/terms-of-service



TERMS AND CONDITION =
== Internal Libraries ==
1. jsTree - v3.2.1
2. Notify.js
3. underscore v6.4.3
4. jquery.modal.min.js 0.9.1 (http://github.com/kylefox/jquery-modal)
5. bootstrap - v5.3.3
6. lsolesen Pel  (https://pel.github.io/pel/)
7. Carbon










