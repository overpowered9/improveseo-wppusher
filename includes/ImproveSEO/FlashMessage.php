<?php

namespace ImproveSEO;

use ImproveSEO\View;
if ( ! defined( 'ABSPATH' ) ) exit;

class FlashMessage
{
	public static function success($message)
	{
		self::message($message, 'success');
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
