/**
 * Registers a new block provided a unique name and an object defining its behavior.
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/#registering-a-block
 */
import { registerBlockVariation } from '@wordpress/blocks';

import { pages } from '@wordpress/icons';

import { select } from '@wordpress/data';

/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * Get stuff to filter block attributes on the fly
 *
 * @see https://github.com/WordPress/gutenberg/issues/10082#issuecomment-642786811
 */
import { createHigherOrderComponent } from '@wordpress/compose';
import { addFilter } from '@wordpress/hooks';

/**
 * Internal dependencies
 */
const PT_PRODUCTION = window.Theater.ProductionPosttype.Slug;
const PT_SUBSITE = PT_PRODUCTION + '_sub';
// const TAX_PRODUCTION_SHADOW = window.Theater.ProductionPosttype.ShadowTaxonomy;

/*
 * New `core/query` block variation.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-variations/
 */
const productionSubsitesQuery = {
	// category:		'theatrebase', // blockvariations can't be added to blockCollections, yet
	name: 'theatrebase/subsites-query',
	title: __('Production Subsites', 'theater-production-subsites'),
	description: __(
		'Shows the subsites of the current production. Used on a production subsite, this block lists all sibling "subsites" under the same parent production.',
		'theater-production-subsites'
	),
	keywords: [
		__('addition', 'theater-production-subsites'),
		__('production', 'theater-production-subsites'),
		__('theater', 'theater-production-subsites'),
	],
	// isDefault: 	true,
	icon: pages, // default: loop
	example: {}, // not working
	attributes: {
		// queryId:		0,
		query: {
			perPage: 16,
			pages: 1,
			offset: 0,
			postType: PT_SUBSITE,
			order: 'asc',
			orderBy: 'title',
			//		author: 	"",
			//      search: 	"",
			//		exclude: 	[], // or pass multiple values in an array, e.g. [ 1, 9098 ]
			//      sticky: 	"exclude",
			inherit: false,
			//		taxQuery:{
			//			ft_production_shadow: [91]
			//		},
			parents: [], // important to be empty, to make the filter work
		},
		displayLayout: {
			type: 'flex', // list | flex
			columns: 4,
		},
		align: 'wide',
		className: 't7b4-subsites-query', // important for isActive callback fn
		customClassName: false,
	},
	innerBlocks: [
		[
			'core/post-template',
			{},
			[
				[
					'core/cover',
					{
						useFeaturedImage: true,
						dimRatio: 20,
						overlayColor: 'primary',
						minHeight: 200,
						minHeightUnit: 'px',
						style: {
							spacing: {
								padding: {
									top: '0px',
								},
							},
						},
					},
					[
						[
							'core/post-title',
							{
								textAlign: 'center',
								level: 3,
								isLink: true,
								style: {
									typography: {
										lineHeight: '1',
									},
								},
								fontSize: 'large',
							},
						],
					],
				],
			],
		],
	],
	// scope: [ 'inserter', 'block', 'transform' ],
	scope: ['inserter'],
	isActive: (blockAttributes) =>
		't7b4-subsites-query' === blockAttributes.className,
};

registerBlockVariation('core/query', productionSubsitesQuery);

const productionSubsitesQueryEngine = createHigherOrderComponent(
	(BlockListBlock) => {
		return (props) => {
			if ('core/query' !== props.name)
				return <BlockListBlock {...props} />;

			if (PT_SUBSITE !== props.attributes.query.postType)
				return <BlockListBlock {...props} />;

			// console.log( props)
			//console.log( props.attributes.query.parents)
			//console.log( 0 !== props.attributes.query.parents.length )

			if (0 !== props.attributes.query.parents.length)
				return <BlockListBlock {...props} />;

			const currentPost = select('core/editor').getCurrentPost();
			if (
				PT_PRODUCTION !== currentPost.type &&
				PT_SUBSITE !== currentPost.type
			)
				return <BlockListBlock {...props} />;

			const currentOrParentProductionId =
				PT_PRODUCTION === currentPost.type
					? currentPost.id
					: currentPost.parent;

			// console.log( currentPost)
			const newquery = props.attributes.query;
			newquery.parents = [currentOrParentProductionId];

			props.setAttributes({ query: newquery });

			return <BlockListBlock {...props} />;
		};
	},
	'productionSubsitesQueryEngine'
);

addFilter(
	'editor.BlockListBlock',
	'theatrebase/production-subsites-query-engine',
	productionSubsitesQueryEngine
);

// const FT_COLOR = '#d20394';
// const shadowed_production = ( current_post.ft_production_shadow[0] ) ? current_post.ft_production_shadow[0] : false;
