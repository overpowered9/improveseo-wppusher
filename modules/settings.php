<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


use ImproveSEO\Geo;
use ImproveSEO\View;
use ImproveSEO\Validator;
use ImproveSEO\FlashMessage;
use ImproveSEO\Models\Country;
use ImproveSEO\Models\GeoData;

function improveseo_settings() {
	global $wpdb;

	$action = isset($_GET['action']) ? $_GET['action'] : 'index';
	$limit = isset($_GET['limit']) ? $_GET['limit'] : 20;
	$offset = isset($_GET['offset']) ? $_GET['offset'] : 0;

	$countryModel = new Country();

	// Only the index branch remains. The add_country / delete_country branches were
	// removed in 2.0.12 along with includes/ImproveSEO/Geo.php:
	//
	//   * add_country fetched country data over plain HTTP from a hardcoded
	//     third-party host and wrote every parsed row into wp_improveseo_geodata.
	//     WordPress.org does not permit loading remote data like this, and over
	//     unencrypted HTTP anyone on the path controlled what landed in the table.
	//   * delete_country read $_GET['country'] unsanitised and deleted rows with no
	//     nonce - a CSRF-able destructive action.
	//
	// Neither had any UI: the Settings screen was redesigned around Business Details
	// and the country installer was dropped, but these branches were left behind.
	// They were reachable only by hand-crafting a URL.
	//
	// The Country and GeoData MODELS stay - includes/functions.php reads them for
	// @city / @state spintax. Only the remote install/delete path is gone.
	if ($action == 'index'):
		View::render('settings.index');
	endif;
}
