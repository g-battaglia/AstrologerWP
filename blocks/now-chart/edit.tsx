import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import {
	PanelBody,
	SelectControl,
	ToggleControl,
	TextControl,
} from '@wordpress/components';

interface Attributes {
	sourceBlockId: string;
	showSvg: boolean;
	showPositions: boolean;
	showAspects: boolean;
	chartTheme: string;
	theme: string;
}

interface Props {
	attributes: Attributes;
	setAttributes: ( attrs: Partial< Attributes > ) => void;
}

const CHART_THEMES = [
	{ label: __( 'Classic', 'astrologer-api' ), value: 'classic' },
	{ label: __( 'Dark', 'astrologer-api' ), value: 'dark' },
	{ label: __( 'Light', 'astrologer-api' ), value: 'light' },
];

export default function Edit( { attributes, setAttributes }: Props ) {
	const blockProps = useBlockProps();
	const {
		sourceBlockId,
		showSvg,
		showPositions,
		showAspects,
		chartTheme,
	} = attributes;

	const onLabel = __( 'on', 'astrologer-api' );
	const offLabel = __( 'off', 'astrologer-api' );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Chart Settings', 'astrologer-api' ) }>
					<TextControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						label={ __( 'Source Block ID', 'astrologer-api' ) }
						value={ sourceBlockId }
						onChange={ ( val: string ) =>
							setAttributes( { sourceBlockId: val } )
						}
						help={ __(
							'Optional ID of the form block providing data.',
							'astrologer-api'
						) }
					/>
					<SelectControl
						label={ __( 'Chart Theme', 'astrologer-api' ) }
						value={ chartTheme }
						options={ CHART_THEMES }
						onChange={ ( val: string ) =>
							setAttributes( { chartTheme: val } )
						}
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __( 'Show SVG Wheel', 'astrologer-api' ) }
						checked={ showSvg }
						onChange={ ( val: boolean ) =>
							setAttributes( { showSvg: val } )
						}
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __( 'Show Positions Table', 'astrologer-api' ) }
						checked={ showPositions }
						onChange={ ( val: boolean ) =>
							setAttributes( { showPositions: val } )
						}
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __( 'Show Aspects', 'astrologer-api' ) }
						checked={ showAspects }
						onChange={ ( val: boolean ) =>
							setAttributes( { showAspects: val } )
						}
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
						{ __( 'Now Chart', 'astrologer-api' ) }
					</p>
					<p style={ { margin: '8px 0 0', color: '#757575' } }>
						{ `${ __( 'Theme:', 'astrologer-api' ) } ${ chartTheme }` }
					</p>
					<p style={ { margin: '4px 0 0', color: '#757575' } }>
						{ `${ __( 'SVG:', 'astrologer-api' ) } ${
							showSvg ? onLabel : offLabel
						} · ${ __( 'Positions:', 'astrologer-api' ) } ${
							showPositions ? onLabel : offLabel
						} · ${ __( 'Aspects:', 'astrologer-api' ) } ${
							showAspects ? onLabel : offLabel
						}` }
					</p>
				</div>
			</div>
		</>
	);
}
