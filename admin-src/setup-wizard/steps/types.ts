/**
 * TypeScript interfaces for the Setup Wizard.
 *
 * @package
 */

export interface WizardData {
	rapidapi_key: string;
	school: string;
	language: string;
	ui_level: string;
}

export interface StepProps {
	data: WizardData;
	setData: ( data: Partial< WizardData > ) => void;
	next: () => void;
	back: () => void;
}
