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

	// \array_map(
	// __NAMESPACE__ . '\\Registration\\add_post_type_supports',
	// $post_types
	// );

	// Block_Loading\bootstrap(); // Should run on init|0 or earlier. 
	// Pattern_Loading\bootstrap(); // Should run on init.
	
	// global $wp_rewrite;
	// \do_action( 'qm/info', $wp_rewrite );

	// \do_action( 'qm/info', \get_post_type_object( self::NAME ) );

	// \do_action( 'qm/debug', __NAMESPACE__ . ' is ready!' );

	// \define('QM_SHOW_ALL_HOOKS', true);
	
	Admin_UI\bootstrap();
	// Post_Type\bootstrap();
	Urls\bootstrap();
}

/**
 * Bootstrap module, when enabled.
 *
 * @return void
 */
function bootstrap(): void {

	/**
	 * Automatically load Plugins.
	 *
	 * @example NameSpace\bootstrap();
	 */

	/**
	 * Load 'Best practices'.
	 *
	 * @example NameSpace\bootstrap();
	 */
}
