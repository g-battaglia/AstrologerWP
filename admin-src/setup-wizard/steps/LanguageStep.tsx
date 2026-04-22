/**
 * Language & UI step — set language and UI complexity.
 *
 * @package Astrologer\Api
 */

import { SelectControl, RadioControl, Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import type { StepProps } from './types';

const LANGUAGE_OPTIONS = [
	{ label: 'English', value: 'EN' },
	{ label: 'Italiano', value: 'IT' },
	{ label: 'Espanol', value: 'ES' },
	{ label: 'Francais', value: 'FR' },
	{ label: 'Deutsch', value: 'DE' },
	{ label: 'Portugues', value: 'PT' },
	{ label: 'Russian', value: 'RU' },
	{ label: 'Chinese', value: 'CN' },
	{ label: 'Japanese', value: 'JP' },
	{ label: 'Hindi', value: 'HI' },
];

const UI_LEVEL_OPTIONS = [
	{
		label: __( 'Basic — Essential chart data only', 'astrologer-api' ),
		value: 'basic',
	},
	{
		label: __( 'Advanced — Includes aspects, houses, and transits', 'astrologer-api' ),
		value: 'advanced',
	},
	{
		label: __( 'Expert — Full data including midpoints and fixed stars', 'astrologer-api' ),
		value: 'expert',
	},
];

const LanguageStep = ( { data, setData, next, back }: StepProps ) => {
	return (
		<div>
			<SelectControl
				label={ __( 'Language', 'astrologer-api' ) }
				value={ data.language }
				options={ LANGUAGE_OPTIONS }
				onChange={ ( value: string ) => setData( { language: value } ) }
				__next40pxDefaultSize
				__nextHasNoMarginBottom
			/>

			<RadioControl
				label={ __( 'UI Complexity Level', 'astrologer-api' ) }
				selected={ data.ui_level }
				options={ UI_LEVEL_OPTIONS }
				onChange={ ( value: string ) =>
					setData( { ui_level: value } )
				}
			/>

			<div style={ { marginTop: '16px', display: 'flex', gap: '8px' } }>
				<Button variant="secondary" onClick={ back }>
					{ __( 'Back', 'astrologer-api' ) }
				</Button>
				<Button variant="primary" onClick={ next }>
					{ __( 'Continue', 'astrologer-api' ) }
				</Button>
			</div>
		</div>
	);
};

export default LanguageStep;
