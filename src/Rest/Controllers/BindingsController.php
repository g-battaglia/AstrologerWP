<?php
/**
 * BindingsController — REST endpoint for block bindings field metadata.
 *
 * Provides a single GET route returning field descriptors grouped by
 * category. These descriptors tell the block editor which chart data
 * fields are available for binding.
 *
 * Route:
 *   GET /bindings/fields — returns 26 field descriptors across 8 groups.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Rest\Controllers;

use Astrologer\Api\Rest\AbstractController;
use Astrologer\Api\Services\RateLimiter;
use WP_REST_Response;
use WP_REST_Server;

/**
 * Handles the block bindings field metadata REST route.
 *
 * Returns a structured map of all available chart data fields,
 * grouped by category (birth_data, chart, positions, aspects,
 * distributions, ai_context, compatibility, moon_phase).
 */
final class BindingsController extends AbstractController {

	/**
	 * Route base for bindings endpoints.
	 *
	 * @var string
	 */
	protected string $rest_base = 'bindings';

	/**
	 * Register the bindings fields route.
	 */
	public function register_routes(): void {
		register_rest_route(
			self::NAMESPACE,
			'/' . $this->rest_base . '/fields',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_fields' ),
					'permission_callback' => array( $this, 'default_permission_callback' ),
				),
			)
		);
	}

	/**
	 * Handle GET /bindings/fields.
	 *
	 * Returns all available block binding field descriptors grouped by
	 * category. Each field includes a dot-notation path, human-readable
	 * label, and data type.
	 *
	 * @return WP_REST_Response
	 */
	public function get_fields(): WP_REST_Response {
		$fields = array(
			'birth_data'    => array(
				array(
					'field' => 'subject.name',
					'label' => 'Name',
					'type'  => 'string',
				),
				array(
					'field' => 'subject.year',
					'label' => 'Birth Year',
					'type'  => 'number',
				),
				array(
					'field' => 'subject.month',
					'label' => 'Birth Month',
					'type'  => 'number',
				),
				array(
					'field' => 'subject.day',
					'label' => 'Birth Day',
					'type'  => 'number',
				),
				array(
					'field' => 'subject.city',
					'label' => 'Birth City',
					'type'  => 'string',
				),
			),
			'chart'         => array(
				array(
					'field' => 'chart.svg',
					'label' => 'Chart SVG',
					'type'  => 'html',
				),
				array(
					'field' => 'chart.type',
					'label' => 'Chart Type',
					'type'  => 'string',
				),
			),
			'positions'     => array(
				array(
					'field' => 'positions.sun.sign',
					'label' => 'Sun Sign',
					'type'  => 'string',
				),
				array(
					'field' => 'positions.moon.sign',
					'label' => 'Moon Sign',
					'type'  => 'string',
				),
				array(
					'field' => 'positions.ascendant.sign',
					'label' => 'Ascendant Sign',
					'type'  => 'string',
				),
				array(
					'field' => 'positions.sun.degree',
					'label' => 'Sun Degree',
					'type'  => 'string',
				),
				array(
					'field' => 'positions.moon.degree',
					'label' => 'Moon Degree',
					'type'  => 'string',
				),
			),
			'aspects'       => array(
				array(
					'field' => 'aspects.count',
					'label' => 'Aspects Count',
					'type'  => 'number',
				),
				array(
					'field' => 'aspects.list',
					'label' => 'Aspects List',
					'type'  => 'array',
				),
			),
			'distributions' => array(
				array(
					'field' => 'distributions.elements',
					'label' => 'Elements Distribution',
					'type'  => 'object',
				),
				array(
					'field' => 'distributions.modalities',
					'label' => 'Modalities Distribution',
					'type'  => 'object',
				),
				array(
					'field' => 'distributions.polarities',
					'label' => 'Polarities Distribution',
					'type'  => 'object',
				),
			),
			'ai_context'    => array(
				array(
					'field' => 'ai_context.summary',
					'label' => 'AI Summary',
					'type'  => 'string',
				),
				array(
					'field' => 'ai_context.keywords',
					'label' => 'AI Keywords',
					'type'  => 'array',
				),
			),
			'compatibility' => array(
				array(
					'field' => 'compatibility.score',
					'label' => 'Compatibility Score',
					'type'  => 'number',
				),
				array(
					'field' => 'compatibility.love',
					'label' => 'Love Score',
					'type'  => 'number',
				),
				array(
					'field' => 'compatibility.communication',
					'label' => 'Communication Score',
					'type'  => 'number',
				),
				array(
					'field' => 'compatibility.conflict',
					'label' => 'Conflict Score',
					'type'  => 'number',
				),
			),
			'moon_phase'    => array(
				array(
					'field' => 'moon_phase.name',
					'label' => 'Moon Phase Name',
					'type'  => 'string',
				),
				array(
					'field' => 'moon_phase.emoji',
					'label' => 'Moon Phase Emoji',
					'type'  => 'string',
				),
				array(
					'field' => 'moon_phase.illumination',
					'label' => 'Moon Illumination',
					'type'  => 'number',
				),
			),
		);

		/**
		 * Filter the block bindings field descriptors.
		 *
		 * @param array<string,array<int,array<string,string>>> $fields Field groups.
		 */
		// phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores -- Slash-separated namespace convention.
		$fields = apply_filters( 'astrologer_api/bindings_fields', $fields );

		return $this->respond( array( 'groups' => $fields ) );
	}
}
