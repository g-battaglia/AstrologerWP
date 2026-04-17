const defaultConfig = require( '@wordpress/scripts/config/jest-unit.config.js' );

module.exports = {
	...defaultConfig,
	roots: [ '<rootDir>/tests/Jest' ],
	transform: {
		'^.+\\.tsx?$': [
			'ts-jest',
			{
				tsconfig: 'tsconfig.json',
			},
		],
	},
	testMatch: [ '**/tests/Jest/**/*.{test,spec}.{js,jsx,ts,tsx}' ],
};
