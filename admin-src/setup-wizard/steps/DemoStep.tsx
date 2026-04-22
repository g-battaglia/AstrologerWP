/**
 * Demo / Preview step — shows a sample natal chart via iframe.
 *
 * @package
 */

import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import type { StepProps } from './types';

const DemoStep = ( { next, back }: StepProps ) => {
	const restUrl =
		window.astrologerSettings?.restUrl || '/wp-json/astrologer/v1/';

	// Einstein birth data: 1879-03-14, 11:30, Ulm, Germany (48.4011, 9.9876).
	const chartUrl = `${ restUrl }natal-chart?date=1879-03-14&time=11:30&lat=48.4011&lng=9.9876`;

	return (
		<div>
			<h3>
				{ __(
					'Preview: Albert Einstein Natal Chart',
					'astrologer-api'
				) }
			</h3>

			<p>
				{ __(
					'Below is a sample natal chart generated with your API key and settings.',
					'astrologer-api'
				) }
			</p>

			<iframe
				src={ chartUrl }
				title={ __( 'Natal Chart Preview', 'astrologer-api' ) }
				style={ {
					width: '100%',
					height: '500px',
					border: '1px solid #ddd',
					borderRadius: '4px',
				} }
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

export default DemoStep;
