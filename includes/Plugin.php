<?php
/**
 * Main Plugin class.
 *
 * @package LightweightPlugins\Memberships
 */

declare(strict_types=1);

namespace LightweightPlugins\Memberships;

use LightweightPlugins\Memberships\Admin\SettingsPage;
use LightweightPlugins\Memberships\Admin\Levels\LevelsPage;
use LightweightPlugins\Memberships\Integrations\WooCommerce\OrderHandler;
use LightweightPlugins\Memberships\Integrations\WooCommerce\RefundHandler;
use LightweightPlugins\Memberships\Admin\MetaBox\ContentRestriction;
use LightweightPlugins\Memberships\Frontend\ContentFilter;
use LightweightPlugins\Memberships\Frontend\Shortcodes\RestrictedContent;
use LightweightPlugins\Memberships\Frontend\Shortcodes\MyMemberships;
use LightweightPlugins\Memberships\Integrations\WooCommerce\Subscriptions\StatusHandler;
use LightweightPlugins\Memberships\Integrations\WooCommerce\Subscriptions\RenewalHandler;
use LightweightPlugins\Memberships\Cron\CronManager;

/**
 * Main plugin class.
 */
final class Plugin {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->init_hooks();
		$this->init_admin();
		$this->init_frontend();
		$this->init_integrations();
		$this->init_cron();
	}

	/**
	 * Initialize hooks.
	 *
	 * @return void
	 */
	private function init_hooks(): void {
		add_action( 'init', [ $this, 'load_textdomain' ] );
		add_filter(
			'plugin_action_links_' . plugin_basename( LW_MEMBERSHIPS_FILE ),
			[ $this, 'add_settings_link' ]
		);
	}

	/**
	 * Initialize admin.
	 *
	 * @return void
	 */
	private function init_admin(): void {
		if ( is_admin() ) {
			new SettingsPage();
			new LevelsPage();
			new ContentRestriction();
		}
	}

	/**
	 * Initialize frontend.
	 *
	 * @return void
	 */
	private function init_frontend(): void {
		new RestrictedContent();
		new MyMemberships();

		if ( ! is_admin() ) {
			new ContentFilter();
		}
	}

	/**
	 * Initialize integrations.
	 *
	 * @return void
	 */
	private function init_integrations(): void {
		if ( self::is_woocommerce_active() ) {
			new OrderHandler();
			new RefundHandler();

			if ( self::is_subscriptions_active() ) {
				new StatusHandler();
				new RenewalHandler();
			}
		}
	}

	/**
	 * Load textdomain.
	 *
	 * @return void
	 */
	public function load_textdomain(): void {
		load_plugin_textdomain(
			'lw-memberships',
			false,
			dirname( plugin_basename( LW_MEMBERSHIPS_FILE ) ) . '/languages'
		);
	}

	/**
	 * Add settings link.
	 *
	 * @param array<string> $links Plugin links.
	 * @return array<string>
	 */
	public function add_settings_link( array $links ): array {
		$url  = admin_url( 'admin.php?page=' . SettingsPage::SLUG );
		$link = '<a href="' . esc_url( $url ) . '">' . __( 'Settings', 'lw-memberships' ) . '</a>';
		array_unshift( $links, $link );
		return $links;
	}

	/**
	 * Check if WooCommerce is active.
	 *
	 * @return bool
	 */
	public static function is_woocommerce_active(): bool {
		return class_exists( 'WooCommerce' );
	}

	/**
	 * Check if WooCommerce Subscriptions is active.
	 *
	 * @return bool
	 */
	public static function is_subscriptions_active(): bool {
		return class_exists( 'WC_Subscriptions' );
	}

	/**
	 * Initialize cron.
	 *
	 * @return void
	 */
	private function init_cron(): void {
		new CronManager();
	}
}
