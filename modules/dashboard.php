<?php

use WorkHorse\View;
use WorkHorse\Validator;
use WorkHorse\Models\Task;
use WorkHorse\FlashMessage;

function workhorse_dashboard() {
	$action = isset($_GET['action']) ? $_GET['action'] : 'index';

	if ($action == 'index') {
		View::render('dashboard.index');
	}
}