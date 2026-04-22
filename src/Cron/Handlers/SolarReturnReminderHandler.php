<?php
/**
 * SolarReturnReminderHandler — e-mails users whose solar return is imminent.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api\Cron\Handlers;

use Astrologer\Api\Repository\BirthDataRepository;
use Astrologer\Api\ValueObjects\BirthData;
use Throwable;
use WP_User;

/**
 * Cron handler that e-mails users 0-7 days before their solar return.
 *
 * Runs once per day. For every user with stored birth data it:
 *   1. Computes how many days remain until the next birth-date anniversary.
 *   2. If the gap is within the configured window, sends a reminder email.
 *   3. Fires `astrologer_api/solar_return_reminder_sent` per message sent.
 *
 * All errors are caught and logged — the scheduler must never die because
 * a single user's record is malformed.
 */
final class SolarReturnReminderHandler {

	/**
	 * Cron hook name this handler responds to.
	 *
	 * @var string
	 */
	public const HOOK = 'astrologer_api_solar_return_reminder';

	/**
	 * How many days before the anniversary a reminder should be sent.
	 *
	 * @var int
	 */
	private const REMINDER_WINDOW_DAYS = 7;

	/**
	 * Maximum number of users processed per tick (defensive cap).
	 *
	 * @var int
	 */
	private const MAX_USERS_PER_TICK = 500;

	/**
	 * User-meta key set once a reminder has been sent for a given return year.
	 *
	 * @var string
	 */
	private const SENT_META_KEY = 'astrologer_solar_return_sent_year';

	/**
	 * Birth-data repository.
	 *
	 * @var BirthDataRepository
	 */
	private BirthDataRepository $birth_data;

	/**
	 * Constructor.
	 *
	 * @param BirthDataRepository $birth_data Birth-data repository.
	 */
	public function __construct( BirthDataRepository $birth_data ) {
		$this->birth_data = $birth_data;
	}

	/**
	 * Execute the cron tick.
	 *
	 * Iterates over users with the `astrologer_birth_data` meta set. A user
	 * is only reminded once per return year, tracked via user meta.
	 */
	public function run(): void {
		do_action( 'astrologer_api/cron_before_tick', self::HOOK );

		$started_at = microtime( true );
		$sent_count = 0;

		try {
			$user_ids = $this->fetch_users_with_birth_data();

			foreach ( $user_ids as $user_id ) {
				if ( $this->process_user( $user_id ) ) {
					++$sent_count;
				}
			}

			do_action(
				'astrologer_api/cron_after_tick',
				self::HOOK,
				array(
					'success'    => true,
					'sent_count' => $sent_count,
					'duration'   => microtime( true ) - $started_at,
				)
			);
		} catch ( Throwable $e ) {
			error_log( // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Cron handlers must log failures without dying.
				sprintf( '[astrologer-api] Solar return reminder cron threw: %s', $e->getMessage() )
			);

			do_action(
				'astrologer_api/cron_after_tick',
				self::HOOK,
				array(
					'success'    => false,
					'error'      => $e->getMessage(),
					'sent_count' => $sent_count,
					'duration'   => microtime( true ) - $started_at,
				)
			);
		}
	}

	/**
	 * Query users with birth data stored in user meta.
	 *
	 * @return list<int>
	 */
	private function fetch_users_with_birth_data(): array {
		$query = new \WP_User_Query(
			array(
				'meta_key' => 'astrologer_birth_data', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Cron-scheduled query, runs once daily.
				'fields'   => 'ID',
				'number'   => self::MAX_USERS_PER_TICK,
			)
		);

		$results = $query->get_results();

		$ids = array();

		foreach ( $results as $raw ) {
			$id = is_object( $raw ) && isset( $raw->ID ) ? (int) $raw->ID : (int) $raw;

			if ( $id > 0 ) {
				$ids[] = $id;
			}
		}

		return $ids;
	}

	/**
	 * Process a single user: compute window, send mail if needed.
	 *
	 * @param int $user_id User ID.
	 * @return bool True when a reminder was dispatched.
	 */
	private function process_user( int $user_id ): bool {
		try {
			$birth = $this->birth_data->get_for_user( $user_id );

			if ( null === $birth ) {
				return false;
			}

			$user = get_user_by( 'id', $user_id );

			if ( ! $user instanceof WP_User ) {
				return false;
			}

			$anniversary = $this->next_anniversary( $birth );

			if ( null === $anniversary ) {
				return false;
			}

			$days_until = $this->days_until( $anniversary );

			if ( $days_until < 0 || $days_until > self::REMINDER_WINDOW_DAYS ) {
				return false;
			}

			$return_year  = (int) $anniversary->format( 'Y' );
			$already_sent = (int) get_user_meta( $user_id, self::SENT_META_KEY, true );

			if ( $already_sent === $return_year ) {
				return false;
			}

			$return_date = $anniversary->format( 'Y-m-d' );

			$sent = $this->send_reminder_email( $user, $days_until, $return_date );

			if ( ! $sent ) {
				return false;
			}

			update_user_meta( $user_id, self::SENT_META_KEY, $return_year );

			do_action(
				'astrologer_api/solar_return_reminder_sent',
				$user_id,
				$days_until,
				$return_date
			);

			return true;
		} catch ( Throwable $e ) {
			error_log( // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Cron handlers must log failures without dying.
				sprintf(
					'[astrologer-api] Solar return reminder failed for user %d: %s',
					$user_id,
					$e->getMessage()
				)
			);

			return false;
		}
	}

	/**
	 * Compute the next birth-date anniversary at or after "today" (UTC).
	 *
	 * @param BirthData $birth Birth data.
	 * @return \DateTimeImmutable|null
	 */
	private function next_anniversary( BirthData $birth ): ?\DateTimeImmutable {
		try {
			$today = new \DateTimeImmutable( 'now', new \DateTimeZone( 'UTC' ) );

			$current_year = (int) $today->format( 'Y' );

			$anniversary = $this->make_date( $current_year, $birth->month, $birth->day );

			if ( null !== $anniversary && $anniversary < $today->setTime( 0, 0 ) ) {
				$anniversary = $this->make_date( $current_year + 1, $birth->month, $birth->day );
			}

			return $anniversary;
		} catch ( Throwable $e ) {
			return null;
		}
	}

	/**
	 * Build a midnight-UTC DateTimeImmutable, coping with leap-year Feb 29.
	 *
	 * @param int $year  Calendar year.
	 * @param int $month Month 1-12.
	 * @param int $day   Day 1-31.
	 * @return \DateTimeImmutable|null
	 */
	private function make_date( int $year, int $month, int $day ): ?\DateTimeImmutable {
		// Shift Feb 29 anniversaries to Feb 28 in non-leap years.
		if ( 2 === $month && 29 === $day && ! $this->is_leap_year( $year ) ) {
			$day = 28;
		}

		$formatted = sprintf( '%04d-%02d-%02dT00:00:00', $year, $month, $day );

		try {
			return new \DateTimeImmutable( $formatted, new \DateTimeZone( 'UTC' ) );
		} catch ( Throwable $e ) {
			return null;
		}
	}

	/**
	 * Whether a year is a leap year.
	 *
	 * @param int $year Calendar year.
	 * @return bool
	 */
	private function is_leap_year( int $year ): bool {
		return ( 0 === $year % 4 && 0 !== $year % 100 ) || 0 === $year % 400;
	}

	/**
	 * Whole days between "now" and a future timestamp.
	 *
	 * @param \DateTimeImmutable $target Target date.
	 * @return int
	 */
	private function days_until( \DateTimeImmutable $target ): int {
		$now      = new \DateTimeImmutable( 'now', new \DateTimeZone( 'UTC' ) );
		$interval = $now->setTime( 0, 0 )->diff( $target );

		$days = (int) $interval->days;

		return $interval->invert ? -1 * $days : $days;
	}

	/**
	 * Render and send the reminder email.
	 *
	 * @param WP_User $user        Recipient.
	 * @param int     $days_until  Days until the return.
	 * @param string  $return_date Return date in YYYY-MM-DD form.
	 * @return bool True on success.
	 */
	private function send_reminder_email( WP_User $user, int $days_until, string $return_date ): bool {
		$template = ASTROLOGER_API_DIR . '/templates/emails/solar-return-reminder.php';

		if ( ! file_exists( $template ) ) {
			return false;
		}

		// These three variables are intentionally in scope for the template.
		$astrologer_user        = $user;
		$astrologer_days_until  = $days_until;
		$astrologer_return_date = $return_date;

		ob_start();

		// Template reads $astrologer_user / $astrologer_days_until / $astrologer_return_date.
		include $template;

		$body = (string) ob_get_clean();

		$subject = sprintf(
			/* translators: %d: number of days until the solar return. */
			_n(
				'Your solar return is %d day away',
				'Your solar return is %d days away',
				max( 1, $days_until ),
				'astrologer-api'
			),
			$days_until
		);

		$headers = array( 'Content-Type: text/html; charset=UTF-8' );

		return (bool) wp_mail( $user->user_email, $subject, $body, $headers );
	}
}
