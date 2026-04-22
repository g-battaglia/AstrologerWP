import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl, TextControl } from '@wordpress/components';

interface Attributes {
	sourceBlockId: string;
	displayMode: string;
}

interface Props {
	attributes: Attributes;
	setAttributes: ( attrs: Partial< Attributes > ) => void;
}

const DISPLAY_MODES = [
	{ label: __( 'Numeric', 'astrologer-api' ), value: 'table' },
	{ label: __( 'Gauge', 'astrologer-api' ), value: 'gauge' },
];

export default function Edit( { attributes, setAttributes }: Props ) {
	const blockProps = useBlockProps();
	const { sourceBlockId, displayMode } = attributes;

	return (
		<>
			<InspectorControls>
				<PanelBody
					title={ __(
						'Relationship Score Settings',
						'astrologer-api'
					) }
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
							'Optional ID of a relationship source block. Leave empty to auto-detect.',
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
						{ __( 'Relationship Score', 'astrologer-api' ) }
					</p>
					<p style={ { margin: '8px 0 0', color: '#757575' } }>
						{ `${ __( 'Display Mode:', 'astrologer-api' ) } ${ displayMode }` }
					</p>
					{ sourceBlockId && (
						<p style={ { margin: '4px 0 0', color: '#757575' } }>
							{ `${ __( 'Source:', 'astrologer-api' ) } ${ sourceBlockId }` }
						</p>
					) }
				</div>
			</div>
		</>
	);
}
