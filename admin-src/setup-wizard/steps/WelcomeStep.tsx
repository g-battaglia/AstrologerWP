/**
 * Welcome step — overview and getting started.
 *
 * @package Astrologer\Api
 */

import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import type { StepProps } from './types';

const WelcomeStep = ( { next }: StepProps ) => {
	return (
		<div>
			<h2>{ __( 'Welcome to Astrologer API', 'astrologer-api' ) }</h2>

			<p>
				{ __(
					'This wizard will help you configure the Astrologer API plugin in a few simple steps. You will need a RapidAPI key to get started.',
					'astrologer-api'
				) }
			</p>

			<p>
				<a
					href="https://rapidapi.com/astrologer-api/api/astrologer"
					target="_blank"
					rel="noopener noreferrer"
				>
					{ __( 'Get your API key on RapidAPI', 'astrologer-api' ) }
				</a>
			</p>

			<Button variant="primary" onClick={ next }>
				{ __( 'Get Started', 'astrologer-api' ) }
			</Button>
		</div>
	);
};

export default WelcomeStep;
