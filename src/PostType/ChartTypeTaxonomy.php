<?php
/**
 * Taxonomy for chart types (natal, synastry, transit, etc.).
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\PostType;

use Astrologer\Api\Enums\ChartType;
use Astrologer\Api\Support\Contracts\Bootable;

/**
 * Registers the astrologer_chart_type taxonomy with fixed terms
 * matching the ChartType enum cases (excluding compatibility and moon_phase).
 */
final class ChartTypeTaxonomy implements Bootable {

	/**
	 * Taxonomy slug.
	 *
	 * @var string
	 */
	public const SLUG = 'astrologer_chart_type';

	/**
	 * Register WordPress hooks.
	 */
	public function boot(): void {
		add_action( 'init', array( $this, 'register' ) );
	}

	/**
	 * Register the taxonomy.
	 */
	public function register(): void {
		$args = array(
			'labels'            => $this->get_labels(),
			'public'            => false,
			'show_ui'           => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'hierarchical'      => false,
			'rewrite'           => false,
			'capabilities'      => array(
				'manage_terms' => 'astrologer_manage_settings',
				'edit_terms'   => 'astrologer_manage_settings',
				'delete_terms' => 'astrologer_manage_settings',
				'assign_terms' => 'edit_astrologer_charts',
			),
		);

		register_taxonomy( self::SLUG, array( AstrologerChartPostType::SLUG ), $args );
	}

	/**
	 * Generate labels for the taxonomy.
	 *
	 * @return array<string, string>
	 */
	private function get_labels(): array {
		return array(
			'name'                       => __( 'Chart Types', 'astrologer-api' ),
			'singular_name'              => __( 'Chart Type', 'astrologer-api' ),
			'search_items'               => __( 'Search Chart Types', 'astrologer-api' ),
			'popular_items'              => __( 'Popular Chart Types', 'astrologer-api' ),
			'all_items'                  => __( 'All Chart Types', 'astrologer-api' ),
			'edit_item'                  => __( 'Edit Chart Type', 'astrologer-api' ),
			'view_item'                  => __( 'View Chart Type', 'astrologer-api' ),
			'update_item'                => __( 'Update Chart Type', 'astrologer-api' ),
			'add_new_item'               => __( 'Add New Chart Type', 'astrologer-api' ),
			'new_item_name'              => __( 'New Chart Type Name', 'astrologer-api' ),
			'separate_items_with_commas' => __( 'Separate chart types with commas', 'astrologer-api' ),
			'add_or_remove_items'        => __( 'Add or remove chart types', 'astrologer-api' ),
			'choose_from_most_used'      => __( 'Choose from the most used chart types', 'astrologer-api' ),
			'not_found'                  => __( 'No chart types found.', 'astrologer-api' ),
			'no_terms'                   => __( 'No chart types', 'astrologer-api' ),
			'items_list_navigation'      => __( 'Chart types list navigation', 'astrologer-api' ),
			'items_list'                 => __( 'Chart types list', 'astrologer-api' ),
			'most_used'                  => __( 'Most Used', 'astrologer-api' ),
			'back_to_items'              => __( '&larr; Go to Chart Types', 'astrologer-api' ),
		);
	}

	/**
	 * Seed the default chart type terms. Called during activation.
	 *
	 * Terms correspond to ChartType enum cases that make sense as
	 * taxonomy terms: natal, synastry, transit, composite,
	 * solar_return, lunar_return, now.
	 *
	 * @return void
	 */
	public static function seed_terms(): void {
		$terms = array(
			ChartType::Natal,
			ChartType::Synastry,
			ChartType::Transit,
			ChartType::Composite,
			ChartType::SolarReturn,
			ChartType::LunarReturn,
			ChartType::Now,
		);

		foreach ( $terms as $chart_type ) {
			$slug  = $chart_type->value;
			$label = $chart_type->label();

			if ( ! term_exists( $slug, self::SLUG ) ) {
				wp_insert_term(
					$label,
					self::SLUG,
					array( 'slug' => $slug )
				);
			}
		}
	}
}
