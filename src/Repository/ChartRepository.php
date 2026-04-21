<?php
/**
 * ChartRepository — CRUD operations for the astrologer_chart CPT.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Repository;

use Astrologer\Api\DTO\ChartRequestDTO;
use Astrologer\Api\DTO\ChartResponseDTO;
use Astrologer\Api\Enums\ChartType;
use Astrologer\Api\PostType\AstrologerChartPostType;
use Astrologer\Api\Support\Contracts\Bootable;
use Astrologer\Api\ValueObjects\BirthData;
use Astrologer\Api\ValueObjects\ChartOptions;
use Astrologer\Api\ValueObjects\ChartRecord;

/**
 * Repository for creating, reading, updating, and deleting chart CPT posts.
 *
 * Each chart post stores birth data, chart options, and optionally the
 * upstream API response (SVG + data) as post meta.
 */
final class ChartRepository implements Bootable {

	/**
	 * Meta key for the chart type.
	 *
	 * @var string
	 */
	private const META_CHART_TYPE = 'chart_type';

	/**
	 * Meta key for birth data (JSON).
	 *
	 * @var string
	 */
	private const META_BIRTH_DATA = 'birth_data';

	/**
	 * Meta key for chart options (JSON).
	 *
	 * @var string
	 */
	private const META_CHART_OPTIONS = 'chart_options';

	/**
	 * Meta key for the SVG response.
	 *
	 * @var string
	 */
	private const META_RESPONSE_SVG = 'response_svg';

	/**
	 * Meta key for the full response data (JSON).
	 *
	 * @var string
	 */
	private const META_RESPONSE_DATA = 'response_data';

	/**
	 * Register post meta on boot so they are available via REST.
	 */
	public function boot(): void {
		add_action( 'init', array( $this, 'register_post_meta' ) );
	}

	/**
	 * Register post meta fields for the astrologer_chart CPT.
	 */
	public function register_post_meta(): void {
		$meta_fields = array(
			self::META_CHART_TYPE    => array(
				'type'         => 'string',
				'description'  => __( 'Chart type identifier', 'astrologer-api' ),
				'single'       => true,
				'show_in_rest' => true,
				'default'      => ChartType::Natal->value,
			),
			self::META_BIRTH_DATA    => array(
				'type'         => 'object',
				'description'  => __( 'Subject birth data', 'astrologer-api' ),
				'single'       => true,
				'show_in_rest' => array(
					'schema' => array(
						'type'                 => 'object',
						'additionalProperties' => true,
					),
				),
			),
			self::META_CHART_OPTIONS => array(
				'type'         => 'object',
				'description'  => __( 'Chart rendering options', 'astrologer-api' ),
				'single'       => true,
				'show_in_rest' => array(
					'schema' => array(
						'type'                 => 'object',
						'additionalProperties' => true,
					),
				),
			),
			self::META_RESPONSE_SVG  => array(
				'type'         => 'string',
				'description'  => __( 'SVG chart image', 'astrologer-api' ),
				'single'       => true,
				'show_in_rest' => true,
				'default'      => '',
			),
			self::META_RESPONSE_DATA => array(
				'type'         => 'object',
				'description'  => __( 'Full API response data', 'astrologer-api' ),
				'single'       => true,
				'show_in_rest' => array(
					'schema' => array(
						'type'                 => 'object',
						'additionalProperties' => true,
					),
				),
			),
		);

		foreach ( $meta_fields as $meta_key => $args ) {
			register_post_meta( AstrologerChartPostType::SLUG, $meta_key, $args );
		}
	}

	/**
	 * Create a new chart CPT post from a request DTO.
	 *
	 * @param ChartRequestDTO    $dto          Chart request data.
	 * @param int                $user_id      The user who owns this chart.
	 * @param ChartResponseDTO|null $response  Optional upstream API response to store.
	 * @return int Post ID on success.
	 * @throws \RuntimeException If wp_insert_post fails.
	 */
	public function create( ChartRequestDTO $dto, int $user_id, ?ChartResponseDTO $response = null ): int {
		$birth_data = $dto->subject->birth_data;
		$options    = $dto->options;
		$chart_type = $dto->type ?? ChartType::Natal;
		$title      = $this->build_title( $birth_data, $chart_type );

		$post_id = wp_insert_post(
			array(
				'post_type'   => AstrologerChartPostType::SLUG,
				'post_title'  => $title,
				'post_status' => 'private',
				'post_author' => $user_id,
			),
			true
		);

		if ( is_wp_error( $post_id ) ) {
			throw new \RuntimeException(
				esc_html( $post_id->get_error_message() )
			);
		}

		// Store meta values.
		update_post_meta( $post_id, self::META_CHART_TYPE, $chart_type->value );
		update_post_meta( $post_id, self::META_BIRTH_DATA, $birth_data->to_array() );
		update_post_meta( $post_id, self::META_CHART_OPTIONS, $options->to_array() );

		if ( null !== $response ) {
			if ( $response->has_svg() ) {
				update_post_meta( $post_id, self::META_RESPONSE_SVG, $response->svg );
			}
			update_post_meta( $post_id, self::META_RESPONSE_DATA, $response->to_array() );
		}

		/** This action is documented in PLAN/F1-core-data-layer.md §F1.8. */
		// phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores -- Slash-separated namespace convention.
		do_action( 'astrologer_api/chart_saved', $post_id, $dto, $user_id );

		return (int) $post_id;
	}

	/**
	 * Find a chart record by post ID.
	 *
	 * @param int $id Post ID.
	 * @return ChartRecord|null The chart record, or null if not found.
	 */
	public function find( int $id ): ?ChartRecord {
		$post = get_post( $id );

		if ( null === $post || AstrologerChartPostType::SLUG !== $post->post_type ) {
			return null;
		}

		return $this->hydrate_record( $post );
	}

	/**
	 * Update a chart record with partial changes.
	 *
	 * @param int                                $id      Post ID.
	 * @param array<string,mixed>                $changes Associative array of fields to update.
	 *                                                     Supported keys: 'title', 'status', 'birth_data',
	 *                                                     'chart_options', 'chart_type', 'response_svg', 'response_data'.
	 */
	public function update( int $id, array $changes ): void {
		$post = get_post( $id );

		if ( null === $post || AstrologerChartPostType::SLUG !== $post->post_type ) {
			return;
		}

		$post_data = array( 'ID' => $id );

		if ( isset( $changes['title'] ) ) {
			$post_data['post_title'] = sanitize_text_field( (string) $changes['title'] );
		}

		if ( isset( $changes['status'] ) ) {
			$post_data['post_status'] = sanitize_key( (string) $changes['status'] );
		}

		// Only call wp_update_post if post-level fields changed.
		if ( count( $post_data ) > 1 ) {
			wp_update_post( $post_data );
		}

		// Meta updates.
		if ( isset( $changes['chart_type'] ) ) {
			$type = ChartType::tryFrom( (string) $changes['chart_type'] );
			if ( null !== $type ) {
				update_post_meta( $id, self::META_CHART_TYPE, $type->value );
			}
		}

		if ( isset( $changes['birth_data'] ) && $changes['birth_data'] instanceof BirthData ) {
			update_post_meta( $id, self::META_BIRTH_DATA, $changes['birth_data']->to_array() );
		}

		if ( isset( $changes['chart_options'] ) && $changes['chart_options'] instanceof ChartOptions ) {
			update_post_meta( $id, self::META_CHART_OPTIONS, $changes['chart_options']->to_array() );
		}

		if ( array_key_exists( 'response_svg', $changes ) ) {
			update_post_meta( $id, self::META_RESPONSE_SVG, (string) $changes['response_svg'] );
		}

		if ( array_key_exists( 'response_data', $changes ) && is_array( $changes['response_data'] ) ) {
			update_post_meta( $id, self::META_RESPONSE_DATA, $changes['response_data'] );
		}
	}

	/**
	 * Delete a chart post by ID.
	 *
	 * @param int  $id       Post ID.
	 * @param bool $force    Whether to bypass trash and force delete.
	 * @return bool True on success, false on failure.
	 */
	public function delete( int $id, bool $force = true ): bool {
		$post = get_post( $id );

		if ( null === $post || AstrologerChartPostType::SLUG !== $post->post_type ) {
			return false;
		}

		return false !== wp_delete_post( $id, $force );
	}

	/**
	 * List chart posts belonging to a specific user.
	 *
	 * @param int                  $user_id User ID.
	 * @param array<string,mixed>  $args    Optional WP_Query overrides.
	 * @return list<ChartRecord>
	 */
	public function list_by_user( int $user_id, array $args = array() ): array {
		$defaults = array(
			'post_type'      => AstrologerChartPostType::SLUG,
			'post_status'    => 'any',
			'author'         => $user_id,
			'posts_per_page' => 50,
			'orderby'        => 'date',
			'order'          => 'DESC',
		);

		$query_args = wp_parse_args( $args, $defaults );

		$query = new \WP_Query( $query_args );

		$records = array();

		foreach ( $query->posts as $post ) {
			if ( ! $post instanceof \WP_Post ) {
				continue;
			}
			$record = $this->hydrate_record( $post );
			if ( null !== $record ) {
				$records[] = $record;
			}
		}

		return $records;
	}

	/**
	 * Check whether the given user is the owner of a chart post.
	 *
	 * @param int $post_id Post ID.
	 * @param int $user_id User ID.
	 * @return bool
	 */
	public function is_owner( int $post_id, int $user_id ): bool {
		$post = get_post( $post_id );

		if ( null === $post || AstrologerChartPostType::SLUG !== $post->post_type ) {
			return false;
		}

		return (int) $post->post_author === $user_id;
	}

	/**
	 * Build a human-readable post title from birth data and chart type.
	 *
	 * @param BirthData $birth_data Subject birth data.
	 * @param ChartType $chart_type Chart type.
	 * @return string
	 */
	private function build_title( BirthData $birth_data, ChartType $chart_type ): string {
		return sprintf(
			/* translators: 1: Subject name, 2: Chart type label. */
			__( '%1$s — %2$s', 'astrologer-api' ),
			$birth_data->name,
			$chart_type->label()
		);
	}

	/**
	 * Hydrate a WP_Post into a ChartRecord value object.
	 *
	 * @param \WP_Post $post The post object.
	 * @return ChartRecord|null
	 */
	private function hydrate_record( \WP_Post $post ): ?ChartRecord {
		$chart_type_raw = get_post_meta( $post->ID, self::META_CHART_TYPE, true );
		$chart_type     = ChartType::tryFrom( (string) $chart_type_raw );

		if ( null === $chart_type ) {
			$chart_type = ChartType::Natal;
		}

		$birth_data_raw = get_post_meta( $post->ID, self::META_BIRTH_DATA, true );

		if ( ! is_array( $birth_data_raw ) || empty( $birth_data_raw ) ) {
			return null;
		}

		$birth_data = BirthData::from_array( $birth_data_raw );

		$options_raw = get_post_meta( $post->ID, self::META_CHART_OPTIONS, true );

		if ( is_array( $options_raw ) && ! empty( $options_raw ) ) {
			$chart_options = ChartOptions::from_array( $options_raw );
		} else {
			$chart_options = ChartOptions::defaults();
		}

		$response_svg  = get_post_meta( $post->ID, self::META_RESPONSE_SVG, true );
		$response_data = get_post_meta( $post->ID, self::META_RESPONSE_DATA, true );

		return new ChartRecord(
			id: $post->ID,
			chart_type: $chart_type,
			birth_data: $birth_data,
			chart_options: $chart_options,
			author_id: (int) $post->post_author,
			status: $post->post_status,
			title: $post->post_title,
			response_svg: is_string( $response_svg ) && '' !== $response_svg ? $response_svg : null,
			response_data: is_array( $response_data ) && ! empty( $response_data ) ? $response_data : null,
			created_date: $post->post_date,
		);
	}
}
