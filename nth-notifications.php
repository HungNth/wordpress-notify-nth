<?php
/**
 * Plugin Name: NTH Notifications
 * Plugin URI: https://wptop.net/
 * Description: Advanced notification system for WooCommerce
 * Version: 1.1.0
 * Author: Hung Nth
 * Author URI: https://wptop.net/
 * Text Domain: nth-notifications
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

namespace NTH\Notifications;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
define( 'NTH_NOTIFY_VERSION', '1.1.0' );
define( 'NTH_NOTIFY_PATH', plugin_dir_path( __FILE__ ) );
define( 'NTH_NOTIFY_URL', plugin_dir_url( __FILE__ ) );

/**
 * Load plugin textdomain for translations.
 */
function load_textdomain() {
	load_plugin_textdomain(
		'nth-notifications',
		false,
		dirname( plugin_basename( __FILE__ ) ) . '/languages'
	);
}

add_action( 'plugins_loaded', __NAMESPACE__ . '\load_textdomain' );

/**
 * Autoload class files.
 *
 * @param string $class Class name.
 */
function autoload( $class ) {
	// Check if class is in our namespace.
	if ( strpos( $class, __NAMESPACE__ ) !== 0 ) {
		return;
	}
	
	// Remove namespace prefix.
	$class = str_replace( __NAMESPACE__ . '\\', '', $class );
	
	// Convert class name to file name.
	$file = 'class-' . strtolower( str_replace( '_', '-', $class ) ) . '.php';
	$path = NTH_NOTIFY_PATH . 'includes/' . $file;
	
	// Load the file if it exists.
	if ( file_exists( $path ) ) {
		require_once $path;
	}
}

spl_autoload_register( __NAMESPACE__ . '\autoload' );

// Load helpers.
require_once NTH_NOTIFY_PATH . 'includes/helpers.php';

/**
 * Initialize the plugin.
 */
function init() {
	Plugin::instance();
}

add_action( 'plugins_loaded', __NAMESPACE__ . '\init' );
