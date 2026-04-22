<?php
/**
 * Integration suite bootstrap shim.
 *
 * Delegates to tests/bootstrap.php (the main integration bootstrap) so that
 * a phpunit configuration pointing at the Integration/ suite directly still
 * initializes the WordPress test environment.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

require_once __DIR__ . '/../bootstrap.php';
