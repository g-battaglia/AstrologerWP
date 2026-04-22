/**
 * Setup Wizard app entry point.
 *
 * @package
 */

import { createRoot } from '@wordpress/element';
import App from './App';
import '../shared/admin.scss';

const container = document.getElementById( 'astrologer-wizard-root' );

if ( container ) {
	const root = createRoot( container );
	root.render( <App /> );
}
