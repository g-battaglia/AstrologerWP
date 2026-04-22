<?php
/**
 * RestServiceProvider — registers all REST controllers on rest_api_init.
 *
 * Accepts a variadic list of AbstractController instances and calls
 * register_routes() on each during the `rest_api_init` action.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Rest;

use Astrologer\Api\Support\Contracts\Bootable;

/**
 * Service provider that boots all REST controllers.
 *
 * Instantiated with all concrete controllers via the DI container,
 * then hooks into `rest_api_init` to register their routes.
 */
final class RestServiceProvider implements Bootable {

	/**
	 * Registered REST controllers.
	 *
	 * @var array<int|string, AbstractController>
	 */
	private array $controllers;

	/**
	 * Constructor.
	 *
	 * @param AbstractController ...$controllers Controllers to register.
	 */
	public function __construct( AbstractController ...$controllers ) {
		$this->controllers = $controllers;
	}

	/**
	 * Hook into rest_api_init to register all controller routes.
	 */
	public function boot(): void {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register routes for all controllers.
	 *
	 * Called during the `rest_api_init` action.
	 */
	public function register_routes(): void {
		foreach ( $this->controllers as $controller ) {
			$controller->register_routes();
		}
	}
}
