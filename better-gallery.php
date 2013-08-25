<?php
/*
Plugin Name: Better Gallery [shortcode]
Plugin URI: http://onesmallpixel.com/plugins/better-gallery-shortcode
Description: Replaces the Wordpress Gallery shortcode with something a bit better!
Author: One Small Pixel
Version: 1.0
Author URI: http://onesmallpixel.com
*/

include( 'osp-options.php' );


function osp_get_currentpage() {
	
	// Set current page for pagination if PAGE or if POST WITH PERMALINKS DISABLED
	$current_page = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1; // Current page if on a PAGE (Wordpress uses different GET vars for paging on Posts and Pages)
	
	// Set pagination rules if Post. The GET variable of $page will only change to $paged for a POST if permalinks are disabled
	if( is_single() && get_option( 'permalink_structure' ) != '' )
		$current_page = ( get_query_var( 'page' ) ) ? get_query_var( 'page' ) : 1; // Current page if on a POST (Wordpress uses different GET vars for paging on Posts and Pages)
	 
	return $current_page;
}

function better_gallery_shortcode( $atts ) {
	
	global $post;

	// Get Order
	if ( isset( $attr['orderby'] ) ) {
		$attr['orderby'] = sanitize_sql_orderby( $attr['orderby'] );
	if ( !$attr['orderby'] )
		unset( $attr['orderby'] );
	}
	
	// Set Plugin Defaults
	$defaults = array(
		'columns' => '4',
		'date' => '',
		'date_compare' => '<',
		'fallback' => 'No photos to display',
		'images_per_page' => '-1',
		'ids' => '',
		'size' => 'thumbnail',
		'orderby' => 'menu_order',
		'order' => 'ASC',
		'hide_content' => 'no'
	);
	
	// Get User Defaults from wp_options table and overwrite the plugin defaults with them.
	$options = wp_parse_args( get_option( 'osp_bettergallery_options' ), $defaults );

	// Set default attributes for the shortcode, then overwrite them with the user inputted attributes
	extract( shortcode_atts( $options, $atts ) );
	
	// Assume there are no photos in the gallery. Set gallery to fallback text.
	$gallery = $fallback;
	
	// Set the Date
	if( ! empty( $date ) ) {
		$GLOBALS['btrgal_date'] = $date;		// Hate to use Globals like this, but Wordpress filters do not accept custom arguments!
		$GLOBALS['btrgal_date_compare'] = $date_compare;
	}
	
	// Get Current Page
	$current_page = osp_get_currentpage();
	
	//Set up query to get images for given Post/Page
	$args = array( 
		'post_type' => 'attachment',  
		'post_status' => 'inherit',  
		'paged' => $current_page,
		'posts_per_page' => $images_per_page 
	); 
	
	// If no Post IDs were given, grab all attachments for the the post
	if ( empty( $ids ) ) {
		$args['post_parent'] = $post->ID;
	} else {
		// Break apart ids into array
		$post_ids = wp_parse_id_list( $ids );
		$args['post__in'] = $post_ids;
	}
	//print_r($args);
	// Run Modified Query
	add_filter( 'posts_where', 'bettergallery_filter_where' ); // Filter only gets attachments from before specific date	

	$attachments  = new WP_Query( $args );	

	remove_filter( 'posts_where', 'bettergallery_filter_where' );

	// If there are images...
	if ( $attachments->found_posts > 0 ) {
		
		// Columns can't be more than 6
		if( 6 < $columns )
			$columns = 6;
		
		$gallery = '<div id="osp-bettergallery">';

		$gallery .= '<div class="osp-bg-yoga">';
		// $gallery .= '<div class="left-nav"><span>L</span></div>';
		// $gallery .= '<div class="right-nav"><span>R</span></div>';	
		$gallery .= '<div class="osp-gal-container cf">';	
		$gallery .= '<div class="img-container cf">';
		
		$attachIndex = 1; # Start the counter
	
		//Loop through all the attachments and output them in a "table" according to values set above.
		
		foreach ( $attachments->posts as $attachment ) {

		    $break = "";

		    if( ( $attachIndex % $columns ) == 0 )
				$break = "cf";
			
			//Display the Thumbnail image with link to attachment page
			$image_source = wp_get_attachment_image_src( $attachment->ID, $size );
				//$image_source[0] -- Image URL
				//$image_source[1] -- Image Width
				//$image_source[2] -- Image Height
				
			$gallery .= "<div class=\"osp-image osp-gal-1-of-$columns $break\">";
	
		   	$gallery .= '<a href="'. get_attachment_link( $attachment->ID ) .'">
		   				 <img src="'. $image_source[0] .'"/>
						 <span>View Photo</span></a>
						 </div>';
			
			$attachIndex = $attachIndex + 1; // Increase counter
		}

		$gallery .= "</div><div style=\"clear:both;\"></div>";
		$gallery .= "</div>"; 
		$gallery .= "</div>"; 
		$gallery .= "</div>"; 
		
		/** Display Pagination Links **/
		// This was pulled from Wordpress Codex (slightly modified)
		// http://codex.wordpress.org/Function_Reference/paginate_links
		// Was modified to handle the custom wp_query objects for images in gallery.
		
		if( ! is_home() ){	// Don't display pagination links on the homepage
			
			$gallery .= "<div class=\"bg-pagination\" style=\"clear:both;\">";

			if( $paginate && ! is_single() ){
				
				// Handle Pages
				$big = 999999999; // need an unlikely integer
				
				$gallery .= paginate_links( array(
					'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
					'format' => '?paged=%#%',
					'current' => max( 1, $current_page ),
					'total' => $attachments->max_num_pages
					) );				
				
			} else {
			
				// Handle Posts
				$big = 999999999; // need an unlikely integer

				//add filter to paginate_links to str_replace '/page/' with 
				add_filter( 'paginate_links', 'bettergallery_paginate_links_filter' );
				$gallery .= paginate_links( array(
					'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
					'format' => '?page=%#%',
					'current' => max( 1, $current_page ),
					'total' => $attachments->max_num_pages
				) );
				remove_filter( 'paginate_links', 'bettergallery_paginate_links_filter' );
						
			}
			$gallery .= "</div>"; 
		}

	}
	
	return $gallery;
	
}

add_shortcode( 'bettergallery', 'better_gallery_shortcode' );


// Hide all the content but the bettergallery (only if the user is not on the first page of content)
$options = get_option( 'osp_bettergallery_options' ); // Get Options

if( isset( $options['hide_content'] ) ) {
	if( $options['hide_content'] == '1' ) {
		add_filter( 'the_content', 'bettergallery_filter_the_content' );
	} 
}
	
function bettergallery_filter_the_content( $content ) {
	// Get Current Page
	$current_page = osp_get_currentpage();

	if( $current_page > 1 ){
		//Find all instances of [bettergallery] shortcode
		$pattern = '/\[bettergallery.*\]/';
		preg_match( $pattern, $content, $matches );
		$content = $matches[0];
		return $content;
	} else {
		return $content;
	}
}


function bettergallery_paginate_links_filter( $link ) {

	$pattern = '(\/\d*\/page\/.*\z)';
   	preg_match( $pattern, $link, $matches );	
 	
 	// On first page of Post, a simple str_replace will do!
 	if( count( $matches ) == 0 ) {
 		$current_page_url = trailingslashit( get_permalink() ) . 'page/'; // Remove Extra Forwardslashes
		$link = str_replace( $current_page_url, trailingslashit( get_permalink() ), $link );
		$link = str_replace( '//', '/', $link ); // Remove crazy double slashes
		
 	} else {

 		// On 2nd page or beyond . . . The link needs more work
 		
 		$pattern = '/\/page\/(\d*)/';					// Get Page Number from end of URL
 		preg_match( $pattern, $link, $matches );
 		
 		$pageNumber = $matches[1];						// Matched Page Number
 		
 		// Add 'JUMP' to the gallery on pages 2 and beyond
 		$url_vars = "#osp-bettergallery";
 		
 		if( ! strpos( $pageNumber, '/' ) ) 
 			$pageNumber = '/' . $pageNumber;
 		
 		$link = get_permalink() . untrailingslashit( $pageNumber );	// Regenerate the link
 		$link = str_replace( '//', '/', $link );  // Remove crazy double slashes
 		$link = $link . $url_vars;
 	}
  
  	return $link; // Return the modified version of the link.
}



// Filter to add a custom where clause to the query
function bettergallery_filter_where( $where = '' ) {
	
	// get posts after a certain date 'YYYY-MM-DD' (Using globals since wordpress filters don't accept custom args)
	if( $GLOBALS['btrgal_date'] ) {
		$date = $GLOBALS['btrgal_date'];
		$operator = $GLOBALS['btrgal_date_compare'];
		$where .= " AND post_date $operator '$date'";
	}
	return $where;
}

// Add settings link on plugin page
function osp_bettergallery_settings_link( $links ) { 
  $settings_link = '<a href="options-general.php?page=osp-bettergallery">Settings</a>'; 
  array_unshift( $links, $settings_link ); 
  return $links; 
}
 
$plugin = plugin_basename( __FILE__ ); 
add_filter("plugin_action_links_$plugin", 'osp_bettergallery_settings_link' );

function bettergallery_styles() {
	// Enqueue Style Sheets
	wp_enqueue_style( 'bettergallery', plugin_dir_url( __FILE__ ) . 'style/bettergallery.css', array(), '0.1', 'screen' );
}
add_action( 'wp_enqueue_scripts', 'bettergallery_styles' );

function bettergallery_javascript() {
	// Enqueue Style Sheets
  	wp_register_script( 'bettergallery-js', plugins_url( 'js/bettergallery.js', __FILE__ ), array( 'jquery' ), '20130823', true ); 
  	wp_enqueue_script( 'bettergallery-js' ); 
}
add_action( 'wp_enqueue_scripts', 'bettergallery_javascript' );
?>