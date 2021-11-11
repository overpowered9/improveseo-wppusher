<?php

use WorkHorse\Geo;
use WorkHorse\View;
use WorkHorse\Validator;
use WorkHorse\FlashMessage;
use WorkHorse\Models\Country;
use WorkHorse\Models\GeoData;

function workhorse_settings() {
	global $wpdb;

	$action = isset($_GET['action']) ? $_GET['action'] : 'index';
	$limit = isset($_GET['limit']) ? $_GET['limit'] : 20;
	$offset = isset($_GET['offset']) ? $_GET['offset'] : 0;

	$countryModel = new Country();

	if ($action == 'index'):
		View::render('settings.index');
	elseif ($action == 'add_country'):
		$exist = $countryModel->getByShort($_GET['country']);
		if (!$exist) {
			$country = null;
			$countries = Geo::getCountriesList();
			foreach ($countries as $cc) {
				if ($cc->code == $_GET['country']) $country = $cc;
			}

			$country_id = $countryModel->create(array(
			  'name'  => $country->country,
			  'short' => $country->code
			));

			Geo::installCountry($country_id, $country);
			FlashMessage::message($country->country .' has been installed successfully.');
		}

		wp_redirect('/wp-admin/admin.php?page=workhorse_settings');
		exit;
	elseif ($action == 'delete_country'):
		$country = $countryModel->getByShort($_GET['country']);
		$geodata = new GeoData();
		$geodata->deleteByCountryId($country->id);
		$countryModel->delete($country->id);

		FlashMessage::message($country->name .' has been deleted successfully.');
		wp_redirect('/wp-admin/admin.php?page=workhorse_settings');
	endif;
}
