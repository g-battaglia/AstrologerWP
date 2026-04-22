/**
 * Astrology Defaults settings tab.
 *
 * @package
 */

import { SelectControl, Button } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import type { AstrologerSettings } from '../types';

interface Props {
	settings: AstrologerSettings;
	onSave: ( data: Record< string, unknown > ) => Promise< void >;
	isSaving: boolean;
}

const SCHOOL_OPTIONS = [
	{
		label: __( 'Modern Western', 'astrologer-api' ),
		value: 'modern_western',
	},
	{ label: __( 'Traditional', 'astrologer-api' ), value: 'traditional' },
	{ label: __( 'Vedic', 'astrologer-api' ), value: 'vedic' },
	{ label: __( 'Uranian', 'astrologer-api' ), value: 'uranian' },
];

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

const AstrologyDefaultsTab = ( { settings, onSave, isSaving }: Props ) => {
	const [ school, setSchool ] = useState(
		settings.school || 'modern_western'
	);
	const [ language, setLanguage ] = useState( settings.language || 'EN' );

	const handleSave = () => {
		onSave( { school, language } );
	};

	return (
		<div className="astrologer-admin__tab-content">
			<SelectControl
				label={ __( 'Astrological School', 'astrologer-api' ) }
				value={ school }
				options={ SCHOOL_OPTIONS }
				onChange={ setSchool }
				help={ __(
					'Select the astrological school used for calculations.',
					'astrologer-api'
				) }
				__next40pxDefaultSize
				__nextHasNoMarginBottom
			/>

			<SelectControl
				label={ __( 'Language', 'astrologer-api' ) }
				value={ language }
				options={ LANGUAGE_OPTIONS }
				onChange={ setLanguage }
				help={ __(
					'Language for API responses and chart labels.',
					'astrologer-api'
				) }
				__next40pxDefaultSize
				__nextHasNoMarginBottom
			/>

			<div style={ { marginTop: '24px' } }>
				<Button
					variant="primary"
					onClick={ handleSave }
					isBusy={ isSaving }
					disabled={ isSaving }
				>
					{ __( 'Save Defaults', 'astrologer-api' ) }
				</Button>
			</div>
		</div>
	);
};

export default AstrologyDefaultsTab;
