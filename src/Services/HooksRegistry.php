<?php
/**
 * HooksRegistry — documentation-centric index of all public hooks.
 *
 * Does NOT call do_action / apply_filters directly. Serves as a single
 * source of truth for hooks exposed by the plugin, consumed by:
 *   - DocumentationPage (F4/F8) to auto-generate a "Hooks Reference" page.
 *   - DoctorCommand (F7) to inspect which hooks are documented.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Services;

/**
 * Central registry of every public action and filter hook provided by the plugin.
 */
final class HooksRegistry {

	/**
	 * Get all documented action hooks.
	 *
	 * @return list<ActionDef>
	 */
	public function actions(): array {
		return array(
			new ActionDef(
				'astrologer_api/before_chart_request',
				array( 'string $chartType', 'array $payload' ),
				'Fired before a chart API call is sent upstream.',
			),
			new ActionDef(
				'astrologer_api/after_chart_response',
				array( 'string $chartType', 'ChartResponseDTO $response' ),
				'Fired after a successful chart API response.',
			),
			new ActionDef(
				'astrologer_api/chart_request_failed',
				array( 'string $chartType', 'WP_Error $error' ),
				'Fired when a chart API call returns a WP_Error.',
			),
			new ActionDef(
				'astrologer_api/before_http_request',
				array( 'string $endpoint', 'array $payload' ),
				'Fired before any HTTP request to the upstream API.',
			),
			new ActionDef(
				'astrologer_api/after_http_response',
				array( 'string $endpoint', 'array|WP_Error $response' ),
				'Fired after any HTTP response from the upstream API.',
			),
			new ActionDef(
				'astrologer_api/chart_saved',
				array( 'int $postId', 'ChartRequestDTO $dto', 'int $userId' ),
				'Fired when a chart is persisted to the astrologer_chart CPT.',
			),
			new ActionDef(
				'astrologer_api/settings_updated',
				array( 'array $newSettings', 'array $oldSettings' ),
				'Fired after plugin settings are saved.',
			),
			new ActionDef(
				'astrologer_api/cron_before_tick',
				array( 'string $cronName' ),
				'Fired before a cron handler executes.',
			),
			new ActionDef(
				'astrologer_api/cron_after_tick',
				array( 'string $cronName', 'array $stats' ),
				'Fired after a cron handler completes, with execution stats.',
			),
			new ActionDef(
				'astrologer_api/setup_wizard_completed',
				array( 'array $wizardData' ),
				'Fired when a user finishes the setup wizard.',
			),
		);
	}

	/**
	 * Get all documented filter hooks.
	 *
	 * @return list<FilterDef>
	 */
	public function filters(): array {
		return array(
			new FilterDef(
				'astrologer_api/chart_request_args',
				'array',
				array( 'array $payload', 'string $chartType' ),
				'Modify the outgoing API payload before it is sent.',
			),
			new FilterDef(
				'astrologer_api/chart_response',
				'ChartResponseDTO',
				array( 'ChartResponseDTO $response', 'string $chartType' ),
				'Modify the API response DTO before it is returned to the caller.',
			),
			new FilterDef(
				'astrologer_api/settings_defaults',
				'array',
				array( 'array $defaults' ),
				'Modify default settings values before first save.',
			),
			new FilterDef(
				'astrologer_api/cpt_args',
				'array',
				array( 'array $args' ),
				'Modify CPT registration arguments for astrologer_chart.',
			),
			new FilterDef(
				'astrologer_api/capability_map',
				'array',
				array( 'array $map' ),
				'Modify the role-to-capabilities mapping.',
			),
			new FilterDef(
				'astrologer_api/rest_endpoint_args',
				'array',
				array( 'array $args', 'string $routeName' ),
				'Modify REST route schema/args for a given endpoint.',
			),
			new FilterDef(
				'astrologer_api/block_attributes_defaults',
				'array',
				array( 'array $attrs', 'string $blockName' ),
				'Modify default block attributes before rendering.',
			),
			new FilterDef(
				'astrologer_api/school_preset',
				'ChartOptions',
				array( 'ChartOptions $options', 'School $school' ),
				'Modify the ChartOptions preset for a given school.',
			),
			new FilterDef(
				'astrologer_api/rate_limit_per_minute',
				'int',
				array( 'int $limit', 'string $endpoint', 'int $userId' ),
				'Modify the per-minute rate limit for API requests.',
			),
			new FilterDef(
				'astrologer_api/http_request_args',
				'array',
				array( 'array $args', 'string $endpoint' ),
				'Modify wp_remote_post / wp_remote_get arguments before dispatch.',
			),
			new FilterDef(
				'astrologer_api/geonames_request_args',
				'array',
				array( 'array $args' ),
				'Modify GeoNames API request arguments.',
			),
			new FilterDef(
				'astrologer_api/svg_allowed_tags',
				'array',
				array( 'array $tags' ),
				'Extend the SVG sanitizer tag allowlist.',
			),
			new FilterDef(
				'astrologer_api/svg_allowed_attrs',
				'array',
				array( 'array $attrs' ),
				'Extend the SVG sanitizer attribute allowlist.',
			),
			new FilterDef(
				'astrologer_api/client_ip',
				'string',
				array( 'string $ip' ),
				'Override client IP detection for rate limiting.',
			),
		);
	}

	/**
	 * Get all documented hooks (actions + filters) as a flat list.
	 *
	 * @return list<ActionDef|FilterDef>
	 */
	public function all(): array {
		return array_merge( $this->actions(), $this->filters() );
	}

	/**
	 * Find an action definition by hook name.
	 *
	 * @param string $name Hook name to search for.
	 * @return ActionDef|null
	 */
	public function find_action( string $name ): ?ActionDef {
		foreach ( $this->actions() as $def ) {
			if ( $def->name === $name ) {
				return $def;
			}
		}
		return null;
	}

	/**
	 * Find a filter definition by hook name.
	 *
	 * @param string $name Hook name to search for.
	 * @return FilterDef|null
	 */
	public function find_filter( string $name ): ?FilterDef {
		foreach ( $this->filters() as $def ) {
			if ( $def->name === $name ) {
				return $def;
			}
		}
		return null;
	}
}
