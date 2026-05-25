<?php





use ImproveSEO\View;


use ImproveSEO\Spintax;





use ImproveSEO\Validator;


use ImproveSEO\LiteSpintax;





use ImproveSEO\Models\Task;
use ImproveSEO\Models\Bulktask;
use ImproveSEO\Models\Bulktasksdetail;


use ImproveSEO\FlashMessage;





function improveseo_dashboard() {


	global $wpdb;





	$action = isset($_GET['action']) ? $_GET['action'] : 'index';


	$model = new Task();





	if ($action == 'index'):


		View::render('dashboard.index');


	elseif($action=='do_create_post'):


		





		if (isset($_POST['create'])) {


			if (!Validator::validate($_POST, array(


				'name' => 'required',


				'title' => 'required',


				'content' => 'required',


				'post_type' => 'required|post_type',





				'max_posts' => 'numeric',





				// Dripfeed


				'dripfeed_x' => 'required_if:dripfeed_enabler|numeric',





				// Image EXIF


				//'exif_locations' => 'required_if:exif_enabler'





				// Channel


				'city_channel_title' => 'required_if:city_channel_page',


				'city_channel_content' => 'required_if:city_channel_page',


				'state_channel_title' => 'required_if:state_channel_page',


				'state_channel_content' => 'required_if:state_channel_page'


			)) && !isset($_POST['draft'])) {


				$ai_modal_type2 = isset($_POST['ai_modal_type']) ? $_POST['ai_modal_type'] : '';
				if ($ai_modal_type2 === 'single') {
					wp_redirect(admin_url('admin.php?page=improveseo_posting&action=create_post_single'));
				} elseif ($ai_modal_type2 === 'bulk') {
					wp_redirect(admin_url('admin.php?page=improveseo_posting&action=create_post_bulk'));
				} else {
					wp_redirect(admin_url('admin.php?page=improveseo_posting&action=create_post'));
				}
				exit;


			}


		}





		$name = $_POST['name'];


		$title = $_POST['title'];


		$content = $_POST['content'];





		// $order   = array("\r\n", "\n");


		// $replace   = array("\n", "\\\n");


		//


		// $content = str_replace($order, $replace, $content);


		//





		$post_type = $_POST['post_type'];





		$project_data = array(


			'title' => stripslashes($title),


			'content' => stripslashes($content),


			'post_type' => $post_type


		);





		$iterations = [


			// title


			Spintax::count(Spintax::parse($title)),


			// content


			Spintax::count(Spintax::parse($content))


		];





		$options_data = array();





		// On-Page SEO


		if (isset($_POST['on_page_seo'])) {


			$options_data['custom_title'] = stripslashes($_POST['custom_title']);


			$options_data['custom_description'] = stripslashes($_POST['custom_description']);


			$options_data['custom_keywords'] = stripslashes($_POST['custom_keywords']);





			$iterations[] = Spintax::count(Spintax::parse($options_data['custom_title']));


			$iterations[] = Spintax::count(Spintax::parse($options_data['custom_description']));


			$iterations[] = Spintax::count(Spintax::parse($options_data['custom_keywords']));


		}





		// Local SEO


		$geo_iterations = 1;





		if (isset($_POST['local_seo_enabler'])) {


			// Search tags and remove non-used locations


			$tags = improveseo_search_geotags(array(


				$title, $content, $_POST['custom_title'], $_POST['custom_description'], $_POST['custom_keywords'], $_POST['permalink'], $_POST['tags']


			));





			$options_data['local_geo_country'] = $_POST['local_country'];


			$options_data['local_geo_locations'] = json_decode(stripslashes($_POST['local_geo_locations']), true);





			// Do not expand geo data if saving as draft


			if (isset($_POST['create'])) {


				$options_data['local_geo_locations'] = improveseo_expand_geodata($options_data['local_geo_country'], $options_data['local_geo_locations'], $tags);


			}





			$geo_iterations = sizeof($options_data['local_geo_locations']);


			if ($geo_iterations == 0) $geo_iterations = 1;





			if (isset($_POST['local_randomize'])) {


				shuffle($options_data['local_geo_locations']);


				$options_data['local_randomize'] = $_POST['local_randomize'];


			}





			// Categorization


			if (isset($_POST['enable_categorization'])) {


				$options_data['enable_categorization'] = true;





				$tags_order = array('country', 'state', 'city', 'zip');





				foreach ($tags_order as $tag) {


					if (!in_array($tag, $tags)) continue;





					$options_data['categorization'][] = $tag;


				}


			}





			//$options_data['local_geo_locations'] = array_unique($options_data['local_geo_locations']);


		}





		// Schema SEO


		if (isset($_POST['schema'])) {


			$options_data['schema'] = true;


			$options_data['schema_business'] = stripslashes($_POST['schema_business']);


			$options_data['schema_description'] = stripslashes($_POST['schema_description']);


			$options_data['schema_email'] = $_POST['schema_email'];


			$options_data['schema_telephone'] = $_POST['schema_telephone'];


			$options_data['schema_social'] = $_POST['schema_social'];


			$options_data['schema_rating_object'] = $_POST['schema_rating_object'];


			$options_data['schema_rating'] = $_POST['schema_rating'];


			$options_data['schema_rating_count'] = $_POST['schema_rating_count'];


			$options_data['schema_address'] = stripslashes($_POST['schema_address']);





			if (isset($_POST['hide_schema'])) {


				$options_data['hide_schema'] = true;


			}





			$iterations[] = Spintax::count(Spintax::parse($options_data['schema_business']));


			$iterations[] = Spintax::count(Spintax::parse($options_data['schema_description']));


			$iterations[] = Spintax::count(Spintax::parse($options_data['schema_email']));


			$iterations[] = Spintax::count(Spintax::parse($options_data['schema_social']));


			$iterations[] = Spintax::count(Spintax::parse($options_data['schema_address']));


			$iterations[] = Spintax::count(Spintax::parse($options_data['schema_rating_object']));


		}





		// Dripfeed Feature


		if (isset($_POST['dripfeed_enabler'])) {


			$options_data['dripfeed_type'] = $_POST['dripfeed_type'];


			$options_data['dripfeed_x'] = $_POST['dripfeed_x'];


		}





		// Image EXIF


		if (isset($_POST['exif_enabler'])) {


			$options_data['exif_locations'] = $_POST['exif_locations'];


		}


		if (isset($_POST['use_post_location'])) {


			$options_data['use_post_location'] = true;


		}





		// Permalink


		if ($_POST['permalink']) {


			$options_data['permalink'] = $_POST['permalink'];


		}


		if ($_POST['permalink_prefix']) {


			$options_data['permalink_prefix'] = sanitize_title($_POST['permalink_prefix']);


		}





		// Tags


		if ($_POST['tags']) {


			$options_data['tags'] = $_POST['tags'];


		}


		if (isset($_POST['noindex_tags'])) {


			$options_data['noindex_tags'] = true;


		}





		// Distribute


		if (isset($_POST['distribute'])) {


			$options_data['distribute'] = true;


		}





		// Channel pages


		if (isset($_POST['state_channel_page'])) {


			$project_data['state_channel_enabled'] = true;


			$project_data['state_channel_title'] = $_POST['state_channel_title'];


			$project_data['state_channel_page'] = $_POST['state_channel_content'];


		}


		if (isset($_POST['city_channel_page'])) {


			$project_data['city_channel_enabled'] = true;


			$project_data['city_channel_title'] = $_POST['city_channel_title'];


			$project_data['city_channel_page'] = $_POST['city_channel_content'];


		}





		// Math maximum number of posts


		// Count list items


        $items = improveseo_count_list_items($_POST);





		if (isset($_POST['local_seo_enabler'])) {


			if (!$items) $items = 1;


			$max = ($_POST['max_posts'] <= 0) ? $geo_iterations * $items : intval($_POST['max_posts']);


		} else {


			$max = ($_POST['max_posts'] <= 0) ? ($items ? $items : Spintax::count(Spintax::parse($title))) : intval($_POST['max_posts']);


		}


		if (isset($_POST['max_posts'])) {


			$options_data['max_posts'] = $_POST['max_posts'];


		}


		// AI Generation Settings (from popup form hidden fields)

		if (isset($_POST['ai_modal_type']) && $_POST['ai_modal_type'] === 'single') {

			$ai_fields = array(

				'ai_seed_keyword',

				'ai_seed_options',

				'ai_content_type',

				'ai_nos_of_words',

				'ai_point_of_view',

				'ai_content_lang',

				'ai_details_to_include',

				'ai_call_to_action',

				'ai_image_option',

				'ai_image_url',

				'ai_generated_title',

				'ai_for_testing_only',

				'set_featured_image'

			);

			foreach ($ai_fields as $field) {

				if (isset($_POST[$field]) && $_POST[$field] !== '') {

					$options_data[$field] = stripslashes($_POST[$field]);

				}

			}

		}


		$data = array(


			'name' => $name,


			'content' => base64_encode(json_encode($project_data)),


			'options' => base64_encode(json_encode($options_data)),


			'state' => isset($_POST['draft']) ? 'Draft' : 'Published',


			'iteration' => 0,


			'spintax_iterations' => max($iterations),


			//'max_iterations' => max($iterations) * $geo_iterations


			'max_iterations' => $max,


			'cats' => json_encode($_POST['cats'])


		);


		$wpdb->query("SET GLOBAL max_allowed_packet = 268435456");

		$model = new Task();
		if (isset($_POST['ai_modal_type']) && $_POST['ai_modal_type'] == 'bulk') {
			$model = new Bulktask();
			$data['max_iterations'] = count(array_filter(array_map('trim', explode("\n", trim($_POST['keywords'] ?? '')))));
		}

		$project_id = isset($_GET['id']) ? $model->update($data, $_GET['id']) : $model->create($data);


		if (isset($_GET['id'])) $project_id = $_GET['id'];

		if (isset($_POST['ai_modal_type']) && $_POST['ai_modal_type'] == 'bulk') {
			$keywords = array_filter(array_map('trim', explode("\n", trim($_POST['keywords'] ?? ''))));
			$detailsModel = new Bulktasksdetail();
			foreach ($keywords as $keyword) {
				$detail_data = array(
					'bulktask_id' => $project_id,
					'keyword_name' => $keyword,
					'select_exisiting_options' => $_POST['select_exisiting_options'] ?? 'seed_option1',
					'nos_of_words' => $_POST['nos_of_words'] ?? '600 to 1200 words',
					'content_lang' => $_POST['content_lang'] ?? 'US English',
					'tone_of_voice' => $_POST['tone_of_voice'] ?? '',
					'point_of_view' => $_POST['point_of_view'] ?? '',
					'details_to_include' => $_POST['details_to_include'] ?? '',
					'call_to_action' => $_POST['call_to_action'] ?? '',
					'aiImage' => $_POST['aiImage'] ?? '',
					'ai_image' => '',
					'cats' => json_encode($_POST['cats']),
					'assigning_authors' => $_POST['assigning_authors'] ?? '',
					'assigning_authors_value' => $_POST['assigning_authors_value'] ?? '',
					'assigning_multi_authors' => $_POST['assigning_multi_authors'] ?? '',
					'schedule_posts' => $_POST['schedule_posts'] ?? '',
					'published_on' => $_POST['published_on'] ?? '',
					'testimonial' => $_POST['testimonial'] ?? '',
					'Button_SC' => $_POST['Button_SC'] ?? '',
					'GoogleMap_SC' => $_POST['GoogleMap_SC'] ?? '',
					'Video_SC' => $_POST['Video_SC'] ?? '',
					'state' => 'Scheduled',
					'status' => 'Pending',
					'post_id' => null,
					'is_published_by_plugin' => 0,
					'ai_title' => '',
					'ai_content' => '',
					'created_at' => current_time('mysql'),
					'updated_at' => current_time('mysql')
				);
				$detailsModel->create($detail_data);
			}

			// Notify admin server — bulk task created
			if (function_exists('improveseo_notify_bulk_status')) {
				$notified = improveseo_notify_bulk_status('task_created', array(
					'task_name'   => $name,
					'total_tasks' => count($keywords),
					'project_id'  => $project_id,
				));
				my_plugin_log('[Dashboard] Bulk creation notification result: ' . ($notified ? 'OK' : 'FAILED'));
			}
		}

		if (isset($_POST['create'])) {


			FlashMessage::success('Project successfully created. It will generate <strong>'. $data['max_iterations'] .'</strong> posts/pages.'


				);


			wp_redirect(admin_url("admin.php?page=improveseo_projects&highlight={$project_id}&build_posts_id={$project_id}"));


		}


		elseif (isset($_POST['draft'])) {


			FlashMessage::success('Project successfully saved. You can continue editing by pressing Continue button.');


			wp_redirect(admin_url("admin.php?page=improveseo_projects&highlight={$project_id}"));


		} 


		exit;





		elseif ($action == 'do_update_post'):


			


			$name = $_POST['name'];


			$title = $_POST['title'];


			$content = $_POST['content'];


	


			// $order   = array("\r\n", "\n");


			// $replace   = array("\n", "\\\n");


			//


			// $content = str_replace($order, $replace, $content);


			//


	


			$post_type = $_POST['post_type'];


	


			$project_data = array(


				'title' => stripslashes($title),


				'content' => stripslashes($content),


				'post_type' => $post_type


			);


	


			$iterations = [


				// title


				Spintax::count(Spintax::parse($title)),


				// content


				Spintax::count(Spintax::parse($content))


			];


	


			$options_data = array();


	


			// On-Page SEO


			if (isset($_POST['on_page_seo'])) {


				$options_data['custom_title'] = stripslashes($_POST['custom_title']);


				$options_data['custom_description'] = stripslashes($_POST['custom_description']);


				$options_data['custom_keywords'] = stripslashes($_POST['custom_keywords']);


	


				$iterations[] = Spintax::count(Spintax::parse($options_data['custom_title']));


				$iterations[] = Spintax::count(Spintax::parse($options_data['custom_description']));


				$iterations[] = Spintax::count(Spintax::parse($options_data['custom_keywords']));


			}


	


			// Local SEO


			$geo_iterations = 1;


	


			if (isset($_POST['local_seo_enabler'])) {


				// Search tags and remove non-used locations


				$tags = improveseo_search_geotags(array(


					$title, $content, $_POST['custom_title'], $_POST['custom_description'], $_POST['custom_keywords'], $_POST['permalink'], $_POST['tags']


				));


	


				$options_data['local_geo_country'] = $_POST['local_country'];


				$options_data['local_geo_locations'] = json_decode(stripslashes($_POST['local_geo_locations']), true);


	


				// Do not expand geo data if saving as draft


				if (isset($_POST['create'])) {


					$options_data['local_geo_locations'] = improveseo_expand_geodata($options_data['local_geo_country'], $options_data['local_geo_locations'], $tags);


				}


	


				$geo_iterations = sizeof($options_data['local_geo_locations']);


				if ($geo_iterations == 0) $geo_iterations = 1;


	


				if (isset($_POST['local_randomize'])) {


					shuffle($options_data['local_geo_locations']);


					$options_data['local_randomize'] = $_POST['local_randomize'];


				}


	


				// Categorization


				if (isset($_POST['enable_categorization'])) {


					$options_data['enable_categorization'] = true;


	


					$tags_order = array('country', 'state', 'city', 'zip');


	


					foreach ($tags_order as $tag) {


						if (!in_array($tag, $tags)) continue;


	


						$options_data['categorization'][] = $tag;


					}


				}


	


				//$options_data['local_geo_locations'] = array_unique($options_data['local_geo_locations']);


			}


	


			// Schema SEO


			if (isset($_POST['schema'])) {


				$options_data['schema'] = true;


				$options_data['schema_business'] = stripslashes($_POST['schema_business']);


				$options_data['schema_description'] = stripslashes($_POST['schema_description']);


				$options_data['schema_email'] = $_POST['schema_email'];


				$options_data['schema_telephone'] = $_POST['schema_telephone'];


				$options_data['schema_social'] = $_POST['schema_social'];


				$options_data['schema_rating_object'] = $_POST['schema_rating_object'];


				$options_data['schema_rating'] = $_POST['schema_rating'];


				$options_data['schema_rating_count'] = $_POST['schema_rating_count'];


				$options_data['schema_address'] = stripslashes($_POST['schema_address']);


	


				if (isset($_POST['hide_schema'])) {


					$options_data['hide_schema'] = true;


				}


	


				$iterations[] = Spintax::count(Spintax::parse($options_data['schema_business']));


				$iterations[] = Spintax::count(Spintax::parse($options_data['schema_description']));


				$iterations[] = Spintax::count(Spintax::parse($options_data['schema_email']));


				$iterations[] = Spintax::count(Spintax::parse($options_data['schema_social']));


				$iterations[] = Spintax::count(Spintax::parse($options_data['schema_address']));


				$iterations[] = Spintax::count(Spintax::parse($options_data['schema_rating_object']));


			}


	


			// Dripfeed Feature


			if (isset($_POST['dripfeed_enabler'])) {


				$options_data['dripfeed_type'] = $_POST['dripfeed_type'];


				$options_data['dripfeed_x'] = $_POST['dripfeed_x'];


			}


	


			// Image EXIF


			if (isset($_POST['exif_enabler'])) {


				$options_data['exif_locations'] = $_POST['exif_locations'];


			}


			if (isset($_POST['use_post_location'])) {


				$options_data['use_post_location'] = true;


			}


	


			// Permalink


			if ($_POST['permalink']) {


				$options_data['permalink'] = $_POST['permalink'];


			}


			if ($_POST['permalink_prefix']) {


				$options_data['permalink_prefix'] = sanitize_title($_POST['permalink_prefix']);


			}


	


			// Tags


			if ($_POST['tags']) {


				$options_data['tags'] = $_POST['tags'];


			}


			if (isset($_POST['noindex_tags'])) {


				$options_data['noindex_tags'] = true;


			}


	


			// Distribute


			if (isset($_POST['distribute'])) {


				$options_data['distribute'] = true;


			}


	


			// Channel pages


			if (isset($_POST['state_channel_page'])) {


				$project_data['state_channel_enabled'] = true;


				$project_data['state_channel_title'] = $_POST['state_channel_title'];


				$project_data['state_channel_page'] = $_POST['state_channel_content'];


			}


			if (isset($_POST['city_channel_page'])) {


				$project_data['city_channel_enabled'] = true;


				$project_data['city_channel_title'] = $_POST['city_channel_title'];


				$project_data['city_channel_page'] = $_POST['city_channel_content'];


			}


	


			// Math maximum number of posts


			// Count list items


			$items = improveseo_count_list_items($_POST);


	


			if (isset($_POST['local_seo_enabler'])) {


				if (!$items) $items = 1;


				$max = ($_POST['max_posts'] <= 0) ? $geo_iterations * $items : intval($_POST['max_posts']);


			} else {


				$max = ($_POST['max_posts'] <= 0) ? ($items ? $items : Spintax::count(Spintax::parse($title))) : intval($_POST['max_posts']);


			}


			if (isset($_POST['max_posts'])) {


				$options_data['max_posts'] = $_POST['max_posts'];


			}


	


			$data = array(


				'name' => $name,


				'content' => base64_encode(json_encode($project_data)),


				'options' => base64_encode(json_encode($options_data)),


				'state' => 'Update_started',


				'iteration' => 0,


				'spintax_iterations' => max($iterations),


				//'max_iterations' => max($iterations) * $geo_iterations


				'max_iterations' => $max,


				'cats' => json_encode($_POST['cats'])


			);


			$wpdb->query("SET GLOBAL max_allowed_packet = 268435456");


	


			$project_id = $model->update($data, $_GET['id']) ;


			if (isset($_GET['id'])) $project_id = $_GET['id'];


			FlashMessage::success('Project successfully updated. You can update old post by clicking update my posts.');


			wp_redirect(admin_url("admin.php?page=improveseo_projects&highlight={$project_id}&build_posts_id={$project_id}"));


			exit;


	


		elseif ($action == 'edit_post'):


		$task = $model->find($_GET['id']);





		View::render('posting.edit-post', compact('task'));


	


	endif;


}