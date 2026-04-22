// Custom webpack config: admin apps -> build/, blocks -> blocks/NAME/build/

const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const path = require( 'path' );
const fs = require( 'fs' );

function getBlockEntries() {
	const blocksDir = path.resolve( __dirname, 'blocks' );
	if ( ! fs.existsSync( blocksDir ) ) {
		return {};
	}
	const entries = {};
	const dirs = fs.readdirSync( blocksDir, { withFileTypes: true } );
	for ( const dir of dirs ) {
		if ( ! dir.isDirectory() ) {
			continue;
		}
		const blockDir = path.join( blocksDir, dir.name );
		const sources = [ 'edit.tsx', 'edit.ts', 'view.ts', 'view.tsx', 'index.tsx', 'index.ts' ];
		for ( const src of sources ) {
			const srcPath = path.join( blockDir, src );
			if ( ! fs.existsSync( srcPath ) ) {
				continue;
			}
			const outName = src.replace( /\.(tsx?|jsx?)$/, '' );
			entries[ `${ dir.name }/build/${ outName }` ] = srcPath;
		}
	}
	return entries;
}

module.exports = [
	{
		...defaultConfig,
		name: 'admin',
		entry: {
			'admin-settings': path.resolve(
				__dirname,
				'admin-src/settings/index.tsx'
			),
			'admin-setup-wizard': path.resolve(
				__dirname,
				'admin-src/setup-wizard/index.tsx'
			),
			'admin-docs': path.resolve(
				__dirname,
				'admin-src/documentation/index.tsx'
			),
			'admin-variations': path.resolve(
				__dirname,
				'admin-src/variations/index.ts'
			),
		},
		output: {
			...defaultConfig.output,
			path: path.resolve( __dirname, 'build' ),
		},
	},
	{
		...defaultConfig,
		name: 'blocks',
		entry: getBlockEntries(),
		output: {
			...defaultConfig.output,
			path: path.resolve( __dirname, 'blocks' ),
			filename: '[name].js',
			clean: false,
		},
	},
];
