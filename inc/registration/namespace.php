<?php
/**
 * Figuren_Theater Production_Subsites.
 *
 * @package figuren-theater/theater-production-subsites
 */

namespace Figuren_Theater\Production_Subsites\Registration;

use Figuren_Theater\Production_Subsites;
use WP_Post;

use function apply_filters;

/**
 * Start the engines.
 *
 * @return void
 */
function bootstrap(): void {

	add_action( 'init', __NAMESPACE__ . '\\register_sub_post_types', 11 ); // Priority of '10' is NOT working, needs to be 10+. Propably because 'ft_production' PT is registered that late, at the moment.
}


/**
 * Get the slug of that post_type that is or should be used for theater-productions.
 *
 * @return string
 */
function get_production_post_type(): string {
	/**
	 * Use the 'production-post-type' of the 'Theater' WordPress plugin as a default.
	 *
	 * @todo This filter is documented at ...
	 */
	return (string) apply_filters(
		'wpt_production-posttype',
		'wp_theatre_prod'
	);
}


/**
 * Get the slug of that taxonomy,
 * that is or should be used for shadowing the theater-productions post_type.
 * 
 * DANGER: THIS IS PSEUDOCODE,
 *         the taxonomy isn't registered anywhere (yet).
 *
 * @return string
 */
function get_production_shadow_taxonomy(): string {
	return (string) apply_filters(
		'wpt_production-shadow-taxonomy',
		'wp_theatre_prod_shadow'
	);
}


/**
 * Get backend-only editor assets.
 *
 * @return string[]
 */
function get_editor_assets(): array {
	return [
		'shadow-related-query',
		'subsites-query',
	];
}












/**
 * Check the given parent-post type's support 
 * for our 'sub-post_types' feature.
 *
 * @param  string $post_type The parent-post type being checked.
 *
 * @return bool
 */
function is_post_type_allowed( string $post_type ): bool {
	return \post_type_supports(
		$post_type,
		Production_Subsites\PT_SUPPORT
	);
}


/**
 * Check the given sub-post_type
 * if it supports our 'sub-post_types' feature.
 *
 * @param  string $post_type The sub-post type being checked.
 *
 * @return bool
 */
function is_subtype_allowed( string $post_type ): bool {
	return is_post_type_allowed( 
		get_parent_type_slug( $post_type )
	);
}


/**
 * Check the given parent-post type's support 
 * for our 'sub-post_types' feature 
 * AND whether the current user is allowed to edit those.
 *
 * @param  WP_Post $post The parent-post being checked.
 *
 * @return bool
 */
function is_post_allowed( WP_Post $post ): bool {

	if ( ! is_post_type_allowed( $post->post_type ) ) {
		return false;     
	}

	if ( ! \current_user_can( 'edit_post', $post->ID ) ) {
		return false;     
	}

	return true;
}

/**
 * Registers multiple (almost) identical post_types.
 *
 * @return void
 */
function register_sub_post_types(): void {
	\array_map(
		__NAMESPACE__ . '\\register_sub_post_type',
		\get_post_types_by_support( Production_Subsites\PT_SUPPORT )
	);
}


/**
 * Registers a new sub-post type based on a given parent-post type.
 *
 * @param  string $parent_post_type  The slug of the post_type that is getting parent.
 *
 * @return void
 */
function register_sub_post_type( string $parent_post_type ): void {

	switch ( \function_exists( 'register_extended_post_type' ) ) {

		case true:
			\register_extended_post_type(
				get_sub_type_slug( $parent_post_type ),
				\array_merge(
					get_sub_type_args( $parent_post_type ),
					get_sub_type_args_extended()
				),
				[]
			);
			break;
		
		default:
			\register_post_type(
				get_sub_type_slug( $parent_post_type ),
				get_sub_type_args( $parent_post_type ) // @phpstan-ignore-line
			);
			break;
	}
}


/**
 * Returns the sub-post_type slug for a given parent-post_type slug.
 *
 * @param  string $parent_post_type_slug  Slug of a parent-post_type.
 *
 * @return string                         Slug of a sub-post_type.
 */
function get_sub_type_slug( string $parent_post_type_slug ): string {
	return $parent_post_type_slug . Production_Subsites\SUB_SUFFIX;
}


/**
 * Returns the parent-post_type slug for a given sub-post_type slug.
 *
 * @param  string $sub_post_type_slug   Slug of a sub-post_type.
 *
 * @return string                       Slug of a parent-post_type.
 */
function get_parent_type_slug( string $sub_post_type_slug ): string {
	return \substr( 
		$sub_post_type_slug,
		0,
		-\mb_strlen( Production_Subsites\SUB_SUFFIX )
	);
}


/**
 * Returns a list of arguments prepared for register_post_type() 
 * to set up a sub-post_type for a given parent-post_type.
 *
 * @param  string $parent_post_type   Slug of a parent-post_type.
 *
 * @return array<string, array<int|string, bool|string>|bool|string>    List of register_post_type() compatible arguments.
 */
function get_sub_type_args( string $parent_post_type ): array {

	$ppo = \get_post_type_object( $parent_post_type );
	if ( null === $ppo ) {
		return [];
	}

	return array(
		'capability_type'   => $ppo->capability_type,
		'supports'          => array(
			'title',
			'editor',
			'thumbnail',
			'excerpt',
			'custom-fields',
			'revisions',
		),
		'public'            => true, // 'TRUE' enables editable post_name, called 'permalink|slug'.

		'show_ui'           => true,
		'show_in_menu'      => 'edit.php?post_type=' . $parent_post_type, // This tipp saved a whole 'add_submenu_page()' function call; https://developer.wordpress.org/reference/functions/register_post_type/#comment-5056.
		'show_in_nav_menus' => true,
		'show_in_admin_bar' => false,
		'show_in_rest'      => true, // This in combination with  'supports' => array('editor') enables the Gutenberg editor.
		'hierarchical'      => true, // Important for rewriting to work with 'parent' PT.
		'description'       => '',

		'rewrite'           => [
			'slug'       => get_sub_type_slug( $ppo->name ), 
			'with_front' => true,       // Defaults to true.
			'feeds'      => false,      // Defaults to 'has_archive'.
			'pages'      => false,      // Defaults to true.
			// 'ep_mask' => 'EP_NONE',  // Defaults to EP_PERMALINK.

		],

		'has_archive'       => false,
		'can_export'        => true,


		/**
		 * Localised Labels
		 * 
		 * ExtendedCPTs generates the default labels in English for your post type. 
		 * If you need to allow your post type labels to be localized, 
		 * then you must explicitly provide all of the labels (in the labels parameter) 
		 * so the strings can be translated. There is no shortcut for this.
		 *
		 * @source https://github.com/johnbillion/extended-cpts/pull/5#issuecomment-33756474
		 * @see https://github.com/johnbillion/extended-cpts/blob/d6d83bb41eba9a3603929244c71f3f806c2a14d8/src/PostType.php#L152
		 */
		'labels'            => [
			'name'                     => __( 'Subsites', 'theater-production-subsites' ),
			'singular_name'            => __( 'Subsite', 'theater-production-subsites' ),
			'add_new'                  => __( 'Add New', 'theater-production-subsites' ),
			'add_new_item'             => __( 'Add New Subsite', 'theater-production-subsites' ),
			'edit_item'                => __( 'Edit Subsite', 'theater-production-subsites' ),
			'new_item'                 => __( 'New Subsite', 'theater-production-subsites' ),
			'view_item'                => __( 'View Subsite', 'theater-production-subsites' ),
			'view_items'               => __( 'View Subsites', 'theater-production-subsites' ),
			'search_items'             => __( 'Search Subsites', 'theater-production-subsites' ),
			'not_found'                => __( 'No Subsites found.', 'theater-production-subsites' ),
			'not_found_in_trash'       => __( 'No Subsites found in Trash.', 'theater-production-subsites' ),
			'parent_item_colon'        => __( 'Parent Subsites:', 'theater-production-subsites' ),
			'all_items'                => __( 'All Subsites', 'theater-production-subsites' ),
			'archives'                 => __( 'Subsite Archives', 'theater-production-subsites' ),
			'attributes'               => __( 'Subsite Attributes', 'theater-production-subsites' ),
			'insert_into_item'         => __( 'Insert into Subsite', 'theater-production-subsites' ),
			'uploaded_to_this_item'    => __( 'Uploaded to this Subsite', 'theater-production-subsites' ),
			'featured_image'           => __( 'Image', 'theater-production-subsites' ),
			'set_featured_image'       => __( 'Set featured image', 'theater-production-subsites' ),
			'remove_featured_image'    => __( 'Remove featured image', 'theater-production-subsites' ),
			'use_featured_image'       => __( 'Use as featured image', 'theater-production-subsites' ),
			'menu_name'                => __( 'Subsites', 'theater-production-subsites' ),
			'filter_items_list'        => __( 'Filter Subsite list', 'theater-production-subsites' ),
			'filter_by_date'           => __( 'Filter by date', 'theater-production-subsites' ),
			'items_list_navigation'    => __( 'Subsites list navigation', 'theater-production-subsites' ),
			'items_list'               => __( 'Subsites list', 'theater-production-subsites' ),
			'item_published'           => __( 'Subsite published.', 'theater-production-subsites' ),
			'item_published_privately' => __( 'Subsite published privately.', 'theater-production-subsites' ),
			'item_reverted_to_draft'   => __( 'Subsite reverted to draft.', 'theater-production-subsites' ),
			'item_scheduled'           => __( 'Subsite scheduled.', 'theater-production-subsites' ),
			'item_updated'             => __( 'Subsite updated.', 'theater-production-subsites' ),
			'item_link'                => __( 'Subsite Link', 'theater-production-subsites' ),
			'item_link_description'    => __( 'A link to a subsite.', 'theater-production-subsites' ),          
		],

	);
}


/**
 * Returns a list of arguments prepared for register_extended_post_type() 
 * to set up a sub-post_type for a given parent-post_type.
 * 
 * @see https://github.com/johnbillion/extended-cpts/wiki
 *
 * @return array<string, string|bool>
 */
function get_sub_type_args_extended(): array {
	
	return [
		// The "Featured Image" text used in various places
		// in the admin area can be replaced with
		// a more appropriate name for the featured image
		// 'featured_image' => _x( 'Image', 'Featured Image Label', 'theater-production-subsites' ), .

		'enter_title_here'   => __( 'Subsite Title', 'theater-production-subsites' ),

		'quick_edit'         => false,

		// Add the post type to the site's main RSS feed.
		'show_in_feed'       => false,

		// Add the post type to the 'Recently Published' section of the dashboard.
		'dashboard_activity' => true,

		// An entry is added to the "At a Glance"
		// dashboard widget for your post type by default.
		'dashboard_glance'   => false,

	];
}


/**
 * Returns a list of post_type slugs for all registered subsite-post_types.
 *
 * @return string[]   List of sub-post_type-slugs.
 */
function get_sub_types(): array {
	return get_supported_post_types( 'sub' );
}


/**
 * Returns a list of post_type slugs for all parent- and subsite-post_types as well.
 * 
 * @param  string $which   3-way-switch to get 'parent', 'sub' or 'all' supported post_type slugs.
 *
 * @return string[]        List of parent- and sub-post_type-slugs.
 */
function get_supported_post_types( $which = 'all' ): array {

	$parent_slugs = \get_post_types_by_support( Production_Subsites\PT_SUPPORT );

	// Ready to go.
	if ( 'parent' === $which ) {
		return $parent_slugs;
	}

	// Loop over all post_type parent_slugs and add the subsite suffix.
	$sub_slugs = $parent_slugs;
	array_walk(
		$sub_slugs,
		function ( string &$parent_post_type_slug ): string {
			return get_sub_type_slug( $parent_post_type_slug );
		}
	);

	// Ready to go.
	if ( 'sub' === $which ) {
		return $sub_slugs;
	}

	// Return list of all slugs.
	return array_merge(
		$parent_slugs,
		$sub_slugs
	);
}
