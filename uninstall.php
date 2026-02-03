<?php
/**
 * Uninstall script.
 *
 * @package LightweightPlugins\Memberships
 */

// Prevent direct access.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete options.
delete_option( 'lw_mship_options' );
delete_option( 'lw_mship_db_version' );

// Clear scheduled hooks.
wp_clear_scheduled_hook( 'lw_mship_daily_expiration_check' );
wp_clear_scheduled_hook( 'lw_mship_hourly_subscription_sync' );

// Optional: Drop custom tables (uncomment if desired).
// global $wpdb;
// $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}lw_mship_plans" );
// $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}lw_mship_plan_products" );
// $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}lw_mship_user_memberships" );
// $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}lw_mship_content_rules" );
