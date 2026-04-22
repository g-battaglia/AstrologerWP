/**
 * TypeScript interfaces for the Settings app.
 *
 * @package
 */

export interface CronSettings {
	daily_transits: boolean;
	daily_moon_phase: boolean;
	solar_return_reminder: boolean;
}

export interface IntegrationSettings {
	enabled: boolean;
	[ key: string ]: unknown;
}

export interface AstrologerSettings {
	has_api_key: boolean;
	rapidapi_key: string;
	geonames_username: string;
	api_base_url: string;
	language: string;
	school: string;
	ui_level: string;
	chart_options: Record< string, unknown >;
	cron: CronSettings;
	integrations: IntegrationSettings;
}

declare global {
	interface Window {
		astrologerSettings: {
			restUrl: string;
			nonce: string;
			adminUrl: string;
		};
	}
}
