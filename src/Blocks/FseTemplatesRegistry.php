<?php
/**
 * FseTemplatesRegistry — registers plugin FSE block templates.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Blocks;

use Astrologer\Api\Support\Contracts\Bootable;

/**
 * Registers FSE block templates for the `astrologer_chart` post type.
 *
 * Uses `register_block_template()` when available (WP 6.7+). Falls back to a
 * no-op gracefully when running against older WordPress versions so that the
 * plugin remains compatible with the minimum supported release.
 */
final class FseTemplatesRegistry implements Bootable {

	/**
	 * Plugin template slug namespace (matches `register_block_template` contract).
	 *
	 * @var string
	 */
	public const NAMESPACE_SLUG = 'astrologer-api';

	/**
	 * Register the init hook.
	 */
	public function boot(): void {
		add_action( 'init', array( $this, 'register' ), 12 );
	}

	/**
	 * Register the FSE templates when the API is available.
	 */
	public function register(): void {
		if ( ! function_exists( 'register_block_template' ) ) {
			return;
		}

		register_block_template(
			self::NAMESPACE_SLUG . '//chart-single',
			array(
				'title'       => __( 'Astrologer Chart (Single)', 'astrologer-api' ),
				'description' => __(
					'Single chart post template with birth details and the natal chart.',
					'astrologer-api'
				),
				'post_types'  => array( 'astrologer_chart' ),
				'content'     => self::single_template_content(),
			)
		);

		register_block_template(
			self::NAMESPACE_SLUG . '//chart-archive',
			array(
				'title'       => __( 'Astrologer Chart Archive', 'astrologer-api' ),
				'description' => __(
					'Archive listing for astrologer charts with a grid of post summaries.',
					'astrologer-api'
				),
				'post_types'  => array( 'astrologer_chart' ),
				'content'     => self::archive_template_content(),
			)
		);
	}

	/**
	 * Markup for the single chart template.
	 *
	 * @return string
	 */
	private static function single_template_content(): string {
		return '<!-- wp:template-part {"slug":"header","tagName":"header"} /-->

<!-- wp:group {"tagName":"main","layout":{"type":"constrained"}} -->
<main class="wp-block-group"><!-- wp:post-title {"level":1} /-->

<!-- wp:post-featured-image {"isLink":false} /-->

<!-- wp:astrologer-api/natal-chart /-->

<!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:astrologer-api/positions-table /--></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:astrologer-api/aspects-table /--></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->

<!-- wp:post-content /--></main>
<!-- /wp:group -->

<!-- wp:template-part {"slug":"footer","tagName":"footer"} /-->';
	}

	/**
	 * Markup for the chart archive template.
	 *
	 * @return string
	 */
	private static function archive_template_content(): string {
		return '<!-- wp:template-part {"slug":"header","tagName":"header"} /-->

<!-- wp:group {"tagName":"main","layout":{"type":"constrained"}} -->
<main class="wp-block-group"><!-- wp:query-title {"type":"archive"} /-->

<!-- wp:query {"query":{"perPage":12,"postType":"astrologer_chart","inherit":false}} -->
<div class="wp-block-query"><!-- wp:post-template {"layout":{"type":"grid","columnCount":3}} -->
<!-- wp:post-featured-image {"isLink":true} /-->
<!-- wp:post-title {"isLink":true} /-->
<!-- wp:post-excerpt /-->
<!-- /wp:post-template -->

<!-- wp:query-pagination -->
<!-- wp:query-pagination-previous /-->
<!-- wp:query-pagination-numbers /-->
<!-- wp:query-pagination-next /-->
<!-- /wp:query-pagination --></div>
<!-- /wp:query --></main>
<!-- /wp:group -->

<!-- wp:template-part {"slug":"footer","tagName":"footer"} /-->';
	}

	/**
	 * Get the registered template slugs (fully-namespaced).
	 *
	 * @return list<string>
	 */
	public static function get_template_slugs(): array {
		return array(
			self::NAMESPACE_SLUG . '//chart-single',
			self::NAMESPACE_SLUG . '//chart-archive',
		);
	}
}
