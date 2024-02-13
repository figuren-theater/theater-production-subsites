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

	load_plugin_textdomain(
		'theater-production-subsites',
		false,
		DIRECTORY . '/languages'
	);

	// Relevant to everybody, 
	// who wants to use a hierachical sub-post_type.
	Registration\bootstrap(); // Runs on 'init':11.

	Admin_UI\bootstrap(); // Runs on 'init':12.
	Urls\bootstrap(); // Runs on 'init':1.


	// PSEUDOCODE !

	// \add_action('wpt_init', function () : void {

		// \add_post_type_support( 'wpt_production', PT_SUPPORT );
		\add_post_type_support( 'ft_production', PT_SUPPORT );
		
		// Only relevant to our theater context for now.
		// Block_Loading\bootstrap(); // Should run on init|0 or earlier. 
		Pattern_Loading\bootstrap(); // Should run on init.
	
	// });
}
