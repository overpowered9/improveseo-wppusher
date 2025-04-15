<?php

use ImproveSEO\Storage;

add_filter('wp_title', 'improveseo_seo_title');
add_filter('the_content', 'improveseo_the_content_filter');
// parse the generated links
add_filter('post_type_link', 'improveseo_custom_post_permalink', 10, 4);
add_filter('page_rewrite_rules', 'improveseo_allow_numeric_stubs');

function improveseo_allow_numeric_stubs($rules) {
    unset( $rules['(.+?)(/[0-9]+)?/?$'] );

    $rules['(.+?)?/?$'] = 'index.php?pagename=$matches[1]';

    return $rules;
}

function improveseo_custom_post_permalink($permalink, $post, $leavename, $sample) {
	$storage = new Storage('improveseo');
	$prefixes = $storage->permalink_prefixes;
	
    // only do our stuff if we're using pretty permalinks
    // and if it's our target post type
    if (isset($prefixes[$post->post_type]) && get_option('permalink_structure')) {
        $struct = "/{$post->post_type}/%category%/%postname%/";

        $rewritecodes = array(
            '%category%',
            '%postname%'
        );

        // setup data
        $terms = get_the_terms($post->ID, 'category');
        
        // this code is from get_permalink()
        $category = '';

        if (strpos($permalink, '%category%') !== false) {
            $cats = get_the_category($post->ID);
            if ( $cats ) {
                usort($cats, '_usort_terms_by_ID'); // order by ID
                $category = $cats[0]->slug;
                if ( $parent = $cats[0]->parent )
                    $category = get_category_parents($parent, false, '/', true) . $category;
            }
            // show default category in permalinks, without
            // having to assign it explicitly
            if ( empty($category) ) {
                $default_category = get_category( get_option( 'default_category' ) );
                $category = is_wp_error( $default_category ) ? '' : $default_category->slug;
            }
        }

        $replacements = array(
            $category,
            $post->post_name
        );

        // finish off the permalink
        $permalink = home_url( str_replace( $rewritecodes, $replacements, $struct ) );
        $permalink = user_trailingslashit($permalink, 'single');
    }

    // for channel pages
    if ($post->post_type == 'channel' && get_option('permalink_structure')) {
        preg_match("/^(state|city)\-(.*)\-(\d+)$/", $post->post_name, $matches);
        $type = $matches[1];
        $place = $matches[2];
        $project_id = $matches[3];
        
        foreach ($prefixes as $prefix => $config) {
            if ($config['project_id'] == $project_id) break;
        }

        if ($config['deep'] == 1) {
            $permalink = home_url("/$prefix/$place/");
        }
        elseif ($config['deep'] == 2) {
            $parent = get_post($post->post_parent);
            preg_match("/^(state|city)\-(.*)\-(\d+)$/", $parent->post_name, $matches);
            $parent_place = $matches[2];
            
            $permalink = home_url($type == 'city' ? "/$prefix/$parent_place/$place/" : "/$prefix/$place/");
        }
    }

    return $permalink;
}
function improveseo_seo_title($title) {
	return $title;
}

function improveseo_the_content_filter($content) {
	global $post;
    
	if ($post->post_type == 'channel') {
		$content = str_replace('@citylist', '', $content);
		$content = str_replace('@ziplist', '', $content);
	}

	return $content;
}

function change_blog_links($post_link, $id = 0){
	$post = get_post($id);
 
	$storage = new Storage('improveseo');
	$prefixes = $storage->permalink_prefixes;
	
	/*if (sizeof($prefixes)) {
		foreach ($prefixes as $prefix => $void) {
			if ($prefix != $post->post_type) continue;

			$siteurl = get_option('siteurl');

			return str_replace($siteurl, "{$siteurl}/{$prefix}", $post_link);
		}
	}*/

	return $post_link;
}

add_filter( 'upload_mimes', 'improveseo_mime_types', 1, 1 );
function improveseo_mime_types( $mime_types ) {
  $mime_types['ogg'] = 'audio/ogg';
  $mime_types['ogg'] = 'video/ogg';
  return $mime_types;
}

function improveseo_skip_loading_lazy_youtube_iframes( $value, $iframe, $context ) {
    if ( false !== strpos( $iframe, 'youtube.com' )  && false !== strpos( $iframe, 'vimeo.com' )) {
        return false;
    }
    return $value;
}
add_filter('wp_iframe_tag_add_loading_attr','improveseo_skip_loading_lazy_youtube_iframes',10,3);

function improveseo_exclude_category_posts($query) {
    if(!is_admin()){
        $improveseo_category = get_category_by_slug('improve-seo');
        $category_id = $improveseo_category->term_id;
        $query->set( 'category__not_in', array($category_id) );
    }
    return $query;
}

// add_filter( 'pre_get_posts', 'improveseo_exclude_category_posts' );

function improveseo_hide_improveseo_category(){
    $improveseo_category = get_category_by_slug('improve-seo');
    if(!empty($improveseo_category->term_id)) {
        $category_id = $improveseo_category->term_id;
    } else {
        $category_id = '';
    }
    
    echo '<style type="text/css">.cat-item-'.$category_id.' { display:none; }</style>';
}
add_action('wp_head', 'improveseo_hide_improveseo_category');