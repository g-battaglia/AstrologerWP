/**
 * Custom webpack configuration extending @wordpress/scripts.
 *
 * Adds block entry points discovered from block.json files.
 *
 * @package Astrologer\Api
 */

const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const path = require( 'path' );

module.exports = {
	...defaultConfig,
	entry: {
		'spike-birth-form-edit': path.resolve(
			__dirname,
			'blocks/spike-birth-form/edit.tsx'
		),
		'spike-birth-form-view': path.resolve(
			__dirname,
			'blocks/spike-birth-form/view.ts'
		),
	},
	output: {
		...defaultConfig.output,
		path: path.resolve( __dirname, 'blocks/spike-birth-form/build' ),
		filename: '[name].js',
	},
};
