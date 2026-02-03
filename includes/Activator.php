<?php
/**
 * Plugin activator.
 *
 * @package LightweightPlugins\Memberships
 */

declare(strict_types=1);

namespace LightweightPlugins\Memberships;

use LightweightPlugins\Memberships\Database\Schema;

/**
 * Handles plugin activation.
 */
final class Activator {

	/**
	 * DB version option key.
	 */
	private const DB_VERSION_KEY = 'lw_mship_db_version';

	/**
	 * Current DB version.
	 */
	private const DB_VERSION = '1.1.0';

	/**
	 * Activate the plugin.
	 *
	 * @return void
	 */
	public static function activate(): void {
		self::create_tables();
		self::set_db_version();
		self::schedule_cron();

		// Flush rewrite rules on next load.
		set_transient( 'lw_mship_flush_rewrite', 1, 60 );
	}

	/**
	 * Create database tables.
	 *
	 * @return void
	 */
	private static function create_tables(): void {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		dbDelta( Schema::get_plans_sql() );
		dbDelta( Schema::get_plan_products_sql() );
		dbDelta( Schema::get_memberships_sql() );
		dbDelta( Schema::get_rules_sql() );
	}

	/**
	 * Set database version.
	 *
	 * @return void
	 */
	private static function set_db_version(): void {
		update_option( self::DB_VERSION_KEY, self::DB_VERSION );
	}

	/**
	 * Schedule cron jobs.
	 *
	 * @return void
	 */
	private static function schedule_cron(): void {
		if ( ! wp_next_scheduled( 'lw_mship_daily_expiration_check' ) ) {
			wp_schedule_event( time(), 'daily', 'lw_mship_daily_expiration_check' );
		}

		if ( ! wp_next_scheduled( 'lw_mship_hourly_subscription_sync' ) ) {
			wp_schedule_event( time(), 'hourly', 'lw_mship_hourly_subscription_sync' );
		}
	}

	/**
	 * Get current DB version.
	 *
	 * @return string
	 */
	public static function get_db_version(): string {
		return get_option( self::DB_VERSION_KEY, '0.0.0' );
	}

	/**
	 * Check if upgrade is needed.
	 *
	 * @return bool
	 */
	public static function needs_upgrade(): bool {
		return version_compare( self::get_db_version(), self::DB_VERSION, '<' );
	}

	/**
	 * Run upgrade if needed.
	 *
	 * @return void
	 */
	public static function maybe_upgrade(): void {
		if ( self::needs_upgrade() ) {
			self::create_tables();
			self::set_db_version();
		}
	}
}
