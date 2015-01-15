<?php
/*
Plugin Name: Oikos Talks
Plugin URI: http://oikos.org.uk/plugins/oikos-talks
Description: Provides an interface for managing audio files of talks with options to categorise by service (intended for churches) and speaker, and to apply multiple "topics" tags.
Version: 0.2.2
Author: Ross Wintle/Oikos
Author URI: http://oikos.org.uk/

Version history:

0.1: Initial release
0.2: Updates for WordPress 3.6's built in media functions
0.2.1: Fix incorrect version checking
*/


function oikos_talks_setup () {
	// Add a custom post type for talks with some custom taxonomies too
	$oikos_talks_speaker_labels = array (
		'name' => 'Speakers',
		'singular_name' => 'Speaker',
		'search_items' => 'Search Speakers',
		'popular_items' => 'Frequent Speakers',
		'all_items' => 'All Speakers',
		'parent_item' => 'Speaker Group',
		'parent_item_colon' => 'Speaker Group:',
		'edit_item' => 'Edit Speaker',
		'update_item' => 'Update Speaker',
		'add_new_item' => 'Add New Speaker',
		'new_item_name' => 'New Speaker Name',
		'separate_items_with_commas' => 'Separate speakers with commas',
		'add_or_remove_items' => 'Add or Remove Speakers',
		'choose_from_most_used' => 'Choose from most frequent speakers'
		);
	register_taxonomy( 'oikos_talks_speaker', 'oikos_talks', array( 'labels' => $oikos_talks_speaker_labels, 'hierarchical' => true, 'rewrite' => array ( 'slug' => 'talks/speaker' ) ) );

	$oikos_talks_service_labels = array (
		'name' => 'Services',
		'singular_name' => 'Service',
		'search_items' => 'Search Services',
		'popular_items' => 'Frequent Services',
		'all_items' => 'All Services',
		'parent_item' => 'Service Group',
		'parent_item_colon' => 'Service Group:',
		'edit_item' => 'Edit Service',
		'update_item' => 'Update Service',
		'add_new_item' => 'Add New Service',
		'new_item_name' => 'New Service Name',
		'separate_items_with_commas' => 'Separate services with commas',
		'add_or_remove_items' => 'Add or Remove Services',
		'choose_from_most_used' => 'Choose from most frequent services'
		);
	register_taxonomy( 'oikos_talks_service', 'oikos_talks', array( 'labels' => $oikos_talks_service_labels, 'hierarchical' => true, 'rewrite' => array ( 'slug' => 'talks/service' ) ) );

	$oikos_talks_topic_labels = array (
		'name' => 'Topics',
		'singular_name' => 'Topic',
		'search_items' => 'Search Topics',
		'popular_items' => 'Frequent Topics',
		'all_items' => 'All Topics',
		'parent_item' => 'Parent Topic',
		'parent_item_colon' => 'Parent Topic:',
		'edit_item' => 'Edit Topic',
		'update_item' => 'Update Topic',
		'add_new_item' => 'Add New Topic',
		'new_item_name' => 'New Topic Name',
		'separate_items_with_commas' => 'Separate topics with commas',
		'add_or_remove_items' => 'Add or Remove Topics',
		'choose_from_most_used' => 'Choose from most frequent topics'
		);
	register_taxonomy( 'oikos_talks_topic', 'oikos_talks', array( 'labels' => $oikos_talks_topic_labels, 'rewrite' => array ( 'slug' => 'talks/topic' ) ) );

	$oikos_talks_post_type_labels = array (
		'name' => 'Talks',
		'singular_name' => 'Talk',
		'add_new' => 'Add New Talk',
		'add_new_item' => 'Add New Talk',
		'edit_item' => 'Edit Talk',
		'new_item' => 'New Talk',
		'view_item' => 'View Talk',
		'search_items' => 'Search Talks',
		'not_found' => 'No talks found',
		'not_found_in_trash' => 'No talks found in trash'
	);
	$oikos_talks_post_type_supports = array ( 'title', 'editor', 'revisions', 'page-attributes', 'thumbnail');
	$oikos_talks_post_type_args= array (
		'label' => 'Talks',
		'labels' => $oikos_talks_post_type_labels,
		'description' => 'List of Talks/Sermons',
		'public' => true,
		'supports' => $oikos_talks_post_type_supports,
		'hierarchical' => false,
		'has_archive' => true,
		'menu_icon' => get_bloginfo('wpurl') . "/wp-content/plugins/oikos-talks/images/talks-menu-icon.png",
		'rewrite' => array( 'slug' => 'talks')
	);
	register_post_type( 'oikos_talks', $oikos_talks_post_type_args );

	
	add_shortcode( 'talks', 'get_oikos_talks');
	
	wp_enqueue_style( 'oikos_talks', '/wp-content/plugins/oikos-talks/css/talks.css' );

}

add_action ( 'init', 'oikos_talks_setup');

function oikos_talks_script() {
?>
		<script type="text/javascript">
			//<![CDATA[
			jQuery(document).ready( function () {
				// Where talks are present add a little hover-over note for download links
				jQuery('div.oikos-talks-download').append(
					'<div class="oikos-talks-download-note">Please note that you may have to right-click and select "Save link as..." to save this to your computer</div>'
					).hover(function () { jQuery(this).children('.oikos-talks-download-note').fadeToggle('fast'); } );
				// Make show more links work on talks
				// Use the 'other-text' data item to switch the label on the link
				jQuery('a.oikos-talks-more').data('other-text', 'Less detail').click(function () {
					jQuery(this).siblings('.oikos-talks-hidden-meta').slideToggle('fast');
					newText=jQuery(this).data('other-text');
					oldText=jQuery(this).html();
					jQuery(this).data('other-text', oldText); 
					jQuery(this).html(newText);
				});
			} );
			// ]]>
		</script>
<?php	
}

/* This function formats the details of a talk. It should be passed:
 *  - the URL to the audio file
 *  - the description of the talk ($content)
 *  - the list of speakers, complete with links, as formatted by get_the_term_list)
 *  - the list of services, complete with links, as formatted by get_the_term_list)
 *  - the list of topics, complete with links, as formatted by get_the_term_list)
 *  - the thumbnail output markup (an img tag!)
 */
function oikos_talks_format_talk ( $audio_url, $content, $speakers, $services, $topics, $thumbnail='' ) {

	// Set up the variables/data to output

	if ($audio_url) {
		global $wp_version;
		$version_bits = explode('.', $wp_version);
		// If we're greater than v3.6 then we have audio shortcode and mediaelement.js built in!
		$audio_player_output = '';
		if ($version_bits >= 4 || ($version_bits[0] == 3 && $version_bits[1] >= 6)) {
			$audio_player_output = do_shortcode('[audio src="' . $audio_url . '"]');
		} else {
			if (function_exists("insert_audio_player")) {
				// This is copied from the source of the insert_audio_player function of
				// the ancient plugin. See http://trac.assembla.com/1pixelout/browser/audio-player/trunk/plugin/audio-player.php
				// We've copied this here because we don't want to echo the output, we want to collect it
				// for later use.
				global $AudioPlayer;
				$audio_player_output = $AudioPlayer->processContent("[audio:$audio_url]");	
			}
		}

		$output = <<<EOT
					<div class="oikos-talks-details">
						<div class="oikos-talks-content">
							$content
						</div><!-- .oikos-talks-content -->
						<div class="oikos-talks-meta">
							<a class="oikos-talks-more">More detail</a>
							<div class="oikos-talks-hidden-meta">
								<p>Talk by $speakers
								in $services service</p>
								<p>Talk topics: $topics</p>
							</div><!-- .oikos-talks-hidden-meta -->
						</div><!-- .oikos-talks-meta -->
					</div><!-- .oikos-talks-details -->
					<div class="oikos-talks-audio">
						<div class="oikos-talks-audio-player">
							$audio_player_output
							<div class="oikos-talks-download"><a href="$audio_url">Download talk</a></div>
						</div><!-- oikos-talks-audio-player -->
					</div><!-- .oikos-talks-audio -->
EOT;
	} else {
		$output = "";
	}

	return $output;

}

/* This function prints a talk.  It must be used within a WordPress loop as it
 * depends on the $post variable being set.
 *
 * It can be used as a filter: by passing content in the $content argument, it will print
 * that content as the description of the talk.
 *
 * It can also be used just to get a talks content: by not passing a $content argument
 * the function will print content using the_content()
 *
 * Note that the function only uses the talk formatting if the current post in the loop
 * is of type 'oikos_talks'.
 *
 * This function is designed to be used either as a filter on the_content or inside the
 * loop in a template file.
 */

function oikos_talks_get_talk ( $content = null ) {
	
	global $post;
		
	if ( is_singular() && get_post_type() == 'oikos_talks' ) {

		$talk_content = is_null($content) ? get_the_content() : $content;
		$talk_url = get_post_meta($post->ID, '_oikos_talks_audio_url', true);
		$talk_speakers = get_the_term_list($post->ID, 'oikos_talks_speaker', '', ', ', '' );
		$talk_services = get_the_term_list($post->ID, 'oikos_talks_service', '', ', ', '' );
		$talk_topics = get_the_term_list ( $post->ID, 'oikos_talks_topic', '', ', ', '' );
		if (has_post_thumbnail($post->ID)) {
			$thumbnail = get_the_post_thumbnail($post->ID, 'medium' );
		}

		// Add the jQuery script to the footer
		add_action( 'wp_footer', 'oikos_talks_script' );
		return oikos_talks_format_talk( $talk_url, $talk_content, $talk_speakers, $talk_services, $talk_topics, $thumbnail);

	} else {

		return $content;

	}
} 

add_filter ('the_content', 'oikos_talks_get_talk' );

/*
 * This function gets the list of talks in a pre-set (but stylable) format.
 * You can put this function in a theme template file (recommended) or use the shortcode
 * "talks" that was registered above.
 *  
 */
function get_oikos_talks ($attrs) {
	
	global $post;
	
	$original_post = $post;
	
	$talks = new WP_Query( array( 'post_type' => 'oikos_talks' ) );

	// We might have the filter on the_content set, so remove it and re-instate it later.
	remove_filter('the_content', 'oikos_talks_get_talk' );
	
	if ( $talks->have_posts() ) :
?>

		<div class="oikos-talks">

<?php		while ( $talks->have_posts() ) : $talks->the_post(); ?>

				<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<div class="oikos-talks-date"><?php the_date( "F j, Y", "<div class=\"talks-timestamp\">", "</div>" ); ?></div>
					<div class="oikos-talks-details">
						<h2 class="entry-title oikos-talks-title"><a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr( 'Permalink to %s' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?></a></h2>
						<div class="oikos-talks-content">
							<?php echo get_the_content(); ?>
						</div><!-- .oikos-talks-content -->
						<div class="oikos-talks-meta">
							<a class="oikos-talks-more">More detail</a>
							<div class="oikos-talks-hidden-meta">
								<p><?php the_terms($post->ID, 'oikos_talks_speaker', 'Talk by ', ', ', ''); ?>
								<?php the_terms($post->ID, 'oikos_talks_service', ' in ', ', ', ' service'); ?></p>
								<p><?php the_terms($post->ID, 'oikos_talks_topic', 'Talk topics: ', ', ', ''); ?></p>
							</div><!-- .oikos-talks-hidden-meta -->
						</div><!-- .oikos-talks-meta -->
					</div><!-- .oikos-talks-details -->
					<div class="oikos-talks-audio">
						<div class="oikos-talks-audio-player">
						<?php
							$audio_url = get_post_meta($post->ID, '_oikos_talks_audio_url', true);
							if ($audio_url) :
								global $wp_version;
								$version_bits = explode('.', $wp_version);
								// If we're greater than v3.6 then we have audio shortcode and mediaelement.js built in!
								if ($version_bits[0] >= 4 || ($version_bits[0] == 3 && $version_bits[1] >= 6)) {
									echo do_shortcode('[audio src="' . $audio_url . '"]');
								} else {								
									if (function_exists("insert_audio_player")) :
										insert_audio_player("[audio:$audio_url]");
									endif;
								}
						?>
								<div class="oikos-talks-download"><a href="<?php echo $audio_url; ?>">Download talk</a></div>
						<?php
							endif;
						?>
						</div><!-- oikos-talks-audio-player -->
					</div><!-- .oikos-talks-audio -->
					<div style="clear:both; height: 0;"></div>
				</div><!-- post -->
<?php
		endwhile;
?>
		<script type="text/javascript">
			//<![CDATA[
			jQuery(document).ready( function () {
				// Where talks are present add a little hover-over note for download links
				jQuery('div.oikos-talks-download').append(
					'<div class="oikos-talks-download-note">Please note that you may have to right-click and select "Save link as..." to save this to your computer</div>'
					).hover(function () { jQuery(this).children('.oikos-talks-download-note').fadeToggle('fast'); } );
				// Make show more links work on talks
				// Use the 'other-text' data item to switch the label on the link
				jQuery('a.oikos-talks-more').data('other-text', 'Less detail').click(function () {
					jQuery(this).siblings('.oikos-talks-hidden-meta').slideToggle('fast');
					newText=jQuery(this).data('other-text');
					oldText=jQuery(this).html();
					jQuery(this).data('other-text', oldText); 
					jQuery(this).html(newText);
				});
			} );
			// ]]>
		</script>
		</div><!-- .oikos-talks -->
<?php
	endif;

	// Re-instate the filter on the_content.
	add_filter('the_content', 'oikos_talks_get_talk' );

	
	$post = $original_post;
	
}


/* 
 * *** TALKS OPTIONS ***
 *
 * The following allows an extra meta box for talks to select a piece of media for the Audio URL 
 *
 */

function oikos_talks_print_meta_box($post)
{
	
	$currentValue = get_post_meta($post->ID, '_oikos_talks_audio_url', true);
?>
	<p>
		Audio File Location: 
        <input id="oikos_talks_audio_url" type="text" name="oikos_talks_audio_url" size="80" maxlength="255" value="<?php echo $currentValue; ?>">
		<input id="oikos_talks_audio_url_select" class="button" type="button" value="Select Audio" name="oikos_talks_audio_button" />
    </p>
<?php
}

// This adds the jQuery for making the audio select button launch the media selector thickbox.
function oikos_talks_meta_scripts() {
?>
	<script>
		jQuery(document).ready( function () {
			var postType = jQuery('#post_type').val();
			if (postType == 'oikos_talks') {

				jQuery('#oikos_talks_audio_url_select').click( function () {
					var postID = jQuery('#post_ID').val();
					window.send_to_editor = function (html) {
						audiourl = jQuery(html).attr('href');
						jQuery('#oikos_talks_audio_url').val(audiourl);
						tb_remove();
					}
					tb_show('', 'media-upload.php?post_id=' + postID + '&type=audio&TB_iframe=true');
					return false;
					
				} );
						
			}
		});
	</script>
<?php
}

function oikos_talks_save_meta_data( $postId )
{
	
	if ( 'oikos_talks' ==  $_POST['post_type'] ) {    
		if (  !current_user_can( 'edit_page', $post_id ))    
			return  $post_id;    
	}
	/* No POST data for custom meta during auto save, so exit to prevent deleting the values */
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
    	return $postId;
	}
	
	$data = $_POST['oikos_talks_audio_url'];
		
	// Should validate URL in here - note: can be full URL or partial path!
	
	if (get_post_meta( $postId, '_oikos_talks_audio_url') == "")
	{
		add_post_meta( $postId, '_oikos_talks_audio_url', $data, true);
	} elseif ( $data != get_post_meta( $postId, '_oikos_talks_audio_url' ) ) {
		update_post_meta( $postId, '_oikos_talks_audio_url', $data );
	} elseif ( $data == "" ) {
		delete_post_meta( $postId, '_oikos_talks_audio_url', get_post_meta( $postId, '_oikos_talks_audio_url'	, true) );
	}
}

function oikos_talks_create_meta_box() 
{
	if (function_exists('add_meta_box')) {
		add_meta_box('talks-meta', "Audio File Location", 'oikos_talks_print_meta_box', 'oikos_talks', 'normal', 'high', '');
	}
}

add_action('admin_menu',  'oikos_talks_create_meta_box');
add_action('admin_head', 'oikos_talks_meta_scripts');
add_action('save_post',  'oikos_talks_save_meta_data');

/* This function checks for the existence of the Audio Player plugin and
 * adds a notice if the player does not exist.  Should be attached to the
 * 'admin_notices' action.
 */
function oikos_talks_check_audio_plugin () {
	global $wp_version;
	$version_bits = explode('.', $wp_version);
	// If we're greater than v3.6 then we have audio shortcode and mediaelement.js built in!
	if (! ($version_bits[0] >= 4 || ($version_bits[0] == 3 && $version_bits[1] >= 6))) {
		if ( ! function_exists('insert_audio_player') ) {
?>
			<div id="message" class="error">
				<p>The Oikos Talks plugin can't find the Audio Player Plugin. Audio Player needs to be installed and activated for Oikos Talks to work. Try <a href="<?php bloginfo( 'wpurl'); ?>/wp-admin/plugin-install.php?tab=search&type=term&s=audio+player">this plugin search</a></p>
			</div>
<?php 
		}
	}
}

add_action('admin_notices', 'oikos_talks_check_audio_plugin')

?>
