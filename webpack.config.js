const defaultConfig = require('@wordpress/scripts/config/webpack.config');
module.exports = {
	...defaultConfig,
	entry: {
		'subsites-query/subsites-query':
			'./src/block-editor/variations/subsites-query',
		'shadow-related-query/shadow-related-query':
			'./src/block-editor/variations/shadow-related-query',
	},
};
