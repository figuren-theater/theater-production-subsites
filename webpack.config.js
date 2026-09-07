const defaultConfig = require('@wordpress/scripts/config/webpack.config');
module.exports = {
	...defaultConfig,
	entry: {
		'subsites-page-list/subsites-page-list':
			'./src/block-editor/variations/subsites-page-list',
	},
};
