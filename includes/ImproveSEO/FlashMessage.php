<?php

namespace ImproveSEO;

use ImproveSEO\View;

if (! defined('ABSPATH')) exit;

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
		$message = isset($_SESSION['improveseo.flashmessage.message']) ? sanitize_text_field($_SESSION['improveseo.flashmessage.message']) : '';
		$type = isset($_SESSION['improveseo.flashmessage.type']) ? sanitize_text_field($_SESSION['improveseo.flashmessage.type']) : '';

		// Store compacted variables before unsetting
		$viewData = compact('message', 'type');

		// Unset session values to clear flash message
		unset($_SESSION['improveseo.flashmessage.message'], $_SESSION['improveseo.flashmessage.type']);

		// Render view with the message and type
		View::render('flashmessage.message', $viewData);
	}
	}
