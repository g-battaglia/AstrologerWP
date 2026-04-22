/**
 * Block variations: 4 school presets x 7 form blocks = 28 variations.
 *
 * Registers editor-side variations that pre-set the `preset` attribute of
 * Astrologer form blocks to one of the four built-in schools so authors can
 * insert pre-configured form variants directly from the inserter.
 *
 * @package
 */

import { registerBlockVariation } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';

interface SchoolPreset {
	readonly name: string;
	readonly title: string;
	readonly description: string;
	readonly value: string;
}

interface FormBlock {
	readonly blockName: string;
	readonly label: string;
}

const SCHOOLS: readonly SchoolPreset[] = [
	{
		name: 'modern-western',
		title: __( 'Modern Western', 'astrologer-api' ),
		description: __(
			'Placidus houses, tropical zodiac, modern planets.',
			'astrologer-api'
		),
		value: 'modern_western',
	},
	{
		name: 'traditional',
		title: __( 'Traditional', 'astrologer-api' ),
		description: __(
			'Whole-sign houses, classical planets, tight orbs.',
			'astrologer-api'
		),
		value: 'traditional',
	},
	{
		name: 'vedic',
		title: __( 'Vedic', 'astrologer-api' ),
		description: __(
			'Sidereal zodiac (Lahiri), Rahu/Ketu, Vedic orbs.',
			'astrologer-api'
		),
		value: 'vedic',
	},
	{
		name: 'uranian',
		title: __( 'Uranian', 'astrologer-api' ),
		description: __(
			'Meridian houses, Hamburg school hypothetical planets.',
			'astrologer-api'
		),
		value: 'uranian',
	},
];

const FORM_BLOCKS: readonly FormBlock[] = [
	{
		blockName: 'astrologer-api/birth-form',
		label: __( 'Birth Form', 'astrologer-api' ),
	},
	{
		blockName: 'astrologer-api/synastry-form',
		label: __( 'Synastry Form', 'astrologer-api' ),
	},
	{
		blockName: 'astrologer-api/transit-form',
		label: __( 'Transit Form', 'astrologer-api' ),
	},
	{
		blockName: 'astrologer-api/composite-form',
		label: __( 'Composite Form', 'astrologer-api' ),
	},
	{
		blockName: 'astrologer-api/solar-return-form',
		label: __( 'Solar Return Form', 'astrologer-api' ),
	},
	{
		blockName: 'astrologer-api/lunar-return-form',
		label: __( 'Lunar Return Form', 'astrologer-api' ),
	},
	{
		blockName: 'astrologer-api/now-form',
		label: __( 'Now Form', 'astrologer-api' ),
	},
];

FORM_BLOCKS.forEach( ( block ) => {
	SCHOOLS.forEach( ( school ) => {
		registerBlockVariation( block.blockName, {
			name: school.name,
			title: `${ block.label } (${ school.title })`,
			description: school.description,
			attributes: {
				preset: school.value,
			},
			scope: [ 'inserter', 'transform' ],
			isDefault: false,
		} );
	} );
} );

export {};
