<?php

if (file_exists(dirname(__FILE__) . '/modules/single_and_bulk_AI_post_function.php'))

	include_once dirname(__FILE__) . '/modules/single_and_bulk_AI_post_function.php';
include_once dirname(__FILE__) . '/modules/GenerateAIpopup.php';

add_action('wp_ajax_getaaldata', 'getaaldata');

function getaaldata()
{
	$arr = [];

	wp_parse_str($_POST['value'], $arr);

	$nos_of_words = utf8_decode(urldecode($arr['nos_of_words']));

	$seed_keyword = $arr['seed_keyword'];

	$keyword_selection = $arr['keyword_selection'];

	$seed_options = $arr['seed_options'];

	$voice_tone = $arr['content_type'];

	$point_of_view = $arr['point_of_view'];

	$call_to_action = $arr['call_to_action'];

	$for_testing_only = $arr['for_testing_only'];



	$details_to_include = $arr['details_to_include'];

	$content_lang = $arr['content_lang'];

	if (!empty($arr['maintitlearea'])) {

		$ai_title = $arr['maintitlearea'];

	} else {

		$ai_title = $arr['aigeneratedtitle'];

	}



	if ($ai_title == '') {

		$search_data = $arr['seed_keyword'];

	} else {

		$search_data = $ai_title;

	}

	$content = createAIpost(

		$seed_keyword,

		$keyword_selection,

		$seed_options,

		$nos_of_words,

		$content_lang,

		$shortcode = '',

		1,

		$voice_tone,

		$point_of_view,

		$search_data,

		$call_to_action,

		$details_to_include,

		$for_testing_only

	);





	//$content = convert_emails_to_links($content);

	// $content = convert_urls_to_links($content);





	$meta_title = generateMetaTitle($arr['ai_tittle'], $arr['seed_keyword']);

	$meta_descreption = generateMetaDescreption($arr['ai_tittle'], $arr['seed_keyword'], $content);

	wp_send_json_success(array("search_data" => $search_data, "content" => $content, "meta_title" => $meta_title, "meta_descreption" => $meta_descreption));

}



function generateMetaDescreption($aigeneratedtitle, $seed_keyword, $content = '')
{

	$question = "Create an SEO optimized meta description. max length of description should be 70-80 characters including spaces. Meta description is based on the blog post title `" . $aigeneratedtitle . "`, the keyword `" . $seed_keyword . "` and the blog post content i.e. " . $content . ".";

	return ChatGPTCall($question);

}
function generateMetaTitle($aigeneratedtitle, $seed_keyword)
{
	$question = "Create an SEO optimized meta title based on the blog post title `" . $aigeneratedtitle . "` and the keyword `" . $seed_keyword . "`. max length of title should be 50-60 characters including spaces.
	";
	return ChatGPTCall($question);
}


function ChatGPTCall($question)
{

	global $wpdb, $user_ID;



	// Your OpenAI API key

	$apiKey = get_option('improveseo_chatgpt_api_key');

	$apiUrl = 'https://api.openai.com/v1/chat/completions';

	// Your chat messages

	$messages = [

		// ['role' => 'system', 'content' => 'You are a helpful assistant.'],

		['role' => 'user', 'content' => $question]

		// ['role' => 'assistant', 'content' => 'Hello, how can I help you today?'],

	];





	// Additional parameters, including language setting (replace with actual parameters)

	$data = [

		'messages' => $messages,

		'model' => "gpt-4o"



		//'language' => 'fr',  // Specify the result language as French

	];



	// Set up cURL

	$ch = curl_init($apiUrl);



	// Set cURL options

	curl_setopt($ch, CURLOPT_POST, 1);

	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	curl_setopt($ch, CURLOPT_HTTPHEADER, [

		'Content-Type: application/json',

		'Authorization: Bearer ' . $apiKey,

	]);



	// Execute the cURL request

	$response = curl_exec($ch);



	// Check for cURL errors

	if (curl_errno($ch)) {

		echo 'Curl error: ' . curl_error($ch);

	}



	// Close cURL session

	curl_close($ch);



	// Decode and display the response

	$result = json_decode($response, true);



	// print_r($result);

	// die();





	$content = preg_replace('~^[\'"]?(.*?)[\'"]?$~', '$1', $result['choices'][0]['message']['content']);

	return $content;



}

function createAIpost($seed_keyword, $keyword_selection, $seed_options, $nos_of_words, $content_lang, $shortcode = '', $is_single_keyword = '', $voice_tone = '', $point_of_view = '', $title = '', $call_to_action = '', $details_to_include = '', $for_testing_only = '')
{
	global $wpdb, $user_ID;

	// Get server configuration
	$server_config = improveseo_get_server_config();
	
	if (!improveseo_is_server_configured()) {
		return '<div style="color: red; padding: 20px; border: 1px solid red; border-radius: 5px; margin: 10px 0;">
				<h3>🔧 ImproveSEO Configuration Required</h3>
				<p>Please configure your API Key and Site Code in the plugin settings first.</p>
				<p><a href="' . admin_url('admin.php?page=improveseo_settings') . '" style="color: #0073aa; text-decoration: none;">→ Go to Settings</a></p>
			   </div>';
	}

	// Get audience data
	$AudienceData = $_COOKIE['AudienceData'] ?? '';
	
	// Prepare data for our server
	$request_data = array(
		'seed_keyword' => $seed_keyword,
		'keyword_selection' => $keyword_selection,
		'seed_options' => $seed_options,
		'nos_of_words' => $nos_of_words,
		'content_lang' => $content_lang,
		'voice_tone' => $voice_tone,
		'point_of_view' => $point_of_view,
		'title' => $title,
		'call_to_action' => $call_to_action,
		'details_to_include' => $details_to_include,
		'for_testing_only' => $for_testing_only,
		'audience_data' => $AudienceData
	);

	// Determine endpoint based on testing mode
	$endpoint = ($for_testing_only == 1) ? '/api/v1/generation/testing' : '/api/v1/generation/active';
	
	// Make request to our server
	$response = wp_remote_post($server_config['server_url'] . $endpoint, array(
		'timeout' => 120, // 2 minutes for AI generation
		'headers' => array(
			'Content-Type' => 'application/json',
			'x-api-key' => $server_config['api_key'],
			'x-site-code' => $server_config['site_code']
		),
		'body' => json_encode($request_data)
	));

	// Handle response errors
	if (is_wp_error($response)) {
		return '<div style="color: red; padding: 20px; border: 1px solid red; border-radius: 5px; margin: 10px 0;">
				<h3>🔌 Connection Error</h3>
				<p>Failed to connect to ImproveSEO server: ' . esc_html($response->get_error_message()) . '</p>
				<p>Please check your internet connection and try again.</p>
			   </div>';
	}

	$body = wp_remote_retrieve_body($response);
	$status_code = wp_remote_retrieve_response_code($response);

	if ($status_code !== 200) {
		$error_data = json_decode($body, true);
		$error_message = isset($error_data['error']) ? $error_data['error'] : 'Unknown error';
		
		return '<div style="color: red; padding: 20px; border: 1px solid red; border-radius: 5px; margin: 10px 0;">
				<h3>⚠️ Generation Failed</h3>
				<p>' . esc_html($error_message) . '</p>
				<p>Status Code: ' . $status_code . '</p>
			   </div>';
	}

	$result = json_decode($body, true);
	
	if (!isset($result['success']) || !$result['success']) {
		return '<div style="color: red; padding: 20px; border: 1px solid red; border-radius: 5px; margin: 10px 0;">
				<h3>❌ Content Generation Failed</h3>
				<p>' . esc_html($result['error'] ?? 'Unknown error occurred') . '</p>
			   </div>';
	}

	// Get the generated content from server
	$content_final = $result['data']['content'];

	// PRESERVE EXISTING: Add the padding style as before
	$content_final = $content_final . '<style> p {padding-bottom: 2px !important;} </style>';

	// PRESERVE EXISTING: All file processing logic (if needed, can be enhanced later)
	$inserted_id = 2; // Keep existing logic
	$dynamic_path = IMPROVESEO_ROOT . '/storage/';
	$file_path = $dynamic_path . $inserted_id . date('Y-m-d-H-i-s') . '.html';
	
	// Save prompt information (modified for server-based approach)
	$prompt_collection = '<b>Server-Generated Content</b><br>';
	$prompt_collection .= '<b>Keyword:</b> ' . $seed_keyword . '<br>';
	$prompt_collection .= '<b>Language:</b> ' . $content_lang . '<br>';
	$prompt_collection .= '<b>Words:</b> ' . $nos_of_words . '<br>';
	$prompt_collection .= '<b>Voice Tone:</b> ' . $voice_tone . '<br>';
	$prompt_collection .= '<b>Generated via:</b> ImproveSEO Server API<br>';
	
	ob_start();
	?>
	<html>
	<head>
		<title>Generated Content Info for <?php echo esc_html($seed_keyword); ?></title>
		<meta charset="UTF-8">
	</head>
	<body>
		<?php echo $prompt_collection; ?>
	</body>
	</html>
	<?php
	$html_content = ob_get_clean();

	// Save to file
	if (file_put_contents($file_path, $html_content) !== false) {
		// File saved successfully
	}

	// PRESERVE EXISTING: Apply all the content processing functions
	$content_final = replace_content($content_final, '<p>—</p>');
	$content_final = removePTags($content_final);
	$content_final = removeConsecutiveSpecialCharacters($content_final);
	$content_final = verifyAndFixTOCLinks($content_final);

	return $content_final;
}

/**



	
 * Function to remove consecutive special characters from content text nodes
 * - Keeps single special characters (may be meaningful)
 * - Removes runs of 2+ special characters (e.g., ###, ***, ----, !!!, ???, ...)
 * - Does not alter HTML tags/attributes
 */
function removeConsecutiveSpecialCharacters($content) {
    // Split content into tags and text nodes
    $parts = preg_split('/(<[^>]+>)/', $content, -1, PREG_SPLIT_DELIM_CAPTURE);
    if ($parts === false) {
        return $content; // Fallback: if splitting fails, return original
    }

    foreach ($parts as $i => $part) {
        // Skip HTML tags
        if ($part !== '' && $part[0] === '<') {
            continue;
        }
        // Within text nodes, remove any run of 2+ non-alphanumeric, non-space characters
        // This preserves single occurrences but strips sequences (e.g., ### -> '', ** -> '')
        $parts[$i] = preg_replace('/([^\p{L}\p{N}\s]){2,}/u', '', $part);
    }

    return implode('', $parts);
}

/**
 * Strip parentheses that wrap raw URLs or emails in plain text.
 * Examples:
 *  - (https://example.com) => https://example.com
 *  - (www.example.com/path) => www.example.com/path
 *  - (user@example.com) => user@example.com
 */
function stripParenthesesWrappingContactTokens($content) {
    // URLs wrapped in parentheses
    $content = preg_replace('/\(\s*((?:https?:\/\/|www\.)[^\s)]+)\s*\)/i', '$1', $content);
    // Emails wrapped in parentheses
    $content = preg_replace('/\(\s*([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[A-Za-z]{2,})\s*\)/', '$1', $content);
    return $content;
}

/**
 * Function to replace specific content patterns
 */
function replace_content($content, $pattern) {
    return str_replace($pattern, '', $content);
}

/**
 * Function to remove empty p tags
 */
function removePTags($content) {
    // Remove empty p tags and p tags containing only whitespace
    $content = preg_replace('/<p[^>]*>\s*<\/p>/', '', $content);
    // Remove p tags containing only non-breaking spaces, dashes, or similar
    $content = preg_replace('/<p[^>]*>[\s&nbsp;—\-]*<\/p>/', '', $content);
    return $content;
}

/**
 * Strip parentheses that directly surround an anchor tag.
 * Example: (<a href="...">link</a>) => <a href="...">link</a>
 */
function stripParenthesesAroundAnchorTags($content) {
    return preg_replace('/\(\s*(<a\b[^>]*>.*?<\/a>)\s*\)/i', '$1', $content);
}

/**
 * Function to verify and fix Table of Contents links
 * This function ensures that TOC links properly connect to their corresponding headings
 */
function verifyAndFixTOCLinks($content) {
    // Parse the content to extract TOC links and headings
    $toc_links = [];
    $headings = [];
    
    // Extract TOC links using regex
    preg_match_all('/<a href="#([^"]+)">([^<]+)<\/a>/', $content, $toc_matches);
    if (!empty($toc_matches[1])) {
        foreach ($toc_matches[1] as $index => $anchor) {
            $toc_links[$anchor] = $toc_matches[2][$index];
        }
    }
    
    // Extract headings with IDs using regex
    preg_match_all('/<h([2-6])(?:\s+id=\"([^\"]+)\")?>([^<]+)<\/h[2-6]>/', $content, $heading_matches);
    if (!empty($heading_matches[2])) {
        foreach ($heading_matches[2] as $index => $id) {
            if (!empty($id)) {
                $headings[$id] = $heading_matches[3][$index];
            }
        }
    }
    
    // Check for missing IDs and fix them
    $updated_content = $content;
    
    foreach ($toc_links as $anchor => $title) {
        if (!isset($headings[$anchor])) {
            // Find the heading with matching title and add ID
            $heading_pattern = '/<h([2-6])(?:\s+[^>]*)?>(' . preg_quote(trim($title), '/') . ')<\/h[2-6]>/i';
            $replacement = '<h$1 id="' . $anchor . '">$2</h$1>';
            $updated_content = preg_replace($heading_pattern, $replacement, $updated_content, 1);
        }
    }
    
    // Generate missing TOC links for headings without them
    foreach ($headings as $id => $title) {
        if (!isset($toc_links[$id])) {
            // Log missing TOC link (for debugging)
            error_log("Missing TOC link for heading: " . $title . " (ID: " . $id . ")");
        }
    }
    
    return $updated_content;
}

/**
 * Function to generate URL-friendly anchor IDs from heading text
 */
function generateAnchorId($text) {
    // Remove HTML tags, convert to lowercase, replace spaces and special chars with hyphens
    $id = strtolower(strip_tags($text));
    $id = preg_replace('/[^a-z0-9\s-]/', '', $id);
    $id = preg_replace('/[\s-]+/', '-', $id);
    $id = trim($id, '-');
    return $id;
}

/**
 * Function to automatically generate TOC from content headings
 */
function generateTOCFromContent($content) {
    preg_match_all('/<h([2-6])(?:\s+id=\"([^\"]+)\")?>([^<]+)<\/h[2-6]>/', $content, $matches);
    
    if (empty($matches[0])) {
        return '<h2 id="table-of-contents">Table of Contents</h2><p>No headings found.</p>';
    }
    
    $toc = '<h2 id="table-of-contents">Table of Contents</h2><ul>';
    
    foreach ($matches[0] as $index => $match) {
        $level = $matches[1][$index];
        $id = !empty($matches[2][$index]) ? $matches[2][$index] : generateAnchorId($matches[3][$index]);
        $title = trim($matches[3][$index]);
        
        // Only include H2 and H3 in TOC for better readability
        if ($level <= 3) {
            $indent = $level == 3 ? 'style="margin-left: 20px;"' : '';
            $toc .= '<li ' . $indent . '><a href="#' . $id . '">' . $title . '</a></li>';
        }
    }
    
    $toc .= '</ul>';
    return $toc;
}

?>