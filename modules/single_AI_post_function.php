<?php

if (file_exists(dirname(__FILE__) . '/modules/single_and_bulk_AI_post_function.php'))

	include_once dirname(__FILE__) . '/modules/single_and_bulk_AI_post_function.php';
include_once dirname(__FILE__) . '/modules/GenerateAIpopup.php';

add_action('wp_ajax_getaaldata', 'getaaldata');
add_action('wp_ajax_test_improveseo_connection', 'test_improveseo_connection');

function test_improveseo_connection() {
	// Verify nonce for security
	if (!wp_verify_nonce($_POST['nonce'], 'test_connection_nonce')) {
		wp_send_json_error(['error' => 'Security check failed']);
		return;
	}
	
	// Get credentials from request
	$api_key = sanitize_text_field($_POST['api_key']);
	$site_code = sanitize_text_field($_POST['site_code']);
	
	if (empty($api_key) || empty($site_code)) {
		wp_send_json_error(['error' => 'API Key and Site Code are required']);
		return;
	}
	
	// Test endpoint - use the actual generation endpoint with minimal payload
	$admin_server_url = 'https://imporve-seo-admin-server.onrender.com';
	$test_endpoint = $admin_server_url . '/api/v1/generate/active';
	
	// Prepare minimal test payload
	$payload = array(
		'seed_keyword' => 'test',
		'nos_of_words' => '600 to 1200 words',
		'content_lang' => 'en',
		'for_testing_only' => 1
	);
	
	// Set up cURL for test request
	$ch = curl_init($test_endpoint);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/json',
		'Accept: application/json',
		'x-api-key: ' . $api_key,
		'x-site-code: ' . $site_code
	));
	curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
	
	// Execute request
	$response = curl_exec($ch);
	$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	$curl_error = curl_error($ch);
	curl_close($ch);
	
	// Handle connection errors
	if (!empty($curl_error)) {
		wp_send_json_error(['error' => 'Connection failed: ' . $curl_error]);
		return;
	}
	
	// Handle HTTP errors
	if ($http_status !== 200) {
		$error_msg = "Server returned status: $http_status";
		if ($response) {
			$result = json_decode($response, true);
			if (isset($result['error'])) {
				$error_msg = $result['error'];
			}
		}
		wp_send_json_error(['error' => $error_msg]);
		return;
	}
	
	// Parse successful response
	$result = json_decode($response, true);
	if (!$result) {
		wp_send_json_error(['error' => 'Invalid response from server']);
		return;
	}
	
	// Return success with server data
	wp_send_json_success([
		'message' => 'Connection successful',
		'server' => 'ImproveSEO Server',
		'credits' => $result['credits'] ?? null,
		'user' => $result['user'] ?? 'Authenticated',
		'timestamp' => date('Y-m-d H:i:s')
	]);
}

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

	$content = createAIpost2(

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
function createAIpost2($seed_keyword, $keyword_selection, $seed_options, $nos_of_words, $content_lang, $shortcode = '', $is_single_keyword = '', $voice_tone = '', $point_of_view = '', $title = '', $call_to_action = '', $details_to_include = '', $for_testing_only = '')
{
	global $wpdb, $user_ID;
	
	// Get audience data from cookie (same as original function)
	$AudienceData = $_COOKIE['AudienceData'];
	
	// Admin server configuration
	$admin_server_url = 'https://imporve-seo-admin-server.onrender.com';
    $api_endpoint = $admin_server_url . '/api/v1/generate/active';
	
	// Get authentication credentials from WordPress options
	$api_key = get_option('improveseo_api_key');
	$site_code = get_option('improveseo_site_code');
	
	// Validate credentials
	if (empty($api_key) || empty($site_code)) {
		error_log("createAIpost2 Authentication Error: Missing API Key or Site Code");
		return "Error: API Key and Site Code must be configured in settings. Please check your ImproveSEO settings.";
	}
	
	// Prepare request payload matching the /active route interface
	$payload = array(
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
		'for_testing_only' => intval($for_testing_only),
		'audienceData' => $AudienceData,
		'useActivePrompts' => true,
		'customPrompts' => new stdClass(), // Empty object
		'templateVariables' => array(
			'seed_keyword' => $seed_keyword,
			'context' => $details_to_include,
			'audience_data' => $AudienceData,
			'meta_title' => $title ?: $seed_keyword,
			'whats_next_content' => $call_to_action
		)
	);
	
	// Set up cURL for HTTP request to admin server
	$ch = curl_init($api_endpoint);
	
	// Configure cURL options
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/json',
		'Accept: application/json',
		'x-api-key: ' . $api_key,
		'x-site-code: ' . $site_code
	));
	curl_setopt($ch, CURLOPT_TIMEOUT, 120); // 2 minutes timeout for AI generation
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); // 10 seconds connection timeout
	
	// Execute the request
	$response = curl_exec($ch);
	$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	
	// Check for cURL errors
	if (curl_errno($ch)) {
		$error = curl_error($ch);
		curl_close($ch);
		error_log("createAIpost2 cURL Error: " . $error);
		return "Error: Failed to connect to content generation server. Please try again.";
	}
	
	curl_close($ch);
	
	// Check HTTP status
	if ($http_status !== 200) {
		error_log("createAIpost2 HTTP Error: Status $http_status, Response: " . $response);
		return "Error: Content generation server returned error status: $http_status";
	}
	
	// Parse JSON response
	$result = json_decode($response, true);
	
	if (!$result || !isset($result['success'])) {
		error_log("createAIpost2 Invalid Response: " . $response);
		return "Error: Invalid response from content generation server.";
	}
	
	if (!$result['success']) {
		$error_msg = isset($result['error']) ? $result['error'] : 'Unknown error';
		error_log("createAIpost2 API Error: " . $error_msg);
		return "Error: " . $error_msg;
	}
	
	// Extract content from successful response
	if (!isset($result['data']['content'])) {
		error_log("createAIpost2 Missing Content: " . $response);
		return "Error: No content returned from generation server.";
	}
	
	$content_final = $result['data']['content'];
	
	// Apply the same post-processing as original function
	// Remove parentheses that wrap raw URLs/emails before converting them to links
	$content_final = stripParenthesesWrappingContactTokens($content_final);
	$content_final = convert_emails_to_links($content_final);
	$content_final = convert_urls_to_links($content_final);
	
	// Remove parentheses that wrap already-linked anchors like (<a href>..</a>)
	$content_final = stripParenthesesAroundAnchorTags($content_final);
	
	// HTML entity processing
	$content_final = htmlentities($content_final, ENT_QUOTES, 'utf-8');
	$content_final = str_replace("&nbsp;", "", $content_final);
	$content_final = str_replace("<p>&nbsp;</p>", "", $content_final);
	$content_final = str_replace("<p> </p>", "", $content_final);
	$content_final = str_replace("<p></p>", "", $content_final);
	$content_final = html_entity_decode($content_final);
	
	// Remove unwanted content
	$content_final = replace_content($content_final, '<h2>Main Content Sections</h2>');
	$content_final = replace_content($content_final, '<p>—</p>');
	
	// Apply final processing
	$content_final = removePTags($content_final);
	$content_final = removeConsecutiveSpecialCharacters($content_final);
	$content_final = verifyAndFixTOCLinks($content_final);
	
	// Add styling like original function
	$content_final = '<div class="main-content-section-improveseo">' . $content_final . '</div>';
	$content_final = $content_final . '<style> p {padding-bottom: 2px !important;} </style>';
	
	// Log the generation metadata if available (for debugging)
	if (isset($result['data']['generationMetadata'])) {
		$metadata = $result['data']['generationMetadata'];
		error_log("createAIpost2 Generation Metadata: " . json_encode($metadata));
	}
	
	return $content_final;
}

function createAIpost($seed_keyword, $keyword_selection, $seed_options, $nos_of_words, $content_lang, $shortcode = '', $is_single_keyword = '', $voice_tone = '', $point_of_view = '', $title = '', $call_to_action = '', $details_to_include = '', $for_testing_only = '')
{

	// Print all incoming parameters to browser console for debugging
	
	global $wpdb, $user_ID;

	$prompt_collection = '<b>LSI_Keyords Prompt : <b><br>';



	// Your OpenAI API key

	$apiKey = get_option('improveseo_chatgpt_api_key');



	// The endpoint URL for OpenAI chat completions API (replace with the correct endpoint)

	$apiUrl = 'https://api.openai.com/v1/chat/completions';

	$AudienceData = $_COOKIE['AudienceData'];

	// create LSI keywords

	$text_for_lsi = 'As an expert SEO manager, you are tasked with generating 50 Latent Semantic Indexing (LSI) keywords. You are provided a word or phrase that is searched by the reader, and the audience data of the reader, including demographic information, tone preferences, reading level preference and emotional needs/pain points. Using this information you should come up with the LSI keywords that will be engaging and interesting for the reader who is described in the audience data and search provided word or phrase. These keywords should be closely related to the provided main keyword, enhancing content relevance and SEO effectiveness. Please compile the keywords in a comma separated text format without any additional explanations or introductions.

   Main keyword: ' . $seed_keyword . '

   Audience data: {' . $AudienceData . '}';

	// Your chat messages

	$messages = [

		['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in ' . $content_lang],

		['role' => 'user', 'content' => $text_for_lsi]

		// ['role' => 'assistant', 'content' => 'Hello, how can I help you today?'],

	];



	// Additional parameters, including language setting (replace with actual parameters)

	$data = [

		'messages' => $messages,

		"model" => "gpt-4o",

		// 'language' => 'fr',  // Specify the result language as French

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

	$LSI_Keyords = $result['choices'][0]['message']['content'];





	$prompt_collection = $prompt_collection . $text_for_lsi . '<br>Roles<br>' . 'role : system,content : You are a helpful assistant. Please respond in ' . $content_lang . ',<br>role: user, content : ' . $text_for_lsi . '] <hr>  <b>Facts Prompt :</b> <br>';









	$facts_prompt = 'Generate 5 most interesting and fun facts with specific details about the "Main keyword" for the audience described in the audience data provided below. Each fact should be one short sentence. As an output, write just a bullet point list of facts without explanation or introduction.

Now generate facts.

Main Keyword: ' . $seed_keyword . '

Audience data: {' . $AudienceData . '}';

	//['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in '.$content_lang],

	$messages = [

		['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in ' . $content_lang],

		['role' => 'user', 'content' => $facts_prompt]

		// ['role' => 'assistant', 'content' => 'Hello, how can I help you today?'],

	];



	// Additional parameters, including language setting (replace with actual parameters)

	$data = [

		'messages' => $messages,

		"model" => "gpt-4o",

		// 'language' => 'fr',  // Specify the result language as French

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

	$facts_prompt_response = $result['choices'][0]['message']['content'];



	$prompt_collection = $prompt_collection . $facts_prompt . '<br> Message <br>' . 'role = system, content = You are a helpful assistant. Please respond in ' . $content_lang . ',<br>role = user, content = ' . $facts_prompt . '<br>';



	$prompt_collection = '';



	///////  nos_of_words  small///////

	if ($nos_of_words == '600 to 1200 words') {

		// small



		$basic_prompt = 'You are a content creator who creates SEO-optimized blog posts. You should aim at a broad audience. Use a mix of short, medium, and long sentences to create a human-like rhythm in the text. Include an analogy to explain any complex concepts or ideas. You should identify the intentions and emotions of the readers as described in the audience data. Your goal is to respond to these emotions and interests with this blog post. Consider the perspectives of both an expert and a beginner. IMPORTANT: Use standard language; avoid academic, scholarly, slang, and jargon. Follow the instructions for the tone preferences based on audience data. Write in a conversational tone and let your personality shine through. This helps build a connection with your audience. It is also important to strike a balance between being relatable/personable and being factual/authoritative. Use positive and encouraging language. NLP emphasizes the impact of positive reinforcement, which can motivate and inspire your readers.



			The user defines the main keyword, and you should make sure that the post is relevant to the main keyword.

			The user provides a title and makes sure that the post is relevant to it. 

			The user provides 50 LSI keywords and tries to incorporate them naturally throughout the content.

			Audience data: The user will include the audience data of the reader, including demographic information, tone preferences, reading level preference, and emotional needs/pain points. Use this information to tailor the content to the audience described in the audience data. Content should respond to their Emotional Needs and Pain Points.

			Details to include: The user will define additional details that need to be incorporated into the blog post.

			Language - The user defines that you should use US English, UK English, or German for the output. The headlines should be in the defined language as well.

			

			Include the following sections in the post:

			

			Introduction - Provide a concise preview of the content`s value and insights and write an engaging and informative introduction, incorporating the primary keyword, applying NLP and EI principles for emotional resonance. Do not create a header for this section, only provide the paragraph. 

			

			Table of Contents - Create a clickable table of contents with anchor links. Each item in the TOC should be formatted as a clickable link that jumps to the corresponding section. Use the format: <a href="#section-id">Section Title</a>. The section IDs should be URL-friendly (lowercase, hyphens instead of spaces, no special characters). Craft attention-grabbing subtitles that entice readers to click and read more. Use numbers, questions, and powerful words to draw interest. Use NLP techniques to craft subtitles that grab attention. Incorporate power words and questions to stimulate curiosity and engagement. Based on the main keyword and the audience data provided to you, you need to understand what are the emotions and intentions reader has while searching it. You should understand what deep questions and concerns user wants to answer and build your subtitles(subsections) based on these. Do not include numbering in the list of subtitles. Make engaging titles in the Table of Contents.  

			

			Main Content Sections - Create content sections with subtitles using keywords and their variations at a 1-2% usage rate per 100 words to prevent keyword stuffing. IMPORTANT: Each H2 heading must include an id attribute that matches the TOC links (e.g., <h2 id="section-id">Section Title</h2>). Each section should contain a detailed content, employing NLP and EI for relatability and actionability. Make the content deep so it responds to the emotions and curiosity of the readers. Use storytelling techniques to make your content more relatable and memorable. Share personal anecdotes, case studies, and real-life examples. Stories are a powerful NLP tool to create an emotional connection. Share personal anecdotes or relatable scenarios to make your content more engaging and memorable. Based on the main keyword and the audience data provided to you, you need to understand what are the emotions and intentions user has while searching it. You should understand what deep questions and concerns users want to answer and build your output based on these. Use the following NLP Techniques for creating content:

				Anchoring: Use anchoring to associate positive emotions with your content. For instance, repeatedly use a specific phrase or concept that evokes a positive response.

				Reframing: Present your points in a way that shifts the reader is perspective. For example, instead of highlighting a problem, focus on the opportunity it presents.

				Vivid Descriptions: Use descriptive language to paint vivid images and evoke emotions. This helps readers feel more connected to your content.

				Addressing Reader Emotions: Acknowledge and validate the emotions your readers might be experiencing. This creates a sense of understanding and connection.

				High-Quality Content: Ensure your content is well-researched, informative, and adds value to your readers. Provide actionable insights and practical tips.

			

			Conclusion - Summarize key insights, encouraging further exploration or engagement. 

			

			FAQ - Come up with 3 FAQ that the reader may have. Provide questions and answers with clear, informative, tone empathize with the reader`s concerns.

			

			What’s Next? - Write a short paragraph inviting the reader to take action in the explained way, including links or phone numbers if provided. Incorporate "Call tu action" provided by user. If call to action is blank you should write a general paragraph without specific contact details or further steps anyway.

			

			Use the following formatting and structure for the output:

			{

			IMPORTANT: Never include the Blog Post Title. Start with the introduction paragraph

			

			Introduction - Introduction should not be more than 100-150 words.(do not include any title, just paragraph)

			

			<h2 id="table-of-contents">Table of Contents</h2> (Heading 2) - should not be more than 50 words. Format as clickable links: <ul><li><a href="#section-id">Section Title</a></li></ul>

			

			Main Content Sections - Create 4 sections. Each section should not be more than 200-250 words of detailed content. Use format: <h2 id="section-id">Section Title</h2>

			

			<h2 id="conclusion">Conclusion</h2> (Heading 2) - Conclusion should not be more than 100-150 words.

			

			<h2 id="faqs">FAQs</h2> (Heading 2) - FAQs should not be more than 100-150 words.

			Q: 

			A:

			

			Q: 

			A: 

			

			Q:

			A: 

			

			<h2 id="whats-next">What\'s Next?</h2> (Heading 2) - What\'s next? should not be more than 100-150 words.

			}

			

			Use the iterative approach to improve upon your initial draft. After each draft, critique your work, give it a score out of 10, and if the score is below 9, improve upon the previous draft. Repeat this process until you achieve a score of 9 or 10. When doing this, review and edit your work to remove any grammatical errors, unnecessary information, and superfluous sentences. Don`t provide output of this critique, this is only for you to analyze internally. Also, check the formatting, output should not include a title of the blog post and each section/subsection should have a title with a specific heading type. 

			Now generate ONLY the Introduction and the Table of Contents based on the following parameters:



				Main keyword: ' . $seed_keyword . '

				Title: "' . $title . '"

				LSI keywords: ' . $LSI_Keyords . '

				Tone of voice: ' . $voice_tone . ' 

				Point of view: ' . $point_of_view . '

				Audience data: {' . $AudienceData . '}

				Details to include: ' . $details_to_include . ' 

				Language: ' . $content_lang . '

				Call to action from user: `' . $call_to_action . '`

				Facts to include: {' . $facts_prompt_response . '} Do not print "Main Content Sections" text in output.';













		// Your chat messages

		$messages = [

			['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in ' . $content_lang],

			['role' => 'user', 'content' => $basic_prompt]

			// ['role' => 'assistant', 'content' => 'Hello, how can I help you today?'],

		];



		// Additional parameters, including language setting (replace with actual parameters)

		$data = [

			'messages' => $messages,

			"model" => "gpt-4o",

			// 'language' => 'fr',  // Specify the result language as French

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

		//echo "<h2> Basic Prompt </h2>".$basic_prompt."<br><h2>Response of basic promot</h2><br>";

		$basic_prompt_response = $result['choices'][0]['message']['content'];

		//exit();





		$prompt_collection = $prompt_collection . '<br> <b> Basic Prompt (600 to 1200 words)  :</b> <br>' . $basic_prompt . '<br><b>Basic Prompt Response</b><br>' . $basic_prompt_response . '<br>';







		$first_call_for_small = 'Now generate the first subtitle content. IMPORTANT: Output should not be more than 200-250 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.';

		// Your chat messages

		$messages = [

			['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in ' . $content_lang],

			['role' => 'user', 'content' => $basic_prompt],

			['role' => 'assistant', 'content' => $basic_prompt_response],

			['role' => 'user', 'content' => $first_call_for_small]

		];



		// Additional parameters, including language setting (replace with actual parameters)

		$data = [

			'messages' => $messages,

			"model" => "gpt-4o",

			// 'language' => 'fr',  // Specify the result language as French

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

		$response_first_call_for_small = $result['choices'][0]['message']['content'];



		$prompt_collection = $prompt_collection . '<br><br><b><h2>Step 1 prompt:<h2></b> <br>' . $first_call_for_small . '<br>' . '<b> Step 1 response</b><br>' . $response_first_call_for_small;







		// second call

		$second_call_for_small = 'Now generate the second subtitle content. IMPORTANT: Output should not be more than 200-250 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.';

		// Your chat messages

		$messages = [

			['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in ' . $content_lang],

			['role' => 'user', 'content' => $basic_prompt],

			['role' => 'assistant', 'content' => $basic_prompt_response],

			['role' => 'user', 'content' => $first_call_for_small],

			['role' => 'assistant', 'content' => $response_first_call_for_small],

			['role' => 'user', 'content' => $second_call_for_small],

		];



		// Additional parameters, including language setting (replace with actual parameters)

		$data = [

			'messages' => $messages,

			"model" => "gpt-4o",

			// 'language' => 'fr',  // Specify the result language as French

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

		$response_secound_call_for_small = $result['choices'][0]['message']['content'];





		$prompt_collection = $prompt_collection . '<br><b>second_call_for_small</b><br>' . $second_call_for_small . '<b>Second call response : </b><br>' . $response_secound_call_for_small;





		///// third call





		$third_call_for_small = 'Now generate the third subtitle content. IMPORTANT: Output should not be more than 200-250 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.';

		// Your chat messages

		$messages = [

			['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in ' . $content_lang],

			['role' => 'user', 'content' => $basic_prompt],

			['role' => 'assistant', 'content' => $basic_prompt_response],

			['role' => 'user', 'content' => $first_call_for_small],

			['role' => 'assistant', 'content' => $response_first_call_for_small],

			['role' => 'user', 'content' => $second_call_for_small],

			['role' => 'assistant', 'content' => $response_secound_call_for_small],

			['role' => 'user', 'content' => $third_call_for_small],

		];



		// Additional parameters, including language setting (replace with actual parameters)

		$data = [

			'messages' => $messages,

			"model" => "gpt-4o",

			// 'language' => 'fr',  // Specify the result language as French

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

		$response_third_call_for_small = $result['choices'][0]['message']['content'];



		$prompt_collection = $prompt_collection . '<br><b>third_call_for_small></b><br>' . $third_call_for_small . '<br><b>Response for 3rd call</b><br>' . $response_third_call_for_small;

		/////// 4th call 



		$fourth_call_for_small = 'Now generate the forth subtitle content. IMPORTANT: Output should not be more than 200-250 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.';

		// Your chat messages

		$messages = [

			['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in ' . $content_lang],

			['role' => 'user', 'content' => $basic_prompt],

			['role' => 'assistant', 'content' => $basic_prompt_response],

			['role' => 'user', 'content' => $first_call_for_small],

			['role' => 'assistant', 'content' => $response_first_call_for_small],

			['role' => 'user', 'content' => $second_call_for_small],

			['role' => 'assistant', 'content' => $response_secound_call_for_small],

			['role' => 'user', 'content' => $third_call_for_small],

			['role' => 'assistant', 'content' => $response_third_call_for_small],

			['role' => 'user', 'content' => $fourth_call_for_small],

		];



		// Additional parameters, including language setting (replace with actual parameters)

		$data = [

			'messages' => $messages,

			"model" => "gpt-4o",

			// 'language' => 'fr',  // Specify the result language as French

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

		$response_fourth_call_for_small = $result['choices'][0]['message']['content'];







		// $prompt_collection = $prompt_collection.'<br>fourth_call_for_small<br>'.$fourth_call_for_small.'<br>'.'[

		// 	[role => system, content => You are a helpful assistant. Please respond in '.$content_lang.'],<br>

		// 	[role => user, content => '.$basic_prompt.'],<br>

		// 	[role => assistant, content => '.$basic_prompt_response.'],<br>

		// 	[role => user, content => '.$first_call_for_small.'],<br>

		// 	[role => assistant, content => '.$response_first_call_for_small.'],<br>

		// 	[role => user, content => '.$second_call_for_small.'],<br>

		// 	[role => assistant, content => '.$response_secound_call_for_small.'],<br>

		// 	[role => user, content => '.$third_call_for_small.'],<br>

		// 	[role => assistant, content => '.$response_third_call_for_small.'],<br>

		// 	[role => user, content => '.$fourth_call_for_small.'],<br>

		// ]';







		// 5th call 





		$fifth_call_for_small = 'Now generate the conclusion content. IMPORTANT: Output should not be more than 100-150 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.';

		// Your chat messages

		$messages = [

			['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in ' . $content_lang],

			['role' => 'user', 'content' => $basic_prompt],

			['role' => 'assistant', 'content' => $basic_prompt_response],

			['role' => 'user', 'content' => $first_call_for_small],

			['role' => 'assistant', 'content' => $response_first_call_for_small],

			['role' => 'user', 'content' => $second_call_for_small],

			['role' => 'assistant', 'content' => $response_secound_call_for_small],

			['role' => 'user', 'content' => $third_call_for_small],

			['role' => 'assistant', 'content' => $response_third_call_for_small],

			['role' => 'user', 'content' => $fourth_call_for_small],

			['role' => 'assistant', 'content' => $response_fourth_call_for_small],

			['role' => 'user', 'content' => $fifth_call_for_small],

		];



		// Additional parameters, including language setting (replace with actual parameters)

		$data = [

			'messages' => $messages,

			"model" => "gpt-4o",

			// 'language' => 'fr',  // Specify the result language as French

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

		$response_fifth_call_for_small = $result['choices'][0]['message']['content'];







		$prompt_collection = $prompt_collection . '<br><b>fifth_call_for_small<b><br>' . $fifth_call_for_small . '<br><b>Fifth call response</b>' . $response_fifth_call_for_small;











		// 6th call 





		$sixth_call_for_small = 'Now generate the FAQs content. IMPORTANT: Output should not be more than 100-150 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.';

		// Your chat messages

		$messages = [

			['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in ' . $content_lang],

			['role' => 'user', 'content' => $basic_prompt],

			['role' => 'assistant', 'content' => $basic_prompt_response],

			['role' => 'user', 'content' => $first_call_for_small],

			['role' => 'assistant', 'content' => $response_first_call_for_small],

			['role' => 'user', 'content' => $second_call_for_small],

			['role' => 'assistant', 'content' => $response_secound_call_for_small],

			['role' => 'user', 'content' => $third_call_for_small],

			['role' => 'assistant', 'content' => $response_third_call_for_small],

			['role' => 'user', 'content' => $fourth_call_for_small],

			['role' => 'assistant', 'content' => $response_fourth_call_for_small],

			['role' => 'user', 'content' => $fifth_call_for_small],

			['role' => 'assistant', 'content' => $response_fifth_call_for_small],

			['role' => 'user', 'content' => $sixth_call_for_small],

		];



		// Additional parameters, including language setting (replace with actual parameters)

		$data = [

			'messages' => $messages,

			"model" => "gpt-4o",

			// 'language' => 'fr',  // Specify the result language as French

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

		$response_sixth_call_for_small = $result['choices'][0]['message']['content'];





		$prompt_collection = $prompt_collection . '<br><b>sixth_call_for_small</b><br>' . $sixth_call_for_small . '<br><b>response for 6th call</b>' . $response_sixth_call_for_small;





		// 7th call 





		$seventh_call_for_small = 'Now generate What is next? content. IMPORTANT: Output should not be more than 100-150 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.

			';

		// Your chat messages

		$messages = [

			['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in ' . $content_lang],

			['role' => 'user', 'content' => $basic_prompt],

			['role' => 'assistant', 'content' => $basic_prompt_response],

			['role' => 'user', 'content' => $first_call_for_small],

			['role' => 'assistant', 'content' => $response_first_call_for_small],

			['role' => 'user', 'content' => $second_call_for_small],

			['role' => 'assistant', 'content' => $response_secound_call_for_small],

			['role' => 'user', 'content' => $third_call_for_small],

			['role' => 'assistant', 'content' => $response_third_call_for_small],

			['role' => 'user', 'content' => $fourth_call_for_small],

			['role' => 'assistant', 'content' => $response_fourth_call_for_small],

			['role' => 'user', 'content' => $fifth_call_for_small],

			['role' => 'assistant', 'content' => $response_fifth_call_for_small],

			['role' => 'user', 'content' => $sixth_call_for_small],

			['role' => 'assistant', 'content' => $response_sixth_call_for_small],

			['role' => 'user', 'content' => $seventh_call_for_small],

		];



		// Additional parameters, including language setting (replace with actual parameters)

		$data = [

			'messages' => $messages,

			"model" => "gpt-4o",

			// 'language' => 'fr',  // Specify the result language as French

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

		$response_seventh_call_for_small = $result['choices'][0]['message']['content'];


		$prompt_collection = $prompt_collection . '<br><b>seventh_call_for_small</b><br>' . $seventh_call_for_small . '<br><b>response for 7th call</b>' . $response_seventh_call_for_small;

		// For Testing purposes - Checklist

		if ($seed_options == 'seed_option3') {

			$title_type = 'Question';

		} else {

			$title_type = 'Regular';

		}

		if ($for_testing_only == 1) {

			$test_prupose = "<h1>For Testing purposes - Checklist</h1>";

			$test_prupose = $test_prupose . "Main keyword: <b>" . $seed_keyword . "</b></br>

			Title: <b>" . $title . "</b></br>

			Title type: <b>" . $title_type . "</b></br>

			Article size/number of words: <b>" . $nos_of_words . "</b></br>

			LSI keywords: <b>" . $LSI_Keyords . "</b></br>

			Tone of voice: <b>" . $voice_tone . "</b></br>

			Point of view: <b>" . $point_of_view . "</b></br>

			Audience data: {<b>" . $AudienceData . "</b>}</br>

			Details to include: <b>" . $details_to_include . "</b></br>

			Language: <b>" . $content_lang . "</b></br>

			Call to action from user: <b>" . $call_to_action . "</b></br>

			Facts to include: {<b>" . $facts_prompt_response . "</b> }</br>

			Words to exclude:  meticulous, meticulously, navigating, complexities, realm, bespoke, tailored, towards, underpins, everchanging, ever-evolving, the world of, not only, seeking more than just, designed to enhance, it’s not merely, our suite, it is advisable, daunting, in the heart of, when it comes to, in the realm of, amongst unlock the secrets, unveil the secrets and robust";

		}





		$content_final = '<div class="main-content-section-improveseo">' . $basic_prompt_response . '<div style="margin-bottom: 15px;margin-top: 50px;">' . $response_first_call_for_small . '</div><div style="margin-bottom: 15px;margin-top: 50px;">' . $response_secound_call_for_small . '</div><div style="margin-bottom: 15px;margin-top: 50px;">' . $response_third_call_for_small . '</div><div style="margin-bottom: 15px;margin-top: 50px;">' . $response_fourth_call_for_small . '</div><div style="margin-bottom: 15px;margin-top: 50px;">' . $response_fifth_call_for_small . '</div><div style="margin-bottom: 15px;margin-top: 50px;">' . $response_sixth_call_for_small . '</div><div style="margin-bottom: 15px;margin-top: 50px;">' . $response_seventh_call_for_small . '</div><div style="margin-bottom: 15px;margin-top: 50px;">' . $test_prupose . '</div></div>';

		// Verify and fix TOC links
		$content_final = verifyAndFixTOCLinks($content_final);





	} elseif ($nos_of_words == '1200 to 2400 words') {

		// medium





		$basic_prompt = 'You are a content creator who creates SEO-optimized blog posts. You should aim at a broad audience. Ensure that sentence and paragraph lengths are varied, as is seen in natural human writing. Ensure heterogeneous paragraphs. Ensure heterogeneous sentence lengths. And stick to primarily short, straightforward sentences to create a human-like rhythm in the text. Engagement is the highest priority. Be conversational, empathetic, and occasionally humorous. Use idioms, metaphors, anecdotes and natural dialogue. Include an analogy to explain any complex concepts or ideas. You should identify the intentions and emotions of the readers as described in the audience data. Your goal is to respond to these emotions and interests with this blog post. Consider the perspectives of both an expert and a beginner. IMPORTANT: Use standard language; avoid academic, scholarly, slang, and jargon. Follow the instructions for the tone preferences based on audience data. Write in a conversational tone and let your personality shine through. This helps build a connection with your audience. It is also important to strike a balance between being relatable/personable and being factual/authoritative. Use positive and encouraging language. NLP emphasizes the impact of positive reinforcement, which can motivate and inspire your readers. Don’t always use the most natural words. Use the following words fewer than 3 times on this page: unique, ensure, utmost, transformative, transformation, routine. Before outputting the content, review it for the following words and rewrite those sentences with appropriate alternatives: meticulous, meticulously, navigating, complexities, realm, bespoke, tailored, towards, underpins, everchanging, ever-evolving, the world of, not only, seeking more than just, designed to enhance, it’s not merely, our suite, it is advisable, daunting, in the heart of, when it comes to, in the realm of, amongst unlock the secrets, unveil the secrets and robust.



The user defines the main keyword, and you should make sure that the post is relevant to the main keyword.

The user provides a title and makes sure that the post is relevant to it. 

The user provides 50 LSI keywords and tries to incorporate them naturally throughout the content.

The user provides "Facts to include" that should be smoothly incorporated in the introduction and the Main content sections of the output.

Audience data: The user will include the audience data of the reader, including demographic information, tone preferences, reading level preference, and emotional needs/pain points. Use this information to tailor the content to the audience described in the audience data. Content should respond to their Emotional Needs and Pain Points.

Details to include: The user will define additional details that need to be incorporated into the blog post.

Language - The user defines that you should use US English, UK English, or German for the output. The headlines should be in the defined language as well.



Include the following sections in the post:



Introduction - Provide a concise preview of the content`s value and insights and write an engaging and informative introduction, incorporating the primary keyword, applying NLP and EI principles for emotional resonance. Use the "Facts to include" provided by the user. Do not use all of them. Incorporate them smoothly so that it is part of the story flow and reads naturally. Don’t create a header for this section, only provide the paragraph. 



Table of Contents - Outline main content areas of the post. Craft attention-grabbing subtitles that entice readers to click and read more. Use numbers, questions, and powerful words to draw interest. Use NLP techniques to craft subtitles that grab attention. Incorporate power words and questions to stimulate curiosity and engagement. Based on the main keyword and the audience data provided to you, you need to understand what are the emotions and intentions reader has while searching it. You should understand what deep questions and concerns user wants to answer and build your subtitles(subsections) based on these. Do not list Section titles, make short list of subtitles that will be described in Main Content Section, do not include numbering in the list of subtitles. Make engaging titles in the Table of Contents. 



Main Content Sections - Create content sections with subtitles using keywords and their variations at a 1-2% usage rate per 100 words to prevent keyword stuffing. Each section should contain a detailed content, employing NLP and EI for relatability and actionability. Make the content deep so it responds to the emotions and curiosity of the readers. Use storytelling techniques to make your content more relatable and memorable. Share personal anecdotes, case studies, and real-life examples. Stories are a powerful NLP tool to create an emotional connection. Share personal anecdotes or relatable scenarios to make your content more engaging and memorable. Prevent from producing worthless fluff content that doesn’t add to the value of the blog post. Do not include any fluff when producing content. Each sentence should provide value to the overall goal of the content piece. Strictly follow this guideline. Ensure to insert interesting and fun facts about the Main keyword when producing the content: use the "Facts to include" provided by the user. Do not use all of them. Incorporate them smoothly so that it is part of the story flow and reads naturally. DO NOT include any conclusion or summary for each content sections. Based on the main keyword and the audience data provided to you, you need to understand what are the emotions and intentions user has while searching it. You should understand what deep questions and concerns users want to answer and build your output based on these. Use the following NLP Techniques for creating content:

    Anchoring: Use anchoring to associate positive emotions with your content. For instance, repeatedly use a specific phrase or concept that evokes a positive response.

    Reframing: Present your points in a way that shifts the reader’s perspective. For example, instead of highlighting a problem, focus on the opportunity it presents.

    Vivid Descriptions: Use descriptive language to paint vivid images and evoke emotions. This helps readers feel more connected to your content.

    Addressing Reader Emotions: Acknowledge and validate the emotions your readers might be experiencing. This creates a sense of understanding and connection.

    High-Quality Content: Ensure your content is well-researched, informative, and adds value to your readers. Provide actionable insights and practical tips.



Conclusion - Summarize key insights, encouraging further exploration or engagement. Do not include call to action details in the conclusion. 



FAQ - Come up with 3 FAQ that the reader may have. Provide questions and answers with clear, informative, tone empathize with the reader`s concerns.



What’s Next? - Write a short paragraph inviting the reader to take action in the explained way, including links or phone numbers if provided. Incorporate "Call to action" provided by user. If call to action is blank you should write a general paragraph without specific contact details or further steps anyway.



Use the following formatting and structure for the output:

{

IMPORTANT: Never include the Blog Post Title. Start with the introduction paragraph



Introduction - Introduction should not be more than 100-150 words.(do not include any title, just paragraph)



<h2 id="table-of-contents">Table of Contents</h2> (Heading 2) - should not be more than 50 words. Format as clickable links: <ul><li><a href="#section-id">Section Title</a></li></ul>



Main Content Sections - Create 4 sections. Create 2-3 subsections and subtitles with formatting H3 for each section so it does not exceed required word quantity. IMPORTANT: Each section should not be more than 350-400 words. Use format: <h2 id="section-id">Section Title</h2> and <h3 id="subsection-id">Subsection Title</h3>



<h2 id="conclusion">Conclusion</h2> (Heading 2) - Conclusion should not be more than 100-150 words. Do not include call to action details in the conclusion.



<h2>FAQs</h2> (Heading 2) - FAQs should not be more than 100-150 words.

Q: 

A:



Q: 

A: 



Q:

A: 



<h2>What’s next?</h2> (Heading 2) - What’s next? should not be more than 100-150 words.

}



Use the iterative approach to improve upon your initial draft. After each draft, critique your work, give it a score out of 10, and if the score is below 9, improve upon the previous draft. Repeat this process until you achieve a score of 9 or 10. When doing this, review and edit your work to remove any grammatical errors, unnecessary information, and superfluous sentences. Don`t provide output of this critique, this is only for you to analyze internally. Also, check the formatting, output should not include a title of the blog post and each section/subsection should have a title with a specific heading type. 

Now generate ONLY the Introduction and the Table of Contents based on the following parameters:





				Main keyword: ' . $seed_keyword . '

				Title: "' . $title . '"

				LSI keywords: ' . $LSI_Keyords . '

				Tone of voice: ' . $voice_tone . ' 

				Point of view: ' . $point_of_view . '

				Audience data: {' . $AudienceData . '}

				Details to include: ' . $details_to_include . ' 

				Language: ' . $content_lang . '

				Call to action from user: `' . $call_to_action . '`

				Facts to include: {' . $facts_prompt_response . '} Do not print "Main Content Sections" text in output.';













		// Your chat messages

		$messages = [

			['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in ' . $content_lang],

			['role' => 'user', 'content' => $basic_prompt]

			// ['role' => 'assistant', 'content' => 'Hello, how can I help you today?'],

		];



		// Additional parameters, including language setting (replace with actual parameters)

		$data = [

			'messages' => $messages,

			"model" => "gpt-4o",

			// 'language' => 'fr',  // Specify the result language as French

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

		$basic_prompt_response = $result['choices'][0]['message']['content'];







		$prompt_collection = $prompt_collection . '<br><b>Basic prompt for 1200 to 2400 words</b><br>' . $basic_prompt . '<br><b>response for basic prompt</b>' . $basic_prompt_response;



		$first_call_for_medium = 'Now generate the first subtitle content. IMPORTANT: Output should not be more than 350-400 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.';

		// Your chat messages

		$messages = [

			['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in ' . $content_lang],

			['role' => 'user', 'content' => $basic_prompt],

			['role' => 'assistant', 'content' => $basic_prompt_response],

			['role' => 'user', 'content' => $first_call_for_medium]

		];



		// Additional parameters, including language setting (replace with actual parameters)

		$data = [

			'messages' => $messages,

			"model" => "gpt-4o",

			// 'language' => 'fr',  // Specify the result language as French

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

		$response_first_call_for_medium = $result['choices'][0]['message']['content'];





		$prompt_collection = $prompt_collection . '<br><b>1st call request</b><br>' . $first_call_for_medium . '<br><b>response for 1st response</b>' . $response_first_call_for_medium;



		// second call

		$second_call_for_medium = 'Now generate the second subtitle content. IMPORTANT: Output should not be more than 350-400 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.';

		// Your chat messages

		$messages = [

			['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in ' . $content_lang],

			['role' => 'user', 'content' => $basic_prompt],

			['role' => 'assistant', 'content' => $basic_prompt_response],

			['role' => 'user', 'content' => $first_call_for_medium],

			['role' => 'assistant', 'content' => $response_first_call_for_medium],

			['role' => 'user', 'content' => $second_call_for_medium],

		];



		// Additional parameters, including language setting (replace with actual parameters)

		$data = [

			'messages' => $messages,

			"model" => "gpt-4o",

			// 'language' => 'fr',  // Specify the result language as French

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

		$response_secound_call_for_medium = $result['choices'][0]['message']['content'];





		$prompt_collection = $prompt_collection . '<br><b>2nd call request</b><br>' . $second_call_for_medium . '<br><b>response for 2nd response</b>' . $response_secound_call_for_medium;



		///// third call





		$third_call_for_medium = 'Now generate the third subtitle content. IMPORTANT: Output should not be more than 350-400 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.';

		// Your chat messages

		$messages = [

			['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in ' . $content_lang],

			['role' => 'user', 'content' => $basic_prompt],

			['role' => 'assistant', 'content' => $basic_prompt_response],

			['role' => 'user', 'content' => $first_call_for_medium],

			['role' => 'assistant', 'content' => $response_first_call_for_medium],

			['role' => 'user', 'content' => $second_call_for_medium],

			['role' => 'assistant', 'content' => $response_secound_call_for_medium],

			['role' => 'user', 'content' => $third_call_for_medium],

		];



		// Additional parameters, including language setting (replace with actual parameters)

		$data = [

			'messages' => $messages,

			"model" => "gpt-4o",

			// 'language' => 'fr',  // Specify the result language as French

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

		$response_third_call_for_medium = $result['choices'][0]['message']['content'];



		$prompt_collection = $prompt_collection . '<br><b>3rd call request</b><br>' . $third_call_for_medium . '<br><b>response for 3rd response</b>' . $response_third_call_for_medium;







		/////// 4th call 



		$fourth_call_for_medium = 'Now generate the forth subtitle content. IMPORTANT: Output should not be more than 350-400 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.';

		// Your chat messages

		$messages = [

			['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in ' . $content_lang],

			['role' => 'user', 'content' => $basic_prompt],

			['role' => 'assistant', 'content' => $basic_prompt_response],

			['role' => 'user', 'content' => $first_call_for_medium],

			['role' => 'assistant', 'content' => $response_first_call_for_medium],

			['role' => 'user', 'content' => $second_call_for_medium],

			['role' => 'assistant', 'content' => $response_secound_call_for_medium],

			['role' => 'user', 'content' => $third_call_for_medium],

			['role' => 'assistant', 'content' => $response_third_call_for_medium],

			['role' => 'user', 'content' => $fourth_call_for_medium],

		];



		// Additional parameters, including language setting (replace with actual parameters)

		$data = [

			'messages' => $messages,

			"model" => "gpt-4o",

			// 'language' => 'fr',  // Specify the result language as French

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

		$response_fourth_call_for_medium = $result['choices'][0]['message']['content'];



		$prompt_collection = $prompt_collection . '<br><b>4th call request</b><br>' . $fourth_call_for_medium . '<br><b>response for 4th response</b>' . $response_fourth_call_for_medium;











		// 5th call 





		$fifth_call_for_medium = 'Now generate the conclusion content. IMPORTANT: Output should not be more than 150-200 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.';

		// Your chat messages

		$messages = [

			['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in ' . $content_lang],

			['role' => 'user', 'content' => $basic_prompt],

			['role' => 'assistant', 'content' => $basic_prompt_response],

			['role' => 'user', 'content' => $first_call_for_medium],

			['role' => 'assistant', 'content' => $response_first_call_for_medium],

			['role' => 'user', 'content' => $second_call_for_medium],

			['role' => 'assistant', 'content' => $response_secound_call_for_medium],

			['role' => 'user', 'content' => $third_call_for_medium],

			['role' => 'assistant', 'content' => $response_third_call_for_medium],

			['role' => 'user', 'content' => $fourth_call_for_medium],

			['role' => 'assistant', 'content' => $response_fourth_call_for_medium],

			['role' => 'user', 'content' => $fifth_call_for_medium],

		];



		// Additional parameters, including language setting (replace with actual parameters)

		$data = [

			'messages' => $messages,

			"model" => "gpt-4o",

			// 'language' => 'fr',  // Specify the result language as French

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

		$response_fifth_call_for_medium = $result['choices'][0]['message']['content'];





		$prompt_collection = $prompt_collection . '<br><b>5th call request</b><br>' . $fifth_call_for_medium . '<br><b>response for 5th response</b>' . $response_fifth_call_for_medium;













		// 6th call 





		$sixth_call_for_medium = 'Now generate the FAQs content. IMPORTANT: Output should not be more than 100-150 words. After writing an output check the word count and regenerate if it is not in the rage. Do not include the word count in the output.';

		// Your chat messages

		$messages = [

			['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in ' . $content_lang],

			['role' => 'user', 'content' => $basic_prompt],

			['role' => 'assistant', 'content' => $basic_prompt_response],

			['role' => 'user', 'content' => $first_call_for_medium],

			['role' => 'assistant', 'content' => $response_first_call_for_medium],

			['role' => 'user', 'content' => $second_call_for_medium],

			['role' => 'assistant', 'content' => $response_secound_call_for_medium],

			['role' => 'user', 'content' => $third_call_for_medium],

			['role' => 'assistant', 'content' => $response_third_call_for_medium],

			['role' => 'user', 'content' => $fourth_call_for_medium],

			['role' => 'assistant', 'content' => $response_fourth_call_for_medium],

			['role' => 'user', 'content' => $fifth_call_for_medium],

			['role' => 'assistant', 'content' => $response_fifth_call_for_medium],

			['role' => 'user', 'content' => $sixth_call_for_medium],

		];



		// Additional parameters, including language setting (replace with actual parameters)

		$data = [

			'messages' => $messages,

			"model" => "gpt-4o",

			// 'language' => 'fr',  // Specify the result language as French

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

		$response_sixth_call_for_medium = $result['choices'][0]['message']['content'];



		$prompt_collection = $prompt_collection . '<br><b>6th call request</b><br>' . $sixth_call_for_medium . '<br><b>response for 6th response</b>' . $response_sixth_call_for_medium;





		// 7th call 





		$seventh_call_for_medium = 'Now generate What is next? content. IMPORTANT: Output should not be more than 150-200 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.';

		// Your chat messages

		$messages = [

			['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in ' . $content_lang],

			['role' => 'user', 'content' => $basic_prompt],

			['role' => 'assistant', 'content' => $basic_prompt_response],

			['role' => 'user', 'content' => $first_call_for_medium],

			['role' => 'assistant', 'content' => $response_first_call_for_medium],

			['role' => 'user', 'content' => $second_call_for_medium],

			['role' => 'assistant', 'content' => $response_secound_call_for_medium],

			['role' => 'user', 'content' => $third_call_for_medium],

			['role' => 'assistant', 'content' => $response_third_call_for_medium],

			['role' => 'user', 'content' => $fourth_call_for_medium],

			['role' => 'assistant', 'content' => $response_fourth_call_for_medium],

			['role' => 'user', 'content' => $fifth_call_for_medium],

			['role' => 'assistant', 'content' => $response_fifth_call_for_medium],

			['role' => 'user', 'content' => $sixth_call_for_medium],

			['role' => 'assistant', 'content' => $response_sixth_call_for_medium],

			['role' => 'user', 'content' => $seventh_call_for_medium],

		];



		// Additional parameters, including language setting (replace with actual parameters)

		$data = [

			'messages' => $messages,

			"model" => "gpt-4o",

			// 'language' => 'fr',  // Specify the result language as French

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

		$response_seventh_call_for_medium = $result['choices'][0]['message']['content'];



		$prompt_collection = $prompt_collection . '<br><b>7th call request</b><br>' . $seventh_call_for_medium . '<br><b>response for 7th response</b>' . $response_seventh_call_for_medium;


		// For Testing purposes - Checklist

		if ($seed_options == 'seed_option3') {

			$title_type = 'Question';

		} else {

			$title_type = 'Regular';

		}



		if ($for_testing_only == 1) {

			$test_prupose = "<h1>For Testing purposes - Checklist</h1>";

			$test_prupose = $test_prupose . "Main keyword: <b>" . $seed_keyword . "</b></br>

			Title: <b>" . $title . "</b></br>

			Title type: <b>" . $title_type . "</b></br>

			Article size/number of words: <b>" . $nos_of_words . "</b></br>

			LSI keywords: <b>" . $LSI_Keyords . "</b></br>

			Tone of voice: <b>" . $voice_tone . "</b></br>

			Point of view: <b>" . $point_of_view . "</b></br>

			Audience data: {<b>" . $AudienceData . "</b>}</br>

			Details to include: <b>" . $details_to_include . "</b></br>

			Language: <b>" . $content_lang . "</b></br>

			Call to action from user: <b>" . $call_to_action . "</b></br>

			Facts to include: {<b>" . $facts_prompt_response . "</b> }</br>

			Words to exclude:  meticulous, meticulously, navigating, complexities, realm, bespoke, tailored, towards, underpins, everchanging, ever-evolving, the world of, not only, seeking more than just, designed to enhance, it’s not merely, our suite, it is advisable, daunting, in the heart of, when it comes to, in the realm of, amongst unlock the secrets, unveil the secrets and robust";

		}





		$content_final = '<div class="main-content-section-improveseo">' . $basic_prompt_response . '<div style="margin-bottom: 15px;margin-top: 50px;">' . $response_first_call_for_medium . '</div><div style="margin-bottom: 15px;margin-top: 50px;">' . $response_secound_call_for_medium . '</div><div style="margin-bottom: 15px;margin-top: 50px;">' . $response_third_call_for_medium . '</div><div style="margin-bottom: 15px;margin-top: 50px;">' . $response_fourth_call_for_medium . '</div><div style="margin-bottom: 15px;margin-top: 50px;">' . $response_fifth_call_for_medium . '</div><div style="margin-bottom: 15px;margin-top: 50px;">' . $response_sixth_call_for_medium . '</div><div style="margin-bottom: 15px;margin-top: 50px;">' . $response_seventh_call_for_medium . '</div><div style="margin-bottom: 15px;margin-top: 50px;">' . $test_prupose . '</div></div>';







	} else {

		//large









		$basic_prompt = 'You are a content creator who creates SEO-optimized blog posts. You should aim at a broad audience. Ensure that sentence and paragraph lengths are varied, as is seen in natural human writing. Ensure heterogeneous paragraphs. Ensure heterogeneous sentence lengths. And stick to primarily short, straightforward sentences to create a human-like rhythm in the text. Engagement is the highest priority. Be conversational, empathetic, and occasionally humorous. Use idioms, metaphors, anecdotes and natural dialogue. Include an analogy to explain any complex concepts or ideas. You should identify the intentions and emotions of the readers as described in the audience data. Your goal is to respond to these emotions and interests with this blog post. Consider the perspectives of both an expert and a beginner. IMPORTANT: Use standard language; avoid academic, scholarly, slang, and jargon. Follow the instructions for the tone preferences based on audience data. Write in a conversational tone and let your personality shine through. This helps build a connection with your audience. It is also important to strike a balance between being relatable/personable and being factual/authoritative. Use positive and encouraging language. NLP emphasizes the impact of positive reinforcement, which can motivate and inspire your readers. Don’t always use the most natural words. Use the following words fewer than 3 times on this page: unique, ensure, utmost, transformative, transformation, routine. Before outputting the content, review it for the following words and rewrite those sentences with appropriate alternatives: meticulous, meticulously, navigating, complexities, realm, bespoke, tailored, towards, underpins, everchanging, ever-evolving, the world of, not only, seeking more than just, designed to enhance, it’s not merely, our suite, it is advisable, daunting, in the heart of, when it comes to, in the realm of, amongst unlock the secrets, unveil the secrets and robust.



The user defines the main keyword, and you should make sure that the post is relevant to the main keyword.

The user provides a title and makes sure that the post is relevant to it. 

The user provides 50 LSI keywords and tries to incorporate them naturally throughout the content.

The user provides "Facts to include" that should be smoothly incorporated in the introduction and the Main content sections of the output.

Audience data: The user will include the audience data of the reader, including demographic information, tone preferences, reading level preference, and emotional needs/pain points. Use this information to tailor the content to the audience described in the audience data. Content should respond to their Emotional Needs and Pain Points.

Details to include: The user will define additional details that need to be incorporated into the blog post.

Language - The user defines that you should use US English, UK English, or German for the output. The headlines should be in the defined language as well.



Include the following sections in the post:



Introduction - Provide a concise preview of the content`s value and insights and write an engaging and informative introduction, incorporating the primary keyword, applying NLP and EI principles for emotional resonance. Use the "Facts to include" provided by the user. Do not use all of them. Incorporate them smoothly so that it is part of the story flow and reads naturally. Don’t create a header for this section, only provide the paragraph. 



Table of Contents - Outline main content areas of the post. Craft attention-grabbing subtitles that entice readers to click and read more. Use numbers, questions, and powerful words to draw interest. Use NLP techniques to craft subtitles that grab attention. Incorporate power words and questions to stimulate curiosity and engagement. Based on the main keyword and the audience data provided to you, you need to understand what are the emotions and intentions reader has while searching it. You should understand what deep questions and concerns user wants to answer and build your subtitles(subsections) based on these. Do not list Section titles, make short list of subtitles that will be described in Main Content Section, do not include numbering in the list of subtitles. Make engaging titles in the Table of Contents. 



Main Content Sections - Create content sections with subtitles using keywords and their variations at a 1-2% usage rate per 100 words to prevent keyword stuffing. Each section should contain a detailed content, employing NLP and EI for relatability and actionability. Make the content deep so it responds to the emotions and curiosity of the readers. Use storytelling techniques to make your content more relatable and memorable. Share personal anecdotes, case studies, and real-life examples. Stories are a powerful NLP tool to create an emotional connection. Share personal anecdotes or relatable scenarios to make your content more engaging and memorable. Prevent from producing worthless fluff content that doesn’t add to the value of the blog post. Do not include any fluff when producing content. Each sentence should provide value to the overall goal of the content piece. Strictly follow this guideline. Ensure to insert interesting and fun facts about the Main keyword when producing the content: use the "Facts to include" provided by the user. Do not use all of them. Incorporate them smoothly so that it is part of the story flow and reads naturally. DO NOT include any conclusion or summary for each content sections. Based on the main keyword and the audience data provided to you, you need to understand what are the emotions and intentions user has while searching it. You should understand what deep questions and concerns users want to answer and build your output based on these. Use the following NLP Techniques for creating content:

    Anchoring: Use anchoring to associate positive emotions with your content. For instance, repeatedly use a specific phrase or concept that evokes a positive response.

    Reframing: Present your points in a way that shifts the reader’s perspective. For example, instead of highlighting a problem, focus on the opportunity it presents.

    Vivid Descriptions: Use descriptive language to paint vivid images and evoke emotions. This helps readers feel more connected to your content.

    Addressing Reader Emotions: Acknowledge and validate the emotions your readers might be experiencing. This creates a sense of understanding and connection.

    High-Quality Content: Ensure your content is well-researched, informative, and adds value to your readers. Provide actionable insights and practical tips.



Conclusion - Summarize key insights, encouraging further exploration or engagement. Do not include call to action details in the conclusion. 



FAQ - Come up with 3 FAQ that the reader may have. Provide questions and answers with clear, informative, tone empathize with the reader`s concerns.



What’s Next? - Write a short paragraph inviting the reader to take action in the explained way, including links or phone numbers if provided. Incorporate "Call to action" provided by user. If call to action is blank you should write a general paragraph without specific contact details or further steps anyway.



Use the following formatting and structure for the output:

{

IMPORTANT: Never include the Blog Post Title. Start with the introduction paragraph



Introduction - Introduction should not be more than 100-150 words.(do not include any title, just paragraph)



<h2>Table of Contents</h2> (Heading 2) - should not be more than 50 words and formatted as a list with bullet points with normal text format



<h2>Main Content Sections</h2> (Heading 2) - Create 5 sections. Create 2-3 subsections and subtitles with formatting H3 for each section so it does not exceed required word quantity. IMPORTANT: Each section should not be more than 450-600 words. (Do not include the header ‘Main Content Sections’)





<h2>Conclusion</h2> (Heading 2) - Conclusion should not be more than 100-150 words. Do not include call to action details in the conclusion.



<h2>FAQs</h2> (Heading 2) - FAQs should not be more than 100-150 words.

Q: 

A:



Q: 

A: 



Q:

A: 



<h2>What’s next?</h2> (Heading 2) - What’s next? should not be more than 100-150 words.

}



Use the iterative approach to improve upon your initial draft. After each draft, critique your work, give it a score out of 10, and if the score is below 9, improve upon the previous draft. Repeat this process until you achieve a score of 9 or 10. When doing this, review and edit your work to remove any grammatical errors, unnecessary information, and superfluous sentences. Don`t provide output of this critique, this is only for you to analyze internally. Also, check the formatting, output should not include a title of the blog post and each section/subsection should have a title with a specific heading type. 

Now generate ONLY the Introduction and the Table of Contents based on the following parameters:

				Main keyword: ' . $seed_keyword . '

				Title: "' . $title . '"

				LSI keywords: ' . $LSI_Keyords . '

				Tone of voice: ' . $voice_tone . ' 

				Point of view: ' . $point_of_view . '

				Audience data: {' . $AudienceData . '}

				Details to include: ' . $details_to_include . ' 

				Language: ' . $content_lang . '

				Call to action from user: `' . $call_to_action . '`

				Facts to include: {' . $facts_prompt_response . '} Do not print "Main Content Sections" text in output.';













		// Your chat messages

		$messages = [

			['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in ' . $content_lang],

			['role' => 'user', 'content' => $basic_prompt]

			// ['role' => 'assistant', 'content' => 'Hello, how can I help you today?'],

		];



		// Additional parameters, including language setting (replace with actual parameters)

		$data = [

			'messages' => $messages,

			"model" => "gpt-4o",

			// 'language' => 'fr',  // Specify the result language as French

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

		$basic_prompt_response = $result['choices'][0]['message']['content'];



		$prompt_collection = $prompt_collection . '<br><b>Basic prompt for Large</b><br>' . $basic_prompt . '<br><b>response for basic prompt</b>' . $basic_prompt_response;







		$first_call_for_large = 'Now generate the first subtitle content. IMPORTANT: Output should not be more than 450-600 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.';

		// Your chat messages

		$messages = [

			['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in ' . $content_lang],

			['role' => 'user', 'content' => $basic_prompt],

			['role' => 'assistant', 'content' => $basic_prompt_response],

			['role' => 'user', 'content' => $first_call_for_large]

		];



		// Additional parameters, including language setting (replace with actual parameters)

		$data = [

			'messages' => $messages,

			"model" => "gpt-4o",

			// 'language' => 'fr',  // Specify the result language as French

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

		$response_first_call_for_large = $result['choices'][0]['message']['content'];



		$prompt_collection = $prompt_collection . '<br><b>1st call request</b><br>' . $first_call_for_large . '<br><b>response for 1st response</b>' . $response_first_call_for_large;





		// second call

		$second_call_for_large = 'Now generate the second subtitle content. IMPORTANT: Output should not be more than 450-600 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.';

		// Your chat messages

		$messages = [

			['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in ' . $content_lang],

			['role' => 'user', 'content' => $basic_prompt],

			['role' => 'assistant', 'content' => $basic_prompt_response],

			['role' => 'user', 'content' => $first_call_for_large],

			['role' => 'assistant', 'content' => $response_first_call_for_large],

			['role' => 'user', 'content' => $second_call_for_large],

		];



		// Additional parameters, including language setting (replace with actual parameters)

		$data = [

			'messages' => $messages,

			"model" => "gpt-4o",

			// 'language' => 'fr',  // Specify the result language as French

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

		$response_secound_call_for_large = $result['choices'][0]['message']['content'];

		$prompt_collection = $prompt_collection . '<br><b>2nd call request</b><br>' . $second_call_for_large . '<br><b>response for 2nd response</b>' . $response_secound_call_for_large;

		///// third call

		$third_call_for_large = 'Now generate the third subtitle content. IMPORTANT: Output should not be more than 450-600 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.';

		// Your chat messages

		$messages = [

			['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in ' . $content_lang],

			['role' => 'user', 'content' => $basic_prompt],

			['role' => 'assistant', 'content' => $basic_prompt_response],

			['role' => 'user', 'content' => $first_call_for_large],

			['role' => 'assistant', 'content' => $response_first_call_for_large],

			['role' => 'user', 'content' => $second_call_for_large],

			['role' => 'assistant', 'content' => $response_secound_call_for_large],

			['role' => 'user', 'content' => $third_call_for_large],

		];



		// Additional parameters, including language setting (replace with actual parameters)

		$data = [

			'messages' => $messages,

			"model" => "gpt-4o",

			// 'language' => 'fr',  // Specify the result language as French

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

		$response_third_call_for_large = $result['choices'][0]['message']['content'];



		$prompt_collection = $prompt_collection . '<br><b>3rd call request</b><br>' . $third_call_for_large . '<br><b>response for 3rd response</b>' . $response_third_call_for_large;

		/////// 4th call 



		$fourth_call_for_large = 'Now generate the forth subtitle content. IMPORTANT: Output should not be more than 450-600 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.';

		// Your chat messages

		$messages = [

			['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in ' . $content_lang],

			['role' => 'user', 'content' => $basic_prompt],

			['role' => 'assistant', 'content' => $basic_prompt_response],

			['role' => 'user', 'content' => $first_call_for_large],

			['role' => 'assistant', 'content' => $response_first_call_for_large],

			['role' => 'user', 'content' => $second_call_for_large],

			['role' => 'assistant', 'content' => $response_secound_call_for_large],

			['role' => 'user', 'content' => $third_call_for_large],

			['role' => 'assistant', 'content' => $response_third_call_for_large],

			['role' => 'user', 'content' => $fourth_call_for_large],

		];



		// Additional parameters, including language setting (replace with actual parameters)

		$data = [

			'messages' => $messages,

			"model" => "gpt-4o",

			// 'language' => 'fr',  // Specify the result language as French

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

		$response_fourth_call_for_large = $result['choices'][0]['message']['content'];



		$prompt_collection = $prompt_collection . '<br><b>4th call request</b><br>' . $fourth_call_for_large . '<br><b>response for 4th response</b>' . $response_fourth_call_for_large;


		// 5th call 


		$fifth_call_for_large = 'Now generate the fifth subtitle content. IMPORTANT: Output should not be more than 450-600 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.';

		// Your chat messages

		$messages = [

			['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in ' . $content_lang],

			['role' => 'user', 'content' => $basic_prompt],

			['role' => 'assistant', 'content' => $basic_prompt_response],

			['role' => 'user', 'content' => $first_call_for_large],

			['role' => 'assistant', 'content' => $response_first_call_for_large],

			['role' => 'user', 'content' => $second_call_for_large],

			['role' => 'assistant', 'content' => $response_secound_call_for_large],

			['role' => 'user', 'content' => $third_call_for_large],

			['role' => 'assistant', 'content' => $response_third_call_for_large],

			['role' => 'user', 'content' => $fourth_call_for_large],

			['role' => 'assistant', 'content' => $response_fourth_call_for_large],

			['role' => 'user', 'content' => $fifth_call_for_large],

		];



		// Additional parameters, including language setting (replace with actual parameters)

		$data = [

			'messages' => $messages,

			"model" => "gpt-4o",

			// 'language' => 'fr',  // Specify the result language as French

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

		$response_fifth_call_for_large = $result['choices'][0]['message']['content'];



		$prompt_collection = $prompt_collection . '<br><b>5th call request</b><br>' . $fifth_call_for_large . '<br><b>response for 5th response</b>' . $response_fifth_call_for_large;


		// 6th call 


		$sixth_call_for_large = 'Now generate the conclusion content. IMPORTANT: Output should not be more than 150-200 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.';

		// Your chat messages

		$messages = [

			['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in ' . $content_lang],

			['role' => 'user', 'content' => $basic_prompt],

			['role' => 'assistant', 'content' => $basic_prompt_response],

			['role' => 'user', 'content' => $first_call_for_large],

			['role' => 'assistant', 'content' => $response_first_call_for_large],

			['role' => 'user', 'content' => $second_call_for_large],

			['role' => 'assistant', 'content' => $response_secound_call_for_large],

			['role' => 'user', 'content' => $third_call_for_large],

			['role' => 'assistant', 'content' => $response_third_call_for_large],

			['role' => 'user', 'content' => $fourth_call_for_large],

			['role' => 'assistant', 'content' => $response_fourth_call_for_large],

			['role' => 'user', 'content' => $fifth_call_for_large],

			['role' => 'assistant', 'content' => $response_fifth_call_for_large],

			['role' => 'user', 'content' => $sixth_call_for_large],

		];



		// Additional parameters, including language setting (replace with actual parameters)

		$data = [

			'messages' => $messages,

			"model" => "gpt-4o",

			// 'language' => 'fr',  // Specify the result language as French

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

		$response_sixth_call_for_large = $result['choices'][0]['message']['content'];

		// 7th call 

		$seventh_call_for_large = 'Now generate the FAQs content. IMPORTANT: Output should not be more than 100-150 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.';

		// Your chat messages

		$messages = [

			['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in ' . $content_lang],

			['role' => 'user', 'content' => $basic_prompt],

			['role' => 'assistant', 'content' => $basic_prompt_response],

			['role' => 'user', 'content' => $first_call_for_large],

			['role' => 'assistant', 'content' => $response_first_call_for_large],

			['role' => 'user', 'content' => $second_call_for_large],

			['role' => 'assistant', 'content' => $response_secound_call_for_large],

			['role' => 'user', 'content' => $third_call_for_large],

			['role' => 'assistant', 'content' => $response_third_call_for_large],

			['role' => 'user', 'content' => $fourth_call_for_large],

			['role' => 'assistant', 'content' => $response_fourth_call_for_large],

			['role' => 'user', 'content' => $fifth_call_for_large],

			['role' => 'assistant', 'content' => $response_fifth_call_for_large],

			['role' => 'user', 'content' => $sixth_call_for_large],

			['role' => 'assistant', 'content' => $response_sixth_call_for_large],

			['role' => 'user', 'content' => $seventh_call_for_large],

		];



		// Additional parameters, including language setting (replace with actual parameters)

		$data = [

			'messages' => $messages,

			"model" => "gpt-4o",

			// 'language' => 'fr',  // Specify the result language as French

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

		$response_seventh_call_for_large = $result['choices'][0]['message']['content'];

		//8th call


		$eigth_call_for_large = 'Now generate What is next? content. IMPORTANT: Output should not be more than 150-200 words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.';

		// Your chat messages

		$messages = [

			['role' => 'system', 'content' => 'You are a helpful assistant. Please respond in ' . $content_lang],

			['role' => 'user', 'content' => $basic_prompt],

			['role' => 'assistant', 'content' => $basic_prompt_response],

			['role' => 'user', 'content' => $first_call_for_large],

			['role' => 'assistant', 'content' => $response_first_call_for_large],

			['role' => 'user', 'content' => $second_call_for_large],

			['role' => 'assistant', 'content' => $response_secound_call_for_large],

			['role' => 'user', 'content' => $third_call_for_large],

			['role' => 'assistant', 'content' => $response_third_call_for_large],

			['role' => 'user', 'content' => $fourth_call_for_large],

			['role' => 'assistant', 'content' => $response_fourth_call_for_large],

			['role' => 'user', 'content' => $fifth_call_for_large],

			['role' => 'assistant', 'content' => $response_fifth_call_for_large],

			['role' => 'user', 'content' => $sixth_call_for_large],

			['role' => 'assistant', 'content' => $response_sixth_call_for_large],

			['role' => 'user', 'content' => $seventh_call_for_large],

			['role' => 'assistant', 'content' => $response_seventh_call_for_large],

			['role' => 'user', 'content' => $eigth_call_for_large],

		];



		// Additional parameters, including language setting (replace with actual parameters)

		$data = [

			'messages' => $messages,

			"model" => "gpt-4o",

			// 'language' => 'fr',  // Specify the result language as French

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

		$response_eigth_call_for_large = $result['choices'][0]['message']['content'];


		// For Testing purposes - Checklist

		if ($seed_options == 'seed_option3') {

			$title_type = 'Question';

		} else {

			$title_type = 'Regular';

		}

		if ($for_testing_only == 1) {

			$test_prupose = "<h1>For Testing purposes - Checklist</h1>";

			$test_prupose = $test_prupose . "Main keyword: <b>" . $seed_keyword . "</b></br>

			Title: <b>" . $title . "</b></br>

			Title type: <b>" . $title_type . "</b></br>

			Article size/number of words: <b>" . $nos_of_words . "</b></br>

			LSI keywords: <b>" . $LSI_Keyords . "</b></br>

			Tone of voice: <b>" . $voice_tone . "</b></br>

			Point of view: <b>" . $point_of_view . "</b></br>

			Audience data: {<b>" . $AudienceData . "</b>}</br>

			Details to include: <b>" . $details_to_include . "</b></br>

			Language: <b>" . $content_lang . "</b></br>

			Call to action from user: <b>" . $call_to_action . "</b></br>

			Facts to include: {<b>" . $facts_prompt_response . "</b> }</br>

			Words to exclude:  meticulous, meticulously, navigating, complexities, realm, bespoke, tailored, towards, underpins, everchanging, ever-evolving, the world of, not only, seeking more than just, designed to enhance, it’s not merely, our suite, it is advisable, daunting, in the heart of, when it comes to, in the realm of, amongst unlock the secrets, unveil the secrets and robust";

		}







		$content_final = '<div class="main-content-section-improveseo">' . $basic_prompt_response . '<div style="margin-bottom: 15px;margin-top: 50px;">' . $response_first_call_for_large . '</div><div style="margin-bottom: 15px;margin-top: 50px;">' . $response_secound_call_for_large . '</div><div style="margin-bottom: 15px;margin-top: 50px;">' . $response_third_call_for_large . '</div><div style="margin-bottom: 15px;margin-top: 50px;">' . $response_fourth_call_for_large . '</div><div style="margin-bottom: 15px;margin-top: 50px;">' . $response_fifth_call_for_large . '</div><div style="margin-bottom: 15px;margin-top: 50px;">' . $response_sixth_call_for_large . '</div><div style="margin-bottom: 15px;margin-top: 50px;">' . $response_seventh_call_for_large . '</div><div style="margin-bottom: 15px;margin-top: 50px;">' . $response_eigth_call_for_large . '</div><div style="margin-bottom: 15px;margin-top: 50px;">' . $test_prupose . '</div></div>';



		$prompt_collection = $prompt_collection . '<br><b>6th call request</b><br>' . $sixth_call_for_large . '<br><b>response for 6th response</b>' . $response_sixth_call_for_large;



		$prompt_collection = $prompt_collection . '<br><b>7th call request</b><br>' . $seventh_call_for_large . '<br><b>response for 7th response</b>' . $response_seventh_call_for_large;



		$prompt_collection = $prompt_collection . '<br><b>8th call request</b><br>' . $eigth_call_for_large . '<br><b>response for 8th response</b>' . $response_eigth_call_for_large;









	}


	//if($is_single_keyword=='') {

	$content_final = $content_final . '<style> p {padding-bottom: 2px !important;} </style>';

	$content = array('title' => $seed_keyword, 'content' => $content_final, 'post_type' => 'post');

	$options = array("max_posts" => "1");

	
	$inserted_id = 2;//$wpdb->insert_id;

	//$upload_dir = wp_upload_dir(); // Get WordPress upload directory

	$dynamic_path = IMPROVESEO_ROOT . '/storage/'; // Dynamic path

	// Ensure the directory exists, create if it doesn't

	if (!file_exists($dynamic_path)) {

		wp_mkdir_p($dynamic_path);

	}

	// Define file path

	$file_path = $dynamic_path . $inserted_id . date('Y-m-d-H-i-s') . '.html'; // File name with timestamp

	ob_start();

	?>

	<html>

	<head>

		<title>Used Prompt for <?php echo $seed_keyword; ?></title>

		<meta charset="UTF-8">

	</head>



	<body>

		<?php echo $prompt_collection; ?>

	</body>



	</html>



	<?php

	// Get the content

	$html_content = ob_get_clean();



	// Save to file

	if (file_put_contents($file_path, $html_content) !== false) {

		//echo '<div class="updated"><p>HTML file has been saved to: ' . esc_html($file_path) . '</p></div>';

	} else {

		//echo '<div class="error"><p>Failed to save HTML file to: ' . esc_html($file_path) . '</p></div>';

	}

	// Remove parentheses that wrap raw URLs/emails before converting them to links
	$content_final = stripParenthesesWrappingContactTokens($content_final);

	$content_final = convert_emails_to_links($content_final);
	$content_final = convert_urls_to_links($content_final);

	// Remove parentheses that wrap already-linked anchors like (<a href>..</a>)
	$content_final = stripParenthesesAroundAnchorTags($content_final);

	$content_final = htmlentities($content_final, null, 'utf-8');
	$content_final = str_replace("&nbsp;", "", $content_final);
	$content_final = str_replace("<p>&nbsp;</p>", "", $content_final);
	$content_final = str_replace("<p> </p>", "", $content_final);
	$content_final = str_replace("<p></p>", "", $content_final);

	$content_final = html_entity_decode($content_final);

	$content_final = replace_content($content_final, '<h2>Main Content Sections</h2>');

	$content_final = replace_content($content_final, '<p>—</p>');

	$content_final = removePTags($content_final);
	// Clean up: remove consecutive special characters in visible text (e.g., ###, ***, ----)
	$content_final = removeConsecutiveSpecialCharacters($content_final);

	// Final TOC verification and fixing
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