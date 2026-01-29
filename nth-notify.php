<?php
/**
 * Plugin Name: NTH Notify
 * Plugin URI: https://wptop.net/
 * Description: Enhance your WordPress site with powerful notification capabilities.
 * Version: 1.1.0
 * Author: Hung Nth
 * Author URI: https://wptop.net/
 * Text Domain: nth-notify
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
define( 'NTH_NOTIFY_IS_PRODUCTION', true );

if ( ! NTH_NOTIFY_IS_PRODUCTION ) {
	$assets_version = time();
} else {
	$assets_version = '1.1.0';
}

define( 'NTH_NOTIFY_VERSION', $assets_version );
define( 'NTH_NOTIFY_PATH', plugin_dir_path( __FILE__ ) );
define( 'NTH_NOTIFY_URL', plugin_dir_url( __FILE__ ) );
define( 'NTH_NOTIFY_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Load plugin textdomain for translations.
 */
function load_textdomain() {
	load_plugin_textdomain(
		'nth-notify',
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
