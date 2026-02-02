<?php
/**
 * Cron manager.
 *
 * @package LightweightPlugins\Memberships
 */

declare(strict_types=1);

namespace LightweightPlugins\Memberships\Cron;

/**
 * Manages cron jobs.
 */
final class CronManager {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'lw_mship_daily_expiration_check', [ ExpirationChecker::class, 'run' ] );
	}

	/**
	 * Schedule cron jobs.
	 *
	 * @return void
	 */
	public static function schedule(): void {
		if ( ! wp_next_scheduled( 'lw_mship_daily_expiration_check' ) ) {
			wp_schedule_event( time(), 'daily', 'lw_mship_daily_expiration_check' );
		}
	}

	/**
	 * Unschedule cron jobs.
	 *
	 * @return void
	 */
	public static function unschedule(): void {
		wp_clear_scheduled_hook( 'lw_mship_daily_expiration_check' );
		wp_clear_scheduled_hook( 'lw_mship_hourly_subscription_sync' );
	}
}
