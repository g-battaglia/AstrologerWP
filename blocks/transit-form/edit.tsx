import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import {
	PanelBody,
	SelectControl,
	ToggleControl,
	TextControl,
} from '@wordpress/components';

interface Attributes {
	uiLevel: string;
	targetBlockId: string;
	showSaveOption: boolean;
	preset: string;
	redirectAfterSubmit: string;
}

interface Props {
	attributes: Attributes;
	setAttributes: ( attrs: Partial< Attributes > ) => void;
}

const UI_LEVELS = [
	{ label: __( 'Basic', 'astrologer-api' ), value: 'basic' },
	{ label: __( 'Advanced', 'astrologer-api' ), value: 'advanced' },
	{ label: __( 'Expert', 'astrologer-api' ), value: 'expert' },
];

const PRESETS = [
	{ label: __( 'Auto (from settings)', 'astrologer-api' ), value: 'auto' },
	{ label: __( 'Modern Western', 'astrologer-api' ), value: 'modern_western' },
	{ label: __( 'Traditional', 'astrologer-api' ), value: 'traditional' },
	{ label: __( 'Vedic', 'astrologer-api' ), value: 'vedic' },
	{ label: __( 'Uranian', 'astrologer-api' ), value: 'uranian' },
];

export default function Edit( { attributes, setAttributes }: Props ) {
	const blockProps = useBlockProps();
	const { uiLevel, showSaveOption, preset, redirectAfterSubmit } = attributes;

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Form Settings', 'astrologer-api' ) }>
					<SelectControl
						label={ __( 'UI Level', 'astrologer-api' ) }
						value={ uiLevel }
						options={ UI_LEVELS }
						onChange={ ( val: string ) =>
							setAttributes( { uiLevel: val } )
						}
					/>
					<SelectControl
						label={ __( 'School Preset', 'astrologer-api' ) }
						value={ preset }
						options={ PRESETS }
						onChange={ ( val: string ) =>
							setAttributes( { preset: val } )
						}
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __( 'Show Save Option', 'astrologer-api' ) }
						checked={ showSaveOption }
						onChange={ ( val: boolean ) =>
							setAttributes( { showSaveOption: val } )
						}
						help={ __(
							'Allow logged-in users to save the chart.',
							'astrologer-api'
						) }
					/>
					<TextControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						label={ __( 'Redirect After Submit', 'astrologer-api' ) }
						value={ redirectAfterSubmit }
						onChange={ ( val: string ) =>
							setAttributes( { redirectAfterSubmit: val } )
						}
						help={ __(
							'URL to redirect after form submission. Leave empty to show results on same page.',
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
						{ __( 'Transit Form', 'astrologer-api' ) }
					</p>
					<p style={ { margin: '8px 0 0', color: '#757575' } }>
						{ `${ __( 'UI Level:', 'astrologer-api' ) } ${ uiLevel }` }
					</p>
				</div>
			</div>
		</>
	);
}
