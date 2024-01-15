=== ImproveSEO ===
Contributors: Nateg108, rahilwazir
Tags: seo, schema, analysis, keyword
Requires at least: 6.0
Tested up to: 6.4.1
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

In the `modules/shortcodes.php` file, we embed YouTube videos using an iframe. The iframe source is set to `http://www.youtube.com`, which is a third-party service.


= Google Maps API =

In the modules/builder.php and modules/googlemaps.php files, we utilize the Google Maps API to geocode addresses and embed maps.

DOMAIN = https://maps.googleapis.com

= Google Autocomplete Suggestions =

In the ImproveSEO/views/features/keyword.php file, we make use of the Google Autocomplete API to fetch search suggestions.

DOMAIN = https://suggestqueries.google.com/complete/search

= YouTube Video Scraper =

In the ImproveSEO/assets/js/videoscraper.js file, we scrape YouTube video data using the YouTube Data API.

DOMAIN = https://www.googleapis.com

= Vimeo Video Embedding =

In the ImproveSEO/modules/videos.php file, we embed Vimeo videos using the Vimeo Player API.

DOMAIN = https://player.vimeo.com/api/player.js


= Check plugin version =

following domain has been used for update the plugin 

DOMAIN = http://www.dexblog.net.


