/**
 * Settings app root component with tabbed interface.
 *
 * @package Astrologer\Api
 */

import { TabPanel, Notice, Spinner } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { useSettings } from './use-settings';
import ApiCredentialsTab from './tabs/ApiCredentialsTab';
import AstrologyDefaultsTab from './tabs/AstrologyDefaultsTab';
import UITab from './tabs/UITab';
import CronTab from './tabs/CronTab';
import CapabilitiesTab from './tabs/CapabilitiesTab';
import IntegrationsTab from './tabs/IntegrationsTab';

const TABS = [
	{
		name: 'api-credentials',
		title: __( 'API Credentials', 'astrologer-api' ),
		className: 'tab-api-credentials',
	},
	{
		name: 'astrology-defaults',
		title: __( 'Astrology Defaults', 'astrologer-api' ),
		className: 'tab-astrology-defaults',
	},
	{
		name: 'ui',
		title: __( 'UI', 'astrologer-api' ),
		className: 'tab-ui',
	},
	{
		name: 'cron',
		title: __( 'Cron', 'astrologer-api' ),
		className: 'tab-cron',
	},
	{
		name: 'capabilities',
		title: __( 'Capabilities', 'astrologer-api' ),
		className: 'tab-capabilities',
	},
	{
		name: 'integrations',
		title: __( 'Integrations', 'astrologer-api' ),
		className: 'tab-integrations',
	},
];

const App = () => {
	const { settings, isLoading, isSaving, error, updateSettings, testConnection } =
		useSettings();
	const [ saveNotice, setSaveNotice ] = useState< string | null >( null );

	if ( isLoading ) {
		return <Spinner />;
	}

	if ( ! settings ) {
		return (
			<Notice status="error" isDismissible={ false }>
				{ error || __( 'Failed to load settings.', 'astrologer-api' ) }
			</Notice>
		);
	}

	const handleSave = async ( data: Record< string, unknown > ) => {
		await updateSettings( data );
		setSaveNotice( __( 'Settings saved.', 'astrologer-api' ) );
	};

	return (
		<div className="astrologer-admin wrap">
			<h1>{ __( 'Astrologer Settings', 'astrologer-api' ) }</h1>

			{ saveNotice && (
				<Notice
					status="success"
					onDismiss={ () => setSaveNotice( null ) }
				>
					{ saveNotice }
				</Notice>
			) }

			{ error && (
				<Notice status="error" isDismissible={ false }>
					{ error }
				</Notice>
			) }

			<TabPanel tabs={ TABS }>
				{ ( tab ) => {
					switch ( tab.name ) {
						case 'api-credentials':
							return (
								<ApiCredentialsTab
									settings={ settings }
									onSave={ handleSave }
									isSaving={ isSaving }
									testConnection={ testConnection }
								/>
							);
						case 'astrology-defaults':
							return (
								<AstrologyDefaultsTab
									settings={ settings }
									onSave={ handleSave }
									isSaving={ isSaving }
								/>
							);
						case 'ui':
							return (
								<UITab
									settings={ settings }
									onSave={ handleSave }
									isSaving={ isSaving }
								/>
							);
						case 'cron':
							return (
								<CronTab
									settings={ settings }
									onSave={ handleSave }
									isSaving={ isSaving }
								/>
							);
						case 'capabilities':
							return <CapabilitiesTab />;
						case 'integrations':
							return <IntegrationsTab />;
						default:
							return null;
					}
				} }
			</TabPanel>
		</div>
	);
};

export default App;
