<?php

// NOTE: The callback function should echo the output.  Use global $post to get the post that's
// being displayed in the list.
// 
// Example usage:
// 
// OikosCustomColumns::add_column('members', 'country', 'Country',
//   function() {
//     global $post;
//     $member_id = $post->ID;
//     $country_id = get_post_meta($member_id, '_country_id', true);
//     $country_name = get_the_title($country_id);
//     echo $country_name;
//   }
// );
// 
class OikosCustomColumns {
	static function add_column($post_type, $column_name, $column_label, $callback) {

		// Add a filter to add extra columns for a specific post type's edit list
		add_filter("manage_edit-${post_type}_columns",

			// This function takes an array of columns and should return an array of columns.  It should add
			// to the list of columns that's given as an input.  The array keys are column ID strings and the array
			// values are column heading strings.
			function($columns) use ($column_name, $column_label) {
				$columns[$column_name] = $column_label;
				return $columns;
			}
		);
 
		// Add an action to populate the custom columns
		add_action("manage_${post_type}_posts_custom_column",
			// This function is called for all columns.  It takes a string that's a column ID from the $columns
			// array mentioned above.  The function should determine which column is being output and output the
			// content of that column for the current post.  Note the column content should be OUTPUT, not
			// returned.
			function($column) use ($column_name, $callback) {
			    if( $column == $column_name ) {
			    	$callback();
			    }
			}
		);
 
 

	}
}