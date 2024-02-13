<?php
/**
 * Figuren_Theater Production_Subsites.
 *
 * @package figuren-theater/theater-production-subsites
 */

namespace Figuren_Theater\Production_Subsites\Block_Loading;

use Figuren_Theater\Production_Subsites;
use Figuren_Theater\Production_Subsites\Registration;
use function add_action;
use function esc_html;
use function load_plugin_textdomain;
use function plugins_url;
use function wp_add_inline_script;
use function wp_enqueue_script;
use function wp_get_environment_type;
use function wp_json_encode;
use function wp_register_script;
use function wp_set_script_translations;



/**
 * Start the engines.
 *
 * @return void
 */
function bootstrap(): void {
	add_action( 'init', __NAMESPACE__ . '\\load', 1 );
}

/**
 * Load translated strings.
 *
 * @return void
 */
function load(): void {

	\array_map(
		__NAMESPACE__ . '\\register_asset',
		Registration\get_editor_assets()
	);
	
	add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\\enqueue_assets' );
}

/**
 * Register a new script and sets translated strings for the script.
 *
 * @throws \Error If build-files doesn't exist errors out in local environments and writes to error_log otherwise.
 *
 * @param  string $asset Slug of the block to register scripts and translations for.
 *
 * @return void
 */
function register_asset( string $asset ): void {

	$dir = Production_Subsites\DIRECTORY;

	$script_asset_path = "$dir/build/$asset/$asset.asset.php";

	
	if ( ! \file_exists( $script_asset_path ) ) {
		$error_message = "You need to run `npm start` or `npm run build` for the '$asset' block-asset first.";
		if ( \in_array( wp_get_environment_type(), [ 'local', 'development' ], true ) ) {
			throw new \Error( esc_html( $error_message ) );
		} else {
			// Should write to the \error_log( $error_message ); if possible.
			return;
		}
	}

	$index_js     = "build/$asset/$asset.js";
	$script_asset = require $script_asset_path; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable

	wp_register_script(
		"theater-production-subsites--$asset",
		plugins_url( $index_js, "$dir/plugin.php" ),
		$script_asset['dependencies'],
		$script_asset['version'],
		true
	);

	wp_set_script_translations(
		"theater-production-subsites--$asset",
		'theater-production-subsites',
		Production_Subsites\DIRECTORY . '/languages'
	);

	if ( 0 === \did_action( __NAMESPACE__ . '\\provide_script_vars' ) ) {
		wp_add_inline_script(
			"theater-production-subsites--$asset",
			'window.Theater = window.Theater || {};'
			. 'window.Theater.ProductionPosttype = window.Theater.ProductionPosttype || {};'
			. 'window.Theater.ProductionPosttype = ' 
			. wp_json_encode(
				[
					'Slug'           => Registration\get_production_post_type(),
					'ShadowTaxonomy' => Registration\get_production_shadow_taxonomy(),
				] 
			) 
			. ';',
			'before'
		);
			
		\do_action( __NAMESPACE__ . '\\provide_script_vars' );
	}
}

/**
 * Enqueue all scripts.
 *
 * @return void
 */
function enqueue_assets(): void {
	\array_map(
		__NAMESPACE__ . '\\enqueue_asset',
		Registration\get_editor_assets()
	);
}

/**
 * Enqueue a script.
 *
 * @param  string $asset Slug of the block to load the frontend scripts for.
 *
 * @return void
 */
function enqueue_asset( string $asset ): void {
	wp_enqueue_script( "theater-production-subsites--$asset" );
}
