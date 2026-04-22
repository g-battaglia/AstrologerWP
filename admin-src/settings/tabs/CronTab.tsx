/**
 * Cron settings tab.
 *
 * @package Astrologer\Api
 */

import { ToggleControl, Button } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import type { AstrologerSettings } from '../types';

interface Props {
	settings: AstrologerSettings;
	onSave: ( data: Record< string, unknown > ) => Promise< void >;
	isSaving: boolean;
}

const CronTab = ( { settings, onSave, isSaving }: Props ) => {
	const [ dailyTransits, setDailyTransits ] = useState(
		settings.cron?.daily_transits ?? false
	);
	const [ dailyMoonPhase, setDailyMoonPhase ] = useState(
		settings.cron?.daily_moon_phase ?? false
	);
	const [ solarReturnReminder, setSolarReturnReminder ] = useState(
		settings.cron?.solar_return_reminder ?? false
	);

	const handleSave = () => {
		onSave( {
			cron: {
				daily_transits: dailyTransits,
				daily_moon_phase: dailyMoonPhase,
				solar_return_reminder: solarReturnReminder,
			},
		} );
	};

	return (
		<div className="astrologer-admin__tab-content">
			<ToggleControl
				label={ __( 'Daily Transits', 'astrologer-api' ) }
				checked={ dailyTransits }
				onChange={ setDailyTransits }
				help={ __(
					'Automatically calculate and cache daily transits.',
					'astrologer-api'
				) }
				__nextHasNoMarginBottom
			/>

			<ToggleControl
				label={ __( 'Daily Moon Phase', 'astrologer-api' ) }
				checked={ dailyMoonPhase }
				onChange={ setDailyMoonPhase }
				help={ __(
					'Automatically calculate and cache the daily moon phase.',
					'astrologer-api'
				) }
				__nextHasNoMarginBottom
			/>

			<ToggleControl
				label={ __( 'Solar Return Reminder', 'astrologer-api' ) }
				checked={ solarReturnReminder }
				onChange={ setSolarReturnReminder }
				help={ __(
					'Send email reminders before a saved chart birthday.',
					'astrologer-api'
				) }
				__nextHasNoMarginBottom
			/>

			<div style={ { marginTop: '24px' } }>
				<Button
					variant="primary"
					onClick={ handleSave }
					isBusy={ isSaving }
					disabled={ isSaving }
				>
					{ __( 'Save Cron Settings', 'astrologer-api' ) }
				</Button>
			</div>
		</div>
	);
};

export default CronTab;
