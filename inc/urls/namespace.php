<?php
/**
 * Figuren_Theater Production_Subsites.
 *
 * @package figuren-theater/theater-production-subsites
 */

namespace Figuren_Theater\Production_Subsites\Urls;

use Figuren_Theater\Production_Subsites;
use Figuren_Theater\Production_Subsites\Registration;
use WP_Post;
use WP_Query;
use WP_Rewrite;
use function add_action;
use function add_filter;
use function add_query_arg;
use function is_network_admin;
use function is_user_admin;


/**
 * Start the engines.
 *
 * @return void
 */
function bootstrap(): void {

	add_action( 'init', __NAMESPACE__ . '\\load_plugin', 1 ); // Important to run before 10, to have especially the filter available within wp-admin.
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

	// Create rewrite rules to help with the hierarchy of non-identical post_types.
	add_action( 'generate_rewrite_rules', __NAMESPACE__ . '\\generate_rewrite_rules' );
	
	// Change permalink to use the same rewrite-base as 'ft_prodution' PT.
	add_filter( 'post_type_link', __NAMESPACE__ . '\\post_type_link', 10, 2 );

	// Filter the main query to return a subsite-post when queried by URL.
	add_action( 'pre_get_posts', __NAMESPACE__ . '\\pre_get_posts' );
}


/**
 * Retrieve rewrite-base of 'parent' PT
 * 
 * @param   string $post_type Slug of a sub-post_type.
 *
 * @return  string            URL part, that indicates our PT, by default it defaults to the rewrite_base of the 'parent' PT.
 */
function get_parent_post_type_permastruct( string $post_type ): string {

	global $wp_rewrite;

	$parent = Registration\get_parent_type_slug( $post_type );

	// Get something like "produktionen/%ft_productions%" 
	// or even changed to "stücke/kinder/%ft_productions%".
	$permastruct = $wp_rewrite->get_extra_permastruct( $parent );

	// could be string|false :: https://developer.wordpress.org/reference/classes/wp_rewrite/get_extra_permastruct/#return.
	$permastruct = ( $permastruct ) ? $permastruct : '';

	// remove the singular-struct of the 'parent' PT.
	$permastruct = str_replace( 
		'/%' . $parent . '%',
		'',
		$permastruct
	);

	// in case that the 'parent' has a changed permastruct
	// from the default, get the used struct.
	return $permastruct;
}


/**
 * Create rewrite rules to help with the hierarchy of non-identical post_types.
 * 
 * Fires after the rewrite rules are generated.
 * 
 * @see    https://developer.wordpress.org/reference/hooks/generate_rewrite_rules/
 *
 * @param  WP_Rewrite $wp_rewrite Current WP_Rewrite instance (passed by reference).
 * 
 * @return void
 */
function generate_rewrite_rules( WP_Rewrite $wp_rewrite ): void {
	/* 
	 * Could also be:
	 * 
	 *     $wp_rewrite->feed_base,
	 *     'rdf',
	 *     'rss',
	 *     'rss2',
	 *     'atom',
	 */
	$endpoints_to_exclude = join(
		'|',
		[
			$wp_rewrite->comments_base,
			$wp_rewrite->pagination_base,
			$wp_rewrite->comments_pagination_base,
			'trackback',
			'embed',
		]
	);

	\array_map(
		function ( string $post_type ) use ( &$wp_rewrite, $endpoints_to_exclude ): void {
			generate_subsite_rewrite_rules( 
				$post_type, 
				$wp_rewrite, 
				$endpoints_to_exclude
			);
		},
		\get_post_types_by_support( 
			Production_Subsites\PT_SUPPORT 
		)
	);
}

/**
 * Create rewrite rules to help with the hierarchy of non-identical post_types.
 * 
 * Fires after the rewrite rules are generated.
 * 
 * @see    https://developer.wordpress.org/reference/hooks/generate_rewrite_rules/
 *
 * @param  string     $parent_post_type      Slug of a parent-post_type.
 * @param  WP_Rewrite $wp_rewrite            Current WP_Rewrite instance (passed by reference).
 * @param  string     $endpoints_to_exclude  Existing endpoints, that should NOT match.
 * 
 * @return void
 */
function generate_subsite_rewrite_rules( string $parent_post_type, WP_Rewrite &$wp_rewrite, string $endpoints_to_exclude ): void {

		$sub_post_type = Registration\get_sub_type_slug( $parent_post_type );
	
	// Exclude EP_PERMALINK endpoints
	// https://regex101.com/r/iIYoHa/1.
	$_url = get_parent_post_type_permastruct( $sub_post_type ) . '/((?!' . $wp_rewrite->pagination_base . ').[^/]*)/((?!' . $endpoints_to_exclude . ').[^/]*)/?$';

	$_match_args = [
		$sub_post_type    => '$matches[2]',
		$parent_post_type => '$matches[1]',
	];
	
	$_match = add_query_arg( 
		$_match_args, 
		'index.php'
	);

	/**
	 * The rewrite rules that will be added look like:
	 * 
	 * @example 'parent-post_type-permastruct/([^/]*)/((?!feed|trackback|...).[^/]*)/?$' => 'index.php?parent_post_type_sub=$matches[2]&parent_post_type=$matches[1]',
	 */
	$subsite_rules = array(
		$_url => $_match,
	);

	$wp_rewrite->rules = $subsite_rules + $wp_rewrite->rules;
}
 

/**
 * Change permalink to use the same rewrite-base as 'parent' PT
 * 
 * @see https://developer.wordpress.org/reference/hooks/post_type_link/
 *
 * @param  string  $permalink URL of the current post.
 * @param  WP_Post $post      current Post aka Subsite.
 * 
 * @return string 
 */
function post_type_link( string $permalink, WP_Post $post ): string {
	global $pagenow;

	if ( ! Registration\is_subtype_allowed( $post->post_type ) ) {
		return $permalink;
	}   

	/**
	 * Disable this filter inside the block-editor
	 * to allow the normal 'post_name' input to be accessible
	 * otherwise, it would be removed by the existence of this filter.
	 *
	 * @todo Use manipulated post_type_link in editor UI, too
	 */
	if ( 'post.php' === $pagenow || 'post-new.php' === $pagenow ) {
		return $permalink;
	}
	
	return str_replace( 
		$post->post_type,
		get_parent_post_type_permastruct( $post->post_type ),
		$permalink
	);
}


/**
 * Even that this is called via an action-hook:
 * The function filters the main query 
 * to successfully return a subsite-post when queried by URL.
 *
 * @example domain.tld/produktionen/faust/bilder
 *
 * @see     https://wordpress.stackexchange.com/a/383691
 *
 * @param   WP_Query $query The WordPress Query class.
 * 
 * @return void
 */
function pre_get_posts( WP_Query &$query ): void {
	
	if ( ! $query->is_main_query() ) {
		return;
	}

	if ( ! isset( $query->query_vars['post_type'] ) ) {
		return;
	}

	\array_map(
		function ( string $parent_slug ) use ( &$query ): void {
			manipulate_main_query( 
				$parent_slug,
				Registration\get_sub_type_slug( $parent_slug ),
				$query
			);
		},
		\get_post_types_by_support( 
			Production_Subsites\PT_SUPPORT 
		)
	);
}


/**
 * Manipulate the main query to allow for mixed post_types within one request.
 *
 * @param  string   $parent_slug    Slug of a parent-post_type.
 * @param  string   $subsite_slug   Slug of a sub-post_type.
 * @param  WP_Query $query          The WordPress Query class (passed by reference).
 *
 * @return void
 */
function manipulate_main_query( string $parent_slug, string $subsite_slug, WP_Query &$query ): void {

	if ( ! isset( $query->query_vars[ $subsite_slug ] ) ) {
		return;
	}

	if ( $subsite_slug !== $query->query_vars['post_type'] ) {
		return;
	}
	
	if ( ! isset( $query->query_vars[ $parent_slug ] ) ) {
		return;
	}
	
	/*
	 * @see https://make.wordpress.org/core/2020/06/26/wordpress-5-5-better-fine-grained-control-of-redirect_guess_404_permalink/
	 * @see https://core.trac.wordpress.org/ticket/16557
	 */
	add_filter( 'do_redirect_guess_404_permalink', '__return_false' );

	$parent_query = new WP_Query(
		array( 
			'post_name__in'          => [ $query->query_vars[ $parent_slug ] ],
			'post_type'              => $parent_slug,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'posts_per_page'         => 1,
		) 
	);

	if ( 0 === count( $parent_query->posts ) ) {
		return;
	}

	if ( ! $parent_query->post instanceof WP_Post ) {
		return;
	}

	$subsite_query = new WP_Query(
		array( 
			'post_name__in'          => [ $query->query_vars[ $subsite_slug ] ],
			'post_type'              => $subsite_slug,
			// 'post_parent' // this can be tricky
			'post_parent__in'        => [ $parent_query->post->ID ],
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'posts_per_page'         => 1,
		) 
	);

	if ( 0 === count( $subsite_query->posts ) ) {
		return;
	}

	if ( ! $subsite_query->post instanceof WP_Post ) {
		return;
	}

	$query->query['p']              = $subsite_query->post->ID;
	$query->query['post_type']      = $subsite_slug;
	$query->query_vars['p']         = $subsite_query->post->ID;
	$query->query_vars['post_type'] = $subsite_slug;

	unset( $query->query['name'] );
	unset( $query->query['pagename'] );
	unset( $query->query[ $subsite_slug ] );
	unset( $query->query[ $parent_slug ] );

	unset( $query->query_vars['name'] );
	unset( $query->query_vars['pagename'] );
	unset( $query->query_vars[ $subsite_slug ] );
	unset( $query->query_vars[ $parent_slug ] );

	// Set some defaults,
	// because we are only viewing is_singular().
	$query->query['no_found_rows']               = true;
	$query->query_vars['no_found_rows']          = true;
	$query->query['update_post_meta_cache']      = false;
	$query->query_vars['update_post_meta_cache'] = false;
	$query->query['update_post_term_cache']      = false;
	$query->query_vars['update_post_term_cache'] = false;
	$query->query['posts_per_page']              = 1;
	$query->query_vars['posts_per_page']         = 1;
}
