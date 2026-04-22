<?php
/**
 * Documentation page — markdown renderer for in-admin docs.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Admin;

use Astrologer\Api\Support\Contracts\Bootable;
use League\CommonMark\CommonMarkConverter;

/**
 * Renders Markdown documentation files and passes them to a React frontend.
 */
final class DocumentationPage implements Bootable {

	/**
	 * Ordered list of documentation files (without .md extension).
	 */
	private const DOC_FILES = array(
		'user-guide',
		'shortcodes',
		'blocks',
		'hooks',
		'cli',
		'rest-api',
	);

	/**
	 * Register hooks.
	 */
	public function boot(): void {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Enqueue the documentation React app and pass converted markdown content.
	 *
	 * @param string $hook_suffix The current admin page hook suffix.
	 */
	public function enqueue_assets( string $hook_suffix ): void {
		if ( 'astrologer_page_astrologer-docs' !== $hook_suffix ) {
			return;
		}

		$asset_file = ASTROLOGER_API_DIR . '/build/admin-documentation.asset.php';
		$asset      = file_exists( $asset_file )
			? require $asset_file
			: array(
				'dependencies' => array(),
				'version'      => ASTROLOGER_API_VERSION,
			);

		$dependencies   = $asset['dependencies'];
		$dependencies[] = 'wp-components';

		wp_enqueue_script(
			'astrologer-docs',
			ASTROLOGER_API_URL . 'build/admin-documentation.js',
			$dependencies,
			$asset['version'],
			true
		);

		wp_enqueue_style(
			'astrologer-docs',
			ASTROLOGER_API_URL . 'build/admin-documentation.css',
			array( 'wp-components' ),
			$asset['version']
		);

		$converter = new CommonMarkConverter();
		$pages     = array();

		foreach ( self::DOC_FILES as $slug ) {
			$file = ASTROLOGER_API_DIR . '/docs/' . $slug . '.md';

			if ( ! file_exists( $file ) ) {
				continue;
			}

			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local file read, not remote.
			$markdown = (string) file_get_contents( $file );
			$html     = $converter->convert( $markdown )->getContent();

			$pages[] = array(
				'slug'  => $slug,
				'title' => ucwords( str_replace( '-', ' ', $slug ) ),
				'html'  => wp_kses_post( $html ),
			);
		}

		wp_localize_script(
			'astrologer-docs',
			'astrologerDocs',
			array( 'pages' => $pages )
		);
	}
}
