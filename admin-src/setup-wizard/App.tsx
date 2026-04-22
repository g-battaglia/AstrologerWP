/**
 * Setup Wizard multi-step application.
 *
 * @package Astrologer\Api
 */

import { Panel, PanelBody } from '@wordpress/components';
import { useState, useCallback } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import type { WizardData } from './steps/types';
import WelcomeStep from './steps/WelcomeStep';
import ApiKeyStep from './steps/ApiKeyStep';
import SchoolStep from './steps/SchoolStep';
import LanguageStep from './steps/LanguageStep';
import DemoStep from './steps/DemoStep';
import DoneStep from './steps/DoneStep';

const STEP_TITLES = [
	__( 'Welcome', 'astrologer-api' ),
	__( 'API Key', 'astrologer-api' ),
	__( 'School', 'astrologer-api' ),
	__( 'Language & UI', 'astrologer-api' ),
	__( 'Preview', 'astrologer-api' ),
	__( 'Done', 'astrologer-api' ),
];

const App = () => {
	const [ step, setStep ] = useState( 0 );
	const [ data, setDataState ] = useState< WizardData >( {
		rapidapi_key: '',
		school: 'modern_western',
		language: 'EN',
		ui_level: 'basic',
	} );

	const setData = useCallback( ( partial: Partial< WizardData > ) => {
		setDataState( ( prev ) => ( { ...prev, ...partial } ) );
	}, [] );

	const next = useCallback( () => {
		setStep( ( prev ) => Math.min( prev + 1, STEP_TITLES.length - 1 ) );
	}, [] );

	const back = useCallback( () => {
		setStep( ( prev ) => Math.max( prev - 1, 0 ) );
	}, [] );

	const stepProps = { data, setData, next, back };

	const STEPS = [
		<WelcomeStep key="welcome" { ...stepProps } />,
		<ApiKeyStep key="api-key" { ...stepProps } />,
		<SchoolStep key="school" { ...stepProps } />,
		<LanguageStep key="language" { ...stepProps } />,
		<DemoStep key="demo" { ...stepProps } />,
		<DoneStep key="done" { ...stepProps } />,
	];

	return (
		<div className="astrologer-admin wrap">
			<h1>{ __( 'Astrologer Setup Wizard', 'astrologer-api' ) }</h1>

			<p className="description">
				{ `${ __( 'Step', 'astrologer-api' ) } ${ step + 1 } / ${
					STEP_TITLES.length
				}: ${ STEP_TITLES[ step ] }` }
			</p>

			<Panel>
				<PanelBody
					title={ STEP_TITLES[ step ] }
					initialOpen={ true }
				>
					{ STEPS[ step ] }
				</PanelBody>
			</Panel>
		</div>
	);
};

export default App;
