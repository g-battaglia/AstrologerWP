<?php
/**
 * Lightweight service container with lazy resolution and singleton caching.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api;

use Closure;

/**
 * Array-based container. No external dependencies.
 */
final class Container {

	/**
	 * Registered factories and instances.
	 *
	 * @var array<string, object>
	 */
	private array $bindings = array();

	/**
	 * Resolved singleton instances.
	 *
	 * @var array<string, object>
	 */
	private array $instances = array();

	/**
	 * Register a service.
	 *
	 * @param string  $id      Service identifier.
	 * @param object  $factory Factory closure or concrete instance.
	 */
	public function set( string $id, object $factory ): void {
		$this->bindings[ $id ] = $factory;

		// If replacing an already-resolved service, clear cached instance.
		unset( $this->instances[ $id ] );
	}

	/**
	 * Resolve a service. Lazy-evaluates closures and caches the result.
	 *
	 * @param string $id Service identifier.
	 * @return object The resolved instance.
	 * @throws \OutOfBoundsException When the service is not registered.
	 */
	public function get( string $id ): object {
		if ( isset( $this->instances[ $id ] ) ) {
			return $this->instances[ $id ];
		}

		if ( ! $this->has( $id ) ) {
			throw new \OutOfBoundsException(
				sprintf( 'Container entry "%s" is not registered.', $id )
			);
		}

		$entry = $this->bindings[ $id ];

		if ( $entry instanceof Closure ) {
			$resolved = $entry( $this );
		} else {
			$resolved = $entry;
		}

		$this->instances[ $id ] = $resolved;

		return $resolved;
	}

	/**
	 * Check whether a service is registered.
	 *
	 * @param string $id Service identifier.
	 * @return bool
	 */
	public function has( string $id ): bool {
		return isset( $this->bindings[ $id ] );
	}
}
