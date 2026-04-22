<?php
/**
 * Solar-return reminder email template.
 *
 * Rendered inside SolarReturnReminderHandler::send_reminder_email().
 * Variables in scope:
 *   - @var \WP_User $astrologer_user
 *   - @var int      $astrologer_days_until
 *   - @var string   $astrologer_return_date YYYY-MM-DD
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** @var \WP_User $astrologer_user */
/** @var int $astrologer_days_until */
/** @var string $astrologer_return_date */

$astrologer_site_name    = (string) get_bloginfo( 'name' );
$astrologer_site_url     = (string) home_url( '/' );
$astrologer_display_name = '' !== $astrologer_user->display_name
	? $astrologer_user->display_name
	: $astrologer_user->user_login;

$astrologer_headline = 0 === $astrologer_days_until
	? __( 'Your solar return is today.', 'astrologer-api' )
	: sprintf(
		/* translators: %d: number of days until the return. */
		_n(
			'Your solar return is only %d day away.',
			'Your solar return is only %d days away.',
			$astrologer_days_until,
			'astrologer-api'
		),
		$astrologer_days_until
	);
?>
<!DOCTYPE html>
<html lang="<?php echo esc_attr( str_replace( '_', '-', (string) get_locale() ) ); ?>">
<head>
	<meta charset="utf-8">
	<title><?php echo esc_html( $astrologer_headline ); ?></title>
</head>
<body style="font-family: Arial, sans-serif; color: #222; background: #fafafa; padding: 24px;">
	<div style="max-width: 560px; margin: 0 auto; background: #fff; padding: 24px; border: 1px solid #eee;">
		<h1 style="font-size: 20px; margin-top: 0;">
			<?php
			echo esc_html(
				sprintf(
					/* translators: %s: user display name. */
					__( 'Hello %s,', 'astrologer-api' ),
					$astrologer_display_name
				)
			);
			?>
		</h1>
		<p style="font-size: 16px;">
			<?php echo esc_html( $astrologer_headline ); ?>
		</p>
		<p>
			<?php
			echo esc_html(
				sprintf(
					/* translators: %s: ISO date of the solar return. */
					__( 'Return date: %s', 'astrologer-api' ),
					$astrologer_return_date
				)
			);
			?>
		</p>
		<p>
			<?php esc_html_e( 'Consider revisiting your natal chart and planning ahead. A solar-return chart for this year can give useful clues about the months to come.', 'astrologer-api' ); ?>
		</p>
		<p>
			<a href="<?php echo esc_url( $astrologer_site_url ); ?>" style="color: #4b3ca7;">
				<?php echo esc_html( $astrologer_site_name ); ?>
			</a>
		</p>
	</div>
</body>
</html>
