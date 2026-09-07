<?php
/**
 * Figuren_Theater Production_Subsites.
 *
 * @package figuren-theater/theater-production-subsites
 */

namespace Figuren_Theater\Production_Subsites\Page_List;

use Figuren_Theater\Production_Subsites\Registration;
use WP_Block;
use WP_Block_Supports;
use WP_Post;
use WP_Query;

use function add_filter;
use function esc_attr;
use function esc_url;
use function get_block_wrapper_attributes;
use function get_permalink;
use function get_post;
use function get_posts;
use function get_queried_object_id;
use function get_the_title;
use function wp_kses_post;

/**
 * The className used to identify our core/page-list block variation.
 *
 * @see ../../src/block-editor/variations/subsites-page-list/index.js
 */
const VARIATION_CLASS_NAME = 'wpt-subsites-page-list';

/**
 * Start the engines.
 *
 * @return void
 */
function bootstrap(): void {
	add_filter( 'pre_render_block', __NAMESPACE__ . '\\pre_render', 10, 3 );
}

/**
 * Short-circuits core/page-list's default (pages-only) rendering for
 * block instances carrying our variation's className, replacing it
 * with a cross-post_type sibling/child list.
 *
 * Resolves the target parent post from the enclosing block's context
 * (e.g. the current Query Loop item) or the queried object as a
 * fallback: the context post's own post_parent when set, otherwise
 * the context post itself - so the same block works unmodified both
 * on a parent post (showing its subsites) and on a subsite post
 * (showing its siblings).
 *
 * @see https://developer.wordpress.org/reference/hooks/pre_render_block/
 *
 * @param  string|null          $pre_render   Default null, short-circuits rendering when non-null.
 * @param  array<string, mixed> $parsed_block The block being rendered.
 * @param  WP_Block|null        $parent_block The parent block, when this is a nested block.
 *
 * @return string|null
 */
function pre_render( $pre_render, array $parsed_block, $parent_block ) {
	if ( null !== $pre_render ) {
		return $pre_render;
	}

	if ( 'core/page-list' !== ( $parsed_block['blockName'] ?? '' ) ) {
		return null;
	}

	$attrs      = isset( $parsed_block['attrs'] ) && is_array( $parsed_block['attrs'] ) ? $parsed_block['attrs'] : array();
	$class_name = isset( $attrs['className'] ) && is_string( $attrs['className'] ) ? $attrs['className'] : '';

	if ( false === \strpos( $class_name, VARIATION_CLASS_NAME ) ) {
		return null;
	}

	$context_post_id = get_context_post_id( $parent_block );

	if ( $context_post_id <= 0 ) {
		return '';
	}

	$context_post = get_post( $context_post_id );

	if ( ! $context_post instanceof WP_Post ) {
		return '';
	}

	$parent_id = $context_post->post_parent ? $context_post->post_parent : $context_post->ID;
	$siblings  = get_siblings( $parent_id );
	if ( empty( $siblings ) ) {
		return '';
	}

	return render_list( $siblings, $parsed_block );
}

/**
 * Resolve the post ID that the block is rendered for/next to.
 *
 * @param  WP_Block|null $parent_block The parent block, when this is a nested block.
 *
 * @return int
 */
function get_context_post_id( $parent_block ): int {
	if ( $parent_block instanceof WP_Block && isset( $parent_block->context['postId'] ) ) {
		return (int) $parent_block->context['postId'];
	}

	return (int) get_queried_object_id();
}

/**
 * Query siblings/children of $parent_id, across all post_types that
 * are part of our hierachical-sub-post_type system - the whole point
 * of this block variation being that $parent_id's own post_type and
 * its children's post_type(s) don't have to match.
 *
 * @param  int $parent_id Post ID all returned posts must have as post_parent.
 *
 * @return WP_Post[]
 */
function get_siblings( int $parent_id ): array {
	$post_types = Registration\get_supported_post_types( 'sub' );

	if ( empty( $post_types ) ) {
		return array();
	}

	$siblings = new WP_Query(
		array(
			'post_parent'            => $parent_id,
			'post_type'              => $post_types,
			'posts_per_page'         => -1,
			'orderby'                => 'menu_order title',
			'order'                  => 'ASC',
			'post_status'            => 'publish',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);
	return $siblings->posts;
}

/**
 * Render posts as page-list-compatible markup, so that existing
 * `wp-block-page-list` styling (theme.json, block styles) keeps
 * applying unchanged.
 *
 * @param  WP_Post[]            $posts        Posts to render as list items.
 * @param  array<string, mixed> $parsed_block The block being rendered, used to
 *                                             re-derive its wrapper attributes.
 *
 * @return string
 */
function render_list( array $posts, array $parsed_block ): string {
	$current_id = get_queried_object_id();
	$items      = '';

	foreach ( $posts as $post ) {
		$is_active    = $current_id === $post->ID;
		$css_class    = 'wp-block-pages-list__item' . ( $is_active ? ' current-menu-item' : '' );
		$aria_current = $is_active ? ' aria-current="page"' : '';
		$title        = get_the_title( $post );
		$title        = ( '' !== $title ) ? $title : \__( '(no title)', 'theater-production-subsites' );
		$html         = ( $is_active )
			? '<span class="wp-block-pages-list__item__current">%s</span>'
			: '<a class="wp-block-pages-list__item__link" href="' . esc_url( (string) get_permalink( $post ) ) . '"' . $aria_current . '>%s</a>';

		$items .= '<li class="' . esc_attr( $css_class ) . '">'
			. sprintf( $html, wp_kses_post( $title ) )
			. '</li>';
	}

	return '<ul ' . get_wrapper_attributes( $parsed_block ) . '>' . $items . '</ul>';
}

/**
 * Re-derive core/page-list's own wrapper attributes (align, color,
 * spacing, border...) for $parsed_block.
 *
 * By the time this runs (a `pre_render_block` callback), WordPress
 * hasn't set up the block-supports context for this specific block
 * yet - that normally happens inside `WP_Block::render()`, which we
 * never reach because we short-circuit before it. Temporarily point
 * `WP_Block_Supports::$block_to_render` at $parsed_block ourselves -
 * the exact same mechanism `WP_Block::render()` itself uses - so
 * `get_block_wrapper_attributes()` resolves correctly.
 *
 * @param  array<string, mixed> $parsed_block The block being rendered.
 *
 * @return string
 */
function get_wrapper_attributes( array $parsed_block ): string {
	$previous                           = WP_Block_Supports::$block_to_render;
	WP_Block_Supports::$block_to_render = $parsed_block;

	$wrapper_attributes = get_block_wrapper_attributes();

	WP_Block_Supports::$block_to_render = $previous;

	return $wrapper_attributes;
}
