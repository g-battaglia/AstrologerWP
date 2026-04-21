<?php
/**
 * BirthDataRepository — user meta storage for birth data.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Repository;

use Astrologer\Api\Support\Contracts\Bootable;
use Astrologer\Api\ValueObjects\BirthData;

/**
 * Repository for storing and retrieving birth data as user meta.
 *
 * Each user has at most one birth data record stored in the
 * `astrologer_birth_data` user meta key. The meta is registered
 * with show_in_rest so it is accessible via the WP REST API.
 */
final class BirthDataRepository implements Bootable {

	/**
	 * User meta key for birth data storage.
	 *
	 * @var string
	 */
	private const META_KEY = 'astrologer_birth_data';

	/**
	 * Register user meta on boot so it is available via REST.
	 */
	public function boot(): void {
		add_action( 'init', array( $this, 'register_user_meta' ) );
	}

	/**
	 * Register the astrologer_birth_data user meta with REST schema.
	 */
	public function register_user_meta(): void {
		register_meta(
			'user',
			self::META_KEY,
			array(
				'type'         => 'object',
				'description'  => __( 'Birth data for astrology calculations', 'astrologer-api' ),
				'single'       => true,
				'show_in_rest' => array(
					'schema' => array(
						'type'       => 'object',
						'properties' => array(
							'name'         => array( 'type' => 'string' ),
							'year'         => array( 'type' => 'integer' ),
							'month'        => array( 'type' => 'integer' ),
							'day'          => array( 'type' => 'integer' ),
							'hour'         => array( 'type' => 'integer' ),
							'minute'       => array( 'type' => 'integer' ),
							'iso_datetime' => array( 'type' => 'string' ),
							'location'     => array(
								'type'       => 'object',
								'properties' => array(
									'latitude'  => array( 'type' => 'number' ),
									'longitude' => array( 'type' => 'number' ),
									'timezone'  => array( 'type' => 'string' ),
									'altitude'  => array( 'type' => 'number' ),
									'is_dst'    => array( 'type' => 'boolean' ),
									'city'      => array( 'type' => 'string' ),
									'nation'    => array( 'type' => 'string' ),
								),
							),
						),
					),
				),
			)
		);
	}

	/**
	 * Get the stored birth data for a user.
	 *
	 * @param int $user_id WordPress user ID.
	 * @return BirthData|null Birth data, or null if not set or invalid.
	 */
	public function get_for_user( int $user_id ): ?BirthData {
		$raw = get_user_meta( $user_id, self::META_KEY, true );

		if ( ! is_array( $raw ) || empty( $raw ) ) {
			return null;
		}

		return BirthData::from_array( $raw );
	}

	/**
	 * Store birth data for a user. Overwrites any existing data.
	 *
	 * @param int       $user_id WordPress user ID.
	 * @param BirthData $data    Birth data to store.
	 */
	public function set_for_user( int $user_id, BirthData $data ): void {
		update_user_meta( $user_id, self::META_KEY, $data->to_array() );
	}

	/**
	 * Remove birth data for a user.
	 *
	 * @param int $user_id WordPress user ID.
	 */
	public function clear_for_user( int $user_id ): void {
		delete_user_meta( $user_id, self::META_KEY );
	}
}
