<?php
/**
 * Bootable interface for classes that register WordPress hooks.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Support\Contracts;

/**
 * Interface for classes that hook into WordPress on boot.
 */
interface Bootable {
	/**
	 * Register WordPress hooks and filters.
	 */
	public function boot(): void;
}
