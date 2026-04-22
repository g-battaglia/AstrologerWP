import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import {
	PanelBody,
	SelectControl,
	TextControl,
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalNumberControl as NumberControl,
} from '@wordpress/components';

interface Attributes {
	sourceBlockId: string;
	displayMode: string;
	refreshInterval: number;
}

interface Props {
	attributes: Attributes;
	setAttributes: ( attrs: Partial< Attributes > ) => void;
}

const DISPLAY_MODES = [
	{ label: __( 'Inline', 'astrologer-api' ), value: 'table' },
	{ label: __( 'Card', 'astrologer-api' ), value: 'card' },
];

export default function Edit( { attributes, setAttributes }: Props ) {
	const blockProps = useBlockProps();
	const { sourceBlockId, displayMode, refreshInterval } = attributes;

	return (
		<>
			<InspectorControls>
				<PanelBody
					title={ __( 'Moon Phase Settings', 'astrologer-api' ) }
				>
					<SelectControl
						label={ __( 'Display Mode', 'astrologer-api' ) }
						value={ displayMode }
						options={ DISPLAY_MODES }
						onChange={ ( val: string ) =>
							setAttributes( { displayMode: val } )
						}
					/>
					<TextControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						label={ __( 'Source Block ID', 'astrologer-api' ) }
						value={ sourceBlockId }
						onChange={ ( val: string ) =>
							setAttributes( { sourceBlockId: val } )
						}
						help={ __(
							'Optional source block. Moon phase is normally self-fetching.',
							'astrologer-api'
						) }
					/>
					<NumberControl
						label={ __(
							'Refresh Interval (seconds)',
							'astrologer-api'
						) }
						value={ refreshInterval }
						min={ 0 }
						max={ 86400 }
						onChange={ ( val: string | number | undefined ) => {
							const num =
								typeof val === 'number'
									? val
									: parseInt( String( val ?? '0' ), 10 );
							setAttributes( {
								refreshInterval: isNaN( num ) ? 0 : num,
							} );
						} }
						help={ __(
							'Auto-refresh interval in seconds. Set to 0 to disable.',
							'astrologer-api'
						) }
					/>
				</PanelBody>
			</InspectorControls>
			<div { ...blockProps }>
				<div
					style={ {
						border: '1px dashed #ccc',
						padding: '24px',
						textAlign: 'center',
						background: '#f9f9f9',
						borderRadius: '4px',
					} }
				>
					<p style={ { margin: 0, fontWeight: 600 } }>
						{ __( 'Moon Phase', 'astrologer-api' ) }
					</p>
					<p style={ { margin: '8px 0 0', color: '#757575' } }>
						{ `${ __(
							'Display Mode:',
							'astrologer-api'
						) } ${ displayMode }` }
					</p>
					<p style={ { margin: '4px 0 0', color: '#757575' } }>
						{ `${ __(
							'Refresh:',
							'astrologer-api'
						) } ${ refreshInterval }s` }
					</p>
				</div>
			</div>
		</>
	);
}
