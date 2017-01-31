<?php

/**
 * Localize Ajax Variables
 */
wp_register_script( 'custom-js', get_stylesheet_directory_uri() . '/js/scripts.js', array( 'jquery' ), '', true );

wp_localize_script( 'custom-js', 'ajax_params', [
	'ajax_url' => admin_url('admin-ajax.php'),
	'ajax_nonce' => wp_create_nonce('ajax-nonce'),
	'query_vars' => json_encode( $wp_query->query )
]);
wp_enqueue_script( 'custom-js' );


/**
 * Blog Load Posts Ajax
 */
add_action('wp_ajax_nopriv_ajax_load_more_posts', 'ajax_load_more_posts');
add_action('wp_ajax_ajax_load_more_posts', 'ajax_load_more_posts');
 
function ajax_load_more_posts(){

    $ppp     = (isset($_POST['ppp'])) ? $_POST['ppp'] : 9;
    $cat     = (isset($_POST['cat'])) ? $_POST['cat'] : 0;
    $offset  = (isset($_POST['offset'])) ? $_POST['offset'] : 0;
    $exclude  = (isset($_POST['exclude'])) ? $_POST['exclude'] : '';

    $args = array(
        'post_type'      => 'post',
        'post_status' 	 => 'publish',
        'posts_per_page' => $ppp,
        'cat'            => $cat,
        'offset'         => $offset,
    );

	if ( $exclude != '' ) {
		if ($exclude == 'latest') {    
	    	$recent_posts = wp_get_recent_posts( array( 'numberposts' => '1' ) );
			$latest_post = $recent_posts[0]['ID'];
			$exclude_array = explode(",", strval($latest_post));
	    } else {
	    	$exclude_array = explode(",", strval($exclude));
	    }
	  	$args['post__not_in'] = $exclude_array;
	}

    $loop = new WP_Query($args);

    $return_string = '';

    if ($loop -> have_posts()) :
    	while ($loop -> have_posts()) :
    		$loop -> the_post();
	    	
	    	$post_id = get_the_ID();
			$post_title = get_the_title();
			$post_url = get_the_permalink();
			$author_url = get_author_posts_url( get_the_author_meta( 'ID' ), get_the_author_meta( 'user_nicename' ) );
			$child_theme_directory = get_stylesheet_directory_uri();
			$featured_image_src = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), 'large')[0];

		     $return_string .= '<div class="post-box post-box-' . $post_num . '">';
		    	if ($featured_image_src != '') {
		    	 $return_string .= '<a href="' . $post_url . '" class="featured-image-box clearfix" style="background-image:url(' . $featured_image_src .');">
		    	 						<img class="spacer spacer-desktop" src="' . $child_theme_directory . '/images/blog-spacer.png">
		    	 					</a>';
		    	}
		    	 $return_string .= '<div class="box-content">
		    							<h3><a href="' . $post_url . '">' . $post_title . '</a></h3>
		    							<div class="post-excerpt">' . wp_trim_words( get_the_content(), 30 ) . '</div>
		    							<div class="post-meta"><a href="' . $author_url . '">By ' . get_the_author() . '</a> <span class="divider-slashes">//</span> ' . get_the_time('F j, Y', $post_id) . '</div>
		    						</div>
		    					</div>';

		    $post_num++; // give each item a unique id

    	endwhile;
    endif;

    wp_reset_postdata();

    echo $return_string;

    wp_die();
}



/**
 * Blog Shortcode
 */
function blog_function($atts) {
    extract(shortcode_atts(array(
    	'type' => 'post',
    	'category' => '',
    	'ids' => '',
    	'exclude' => '',
    	'class' => '',
    ), $atts));

    $args = array(
	    'post_type'      => $type,
      	'showposts'      => 9,
      	'orderby'        => 'post_date',
      	'post_status'    => 'publish',
      	'order'          => 'DESC',
      	'posts_per_page' => 9,
	);

	if ( $ids != '' ) {
		$ids_array = explode(",", strval($ids));
	  	$args['post__in'] = $ids_array;
	} else {
		if ( $category != '' ) {
		  	$args['meta_query'] =array(
		        array(
					'key' => 'member_of',
					'value' => $category,
					'compare' => 'LIKE'
				),
		    );
		}
	}

	$exclude_array = '';
	if ( $exclude != '' ) {
		if ($exclude == 'latest') {    
	    	$recent_posts = wp_get_recent_posts( array( 'numberposts' => '1' ) );
			$latest_post = $recent_posts[0]['ID'];
			$exclude_array = explode(",", strval($latest_post));
	    } else {
	    	$exclude_array = explode(",", strval($exclude));
	    }
	  	$args['post__not_in'] = $exclude_array;
	}

    $query = new WP_Query($args);

	// set the return string variable
	$return_string = '';

	if ( $query->have_posts() ) {

		$post_num = 1;
		$post_count = $query->post_count;
		$return_string .= '<div class="post-list cf ajax-posts ' . $class . ' post-count-' . $post_count . ' blog-list">';

		while ( $query->have_posts() ) : $query->the_post();

			$post_id = get_the_ID();
			$post_title = get_the_title();
			$post_url = get_the_permalink();
			$author_url = get_author_posts_url( get_the_author_meta( 'ID' ), get_the_author_meta( 'user_nicename' ) );
			$child_theme_directory = get_stylesheet_directory_uri();
			$featured_image_src = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), '800')[0];

		    $return_string .= '<div class="post-box post-box-' . $post_num . '">';
		    	if ($featured_image_src != '') {
		    	 $return_string .= '<a href="' . $post_url . '" class="featured-image-box clearfix" style="background-image:url(' . $featured_image_src .');">
		    							<img class="spacer spacer-desktop" src="' . $child_theme_directory . '/images/blog-spacer.png">
		    						</a>';
		    	}
		    	 $return_string .= '<div class="box-content">
		    	 						<h3><a href="' . $post_url . '">' . $post_title . '</a></h3>
		    	 						<div class="post-excerpt">' . wp_trim_words( get_the_content(), 30 ) . '</div>
		    	 							<div class="post-meta">
		    	 								<a href="' . $author_url . '">By ' . get_the_author() . '</a> 
		    	 								<span class="divider-slashes">//</span> ' . get_the_time('F j, Y', $post_id) . '
		    	 							</div>
		    	 						</div>
		    	 					</div>';

		    $post_num++; // give each item a unique id

		endwhile; wp_reset_query();
		 $return_string .= '</div>
							<div class="after-posts">
								<div class="after-post-component hidden loading-animation sk-double-bounce">
									<div class="sk-child sk-double-bounce1"></div>
									<div class="sk-child sk-double-bounce2"></div>
								</div>
								<a id="load-more-posts" class="after-post-component btn btn-orange btn-md-padding load-more-posts" data-category="" data-exclude="' . $exclude . '">Load More Articles</a>
								<p class="after-post-component hidden no-more-message">No More Articles</p>
							</div>';
	} // end if

	return $return_string;
}

add_shortcode('blog', 'blog_function');



/**
 * Blog Category Filter Shortcode
 */
function blog_category_filter_function($atts) {

	$return_string = '';

	$categories = get_categories(); 
	$return_string .= '<select name="blog-category-select" class="blog-category-select">';
		$return_string .= '<option value="0">All Categories</option>';
		foreach ( $categories as $cat ) { 
			$return_string .= '<option value="' . $cat->term_id . '">' . $cat->name . '</option>';
		}
	$return_string .= '</select>';

	return $return_string;
}

add_shortcode('blog-category-filter', 'blog_category_filter_function');


?>