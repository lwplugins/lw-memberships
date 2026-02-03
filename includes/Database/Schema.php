<?php
/**
 * Database schema definitions.
 *
 * @package LightweightPlugins\Memberships
 */

declare(strict_types=1);

namespace LightweightPlugins\Memberships\Database;

/**
 * Database table schema.
 */
final class Schema {

	/**
	 * Get plans table name.
	 *
	 * @return string
	 */
	public static function plans_table(): string {
		global $wpdb;
		return $wpdb->prefix . 'lw_mship_plans';
	}

	/**
	 * Get plan products table name.
	 *
	 * @return string
	 */
	public static function plan_products_table(): string {
		global $wpdb;
		return $wpdb->prefix . 'lw_mship_plan_products';
	}

	/**
	 * Get user memberships table name.
	 *
	 * @return string
	 */
	public static function memberships_table(): string {
		global $wpdb;
		return $wpdb->prefix . 'lw_mship_user_memberships';
	}

	/**
	 * Get content rules table name.
	 *
	 * @return string
	 */
	public static function rules_table(): string {
		global $wpdb;
		return $wpdb->prefix . 'lw_mship_content_rules';
	}

	/**
	 * Get SQL for plans table creation.
	 *
	 * @return string
	 */
	public static function get_plans_sql(): string {
		$table   = self::plans_table();
		$charset = self::get_charset_collate();

		return "CREATE TABLE {$table} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			name VARCHAR(255) NOT NULL,
			slug VARCHAR(100) NOT NULL,
			description TEXT NULL,
			duration_type ENUM('forever', 'days', 'months', 'years') DEFAULT 'forever',
			duration_value INT(11) NULL,
			priority INT(11) DEFAULT 0,
			status ENUM('active', 'inactive') DEFAULT 'active',
			created_at DATETIME NOT NULL,
			updated_at DATETIME NOT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY slug (slug)
		) {$charset};";
	}

	/**
	 * Get SQL for plan products table creation.
	 *
	 * @return string
	 */
	public static function get_plan_products_sql(): string {
		$table   = self::plan_products_table();
		$charset = self::get_charset_collate();

		return "CREATE TABLE {$table} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			plan_id BIGINT(20) UNSIGNED NOT NULL,
			product_id BIGINT(20) UNSIGNED NOT NULL,
			product_type VARCHAR(50) DEFAULT 'simple',
			created_at DATETIME NOT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY plan_product (plan_id, product_id)
		) {$charset};";
	}

	/**
	 * Get SQL for user memberships table creation.
	 *
	 * @return string
	 */
	public static function get_memberships_sql(): string {
		$table   = self::memberships_table();
		$charset = self::get_charset_collate();

		return "CREATE TABLE {$table} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id BIGINT(20) UNSIGNED NOT NULL,
			plan_id BIGINT(20) UNSIGNED NOT NULL,
			order_id BIGINT(20) UNSIGNED NULL,
			subscription_id BIGINT(20) UNSIGNED NULL,
			source ENUM('purchase', 'subscription', 'manual', 'import') DEFAULT 'manual',
			status ENUM('active', 'expired', 'cancelled', 'paused') DEFAULT 'active',
			start_date DATETIME NOT NULL,
			end_date DATETIME NULL,
			cancelled_at DATETIME NULL,
			created_at DATETIME NOT NULL,
			updated_at DATETIME NOT NULL,
			PRIMARY KEY (id),
			KEY user_status (user_id, status),
			KEY plan_id (plan_id),
			KEY subscription_id (subscription_id)
		) {$charset};";
	}

	/**
	 * Get SQL for content rules table creation.
	 *
	 * @return string
	 */
	public static function get_rules_sql(): string {
		$table   = self::rules_table();
		$charset = self::get_charset_collate();

		return "CREATE TABLE {$table} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			post_id BIGINT(20) UNSIGNED NOT NULL,
			post_type VARCHAR(50) NOT NULL,
			plan_id BIGINT(20) UNSIGNED NOT NULL,
			created_at DATETIME NOT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY post_plan (post_id, plan_id),
			KEY plan_id (plan_id)
		) {$charset};";
	}

	/**
	 * Get charset collate.
	 *
	 * @return string
	 */
	private static function get_charset_collate(): string {
		global $wpdb;
		return $wpdb->get_charset_collate();
	}
}
