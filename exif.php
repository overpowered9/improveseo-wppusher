<?php

use WorkHorse\View;

define('WORKHORSE_ROOT', dirname(__FILE__));

include_once 'autoloader.php';

View::render('exif.index');