/**
 * School selection step — choose astrological school.
 *
 * @package Astrologer\Api
 */

import { Card, CardBody, CardHeader } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import type { StepProps } from './types';

const SCHOOLS = [
	{
		value: 'modern_western',
		label: __( 'Modern Western', 'astrologer-api' ),
		description: __(
			'Contemporary Western astrology with outer planets, Placidus houses, and psychological interpretation.',
			'astrologer-api'
		),
	},
	{
		value: 'traditional',
		label: __( 'Traditional', 'astrologer-api' ),
		description: __(
			'Classical Western astrology with Whole Sign houses, traditional rulerships, and sect-based interpretation.',
			'astrologer-api'
		),
	},
	{
		value: 'vedic',
		label: __( 'Vedic', 'astrologer-api' ),
		description: __(
			'Jyotish (Indian) astrology using the sidereal zodiac, dashas, and Vedic house systems.',
			'astrologer-api'
		),
	},
	{
		value: 'uranian',
		label: __( 'Uranian', 'astrologer-api' ),
		description: __(
			'Hamburg School astrology with hypothetical planets, midpoint structures, and 90-degree dial.',
			'astrologer-api'
		),
	},
];

const SchoolStep = ( { data, setData, next, back }: StepProps ) => {
	const handleSelect = ( value: string ) => {
		setData( { school: value } );
		next();
	};

	return (
		<div>
			<p>
				{ __(
					'Choose your preferred astrological school. This can be changed later in Settings.',
					'astrologer-api'
				) }
			</p>

			<div
				style={ {
					display: 'grid',
					gridTemplateColumns: 'repeat(auto-fill, minmax(250px, 1fr))',
					gap: '16px',
					marginBottom: '16px',
				} }
			>
				{ SCHOOLS.map( ( school ) => (
					<Card
						key={ school.value }
						onClick={ () => handleSelect( school.value ) }
						isRounded
						style={ {
							cursor: 'pointer',
							border:
								data.school === school.value
									? '2px solid var(--wp-admin-theme-color, #3858e9)'
									: undefined,
						} }
					>
						<CardHeader>
							<strong>{ school.label }</strong>
						</CardHeader>
						<CardBody>{ school.description }</CardBody>
					</Card>
				) ) }
			</div>

			<button
				type="button"
				className="components-button is-secondary"
				onClick={ back }
			>
				{ __( 'Back', 'astrologer-api' ) }
			</button>
		</div>
	);
};

export default SchoolStep;
