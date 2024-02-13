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
	
	// Only relevant to our theater context (for now).
	// Block_Loading\bootstrap(); // Should run on init|0 or earlier. 
	Pattern_Loading\bootstrap(); // Should run on init.
	

	/**
	 * Load our addition for the "Theater" WordPress plugin.
	 * 
	 * From the plugin docs:
	 * "Use this [hook] to safely load plugins that depend on Theater."
	 * 
	 * By the design of "Theater" this NEEDS TO BE DONE on or before 'plugins_loaded'!
	 * 
	 * @source https://github.com/slimndap/wp-theatre/blob/70bfc1efff2f1b6e89631820befb5e67cfe4d34c/theater.php#L216
	 */ 
	\add_action(
		'wpt_loaded',
		function (): void {

			\add_post_type_support( 'wp_theatre_prod', PT_SUPPORT );
		}
	);
}
