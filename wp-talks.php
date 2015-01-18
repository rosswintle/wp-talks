<?php
/*
Plugin Name: WP Talks
Plugin URI: http://oikos.org.uk/plugins/wp-talks
Description: Provides an interface for managing audio files of talks with options to categorise by service (intended for churches) and speaker, and to apply multiple "topics" tags.
Version: 0.2.6
Author: Ross Wintle/Oikos
Author URI: http://oikos.org.uk/

Version history:

0.2.6: Minor styling fixes to output
0.2.5: Squashed some bugs. Added a "Series" column to the Talks edit list.
0.2.4: Renamed and prepped for public release
0.2.3: Add post series
0.2.2: Add features images in output and fix output bugs
0.2.1: Fix incorrect version checking
0.2: Updates for WordPress 3.6's built in media functions
0.1: Initial release
*/


function wp_talks_setup () {
	// Add a custom post type for talks with some custom taxonomies too
	$wp_talks_speaker_labels = array (
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
	register_taxonomy( 'wp_talks_speaker', 'wp_talks', array( 'labels' => $wp_talks_speaker_labels, 'hierarchical' => true, 'rewrite' => array ( 'slug' => 'talks/speaker' ), 'show_admin_column' => true ) );

	$wp_talks_service_labels = array (
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
	register_taxonomy( 'wp_talks_service', 'wp_talks', array( 'labels' => $wp_talks_service_labels, 'hierarchical' => true, 'rewrite' => array ( 'slug' => 'talks/service' ), 'show_admin_column' => true ) );

	$wp_talks_topic_labels = array (
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
	register_taxonomy( 'wp_talks_topic', 'wp_talks', array( 'labels' => $wp_talks_topic_labels, 'rewrite' => array ( 'slug' => 'talks/topic' ), 'show_admin_column' => true ) );

	$wp_talks_post_type_labels = array (
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
	$wp_talks_post_type_supports = array ( 'title', 'editor', 'revisions', 'page-attributes', 'thumbnail', 'author');
	$wp_talks_post_type_args= array (
		'label' => 'Talks',
		'labels' => $wp_talks_post_type_labels,
		'description' => 'List of Talks/Sermons',
		'public' => true,
		'supports' => $wp_talks_post_type_supports,
		'hierarchical' => false,
		'has_archive' => true,
		'menu_icon' => 'dashicons-testimonial',
		'rewrite' => array( 'slug' => 'talks')
	);
	register_post_type( 'wp_talks', $wp_talks_post_type_args );

	$wp_talk_series_post_type_labels = array (
		'name' => 'Talk Series',
		'singular_name' => 'Talk Series',
		'add_new' => 'Add New Talk Series',
		'add_new_item' => 'Add New Talk Series',
		'edit_item' => 'Edit Talk Series',
		'new_item' => 'New Talk Series',
		'view_item' => 'View Talk Series',
		'search_items' => 'Search Talk Seriess',
		'not_found' => 'No talk series found',
		'not_found_in_trash' => 'No talk series found in trash'
	);
	$wp_talk_series_post_type_supports = array ( 'title', 'editor', 'revisions', 'page-attributes', 'thumbnail');
	$wp_talk_series_post_type_args= array (
		'label' => 'Talk series',
		'labels' => $wp_talk_series_post_type_labels,
		'description' => 'List of Talk/Sermon Series',
		'public' => true,
		'supports' => $wp_talk_series_post_type_supports,
		'hierarchical' => false,
		'has_archive' => true,
		'menu_icon' => 'dashicons-format-chat',
		'rewrite' => array( 'slug' => 'talk-series')
	);
	register_post_type( 'wp_talk_series', $wp_talk_series_post_type_args );
	
	add_shortcode( 'talks', 'get_wp_talks');
	
	wp_enqueue_style( 'wp_talks', '/wp-content/plugins/wp-talks/css/talks.css' );

}

add_action ( 'init', 'wp_talks_setup');

function wp_talks_script() {
?>
		<script type="text/javascript">
			//<![CDATA[
			jQuery(document).ready( function () {
				// Where talks are present add a little hover-over note for download links
				jQuery('div.wp-talks-download').append(
					'<div class="wp-talks-download-note">Please note that you may have to right-click and select "Save link as..." to save this to your computer</div>'
					).hover(function () { jQuery(this).children('.wp-talks-download-note').fadeToggle('fast'); } );
				// Make show more links work on talks
				// Use the 'other-text' data item to switch the label on the link
				jQuery('a.wp-talks-more').data('other-text', 'Less detail').click(function () {
					jQuery(this).siblings('.wp-talks-hidden-meta').slideToggle('fast');
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
function wp_talks_format_talk ( $audio_url, $content, $speakers, $services, $topics, $thumbnail='' ) {

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
					<div class="wp-talks-container">
						<div class="wp-talks-details">
							<div class="wp-talks-content">
								$content
							</div><!-- .wp-talks-content -->
							<div class="wp-talks-meta">
								<a class="wp-talks-more">More detail</a>
								<div class="wp-talks-hidden-meta">
									<p>Talk by $speakers
									in $services service</p>
									<p>Talk topics: $topics</p>
								</div><!-- .wp-talks-hidden-meta -->
							</div><!-- .wp-talks-meta -->
						</div><!-- .wp-talks-details -->
						<div class="wp-talks-audio">
							<div class="wp-talks-audio-player">
								$audio_player_output
								<div class="wp-talks-download"><a href="$audio_url">Download talk</a></div>
							</div><!-- wp-talks-audio-player -->
						</div><!-- .wp-talks-audio -->
					</div><!-- .wp-talks-container -->
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
 * is of type 'wp_talks'.
 *
 * This function is designed to be used either as a filter on the_content or inside the
 * loop in a template file.
 */

function wp_talks_get_talk ( $content = null ) {
	
	global $post;
		
	if ( is_singular() && get_post_type() == 'wp_talks' ) {

		$talk_content = is_null($content) ? get_the_content() : $content;
		$talk_url = get_post_meta($post->ID, '_wp_talks_audio_url', true);
		$talk_speakers = get_the_term_list($post->ID, 'wp_talks_speaker', '', ', ', '' );
		$talk_services = get_the_term_list($post->ID, 'wp_talks_service', '', ', ', '' );
		$talk_topics = get_the_term_list ( $post->ID, 'wp_talks_topic', '', ', ', '' );
		if (has_post_thumbnail($post->ID)) {
			$thumbnail = get_the_post_thumbnail($post->ID, 'medium' );
		}

		// Add the jQuery script to the footer
		add_action( 'wp_footer', 'wp_talks_script' );
		return wp_talks_format_talk( $talk_url, $talk_content, $talk_speakers, $talk_services, $talk_topics, $thumbnail);

	} else {

		return $content;

	}
} 

add_filter ('the_content', 'wp_talks_get_talk' );

/*
 * This function gets the list of talks in a pre-set (but stylable) format.
 * You can put this function in a theme template file (recommended) or use the shortcode
 * "talks" that was registered above.
 *  
 */
function get_wp_talks ($attrs) {
	
	global $post;
	
	$original_post = $post;
	
	$talks = new WP_Query( array( 'post_type' => 'wp_talks' ) );

	// We might have the filter on the_content set, so remove it and re-instate it later.
	remove_filter('the_content', 'wp_talks_get_talk' );
	
	if ( $talks->have_posts() ) :
?>

		<div class="wp-talks">

<?php		while ( $talks->have_posts() ) : $talks->the_post(); ?>

				<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<div class="wp-talks-date"><?php the_date( "F j, Y", "<div class=\"talks-timestamp\">", "</div>" ); ?></div>
					<div class="wp-talks-details">
						<h2 class="entry-title wp-talks-title"><a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr( 'Permalink to %s' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?></a></h2>
						<div class="wp-talks-content">
							<?php echo get_the_content(); ?>
						</div><!-- .wp-talks-content -->
						<div class="wp-talks-meta">
							<a class="wp-talks-more">More detail</a>
							<div class="wp-talks-hidden-meta">
								<p><?php the_terms($post->ID, 'wp_talks_speaker', 'Talk by ', ', ', ''); ?>
								<?php the_terms($post->ID, 'wp_talks_service', ' in ', ', ', ' service'); ?></p>
								<p><?php the_terms($post->ID, 'wp_talks_topic', 'Talk topics: ', ', ', ''); ?></p>
							</div><!-- .wp-talks-hidden-meta -->
						</div><!-- .wp-talks-meta -->
					</div><!-- .wp-talks-details -->
					<div class="wp-talks-audio">
						<div class="wp-talks-audio-player">
						<?php
							$audio_url = get_post_meta($post->ID, '_wp_talks_audio_url', true);
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
								<div class="wp-talks-download"><a href="<?php echo $audio_url; ?>">Download talk</a></div>
						<?php
							endif;
						?>
						</div><!-- wp-talks-audio-player -->
					</div><!-- .wp-talks-audio -->
					<div style="clear:both; height: 0;"></div>
				</div><!-- post -->
<?php
		endwhile;
?>
		<script type="text/javascript">
			//<![CDATA[
			jQuery(document).ready( function () {
				// Where talks are present add a little hover-over note for download links
				jQuery('div.wp-talks-download').append(
					'<div class="wp-talks-download-note">Please note that you may have to right-click and select "Save link as..." to save this to your computer</div>'
					).hover(function () { jQuery(this).children('.wp-talks-download-note').fadeToggle('fast'); } );
				// Make show more links work on talks
				// Use the 'other-text' data item to switch the label on the link
				jQuery('a.wp-talks-more').data('other-text', 'Less detail').click(function () {
					jQuery(this).siblings('.wp-talks-hidden-meta').slideToggle('fast');
					newText=jQuery(this).data('other-text');
					oldText=jQuery(this).html();
					jQuery(this).data('other-text', oldText); 
					jQuery(this).html(newText);
				});
			} );
			// ]]>
		</script>
		</div><!-- .wp-talks -->
<?php
	endif;

	// Re-instate the filter on the_content.
	add_filter('the_content', 'wp_talks_get_talk' );

	
	$post = $original_post;
	
}


/* 
 * *** TALKS OPTIONS ***
 *
 * The following allows an extra meta box for talks to select a piece of media for the Audio URL 
 *
 */

function wp_talks_print_meta_box($post)
{
	
	$currentValue = get_post_meta($post->ID, '_wp_talks_audio_url', true);
	$currentSeries = get_post_meta($post->ID, '_wp_talks_series');
	if (! is_array($currentSeries)) {
		$currentSeries = array();
	}

	// Add an nonce field so we can check for it later.
	wp_nonce_field( 'wp_talks_meta_box', 'wp_talks_meta_box_nonce' );
?>

	<p>
		<label for="wp_talks_audio_url">Audio File Location:</label><br>
        <input id="wp_talks_audio_url" type="text" name="wp_talks_audio_url" size="80" maxlength="255" value="<?php echo $currentValue; ?>">
		<input id="wp_talks_audio_url_select" class="button" type="button" value="Select Audio" name="wp_talks_audio_button" />
    </p>
    <p>
    	<label for="wp_talks_series[]">Series:</label><br>
		<?php
			$series = get_posts(array(
									'post_type' => 'wp_talk_series',
									'posts_per_page' => -1,
									'order_by' => 'title',
									'order' => 'ASC')
								);
			if (is_array($series) && !empty($series)) {
		?>
    			<select id="wp_talks_series_select" name="wp_talks_series[]" multiple="true">
    				<?php
	    				foreach($series as $this_series) {
	    					if (in_array($this_series->ID, $currentSeries)) {
	    						$selected = ' selected="selected"';
	    					} else {
	    						$selected = '';
	    					}
	    					printf('<option value="%1$d" %2$s>%3$s</option>', $this_series->ID, $selected, $this_series->post_title);
	    				}
	    			?>
		    	</select>
		<?php
			} else {
				echo "No series available.";
			}
		?>
	</p>
<?php
}

// This adds the jQuery for making the audio select button launch the media selector thickbox.
function wp_talks_meta_scripts() {
?>
	<script>
		jQuery(document).ready( function () {
			var postType = jQuery('#post_type').val();
			if (postType == 'wp_talks') {

				jQuery('#wp_talks_audio_url_select').click( function () {
					var postID = jQuery('#post_ID').val();
					window.send_to_editor = function (html) {
						audiourl = jQuery(html).attr('href');
						jQuery('#wp_talks_audio_url').val(audiourl);
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

function wp_talks_save_meta_data( $postId )
{
	
	if ( isset($_POST['post_type']) && 'wp_talks' ==  $_POST['post_type'] ) {    
		if (  !current_user_can( 'edit_page', $postId ))    
			return  $postId;    
	}

	// Check if our nonce is set.
	if ( ! isset( $_POST['wp_talks_meta_box_nonce'] ) ) {
		return;
	}

	// Verify that the nonce is valid.
	if ( ! wp_verify_nonce( $_POST['wp_talks_meta_box_nonce'], 'wp_talks_meta_box' ) ) {
		return;
	}

	/* No POST data for custom meta during auto save, so exit to prevent deleting the values */
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
    	return $postId;
	}
	
	$data = $_POST['wp_talks_audio_url'];
		
	// Should validate URL in here - note: can be full URL or partial path!
	
	if (get_post_meta( $postId, '_wp_talks_audio_url') == "")
	{
		add_post_meta( $postId, '_wp_talks_audio_url', $data, true);
	} elseif ( $data != get_post_meta( $postId, '_wp_talks_audio_url' ) ) {
		update_post_meta( $postId, '_wp_talks_audio_url', $data );
	} elseif ( $data == "" ) {
		delete_post_meta( $postId, '_wp_talks_audio_url', get_post_meta( $postId, '_wp_talks_audio_url'	, true) );
	}

	if (isset($_POST['wp_talks_series'])) {
		$series = $_POST['wp_talks_series'];
		if (! is_array($series)) {
			$series = array();
		}

		delete_post_meta($postId, '_wp_talks_series');
		if (!empty($series)) {
			foreach($series as $this_series_id) {
				add_post_meta( $postId, '_wp_talks_series', $this_series_id, false);
			}
		}
	}
}

function wp_talks_create_meta_box() 
{
	if (function_exists('add_meta_box')) {
		add_meta_box('talks-meta', "Audio File Location", 'wp_talks_print_meta_box', 'wp_talks', 'normal', 'high', '');
	}
}

add_action('admin_menu',  'wp_talks_create_meta_box');
add_action('admin_head', 'wp_talks_meta_scripts');
add_action('save_post',  'wp_talks_save_meta_data');

/* This function checks for the existence of the Audio Player plugin and
 * adds a notice if the player does not exist.  Should be attached to the
 * 'admin_notices' action.
 */
function wp_talks_check_audio_plugin () {
	global $wp_version;
	$version_bits = explode('.', $wp_version);
	// If we're greater than v3.6 then we have audio shortcode and mediaelement.js built in!
	if (! ($version_bits[0] >= 4 || ($version_bits[0] == 3 && $version_bits[1] >= 6))) {
		if ( ! function_exists('insert_audio_player') ) {
?>
			<div id="message" class="error">
				<p>The WP Talks plugin can't find the Audio Player Plugin. Audio Player needs to be installed and activated for WP Talks to work. Try <a href="<?php bloginfo( 'wpurl'); ?>/wp-admin/plugin-install.php?tab=search&type=term&s=audio+player">this plugin search</a></p>
			</div>
<?php 
		}
	}
}

add_action('admin_notices', 'wp_talks_check_audio_plugin');

function oikos_talks_list_series_for_talk($talk_id) {
	$series = get_post_meta($talk_id, '_wp_talks_series', false);
	$series_names = array();
	$result = "";
	if (is_array($series) && !empty($series)) {
		foreach ($series as $this_series_id) {
			$series_names[] = get_the_title($this_series_id);
		}
	}
	if (!empty($series_names)) {
		$result = implode(' ', $series_names);
	}
	return $result;
}

include_once('lib/class_oikos_custom_columns.php');

OikosCustomColumns::add_column('wp_talks', 'wp_talk_series', 'Series',
	function () {
		global $post;
		$post_id = $post->ID;
		echo oikos_talks_list_series_for_talk($post_id);
	}
);
