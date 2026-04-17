<?php
/**
 * Astrologer API — Uninstall handler.
 *
 * Runs when the plugin is deleted via the WordPress admin.
 * Removes all plugin data: options, transients, user meta, custom caps,
 * cron events, and CPT posts.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

/**
 * Clean up options prefixed with 'astrologer_api_'.
 *
 * @return void
 */
function astrologer_api_uninstall(): void {
	global $wpdb;

	if ( is_multisite() ) {
		$blog_ids = get_sites( array( 'fields' => 'ids' ) );

		foreach ( $blog_ids as $blog_id ) {
			switch_to_blog( $blog_id );
			astrologer_api_cleanup_single_site();
			restore_current_blog();
		}
	} else {
		astrologer_api_cleanup_single_site();
	}
}

/**
 * Perform cleanup for a single site.
 *
 * @return void
 */
function astrologer_api_cleanup_single_site(): void {
	global $wpdb;

	// Delete options.
	$options = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s",
			$wpdb->esc_like( 'astrologer_api_' ) . '%'
		)
	);

	foreach ( $options as $option ) {
		delete_option( $option );
	}

	// Delete transients.
	$transients = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
			'_transient_astrologer_api_%',
			'_transient_timeout_astrologer_api_%'
		)
	);

	foreach ( $transients as $transient ) {
		delete_option( $transient );
	}

	// Remove custom capabilities from all roles.
	astrologer_api_remove_capabilities();

	// Unschedule cron events.
	astrologer_api_unschedule_cron();

	// Delete CPT posts.
	astrologer_api_delete_cpt_posts();

	// Delete user meta for birth data.
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE %s",
			$wpdb->esc_like( 'astrologer_birth_data' ) . '%'
		)
	);
}

/**
 * Remove custom capabilities from all roles.
 *
 * @return void
 */
function astrologer_api_remove_capabilities(): void {
	$caps = array(
		'astrologer_manage_settings',
		'astrologer_calculate_chart',
		'astrologer_save_chart',
		'astrologer_view_any_chart',
		'astrologer_run_cli',
	);

	$roles = wp_roles()->roles;

	foreach ( array_keys( $roles ) as $role_name ) {
		$role = get_role( $role_name );

		if ( $role === null ) {
			continue;
		}

		foreach ( $caps as $cap ) {
			$role->remove_cap( $cap );
		}
	}
}

/**
 * Unschedule all plugin cron events.
 *
 * @return void
 */
function astrologer_api_unschedule_cron(): void {
	$hooks = array(
		'astrologer_api_daily_transits',
		'astrologer_api_daily_moon_phase',
		'astrologer_api_solar_return_reminder',
	);

	foreach ( $hooks as $hook ) {
		$timestamp = wp_next_scheduled( $hook );

		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, $hook );
		}
	}
}

/**
 * Delete all astrologer_chart CPT posts.
 *
 * @return void
 */
function astrologer_api_delete_cpt_posts(): void {
	$posts = get_posts(
		array(
			'post_type'   => 'astrologer_chart',
			'numberposts' => -1,
			'post_status' => 'any',
		)
	);

	foreach ( $posts as $post ) {
		wp_delete_post( $post->ID, true );
	}
}

astrologer_api_uninstall();
