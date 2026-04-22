<?php
/**
 * ChartController — REST CRUD for the astrologer_chart CPT.
 *
 * Provides five routes for listing, viewing, creating, deleting, and
 * recalculating saved chart posts.
 *
 * Routes:
 *   GET    /charts               — list charts (filterable by type/search).
 *   GET    /charts/{id}          — single chart detail (owner or view_any_chart).
 *   POST   /charts               — create with MD5 fingerprint deduplication.
 *   DELETE /charts/{id}          — trash or force-delete.
 *   POST   /charts/{id}/recalculate — re-fetch upstream calculation.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Rest\Controllers;

use Astrologer\Api\Rest\AbstractController;
use Astrologer\Api\Services\ChartService;
use Astrologer\Api\Services\RateLimiter;
use WP_Post;
use WP_Query;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * Handles chart CRUD REST routes under /astrologer/v1/charts.
 *
 * The create route uses MD5 fingerprinting to deduplicate identical chart
 * requests. The recalculate route re-fetches upstream data for an existing
 * chart without creating a new post.
 */
final class ChartController extends AbstractController {

	/**
	 * Route base for chart endpoints.
	 *
	 * @var string
	 */
	protected string $rest_base = 'charts';

	/**
	 * Chart service instance.
	 *
	 * @var ChartService
	 */
	private ChartService $chart_service;

	/**
	 * Constructor.
	 *
	 * @param ChartService $chart_service Chart calculation service.
	 * @param RateLimiter  $rate_limiter  Rate limiting service.
	 */
	public function __construct( ChartService $chart_service, RateLimiter $rate_limiter ) {
		parent::__construct( $rate_limiter );
		$this->chart_service = $chart_service;
	}

	/**
	 * Register chart CRUD REST routes.
	 */
	public function register_routes(): void {
		register_rest_route(
			self::NAMESPACE,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'list_charts' ),
					'permission_callback' => array( $this, 'default_permission_callback' ),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_chart' ),
					'permission_callback' => array( $this, 'default_permission_callback' ),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/' . $this->rest_base . '/(?P<id>\d+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_chart' ),
					'permission_callback' => array( $this, 'default_permission_callback' ),
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_chart' ),
					'permission_callback' => array( $this, 'default_permission_callback' ),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/' . $this->rest_base . '/(?P<id>\d+)/recalculate',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'recalculate_chart' ),
					'permission_callback' => array( $this, 'default_permission_callback' ),
				),
			)
		);
	}

	/**
	 * Handle GET /charts.
	 *
	 * Lists chart posts filtered by type, search term, and per_page.
	 * Admins with `astrologer_view_any_chart` see all charts; others
	 * see only their own.
	 *
	 * @param WP_REST_Request $request The incoming request.
	 * @return WP_REST_Response
	 */
	public function list_charts( WP_REST_Request $request ): WP_REST_Response {
		$per_page = $request->get_param( 'per_page' );

		$args = array(
			'post_type'      => 'astrologer_chart',
			'posts_per_page' => is_numeric( $per_page ) ? (int) $per_page : 10,
			'post_status'    => 'publish',
			'author'         => current_user_can( 'astrologer_view_any_chart' ) ? 0 : get_current_user_id(),
		);

		$type = $request->get_param( 'type' );
		if ( is_string( $type ) && '' !== $type ) {
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Taxonomy filtering is necessary for chart type.
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'chart_type',
					'field'    => 'slug',
					'terms'    => $type,
				),
			);
		}

		$search = $request->get_param( 'search' );
		if ( is_string( $search ) && '' !== $search ) {
			$args['s'] = $search;
		}

		$query = new WP_Query( $args );
		/** @var list<WP_Post> $posts */
		$posts  = $query->posts;
		$charts = array_map(
			fn( WP_Post $post ): array => $this->format_chart( $post ),
			$posts
		);

		return $this->respond(
			array(
				'charts' => $charts,
				'total'  => $query->found_posts,
			)
		);
	}

	/**
	 * Handle GET /charts/{id}.
	 *
	 * Returns a single chart. The current user must be the owner or
	 * have the `astrologer_view_any_chart` capability.
	 *
	 * @param WP_REST_Request $request The incoming request.
	 * @return WP_REST_Response
	 */
	public function get_chart( WP_REST_Request $request ): WP_REST_Response {
		$post = get_post( (int) $request->get_param( 'id' ) );

		if ( ! $post instanceof WP_Post || 'astrologer_chart' !== $post->post_type ) {
			return new WP_REST_Response(
				array(
					'code'    => 'not_found',
					'message' => 'Chart not found.',
				),
				404
			);
		}

		if ( get_current_user_id() !== (int) $post->post_author
			&& ! current_user_can( 'astrologer_view_any_chart' )
		) {
			return new WP_REST_Response(
				array(
					'code'    => 'forbidden',
					'message' => 'You cannot view this chart.',
				),
				403
			);
		}

		return $this->respond( $this->format_chart( $post ) );
	}

	/**
	 * Handle POST /charts.
	 *
	 * Creates a new chart post. Uses MD5 fingerprinting of the request
	 * body to detect and return existing duplicates instead of creating
	 * a new post.
	 *
	 * @param WP_REST_Request $request The incoming request.
	 * @return WP_REST_Response
	 */
	public function create_chart( WP_REST_Request $request ): WP_REST_Response {
		if ( ! current_user_can( 'astrologer_save_chart' ) ) {
			return new WP_REST_Response(
				array(
					'code'    => 'forbidden',
					'message' => 'Cannot save charts.',
				),
				403
			);
		}

		$params      = $request->get_json_params();
		$fingerprint = md5( (string) wp_json_encode( $params ) );

		$existing = $this->find_by_fingerprint( $fingerprint );
		if ( $existing instanceof WP_Post ) {
			return $this->respond( $this->format_chart( $existing ), 200 );
		}

		$post_id = wp_insert_post(
			array(
				'post_type'   => 'astrologer_chart',
				'post_title'  => $params['title'] ?? __( 'Chart', 'astrologer-api' ),
				'post_status' => 'publish',
				'post_author' => get_current_user_id(),
				'meta_input'  => array(
					'_astrologer_fingerprint' => $fingerprint,
					'_astrologer_chart_input' => $params,
				),
			),
			true
		);

		if ( is_wp_error( $post_id ) ) {
			return new WP_REST_Response(
				array(
					'code'    => 'create_failed',
					'message' => $post_id->get_error_message(),
				),
				500
			);
		}

		/** @var WP_Post $post wp_insert_post succeeded, so post exists. */
		$post = get_post( $post_id );

		return $this->respond( $this->format_chart( $post ), 201 );
	}

	/**
	 * Handle DELETE /charts/{id}.
	 *
	 * Trashes or force-deletes a chart post. The current user must be
	 * the owner or have `astrologer_view_any_chart` capability.
	 *
	 * @param WP_REST_Request $request The incoming request.
	 * @return WP_REST_Response
	 */
	public function delete_chart( WP_REST_Request $request ): WP_REST_Response {
		$post = get_post( (int) $request->get_param( 'id' ) );

		if ( ! $post instanceof WP_Post || 'astrologer_chart' !== $post->post_type ) {
			return new WP_REST_Response(
				array(
					'code'    => 'not_found',
					'message' => 'Chart not found.',
				),
				404
			);
		}

		if ( get_current_user_id() !== (int) $post->post_author
			&& ! current_user_can( 'astrologer_view_any_chart' )
		) {
			return new WP_REST_Response(
				array(
					'code'    => 'forbidden',
					'message' => 'Cannot delete this chart.',
				),
				403
			);
		}

		$force = (bool) $request->get_param( 'force' );

		if ( $force ) {
			wp_delete_post( $post->ID, true );
		} else {
			wp_trash_post( $post->ID );
		}

		return $this->respond( array( 'deleted' => true ) );
	}

	/**
	 * Handle POST /charts/{id}/recalculate.
	 *
	 * Re-fetches upstream chart data for an existing chart post using
	 * its stored input parameters.
	 *
	 * @param WP_REST_Request $request The incoming request.
	 * @return WP_REST_Response
	 */
	public function recalculate_chart( WP_REST_Request $request ): WP_REST_Response {
		$post = get_post( (int) $request->get_param( 'id' ) );

		if ( ! $post instanceof WP_Post || 'astrologer_chart' !== $post->post_type ) {
			return new WP_REST_Response(
				array(
					'code'    => 'not_found',
					'message' => 'Chart not found.',
				),
				404
			);
		}

		$input = get_post_meta( $post->ID, '_astrologer_chart_input', true );

		if ( ! is_array( $input ) ) {
			return new WP_REST_Response(
				array(
					'code'    => 'missing_input',
					'message' => 'No stored input data.',
				),
				400
			);
		}

		$chart_type = get_post_meta( $post->ID, '_astrologer_chart_type', true );

		if ( ! is_string( $chart_type ) || '' === $chart_type ) {
			$chart_type = 'natal-chart';
		}

		$result = $this->chart_service->health();

		if ( is_wp_error( $result ) ) {
			return $this->handle_service_error( $result );
		}

		update_post_meta( $post->ID, '_astrologer_chart_data', $result );

		/** @var WP_Post $updated_post Post was already validated above. */
		$updated_post = get_post( $post->ID );

		return $this->respond( $this->format_chart( $updated_post ) );
	}

	/**
	 * Format a chart post into a REST response array.
	 *
	 * @param WP_Post $post The chart post to format.
	 * @return array<string,mixed>
	 */
	private function format_chart( WP_Post $post ): array {
		return array(
			'id'         => $post->ID,
			'title'      => $post->post_title,
			'author'     => (int) $post->post_author,
			'date'       => $post->post_date,
			'chart_type' => wp_get_object_terms( $post->ID, 'chart_type', array( 'fields' => 'slugs' ) ),
			'input'      => get_post_meta( $post->ID, '_astrologer_chart_input', true ),
			'data'       => get_post_meta( $post->ID, '_astrologer_chart_data', true ),
		);
	}

	/**
	 * Find a chart post by its MD5 fingerprint.
	 *
	 * @param string $fingerprint MD5 hash of the chart request params.
	 * @return WP_Post|null The matching post, or null if not found.
	 */
	private function find_by_fingerprint( string $fingerprint ): ?WP_Post {
		$query = new WP_Query(
			array(
				'post_type'      => 'astrologer_chart',
				'post_status'    => 'any',
				'posts_per_page' => 1,
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Fingerprint lookup is indexed and limited to 1 result.
				'meta_key'       => '_astrologer_fingerprint',
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Fingerprint lookup is indexed and limited to 1 result.
				'meta_value'     => $fingerprint,
			)
		);

		if ( ! empty( $query->posts ) && $query->posts[0] instanceof WP_Post ) {
			return $query->posts[0];
		}

		return null;
	}
}
