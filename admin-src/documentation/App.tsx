/**
 * Documentation viewer — two-column layout with sidebar navigation.
 *
 * @package
 */

import { Button } from '@wordpress/components';
import { useState, useEffect, useRef } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

interface DocPage {
	slug: string;
	title: string;
	html: string;
}

declare global {
	interface Window {
		astrologerDocs?: {
			pages: DocPage[];
		};
	}
}

const App = () => {
	const pages = window.astrologerDocs?.pages || [];
	const [ activeSlug, setActiveSlug ] = useState(
		pages.length > 0 ? pages[ 0 ].slug : ''
	);
	const contentRef = useRef< HTMLDivElement >( null );

	const activePage = pages.find( ( p ) => p.slug === activeSlug );

	// Handle anchor links within documentation content.
	useEffect( () => {
		const container = contentRef.current;
		if ( ! container ) {
			return;
		}

		const handleClick = ( event: MouseEvent ) => {
			const target = event.target as HTMLAnchorElement;
			if ( target.tagName !== 'A' || ! target.hash ) {
				return;
			}

			const id = target.hash.slice( 1 );
			const element = container.querySelector( `#${ CSS.escape( id ) }` );
			if ( element ) {
				event.preventDefault();
				element.scrollIntoView( { behavior: 'smooth' } );
			}
		};

		container.addEventListener( 'click', handleClick );
		return () => container.removeEventListener( 'click', handleClick );
	}, [ activeSlug ] );

	if ( pages.length === 0 ) {
		return (
			<div className="astrologer-admin wrap">
				<h1>{ __( 'Documentation', 'astrologer-api' ) }</h1>
				<p>
					{ __( 'No documentation files found.', 'astrologer-api' ) }
				</p>
			</div>
		);
	}

	return (
		<div className="astrologer-admin wrap">
			<h1>{ __( 'Documentation', 'astrologer-api' ) }</h1>

			<div
				style={ {
					display: 'grid',
					gridTemplateColumns: '200px 1fr',
					gap: '24px',
					marginTop: '16px',
				} }
			>
				{ /* Sidebar */ }
				<nav>
					{ pages.map( ( page ) => (
						<Button
							key={ page.slug }
							variant={
								activeSlug === page.slug
									? 'primary'
									: 'secondary'
							}
							onClick={ () => setActiveSlug( page.slug ) }
							style={ {
								display: 'block',
								width: '100%',
								marginBottom: '4px',
							} }
						>
							{ page.title }
						</Button>
					) ) }
				</nav>

				{ /* Content */ }
				<div
					ref={ contentRef }
					className="astrologer-docs-content"
					dangerouslySetInnerHTML={ {
						__html: activePage?.html || '',
					} }
				/>
			</div>
		</div>
	);
};

export default App;
