<?php
/**
 * Custom Post Type for astrological charts.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\PostType;

use Astrologer\Api\Support\Contracts\Bootable;

/**
 * Registers the astrologer_chart custom post type.
 *
 * Private by default, visible in admin, REST-enabled for block bindings.
 * Uses custom capability_type for granular access control.
 */
final class AstrologerChartPostType implements Bootable {

	/**
	 * Post type slug.
	 *
	 * @var string
	 */
	public const SLUG = 'astrologer_chart';

	/**
	 * Register WordPress hooks.
	 */
	public function boot(): void {
		add_action( 'init', array( $this, 'register' ) );
	}

	/**
	 * Register the custom post type.
	 */
	public function register(): void {
		$args = array(
			'labels'              => $this->get_labels(),
			'public'              => false,
			'show_ui'             => true,
			'show_in_rest'        => true,
			'supports'            => array( 'title', 'author', 'custom-fields' ),
			'has_archive'         => false,
			'rewrite'             => false,
			'capability_type'     => array( 'astrologer_chart', 'astrologer_charts' ),
			'map_meta_cap'        => true,
			'menu_icon'           => 'dashicons-star-filled',
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
		);

		/** This filter is documented in PLAN/F1-core-data-layer.md §F1.7. */
		$args = apply_filters( 'astrologer_api/cpt_args', $args );

		register_post_type( self::SLUG, $args );
	}

	/**
	 * Generate labels for the post type.
	 *
	 * @return array<string, string>
	 */
	private function get_labels(): array {
		return array(
			'name'                  => __( 'Charts', 'astrologer-api' ),
			'singular_name'         => __( 'Chart', 'astrologer-api' ),
			'add_new'               => __( 'Add New Chart', 'astrologer-api' ),
			'add_new_item'          => __( 'Add New Chart', 'astrologer-api' ),
			'edit_item'             => __( 'Edit Chart', 'astrologer-api' ),
			'new_item'              => __( 'New Chart', 'astrologer-api' ),
			'view_item'             => __( 'View Chart', 'astrologer-api' ),
			'view_items'            => __( 'View Charts', 'astrologer-api' ),
			'search_items'          => __( 'Search Charts', 'astrologer-api' ),
			'not_found'             => __( 'No charts found.', 'astrologer-api' ),
			'not_found_in_trash'    => __( 'No charts found in trash.', 'astrologer-api' ),
			'all_items'             => __( 'All Charts', 'astrologer-api' ),
			'archives'              => __( 'Chart Archives', 'astrologer-api' ),
			'attributes'            => __( 'Chart Attributes', 'astrologer-api' ),
			'insert_into_item'      => __( 'Insert into chart', 'astrologer-api' ),
			'uploaded_to_this_item' => __( 'Uploaded to this chart', 'astrologer-api' ),
			'filter_items_list'     => __( 'Filter charts list', 'astrologer-api' ),
			'items_list_navigation' => __( 'Charts list navigation', 'astrologer-api' ),
			'items_list'            => __( 'Charts list', 'astrologer-api' ),
		);
	}
}
