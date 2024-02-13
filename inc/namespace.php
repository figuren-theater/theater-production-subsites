<?php
/**
 * Figuren_Theater Production_Subsites.
 *
 * @package figuren-theater/theater-production-subsites
 */

namespace Figuren_Theater\Production_Subsites;

/**
 * Register module.
 *
 * @return void
 */
function register(): void {

	$post_types = Registration\get_post_types();

	if ( empty( $post_types ) ) {
		return;
	}

	\array_map(
		__NAMESPACE__ . '\\Registration\\add_post_type_supports',
		$post_types
	);

	// Block_Loading\bootstrap(); // Should run on init|0 or earlier. 
	Pattern_Loading\bootstrap(); // Should run on init.
	
	Admin_UI\bootstrap(); // Should run on init.
	// Post_Type\bootstrap();// Should run on .... .
	Urls\bootstrap(); // Should run on init.
}
