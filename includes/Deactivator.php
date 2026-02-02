<?php
/**
 * Plugin deactivator.
 *
 * @package LightweightPlugins\Memberships
 */

declare(strict_types=1);

namespace LightweightPlugins\Memberships;

/**
 * Handles plugin deactivation.
 */
final class Deactivator {

	/**
	 * Deactivate the plugin.
	 *
	 * @return void
	 */
	public static function deactivate(): void {
		self::clear_cron();
		self::clear_transients();
	}

	/**
	 * Clear scheduled cron jobs.
	 *
	 * @return void
	 */
	private static function clear_cron(): void {
		wp_clear_scheduled_hook( 'lw_mship_daily_expiration_check' );
		wp_clear_scheduled_hook( 'lw_mship_hourly_subscription_sync' );
	}

	/**
	 * Clear transients.
	 *
	 * @return void
	 */
	private static function clear_transients(): void {
		delete_transient( 'lw_mship_flush_rewrite' );
	}
}
