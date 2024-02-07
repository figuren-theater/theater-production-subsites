import assign from 'lodash.assign';

import { select, useSelect } from '@wordpress/data';

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
const PT_SUBSITE = ;
const TAX_PRODUCTION_SHADOW = window.Theater.ProductionPosttype.ShadowTaxonomy;

const productionShadowSubsiteRelatedQueryEngine = createHigherOrderComponent(
	(BlockListBlock) => {
		return (props) => {
			if (
				'wpt-production-shadow-related-query' !==
				props.attributes.className
			)
				return <BlockListBlock {...props} />;

			if ('core/query' !== props.name)
				return <BlockListBlock {...props} />;

			// console.log(props);
			// console.log(props.attributes.query.taxQuery.ft_production_shadow);
			// console.log(props.attributes.query.taxQuery[TAX_PRODUCTION_SHADOW]);
			// console.log(props.attributes.query.taxQuery[TAX_PRODUCTION_SHADOW][0]);

			// VARIANT 1 // run only one time
			if (
				// 'undefined' !== props.attributes.query.taxQuery.ft_production_shadow
				// &&
				0 !== props.attributes.query.taxQuery[TAX_PRODUCTION_SHADOW][0]
			)
				// VARIANT 2 // run everytime and update previous block
				// if ( 1 !== props.attributes.query.taxQuery.length )
				return <BlockListBlock {...props} />;

			const currentPost = select('core/editor').getCurrentPost();
			// console.log(currentPost);
			// go on if it's a 'production' or if current post can have 'production_shadow' terms
			// otherwise exit
			if (
				PT_PRODUCTION !== currentPost.type 
				&&
				PT_SUBSITE !== currentPost.type
				&&
				!currentPost.TAX_PRODUCTION_SHADOW
			)
				return <BlockListBlock {...props} />;

			// empty default,
			// like in the block-variation/template
			let shadowedProductions = [];

			if (PT_PRODUCTION === currentPost.type) {
				shadowedProductions = [
					currentPost.meta.shadow_ft_production_shadow_term_id,
				];
				// console.log(shadowedProductions);
			}

			else if (PT_SUBSITE === currentPost.type) 
			{
				const getShadowedProductions = (  ) => {
					 let parentProduction = select('core').getEntityRecord( 'postType', PT_PRODUCTION, currentPost.parent ) 
		
					if ( 'undefined' !== typeof parentProduction && 0 !== parentProduction.meta.length)
					{
						shadowedProductions = [ parentProduction.meta.shadow_ft_production_shadow_term_id ];
					}
					return;
				};
				getShadowedProductions(  );
			}			
			
			else {
				/**
				 * HOly holy holy
				 *
				 * @param {Function} select Curreent posts terms of production-shadow taxonomy.
				 * @return  Array           List of term-IDs
				 */
				shadowedProductions = useSelect((select) => {
					const { getEditedPostAttribute } = select('core/editor');
					return getEditedPostAttribute(TAX_PRODUCTION_SHADOW);
				}, []);
			}

			// still using the defaults
			if (0 === shadowedProductions.length)
				return <BlockListBlock {...props} />;

			// Use Lodash's assign to gracefully handle if attributes are undefined
			// props.attributes.query = assign( props.attributes.query, {
			assign(props.attributes.query, {
				exclude: [currentPost.id],
				taxQuery: {
					[TAX_PRODUCTION_SHADOW]: shadowedProductions,
				},
			});

			return <BlockListBlock {...props} />;
		};
	},
	'productionShadowSubsiteRelatedQueryEngine'
);

addFilter(
	'editor.BlockListBlock',
	'wpt/production-shadow-subsite-related-query',
	productionShadowSubsiteRelatedQueryEngine,
	15
);

// const FT_COLOR = '#d20394';

// 1.
// EDITED_POST = wp.data.select('core/editor').getCurrentPost()
// VARIANT 1.1
// is a 'ft_production'

// VARIANT 1.2.
// is not a 'ft_production', but allows connection to 'ft_production_shadow' taxononmy

// 2.
// PRODUCTION_SHADOW = wp.data.select('core').getEntityRecord('taxonomy','ft_production_shadow',91)

// VARIANT 2.1
// get other posts of different type than EDITED_POST, that belong to the same PRODUCTION_SHADOW taxonomy

// 3.
// PRODUCTION = PRODUCTION_SHADOW.meta.shadow_ft_production_shadow_post_id

// VARIANT 3
// get PRODUCTION
