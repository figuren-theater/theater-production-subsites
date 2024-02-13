<?php
/**
 * Figuren_Theater Production_Subsites.
 *
 * @package figuren-theater/theater-production-subsites
 */

namespace Figuren_Theater\Production_Subsites\Admin_UI;

use Figuren_Theater\Production_Subsites;
use Figuren_Theater\Production_Subsites\Registration;
use WP_Post;
use function __;
use function add_action;
use function add_submenu_page;
use function add_query_arg;
use function admin_url;
use function current_user_can;
use function do_action;
use function get_post;
use function get_post_type;
use function is_network_admin;
use function is_user_admin;
use function sanitize_key;
use function wp_insert_post;
use function wp_nonce_url;
use function wp_safe_redirect;
use function wp_verify_nonce;

const ACTION = Production_Subsites\PT_SLUG . '_as_draft';
const NONCE  = ACTION . '_nonce';

/**
 * Start the engines.
 *
 * @return void
 */
function bootstrap(): void {

	add_action( 'init', __NAMESPACE__ . '\\load_plugin' );          
}


/**
 * Conditionally load the plugin itself and its modifications.
 *
 * @return void
 */
function load_plugin(): void {

	// Do only load in "normal" admin view
	// and public views.
	// Not for:
	// - network-admin views
	// - user-admin views.
	if ( is_network_admin() || is_user_admin() ) {
		return;
	}

		add_action( 'admin_menu', __NAMESPACE__ . '\\posttype_as_posttype_submenu' );
	
	// Handle the "New Production-Subsite" Action.
	add_action( 'admin_action_' . ACTION, __NAMESPACE__ . '\\admin_action_subsite_as_draft' );
	
	// Add "New Production-Subsite" link to the quickedit row-actions.
	add_filter( 'page_row_actions', __NAMESPACE__ . '\\row_actions', 10, 2 );

	// Add "New Subsite" link to the "+ New" menu of the Admin_Bar.
	add_action( 'wp_before_admin_bar_render', __NAMESPACE__ . '\\admin_bar_render' );

	// Remove "Add New" Button from Admin List View.
	add_action( 'admin_head-edit.php', __NAMESPACE__ . '\\admin_head' );
}


/**
 * Add Submenu
 *
 * @see https://developer.wordpress.org/reference/functions/add_submenu_page/
 * @see https://github.com/WordPress/wordpress-develop/blob/5.9/src/wp-admin/includes/plugin.php#L1375-L1465
 * 
 * @return void
 */
function posttype_as_posttype_submenu(): void {
	add_submenu_page(
		'edit.php?post_type=' . Registration\get_production_post_type(),
		__( 'Show All Production-Subsites', 'theater-production-subsites' ),
		__( 'All Production-Subsites', 'theater-production-subsites' ),
		'edit_posts',
		'edit.php?post_type=' . Production_Subsites\PT_SLUG,
		null, // @phpstan-ignore-line
		2
	);
}


/**
 * Handles the "New Production-Subsite" Action
 * and creates a new "Production-Subsite" as draft.
 * 
 * Automatically sets post_parent based on 
 * the requesting production-post ID.
 * 
 * On Success, user is redirected to the edit screen.
 *
 * The FUNCTIONNAME is important as it is part of the 
 * called admin_{hook}. Be carefull on change!
 *
 * @return void
 */
function admin_action_subsite_as_draft(): void {

	if ( ! ( isset( $_GET['post'] ) || isset( $_POST['post'] ) || ( isset( $_REQUEST['action'] ) && ACTION === $_REQUEST['action'] ) ) ) {

		do_action( 'qm/error', 'Production-Subsite creation failed because there was no production ID.' );
		return;
	}
	
	
	// Nonce verification.
	if ( ! isset( $_GET[ NONCE ] ) || ! \is_string( $_GET[ NONCE ] ) || false === wp_verify_nonce( sanitize_key( $_GET[ NONCE ] ), ACTION ) ) {
		return;
	}
	 
	// Get the original post id.
	$post_id = ( isset( $_GET['post'] ) ? absint( $_GET['post'] ) : absint( $_POST['post'] ) );
	
	// And all the original post data then.
	$post = get_post( $post_id );

	if ( ! $post instanceof WP_Post ) {
		do_action(
			'qm/error',
			'Production-Subsite creation failed, could not find original production with ID: {post_id}',
			[
				'post_id' => $post_id,
			] 
		);
		return;
	}

	// If post data exists, 
	// create the 'prod_subsite' post.
	// Note: post_title and post_content are required.
	$args = [
		// 'post_author' // Defaults to the current user ID.
		
		// A pre-filled title prevents the pattern-modal to trigger.
		'post_title'     => '', // This is required.
		'post_content'   => ' ', // This is required.

		'post_status'    => 'draft',
		'post_parent'    => $post_id,
		'post_type'      => Production_Subsites\PT_SLUG,

		'comment_status' => 'closed',
		'ping_status'    => 'closed',
	];

		$new_post_id = wp_insert_post( $args );

		$_wp_redirect_url_args = [
			'action' => 'edit',
			'post'   => $new_post_id,
		];
	
		// Build a URL like this: wp-admin/post.php?action=edit&post=123 .
		$_wp_redirect_url = add_query_arg( 
			$_wp_redirect_url_args, 
			admin_url( 'post.php' )
		);

	
	// Finally, redirect to the edit post screen for the new draft.
	wp_safe_redirect( $_wp_redirect_url );
	exit;
}


/**
 * Add a "New Production-Subsite" Button
 * to the action list next to 'Quickedit'.
 *
 * Filters the array of row action links on the Pages list table.
 *
 * The filter is evaluated only for hierarchical post types.
 *
 * @see     https://developer.wordpress.org/reference/hooks/page_row_actions/
 * @see     https://core.trac.wordpress.org/browser/tags/5.9/src/wp-admin/includes/class-wp-posts-list-table.php#L1521
 *
 * @param  array<string, string> $actions An array of row action links. Defaults are
 *                                        'Edit', 'Quick Edit', 'Restore', 'Trash',
 *                                        'Delete Permanently', 'Preview', and 'View'.
 * @param  WP_Post               $post    The post object.
 * 
 * @return array<string, string>          List of Actions, now available to this PT on the edit.php
 */
function row_actions( array $actions, WP_Post $post ): array {
	
	if ( ! current_user_can( 'edit_posts' ) ) {
		return $actions;
	}        

	if ( Registration\get_production_post_type() !== get_post_type( $post ) ) {
		return $actions;
	}        
		
		$actions[ ACTION ] = sprintf(
			'<a href="%1$s" title="%2$s">%3$s</a>',
			get_add_new_url( $post ),
			__( 'New Production-Subsite', 'theater-production-subsites' ),
			__( 'New Production-Subsite', 'theater-production-subsites' ),
		);

	return $actions;
}


/**
 * Get an nonced Admin-URL to create a new 
 * "Production Subsite" based on a Production-post-ID
 *
 * @param   WP_Post $post This should be a "Production" post.
 * 
 * @return  string        Admin-URL 
 */
function get_add_new_url( WP_Post $post ): string {

		$_wp_action_url_args = [
			'action' => ACTION,
			'post'   => $post->ID,
		];
	
		$_wp_action_url = add_query_arg( 
			$_wp_action_url_args, 
			admin_url( 'admin.php' )
		);

		$_wp_nonce_url = wp_nonce_url(
			$_wp_action_url, 
			ACTION,
			NONCE
		);

	return $_wp_nonce_url;
}


/**
 * This adds a "New Subsite" Link to the "+ New" menu of the Admin_Bar
 * if the currently viewed URL is a singular production.
 * 
 * The wp_before_admin_bar_render action allows developers 
 * to modify the $wp_admin_bar object 
 * before it is used to render the Toolbar to the screen.
 *
 * Please note that you must declare the $wp_admin_bar global object, 
 * as this hook is primarily intended to give you direct access 
 * to this object before it is rendered to the screen.
 *
 * @see     https://developer.wordpress.org/reference/hooks/wp_before_admin_bar_render/
 *
 * @return void
 */
function admin_bar_render(): void {
	global $wp_admin_bar, $post;

	if ( ! is_a( $post, 'WP_Post' ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_posts' ) ) {
		return;     
	}

	if ( Registration\get_production_post_type() !== get_post_type( $post ) ) {
		return;
	}

	$wp_admin_bar->add_menu(
		array(
			'parent' => 'new-content',
			'id'     => 'new_' . Production_Subsites\PT_SLUG,
			'title'  => __( 'Production-Subsite', 'theater-production-subsites' ),
			'href'   => get_add_new_url( $post ),
		) 
	);
}


/**
 * Remove "Add New" Button from Admin List View
 *
 * @see     /wp-admin/edit.php#L408
 * @see     https://developer.wordpress.org/reference/hooks/admin_head-hook_suffix/
 * 
 * @return void
 */
function admin_head(): void {
	global $typenow;

	if ( Production_Subsites\PT_SLUG !== $typenow ) {
		return;
	}

	echo '<style type="text/css">'
		. 'a.page-title-action { display: none !important; }'
		. '</style>';
}
