<?php
/**
 * SettingsController — REST endpoints for admin settings management.
 *
 * Provides three routes for reading, updating, and testing API settings.
 * All routes require the `astrologer_manage_settings` capability.
 *
 * Routes:
 *   GET  /settings              — returns all settings (API key masked).
 *   POST /settings              — partial update with allowlist filtering.
 *   POST /settings/test-connection — tests upstream API with provided key.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Rest\Controllers;

use Astrologer\Api\Repository\SettingsRepository;
use Astrologer\Api\Rest\AbstractController;
use Astrologer\Api\Services\RateLimiter;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * Handles admin settings REST routes under /astrologer/v1/settings.
 *
 * The GET route masks the API key by returning a boolean `has_api_key`
 * instead of the actual key. The POST route uses an allowlist to prevent
 * injection of unexpected fields. The test-connection route validates
 * connectivity without persisting the key.
 */
final class SettingsController extends AbstractController {

	/**
	 * Route base for settings endpoints.
	 *
	 * @var string
	 */
	protected string $rest_base = 'settings';

	/**
	 * Settings repository instance.
	 *
	 * @var SettingsRepository
	 */
	private SettingsRepository $settings;

	/**
	 * Allowed keys for settings update.
	 *
	 * @var list<string>
	 */
	private const ALLOWED_KEYS = array(
		'rapidapi_key',
		'geonames_username',
		'school',
		'language',
		'ui_level',
		'cron',
		'setup_completed',
	);

	/**
	 * Constructor.
	 *
	 * @param SettingsRepository $settings    Plugin settings repository.
	 * @param RateLimiter        $rate_limiter Rate limiting service.
	 */
	public function __construct( SettingsRepository $settings, RateLimiter $rate_limiter ) {
		parent::__construct( $rate_limiter );
		$this->settings = $settings;
	}

	/**
	 * Register settings REST routes.
	 */
	public function register_routes(): void {
		register_rest_route(
			self::NAMESPACE,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_settings' ),
					'permission_callback' => array( $this, 'admin_permission_check' ),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'update_settings' ),
					'permission_callback' => array( $this, 'admin_permission_check' ),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/' . $this->rest_base . '/test-connection',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'test_connection' ),
					'permission_callback' => array( $this, 'admin_permission_check' ),
				),
			)
		);
	}

	/**
	 * Permission check requiring astrologer_manage_settings capability.
	 *
	 * @return bool True if current user can manage settings.
	 */
	public function admin_permission_check(): bool {
		return current_user_can( 'astrologer_manage_settings' );
	}

	/**
	 * Handle GET /settings.
	 *
	 * Returns all settings with the API key masked — replaced by a boolean
	 * `has_api_key` field indicating whether a key is configured.
	 *
	 * @return WP_REST_Response
	 */
	public function get_settings(): WP_REST_Response {
		$all = $this->settings->all();

		// Mask sensitive API key — expose only a boolean flag.
		$has_key = isset( $all['rapidapi_key'] )
			&& is_string( $all['rapidapi_key'] )
			&& '' !== $all['rapidapi_key'];

		unset( $all['rapidapi_key'] );
		$all['has_api_key'] = $has_key;

		return $this->respond( $all );
	}

	/**
	 * Handle POST /settings.
	 *
	 * Accepts a partial JSON body and updates only the allowed keys.
	 * Fires the `astrologer_api/settings_updated` action after saving.
	 *
	 * @param WP_REST_Request $request The incoming request.
	 * @return WP_REST_Response
	 */
	public function update_settings( WP_REST_Request $request ): WP_REST_Response {
		$params = $request->get_json_params();

		if ( empty( $params ) ) {
			return new WP_REST_Response(
				array(
					'code'    => 'empty_body',
					'message' => 'Request body is empty.',
				),
				400
			);
		}

		$filtered = array_intersect_key( $params, array_flip( self::ALLOWED_KEYS ) );

		$this->settings->update( $filtered );

		/**
		 * Fires after settings are updated via REST.
		 *
		 * @param array<string,mixed> $filtered The filtered settings that were saved.
		 */
		// phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores -- Slash-separated namespace convention.
		do_action( 'astrologer_api/settings_updated', $filtered );

		return $this->get_settings();
	}

	/**
	 * Handle POST /settings/test-connection.
	 *
	 * Tests upstream API connectivity with the provided API key.
	 * The key is NOT persisted — it is only used for the test request.
	 *
	 * @param WP_REST_Request $request The incoming request.
	 * @return WP_REST_Response
	 */
	public function test_connection( WP_REST_Request $request ): WP_REST_Response {
		$api_key = $request->get_param( 'api_key' );

		if ( ! is_string( $api_key ) || '' === $api_key ) {
			return new WP_REST_Response(
				array(
					'connected' => false,
					'message'   => 'API key is required.',
				),
				400
			);
		}

		$response = wp_remote_get(
			'https://astrologer.p.rapidapi.com/api/v4/health',
			array(
				'headers' => array(
					'X-RapidAPI-Key'  => $api_key,
					'X-RapidAPI-Host' => 'astrologer.p.rapidapi.com',
				),
				'timeout' => 10,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $this->respond(
				array(
					'connected' => false,
					'message'   => $response->get_error_message(),
				)
			);
		}

		$code      = wp_remote_retrieve_response_code( $response );
		$connected = $code >= 200 && $code < 300;

		return $this->respond(
			array(
				'connected' => $connected,
				'message'   => $connected
					? 'Connection successful.'
					: 'Connection failed (HTTP ' . $code . ').',
			)
		);
	}
}
