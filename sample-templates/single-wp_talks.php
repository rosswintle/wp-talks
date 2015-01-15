<?php
/**
 * The Template for displaying single talks from the wp-talks plugin.
 *
 */

get_header(); ?>

        		<div id="content" class="full-width">

					<?php
					/* Run the loop to output the post. */
					get_template_part( 'loop', 'single' );
					?>

				</div><!-- #content -->
					
<?php get_footer(); ?>
