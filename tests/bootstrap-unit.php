<?php
/**
 * PHPUnit bootstrap file for pure unit tests (no WordPress).
 *
 * Loads Composer autoloader and Brain\Monkey for WP function mocking.
 * Integration tests use the wp-phpunit bootstrap instead.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

require_once __DIR__ . '/../vendor/autoload.php';
