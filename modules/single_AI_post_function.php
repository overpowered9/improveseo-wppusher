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

	//$content = convert_urls_to_links($content);





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

			

			Table of Contents - Outline main content areas of the post. Craft attention-grabbing subtitles that entice readers to click and read more. Use numbers, questions, and powerful words to draw interest. Use NLP techniques to craft subtitles that grab attention. Incorporate power words and questions to stimulate curiosity and engagement. Based on the main keyword and the audience data provided to you, you need to understand what are the emotions and intentions reader has while searching it. You should understand what deep questions and concerns user wants to answer and build your subtitles(subsections) based on these. Do not list Section titles, make short list of subtitles that will be described in Main Content Section, do not include numbering in the list of subtitles. Make engaging titles in the Table of Contents. 

			

			Main Content Sections - Create content content sections with subtitles using keywords and their variations at a 1-2% usage rate per 100 words to prevent keyword stuffing. Each section should contain a detailed content, employing NLP and EI for relatability and actionability. Make the content deep so it responds to the emotions and curiosity of the readers. Use storytelling techniques to make your content more relatable and memorable. Share personal anecdotes, case studies, and real-life examples. Stories are a powerful NLP tool to create an emotional connection. Share personal anecdotes or relatable scenarios to make your content more engaging and memorable. Based on the main keyword and the audience data provided to you, you need to understand what are the emotions and intentions user has while searching it. You should understand what deep questions and concerns users want to answer and build your output based on these. Use the following NLP Techniques for creating content:

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

			

			<h2>Table of Contents</h2> (Heading 2) - should not be more than 50 words

			

			<h2>Main Content Sections</h2> (Heading 2) - Create 4 sections. Each section should not be more than 200-250 words of detailed content.

			

			<h2>Conclusion</h2> (Heading 2) - Conclusion should not be more than 100-150 words.

			

			<h2>FAQs</h2> (Heading 2) - FAQs should not be more than 100-150 words.

			Q: 

			A:

			

			Q: 

			A: 

			

			Q:

			A: 

			

			<h2>What is next?</h2> (Heading 2) - What is next? should not be more than 100-150 words.

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





		/*return array("first_subtitle"=>$response_first_call_for_small,

																	   "second_subtitle"=>$response_secound_call_for_small,

																	   "third_subtitle"=>$response_third_call_for_small,

																	   "fourth_subtitle"=>$response_fourth_call_for_small,

																	   "conclusion"=>$response_fifth_call_for_small,

																	   "faq"=>$response_sixth_call_for_small,

																	   "whats_next"=>$response_seventh_call_for_small);*/

		$prompt_collection = $prompt_collection . '<br><b>seventh_call_for_small</b><br>' . $seventh_call_for_small . '<br><b>response for 7th call</b>' . $response_seventh_call_for_small;





		// $prompt_collection = $prompt_collection.'<br>seventh_call_for_small<br>'.$seventh_call_for_small.'<br>'.'[

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

		// 	[role => user, content => '.$fifth_call_for_small.'],<br>

		// 	[role => assistant, content => '.$response_fifth_call_for_small.'],<br>

		// 	[role => user, content => '.$sixth_call_for_small.'],<br>

		// 	[role => assistant, content => '.$response_sixth_call_for_small.'],<br>

		// 	[role => user, content => '.$seventh_call_for_small.'],<br>

		// ]';





		//echo $prompt_collection;

		//exit('ttttttttt1111111111111');







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



<h2>Table of Contents</h2> (Heading 2) - should not be more than 50 words and formatted as a list with bullet points with normal text format



<h2>Main Content Sections</h2> (Heading 2) - Create 4 sections. Create 2-3 subsections and subtitles with formatting H3 for the each section so it does not exceed required word quantity. IMPORTANT: Each section should not be more than 350-400 words



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



		/*return array("first_subtitle"=>$response_first_call_for_medium ,

																	   "second_subtitle"=>$response_secound_call_for_medium ,

																	   "third_subtitle"=>$response_third_call_for_medium ,

																	   "fourth_subtitle"=>$response_fourth_call_for_medium ,

																	   "conclusion"=>$response_fifth_call_for_medium ,

																	   "faq"=>$response_sixth_call_for_medium ,

																	   "whats_next"=>$response_seventh_call_for_medium );*/

		//$content_final = $basic_prompt_response.'<br><br>'.$response_first_call_for_medium .'<br><br>'.$response_secound_call_for_medium .'<br><br>'.$response_third_call_for_medium .'<br><br>'.$response_fourth_call_for_medium .'<br><br>'.$response_fifth_call_for_medium .'<br><br>'.$response_sixth_call_for_medium .'<br><br>'.$response_seventh_call_for_medium ;





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











		/*return array("first_subtitle"=>$response_first_call_for_large,

																	   "second_subtitle"=>$response_secound_call_for_large,

																	   "third_subtitle"=>$response_third_call_for_large,

																	   "fourth_subtitle"=>$response_fourth_call_for_large,

																	   "conclusion"=>$response_fifth_call_for_large,

																	   "faq"=>$response_sixth_call_for_large,

																	   "whats_next"=>$response_seventh_call_for_large);*/

		//$content_final = $basic_prompt_response.'<br><br>'.$response_first_call_for_large.'<br><br>'.$response_secound_call_for_large.'<br><br>'.$response_third_call_for_large.'<br><br>'.$response_fourth_call_for_large.'<br><br>'.$response_fifth_call_for_large.'<br><br>'.$response_sixth_call_for_large.'<br><br>'.$response_seventh_call_for_large.'<br><br>'.$response_eigth_call_for_large;







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



	//    if($details_to_include == ''){

	// 	$details_to_include = '';

	//    }else{

	// 	$details_to_include =  '11. Call To Action:'.$details_to_include.' ' ;

	//    }



	// 'write a '.$nos_of_words.' blog post about '.$seed_keyword ;

	/* $call_to_actions = '11. Call To Action:'.$call_to_action.'';







					  $question = 'Pretend you are Malcolm Gladwell. Using the provided outline, write a '.$nos_of_words.' blog post about '.$seed_keyword.'

					  Your task is to: Aim the piece at a broad audience.		

					  Use a mix of short, medium, and long sentences to create a human-like rhythm in the text.

					  Incorporate humor, where appropriate, to make the piece more enjoyable to read.

					  Include an analogy to explain any complex concepts or ideas.

					  Use a '.$voice_tone.' tone to make the piece more relatable.

					  Consider the perspectives of both an expert and a beginner.

					  Write from the following point of view: '.$point_of_view.'

					  Incorporate the provided "LSI keywords" naturally throughout the content. LSI keywords: '.$LSI_Keyords.'

					  

					  Consider the context that is provided to make the blog post more relevant to the main keyword '.$seed_keyword.'

					  

					  Context:'.$details_to_include.'.



					  Use the iterative approach to improve upon your initial draft. After each draft, critique your work, give it a score out of 10, and if the score is below 9, improve upon the previous draft. Repeat this process until you achieve a score of 9 or 10. When doing this, review and edit your work to remove any grammatical errors, unnecessary information, and superfluous sentences. Don`t provide output of this critique, this is only for you to analyze internally.

					  

					  The blog post should contain the following:

					  

						 - Provide a concise preview of the content`s value and insights and write an engaging and informative introduction, incorporating the primary keyword within the first 100-150 words, applying NLP and EI principles for emotional resonance. Don’t create a header for this section, only provide the paragraph.

					  

					  2. <h2>Table of Contents:</h2>

						 - Outline main content areas with H2 section subheaders, non-bolded and in medium gray, for SEO and easy navigation. Please make sure that H2 section subheaders are not bold. Don’t include a header for the following section: ‘Introduction’		

					  

					  3. <h2>Main Content Sections:</h2>

						 - Create H2 sections with titles using keywords and their variations at a 1-2% usage rate per 100 words to prevent keyword stuffing. Each section should contain 3-5 sentences of detailed content, employing NLP and EI for relatability and actionability. Please make sure that the content for each section is at least 150 words.

					  

					  4. <h2>Conclusion:</h2>

						 - Summarize key insights in an H2 header, medium gray, encouraging further exploration or engagement.

					  

					  5.  <h2>Frequently Asked Questions</h2>

						  -  Answer common questions about '.$seed_keyword.' with clear, informative, non-bolded answers that empathize with the reader`s concerns. Output should just be the questions and answers, don’t write ‘Q’ in front of the questions and ‘A’ in front of the answers.

					  6. <h2>What’s Next? </h2>

						  -Write a short paragraph inviting the reader to take action in the explained way, including links or phone numbers if provided.

						 '.$call_to_actions;

					  

						 // Your chat messages

						 $messages = [

							 // ['role' => 'system', 'content' => 'You are a helpful assistant.'],

							 ['role' => 'user', 'content' => $question]

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

						 

						 // print_r($result);

						 

						 // echo 'Generated Text in French: ' . $result['choices'][0]['message']['content'];

						 // die();

						 

						 // print_r($shortcode);

						 // $scode = '';

						 // for ($s=0; $s<=count($shortcode)-1; $s++)

						 // {

						 //     $scode.= $shortcode[$s]."<br/>";

						 // }

						 // die();

						 $content_final = $result['choices'][0]['message']['content'];

						 

						 */



	//echo $prompt_collection;

	//exit();





	//if($is_single_keyword=='') {

	$content_final = $content_final . '<style> p {padding-bottom: 2px !important;} </style>';

	$content = array('title' => $seed_keyword, 'content' => $content_final, 'post_type' => 'post');

	$options = array("max_posts" => "1");

	/*$wpdb->insert($wpdb->prefix . "improveseo_tasks", array(

										  

								   'name' => $seed_keyword,

								   'content' => base64_encode(json_encode($content)),

								   'options' => base64_encode(json_encode($options)),

								   'iteration' => 0,

								   'spintax_iterations' => 1,

								   'max_iterations' => 1,

								   'state' => "Published",

								   'options' => base64_encode(json_encode($options)),

								//'prompt_collection' => base64_encode($prompt_collection),

								   'created_at' => date('Y-m-d h:m:s')

							   ));*/

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









	//	} else {













	$content_final = convert_emails_to_links($content_final);

	$content_final = convert_urls_to_links($content_final);



	$content_final = htmlentities($content_final, null, 'utf-8');

	$content_final = str_replace("&nbsp;", "", $content_final);

	$content_final = str_replace("<p>&nbsp;</p>", "", $content_final);

	$content_final = str_replace("<p> </p>", "", $content_final);

	$content_final = str_replace("<p></p>", "", $content_final);



	$content_final = html_entity_decode($content_final);



	$content_final = replace_content($content_final, '<h2>Main Content Sections</h2>');



	$content_final = replace_content($content_final, '<p>—</p>');



	$content_final = removePTags($content_final);

	return $content_final;

	//}



}

?>