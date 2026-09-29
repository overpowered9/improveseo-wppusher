<?php

namespace ImproveSEO;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


use ImproveSEO\View;

class FlashMessage
{
	public static function success($message)
	{
		self::message($message, 'success');
	}

	// Called from modules/projects.php and modules/bulkprojects.php but never defined,
	// so each of those paths ended in a fatal "Call to undefined method" instead of the
	// message. 'error' maps to WordPress core's red .notice-error.
	public static function error($message)
	{
		self::message($message, 'error');
	}

	public static function message($message, $type = 'success')
	{
		$_SESSION['improveseo.flashmessage.message'] = $message;
		$_SESSION['improveseo.flashmessage.type'] = $type;
	}

	public static function handle()
	{
		$message = isset($_SESSION['improveseo.flashmessage.message'])?$_SESSION['improveseo.flashmessage.message']:'';
		$type = isset($_SESSION['improveseo.flashmessage.type'])?$_SESSION['improveseo.flashmessage.type']:'';

		unset($_SESSION['improveseo.flashmessage.message']);
		unset($_SESSION['improveseo.flashmessage.type']);

		View::render('flashmessage.message', compact('message', 'type'));
	}
}
