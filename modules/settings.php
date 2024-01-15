<?php

use ImproveSEO\Geo;
use ImproveSEO\View;
use ImproveSEO\Validator;
use ImproveSEO\FlashMessage;
use ImproveSEO\Models\Country;
use ImproveSEO\Models\GeoData;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly  

function improveseo_settings()
{
	global $wpdb;

	$action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'index';
	$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 20;
	$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;

	$countryModel = new Country();

	if ($action == 'index') :
		View::render('settings.index');
	elseif ($action == 'add_country') :
		$countryCode = isset($_GET['country']) ? sanitize_text_field($_GET['country']) : '';

		$exist = $countryModel->getByShort($countryCode);
		if (!$exist) {
			$country = null;
			$countries = Geo::getCountriesList();
			foreach ($countries as $cc) {
				if ($cc->code == $countryCode) $country = $cc;
			}

			if ($country) {
				$country_id = $countryModel->create(array(
					'name'  => sanitize_text_field($country->country),
					'short' => $countryCode,
				));

				Geo::installCountry($country_id, $country);
				FlashMessage::message(esc_html($country->country) . ' has been installed successfully.');
			}
		}
		wp_redirect(admin_url('admin.php?page=improveseo_settings'));
		exit;
	elseif ($action == 'delete_country') :
		$countryCode = isset($_GET['country']) ? sanitize_text_field($_GET['country']) : '';
		$country = $countryModel->getByShort($countryCode);

		if ($country) {
			$geodata = new GeoData();
			$geodata->deleteByCountryId($country->id);
			$countryModel->delete($country->id);

			FlashMessage::message(esc_html($country->name) . ' has been deleted successfully.');
			wp_redirect(admin_url('admin.php?page=improveseo_settings'));
			exit;
		}
	endif;
}
