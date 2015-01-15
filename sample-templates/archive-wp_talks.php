<?php
/**
 * The template for displaying archives od the "wp-talks" type from the wp-talks plugin.
 *
 */

get_header(); ?>

        		<div id="content" class="full-width">

			<h1 class="page-title">
				Message Archive
				<?php
					if (is_tax('wp_talks_speaker')) {
						$speaker_slug = get_query_var('wp_talks_speaker');
						$speaker = get_term_by( 'slug', $speaker_slug, 'wp_talks_speaker');
						echo ": Talks by " . $speaker->name;
					} else if (is_tax('wp_talks_service')) {
						$service_slug = get_query_var('wp_talks_service');
						$service = get_term_by( 'slug', $service_slug, 'wp_talks_service');
						echo ": Talks in service " . $service->name;						
					} else if (is_tax('wp_talks_topic')) {
						$topic_slug = get_query_var('wp_talks_topic');
						$topic = get_term_by( 'slug', $topic_slug, 'wp_talks_topic');
						echo ": Talks on topic " . $topic->name;												
					}
				?>
			</h1>

<?php
	/* Run the loop for the archives page to output the posts. */
	 get_template_part( 'loop', 'archive' );
?>

				</div><!-- #content -->
					
<?php get_footer(); ?>
