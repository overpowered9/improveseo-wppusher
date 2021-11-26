<?php

use ImproveSEO\View;
use ImproveSEO\Validator;
use ImproveSEO\Models\Task;
use ImproveSEO\FlashMessage;

function improveseo_dashboard() {
	$action = isset($_GET['action']) ? $_GET['action'] : 'index';

	if ($action == 'index') {
		View::render('dashboard.index');
	}
}