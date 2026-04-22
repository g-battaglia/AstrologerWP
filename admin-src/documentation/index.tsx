/**
 * Documentation app entry point.
 *
 * @package Astrologer\Api
 */

import { createRoot } from '@wordpress/element';
import App from './App';
import '../shared/admin.scss';

const container = document.getElementById( 'astrologer-docs-root' );

if ( container ) {
	const root = createRoot( container );
	root.render( <App /> );
}
