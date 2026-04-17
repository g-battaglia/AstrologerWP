/**
 * Editor preview for the spike birth-form block.
 *
 * Displays a placeholder — no interactivity in the editor.
 *
 * @package
 */

import { useBlockProps } from '@wordpress/block-editor';

export default function Edit(): JSX.Element {
	const blockProps = useBlockProps();

	return (
		<div { ...blockProps }>
			<div
				style={ {
					padding: '24px',
					textAlign: 'center',
					border: '2px dashed #888',
					borderRadius: '8px',
					background: '#f9f9f9',
				} }
			>
				<span
					role="img"
					aria-label="star"
					style={ {
						fontSize: '32px',
						display: 'block',
						marginBottom: '8px',
					} }
				>
					&#9733;
				</span>
				<strong>Spike Birth Form</strong>
				<p style={ { color: '#666', margin: '8px 0 0' } }>
					Interactivity API spike — do not use in production.
				</p>
			</div>
		</div>
	);
}
