<?php
/**
 * Temporary REST controller for the Interactivity API spike (F0.5).
 *
 * This file will be moved to tests/spikes/ after the spike is validated.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Rest;

use Astrologer\Api\Support\Contracts\Bootable;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * Registers a mock POST endpoint returning hardcoded chart SVG + positions.
 * Used by the spike-birth-form block to validate the Interactivity API.
 */
final class SpikeController implements Bootable {

	/**
	 * Register the REST route on boot.
	 */
	public function boot(): void {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register the mock spike endpoint.
	 */
	public function register_routes(): void {
		register_rest_route(
			'astrologer/v1',
			'/spike',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'handle_spike' ),
					'permission_callback' => '__return_true',
				),
			)
		);
	}

	/**
	 * Return a hardcoded mock response with SVG and positions.
	 *
	 * @param WP_REST_Request $request The incoming request.
	 * @return WP_REST_Response
	 */
	public function handle_spike( WP_REST_Request $request ): WP_REST_Response {
		$params = $request->get_json_params();

		$name = isset( $params['name'] ) ? sanitize_text_field( (string) $params['name'] ) : '';
		$date = isset( $params['date'] ) ? sanitize_text_field( (string) $params['date'] ) : '';

		$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200" width="200" height="200">'
			. '<circle cx="100" cy="100" r="95" fill="none" stroke="#333" stroke-width="1"/>'
			. '<text x="100" y="40" text-anchor="middle" font-size="8" fill="#666">Aries</text>'
			. '<text x="160" y="105" text-anchor="middle" font-size="8" fill="#666">Cancer</text>'
			. '<text x="100" y="175" text-anchor="middle" font-size="8" fill="#666">Libra</text>'
			. '<text x="40" y="105" text-anchor="middle" font-size="8" fill="#666">Capricorn</text>'
			. '<circle cx="120" cy="80" r="4" fill="#e74c3c" title="Sun"/>'
			. '<circle cx="80" cy="120" r="3" fill="#3498db" title="Moon"/>'
			. '<text x="100" y="100" text-anchor="middle" font-size="10" fill="#333">'
			. esc_html( $name )
			. '</text>'
			. '<text x="100" y="115" text-anchor="middle" font-size="8" fill="#999">'
			. esc_html( $date )
			. '</text>'
			. '</svg>';

		$positions = array(
			array(
				'point'        => 'Sun',
				'sign'         => 'Aries',
				'degree'       => 15.5,
				'house'        => 1,
				'isRetrograde' => false,
			),
			array(
				'point'        => 'Moon',
				'sign'         => 'Cancer',
				'degree'       => 22.3,
				'house'        => 4,
				'isRetrograde' => false,
			),
			array(
				'point'        => 'Mercury',
				'sign'         => 'Pisces',
				'degree'       => 8.1,
				'house'        => 12,
				'isRetrograde' => true,
			),
		);

		return new WP_REST_Response(
			array(
				'svg'       => $svg,
				'positions' => $positions,
			),
			200
		);
	}
}
