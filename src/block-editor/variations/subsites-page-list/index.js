/**
 * WordPress dependencies
 */
import { registerBlockVariation } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import { list } from '@wordpress/icons';
import { createHigherOrderComponent } from '@wordpress/compose';
import { addFilter } from '@wordpress/hooks';
import { useBlockProps } from '@wordpress/block-editor';
import ServerSideRender from '@wordpress/server-side-render';

/**
 * className used to identify this variation.
 *
 * Keep in sync with inc/page-list/namespace.php's VARIATION_CLASS_NAME.
 */
const VARIATION_CLASS_NAME = 'wpt-subsites-page-list';

/**
 * New `core/page-list` block variation.
 *
 * Unlike a plain Page List, this automatically lists all posts that
 * have the current post - or the current post's parent - set as
 * their post_parent, regardless of whether those posts share the
 * current post's post_type. All the actual list-building happens
 * server-side (see inc/page-list/namespace.php); this variation only
 * marks the block instance so the server knows to take over.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-variations/
 */
const subsitesPageList = {
	name: 'theatrebase/subsites-page-list',
	title: __('Production Subsites', 'theater-production-subsites'),
	description: __(
		'Lists the subsites of a production, or - placed on a subsite itself - its sibling subsites. Works across mixed post types.',
		'theater-production-subsites'
	),
	keywords: [
		__('production', 'theater-production-subsites'),
		__('subsite', 'theater-production-subsites'),
		__('theater', 'theater-production-subsites'),
	],
	icon: list,
	attributes: {
		parentPageID: 0,
		className: VARIATION_CLASS_NAME,
	},
	scope: ['inserter'],
	isActive: (blockAttributes) =>
		VARIATION_CLASS_NAME === blockAttributes.className,
};

registerBlockVariation('core/page-list', subsitesPageList);

/**
 * Swaps the editor preview for our variation with a server-side
 * render, since core/page-list's own edit.js only ever fetches
 * `page` post type entities and can't reflect our cross-post_type
 * result set.
 */
const subsitesPageListPreview = createHigherOrderComponent(
	(BlockEdit) => (props) => {
		if (
			'core/page-list' !== props.name ||
			VARIATION_CLASS_NAME !== props.attributes.className
		) {
			return <BlockEdit {...props} />;
		}

		const blockProps = useBlockProps();

		return (
			<div {...blockProps}>
				<ServerSideRender
					block="core/page-list"
					attributes={props.attributes}
				/>
			</div>
		);
	},
	'subsitesPageListPreview'
);

addFilter(
	'editor.BlockEdit',
	'theatrebase/subsites-page-list-preview',
	subsitesPageListPreview
);
