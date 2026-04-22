/**
 * UI settings tab.
 *
 * @package
 */

import { RadioControl, Button } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import type { AstrologerSettings } from '../types';

interface Props {
	settings: AstrologerSettings;
	onSave: ( data: Record< string, unknown > ) => Promise< void >;
	isSaving: boolean;
}

const UI_LEVEL_OPTIONS = [
	{
		label: __( 'Basic — Essential chart data only', 'astrologer-api' ),
		value: 'basic',
	},
	{
		label: __(
			'Advanced — Includes aspects, houses, and transits',
			'astrologer-api'
		),
		value: 'advanced',
	},
	{
		label: __(
			'Expert — Full data including midpoints and fixed stars',
			'astrologer-api'
		),
		value: 'expert',
	},
];

const UITab = ( { settings, onSave, isSaving }: Props ) => {
	const [ uiLevel, setUiLevel ] = useState( settings.ui_level || 'basic' );

	const handleSave = () => {
		onSave( { ui_level: uiLevel } );
	};

	return (
		<div className="astrologer-admin__tab-content">
			<RadioControl
				label={ __( 'UI Complexity Level', 'astrologer-api' ) }
				selected={ uiLevel }
				options={ UI_LEVEL_OPTIONS }
				onChange={ setUiLevel }
				help={ __(
					'Controls the amount of astrological detail shown in the interface.',
					'astrologer-api'
				) }
			/>

			<div style={ { marginTop: '24px' } }>
				<Button
					variant="primary"
					onClick={ handleSave }
					isBusy={ isSaving }
					disabled={ isSaving }
				>
					{ __( 'Save UI Settings', 'astrologer-api' ) }
				</Button>
			</div>
		</div>
	);
};

export default UITab;
