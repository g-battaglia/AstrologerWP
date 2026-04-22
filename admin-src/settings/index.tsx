/**
 * Settings app entry point.
 *
 * @package
 */

import { createRoot } from '@wordpress/element';
import { SlotFillProvider } from '@wordpress/components';
import App from './App';
import '../shared/admin.scss';

const container = document.getElementById( 'astrologer-settings-root' );

if ( container ) {
	const root = createRoot( container );
	root.render(
		<SlotFillProvider>
			<App />
		</SlotFillProvider>
	);
}
