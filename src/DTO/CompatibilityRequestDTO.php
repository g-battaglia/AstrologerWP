<?php
/**
 * CompatibilityRequestDTO — request payload for compatibility score.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\DTO;

/**
 * Data Transfer Object for Ciro Discepolo compatibility score requests.
 *
 * Computes a numeric compatibility score between two subjects based on
 * their synastry aspects.
 */
final readonly class CompatibilityRequestDTO {

	/**
	 * Constructor.
	 *
	 * @param SubjectDTO $first_subject  First subject.
	 * @param SubjectDTO $second_subject Second subject.
	 * @param bool       $ai_ctx         Whether to include AI context text.
	 */
	public function __construct(
		public SubjectDTO $first_subject,
		public SubjectDTO $second_subject,
		public bool $ai_ctx = true,
	) {
	}

	/**
	 * Create from an associative array.
	 *
	 * @param array<string,mixed> $data Keyed array with compatibility request fields.
	 * @return self
	 */
	public static function from_array( array $data ): self {
		$first  = SubjectDTO::from_array( $data['first_subject'] ?? array() );
		$second = SubjectDTO::from_array( $data['second_subject'] ?? array() );

		return new self(
			first_subject: $first,
			second_subject: $second,
			ai_ctx: (bool) ( $data['ai_ctx'] ?? true ),
		);
	}

	/**
	 * Convert to an associative array for the upstream API payload.
	 *
	 * @return array<string,mixed>
	 */
	public function to_array(): array {
		return array(
			'first_subject'      => $this->first_subject->to_array(),
			'second_subject'     => $this->second_subject->to_array(),
			'include_ai_context' => $this->ai_ctx,
		);
	}
}
