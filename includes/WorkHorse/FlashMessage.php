<?php

namespace WorkHorse;

use WorkHorse\View;

class FlashMessage
{
	public static function success($message)
	{
		self::message($message, 'success');
	}

	public static function message($message, $type = 'success')
	{
		$_SESSION['workhorse.flashmessage.message'] = $message;
		$_SESSION['workhorse.flashmessage.type'] = $type;
	}

	public static function handle()
	{
		$message = $_SESSION['workhorse.flashmessage.message'];
		$type = $_SESSION['workhorse.flashmessage.type'];

		unset($_SESSION['workhorse.flashmessage.message']);
		unset($_SESSION['workhorse.flashmessage.type']);

		View::render('flashmessage.message', compact('message', 'type'));
	}
}
