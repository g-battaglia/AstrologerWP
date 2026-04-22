<?php
/**
 * Contextual help tabs for admin screens.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Admin;

use Astrologer\Api\Support\Contracts\Bootable;
use WP_Screen;

/**
 * Adds contextual help tabs on Astrologer admin screens.
 */
final class HelpTabsProvider implements Bootable {

	/**
	 * Register hooks for each relevant admin screen.
	 */
	public function boot(): void {
		add_action( 'load-toplevel_page_astrologer-api', array( $this, 'add_settings_help' ) );
		add_action( 'load-admin_page_astrologer-setup', array( $this, 'add_wizard_help' ) );
		add_action( 'load-edit.php', array( $this, 'add_chart_list_help' ) );
		add_action( 'load-post.php', array( $this, 'add_chart_edit_help' ) );
	}

	/**
	 * Add help tabs to the Settings page.
	 */
	public function add_settings_help(): void {
		$screen = get_current_screen();

		if ( ! $screen instanceof WP_Screen ) {
			return;
		}

		$screen->add_help_tab(
			array(
				'id'      => 'astrologer-overview',
				'title'   => __( 'Overview', 'astrologer-api' ),
				'content' => '<p>' . __( 'Astrologer API integrates professional astrology calculations into your WordPress site. Configure your API credentials and preferences from this page.', 'astrologer-api' ) . '</p>',
			)
		);

		$screen->add_help_tab(
			array(
				'id'      => 'astrologer-api-credentials',
				'title'   => __( 'API Credentials', 'astrologer-api' ),
				'content' => '<p>' . __( 'Enter your RapidAPI key and GeoNames username to connect to the Astrologer API. You can obtain a key from the RapidAPI marketplace.', 'astrologer-api' ) . '</p>',
			)
		);

		$screen->add_help_tab(
			array(
				'id'      => 'astrologer-schools',
				'title'   => __( 'Schools', 'astrologer-api' ),
				'content' => '<p>' . __( 'Choose your preferred astrological school: Modern Western, Traditional, Vedic, or Uranian. This affects house systems, aspect orbs, and available features.', 'astrologer-api' ) . '</p>',
			)
		);

		$screen->add_help_tab(
			array(
				'id'      => 'astrologer-troubleshooting',
				'title'   => __( 'Troubleshooting', 'astrologer-api' ),
				'content' => '<p>' . __( 'If the API connection test fails, verify your API key is active, your server can make outbound HTTPS requests, and your RapidAPI subscription is current.', 'astrologer-api' ) . '</p>',
			)
		);

		$screen->set_help_sidebar(
			'<p><strong>' . __( 'For more information:', 'astrologer-api' ) . '</strong></p>' .
			'<p><a href="' . esc_url( admin_url( 'admin.php?page=astrologer-docs' ) ) . '">' . __( 'Documentation', 'astrologer-api' ) . '</a></p>'
		);
	}

	/**
	 * Add help tab to the Setup Wizard page.
	 */
	public function add_wizard_help(): void {
		$screen = get_current_screen();

		if ( ! $screen instanceof WP_Screen ) {
			return;
		}

		$screen->add_help_tab(
			array(
				'id'      => 'astrologer-wizard-overview',
				'title'   => __( 'Overview', 'astrologer-api' ),
				'content' => '<p>' . __( 'The setup wizard guides you through initial configuration: entering your API key, choosing an astrological school, and setting your language and UI preferences.', 'astrologer-api' ) . '</p>',
			)
		);
	}

	/**
	 * Add help tabs to the Chart list screen.
	 */
	public function add_chart_list_help(): void {
		$screen = get_current_screen();

		if ( ! $screen instanceof WP_Screen || 'edit-astrologer_chart' !== $screen->id ) {
			return;
		}

		$this->add_chart_help_tabs( $screen );
	}

	/**
	 * Add help tabs to the Chart edit screen.
	 */
	public function add_chart_edit_help(): void {
		$screen = get_current_screen();

		if ( ! $screen instanceof WP_Screen || 'astrologer_chart' !== $screen->id ) {
			return;
		}

		$this->add_chart_help_tabs( $screen );
	}

	/**
	 * Shared chart help tabs for both list and edit screens.
	 *
	 * @param WP_Screen $screen The current admin screen.
	 */
	private function add_chart_help_tabs( WP_Screen $screen ): void {
		$screen->add_help_tab(
			array(
				'id'      => 'astrologer-about-charts',
				'title'   => __( 'About Charts', 'astrologer-api' ),
				'content' => '<p>' . __( 'Charts are custom post types that store birth data and calculated astrological information. Each chart includes date, time, and location of birth.', 'astrologer-api' ) . '</p>',
			)
		);

		$screen->add_help_tab(
			array(
				'id'      => 'astrologer-capabilities',
				'title'   => __( 'Capabilities', 'astrologer-api' ),
				'content' => '<p>' . __( 'Chart access is controlled by custom capabilities. Administrators can manage all charts, while other roles may be limited to viewing or editing their own charts.', 'astrologer-api' ) . '</p>',
			)
		);
	}
}
