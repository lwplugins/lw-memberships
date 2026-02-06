<?php
/**
 * Plugin Name:       LW Memberships
 * Plugin URI:        https://github.com/lwplugins/lw-memberships
 * Description:       Lightweight membership system with WooCommerce integration.
 * Version:           1.1.1
 * Requires at least: 6.0
 * Requires PHP:      8.2
 * Author:            LW Plugins
 * Author URI:        https://lwplugins.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       lw-memberships
 * Domain Path:       /languages
 *
 * @package LightweightPlugins\Memberships
 */

declare(strict_types=1);

namespace LightweightPlugins\Memberships;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin constants.
define( 'LW_MEMBERSHIPS_VERSION', '1.1.1' );
define( 'LW_MEMBERSHIPS_FILE', __FILE__ );
define( 'LW_MEMBERSHIPS_PATH', plugin_dir_path( __FILE__ ) );
define( 'LW_MEMBERSHIPS_URL', plugin_dir_url( __FILE__ ) );

// Autoloader (required for PSR-4 class loading).
if ( file_exists( LW_MEMBERSHIPS_PATH . 'vendor/autoload.php' ) ) {
	require_once LW_MEMBERSHIPS_PATH . 'vendor/autoload.php';
}

// Activation/Deactivation hooks.
register_activation_hook( __FILE__, [ Activator::class, 'activate' ] );
register_deactivation_hook( __FILE__, [ Deactivator::class, 'deactivate' ] );

/**
 * Returns the main plugin instance.
 *
 * @return Plugin
 */
function lw_memberships(): Plugin {
	static $instance = null;

	if ( null === $instance ) {
		$instance = new Plugin();
	}

	return $instance;
}

// Initialize the plugin.
add_action( 'plugins_loaded', __NAMESPACE__ . '\\lw_memberships' );
