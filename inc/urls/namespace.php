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

	// Create rewrite rules to help with the hierarchy of non-identical post_types.
	add_action( 'generate_rewrite_rules', __NAMESPACE__ . '\\generate_rewrite_rules' );
	
	// Change permalink to use the same rewrite-base as 'ft_prodution' PT.
	add_filter( 'post_type_link', __NAMESPACE__ . '\\post_type_link', 10, 2 );

	// Filter the main query to return a subsite-post when queried by URL.
	add_action( 'pre_get_posts', __NAMESPACE__ . '\\pre_get_posts' );
}


/**
 * Retrieve rewrite-base of 'ft_production' PT
 *
 * @return  string       the URL part, that indicates our PT, by default it's: 'produktionen'
 */
function get_production_post_type_permastruct(): string {

	global $wp_rewrite;

	// Setup.
	$_prod_pt = Registration\get_production_post_type();

	// Get something like "produktionen/%ft_productions%" 
	// or even changed to "stücke/kinder/%ft_productions%".
	$_prod_pt_permastruct = $wp_rewrite->get_extra_permastruct( $_prod_pt );

	// could be string|false :: https://developer.wordpress.org/reference/classes/wp_rewrite/get_extra_permastruct/#return.
	$_prod_pt_permastruct = ( $_prod_pt_permastruct ) ? $_prod_pt_permastruct : '';

	// remove the singular-struct of the 'ft_production' PT.
	$_prod_pt_permastruct = str_replace( '/%' . $_prod_pt . '%', '', $_prod_pt_permastruct );

	// in case that the 'ft_production' has a changed permastruct
	// from the default, get the used struct.
	return $_prod_pt_permastruct;
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
	$_endpoints_to_exclude = join(
		'|',
		[
			$wp_rewrite->comments_base,
			$wp_rewrite->pagination_base,
			$wp_rewrite->comments_pagination_base,
			'trackback',
			'embed',
		]
	);

	// Exclude EP_PERMALINK endpoints
	// https://regex101.com/r/iIYoHa/1.
	$_url = get_production_post_type_permastruct() . '/((?!' . $wp_rewrite->pagination_base . ').[^/]*)/((?!' . $_endpoints_to_exclude . ').[^/]*)/?$';

	$_match_args = [
		Production_Subsites\PT_SLUG             => '$matches[2]',
		Registration\get_production_post_type() => '$matches[1]',
	];
	
		$_match = add_query_arg( 
			$_match_args, 
			'index.php'
		);

	/**
	 * The rewrite rules that will be added look like:
	 * 
	 * @example 'produktionen/([^/]*)/((?!feed|trackback|...).[^/]*)/?$' => 'index.php?tb_prod_subsite=$matches[2]&ft_production=$matches[1]',
	 */
	$subsite_rules = array(
		$_url => $_match,
	);

	$wp_rewrite->rules = $subsite_rules + $wp_rewrite->rules;
}
 

/**
 * Change permalink to use the same rewrite-base as 'ft_prodution' PT
 * 
 * @see https://developer.wordpress.org/reference/hooks/post_type_link/
 *
 * @param  string  $permalink URL of the current post.
 * @param  WP_Post $post      current Post aka Production-Subsite.
 * 
 * @return string 
 */
function post_type_link( string $permalink, WP_Post $post ): string {
	global $pagenow;

	if ( Production_Subsites\PT_SLUG !== $post->post_type ) {
		return $permalink;
	}
	
	// Disable this filter inside the block-editor
	// to allow the normal 'post_name' input to be accessible
	// otherwise, it would be removed by the existence of this filter.
	if ( 'post.php' === $pagenow || 'post-new.php' === $pagenow ) {
		return $permalink;
	}
	
	return str_replace( 
		Production_Subsites\PT_SLUG,
		get_production_post_type_permastruct(),
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
function pre_get_posts( WP_Query $query ): void {

	// Setup.
	$_prod_pt = Registration\get_production_post_type();
	
	if ( ! $query->is_main_query() ) {
		return;
	}

	if ( ! isset( $query->query_vars['post_type'] ) ) {
		return;
	}
	
	if ( ! isset( $query->query_vars[ Production_Subsites\PT_SLUG ] ) ) {
		return;
	}

	if ( Production_Subsites\PT_SLUG !== $query->query_vars['post_type'] ) {
		return;
	}
	
	if ( ! isset( $query->query_vars[ $_prod_pt ] ) ) {
		return;
	}
	
	/*
	 * @see https://make.wordpress.org/core/2020/06/26/wordpress-5-5-better-fine-grained-control-of-redirect_guess_404_permalink/
	 * @see https://core.trac.wordpress.org/ticket/16557
	 */
	add_filter( 'do_redirect_guess_404_permalink', '__return_false' );

	$production_query = new WP_Query(
		array( 
			'post_name__in'          => [ $query->query_vars[ $_prod_pt ] ],
			'post_type'              => $_prod_pt,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'posts_per_page'         => 1,
		) 
	);

	if ( 0 === count( $production_query->posts ) ) {
		return;
	}

	if ( ! $production_query->post instanceof WP_Post ) {
		return;
	}

	$subsite_query = new WP_Query(
		array( 
			'post_name__in'          => [ $query->query_vars[ Production_Subsites\PT_SLUG ] ],
			'post_type'              => Production_Subsites\PT_SLUG,
			// 'post_parent' // this can be tricky
			'post_parent__in'        => [ $production_query->post->ID ],
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
	$query->query['post_type']      = Production_Subsites\PT_SLUG;
	$query->query_vars['p']         = $subsite_query->post->ID;
	$query->query_vars['post_type'] = Production_Subsites\PT_SLUG;

	unset( $query->query['name'] );
	unset( $query->query['pagename'] );
	unset( $query->query[ Production_Subsites\PT_SLUG ] );
	unset( $query->query[ $_prod_pt ] );

	unset( $query->query_vars['name'] );
	unset( $query->query_vars['pagename'] );
	unset( $query->query_vars[ Production_Subsites\PT_SLUG ] );
	unset( $query->query_vars[ $_prod_pt ] );

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
