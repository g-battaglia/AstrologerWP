<?php
/**
 * ActiveAspect value object — an aspect type with its configured orb.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\ValueObjects;

use Astrologer\Api\Enums\AspectType;

/**
 * Immutable value object representing an active aspect configuration.
 *
 * Pairs an aspect type with a user-configurable orb override.
 */
final readonly class ActiveAspect {

	/**
	 * Constructor.
	 *
	 * @param AspectType $type The aspect type.
	 * @param float      $orb  Orb in degrees (must be >= 0).
	 */
	public function __construct(
		public AspectType $type,
		public float $orb,
	) {
		if ( $orb < 0.0 ) {
			throw new \InvalidArgumentException(
				esc_html( sprintf( 'Orb must be non-negative, got %s.', $orb ) )
			);
		}
	}

	/**
	 * Create from an associative array.
	 *
	 * Expected format: ['type' => 'Conjunction', 'orb' => 8.0]
	 *
	 * @param array<string,mixed> $data Keyed array with aspect fields.
	 * @return self
	 */
	public static function from_array( array $data ): self {
		$type = AspectType::from( (string) ( $data['type'] ?? '' ) );
		$orb  = isset( $data['orb'] ) ? (float) $data['orb'] : $type->default_orb();

		return new self( $type, $orb );
	}

	/**
	 * Convert to an associative array suitable for API payloads.
	 *
	 * @return array{type:string,orb:float}
	 */
	public function to_array(): array {
		return array(
			'type' => $this->type->value,
			'orb'  => $this->orb,
		);
	}
}
