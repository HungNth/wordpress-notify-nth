<?php
/**
 * Main Plugin Class
 *
 * @package NTH\Notifications
 */

namespace NTH\Notifications;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main Plugin Class
 */
class Plugin {
	
	/**
	 * Singleton instance
	 *
	 * @var Plugin
	 */
	private static ?Plugin $instance = null;
	
	/**
	 * Admin instance
	 *
	 * @var Admin
	 */
	public Admin $admin;
	
	/**
	 * Telegram instance
	 *
	 * @var Telegram
	 */
	public Telegram $telegram;
	
	/**
	 * Zalo instance
	 *
	 * @var Zalo
	 */
	public Zalo $zalo;
	
	/**
	 * Get singleton instance
	 *
	 * @return Plugin|null
	 */
	public static function instance(): ?Plugin {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	/**
	 * Constructor
	 */
	private function __construct() {
		$this->load_dependencies();
		$this->init_hooks();
	}
	
	/**
	 * Load dependencies
	 */
	private function load_dependencies(): void {
		// Admin class will be autoloaded.
		if ( is_admin() ) {
			$this->admin = new Admin();
		}
		
		// Always initialize Telegram class (it will check if enabled internally).
		$this->telegram = new Telegram();
		
		// Always initialize Zalo class (it will check if enabled internally).
		$this->zalo = new Zalo();
	}
	
	/**
	 * Initialize hooks
	 */
	private function init_hooks(): void {
		// Register activation hook.
		register_activation_hook( NTH_NOTIFY_PATH . 'nth-notifications.php', [ $this, 'activate' ] );
		
		// WooCommerce hooks for order notifications.
		add_action( 'woocommerce_order_status_changed', [ $this, 'handle_order_status_changed' ], 10, 4 );
	}
	
	/**
	 * Plugin activation
	 */
	public function activate(): void {
		// Set default options for general settings
		if ( false === get_option( 'nth_notifications_settings' ) ) {
			update_option( 'nth_notifications_settings', [
				'telegram_enabled' => false,
				'zalo_enabled'     => false,
				'enabled_statuses' => [ 'processing', 'completed', 'cancelled', 'failed' ],
			], 'no' );
		}
		
		// Set default options for Telegram
		if ( false === get_option( 'nth_notifications_telegram' ) ) {
			update_option( 'nth_notifications_telegram', [
				'bot_token' => '',
				'chat_ids'  => [],
			], 'no' );
		}
		
		// Set default options for Zalo
		if ( false === get_option( 'nth_notifications_zalo' ) ) {
			update_option( 'nth_notifications_zalo', [
				'bot_token' => '',
				'chat_ids'  => [],
			], 'no' );
		}
	}
	
	/**
	 * Handle WooCommerce order status change
	 *
	 * @param int    $order_id Order ID.
	 * @param string $old_status Old status.
	 * @param string $new_status New status.
	 * @param object $order Order object.
	 */
	public function handle_order_status_changed( int $order_id, string $old_status, string $new_status, $order ): void {
		// Get enabled statuses from settings
		$settings = get_option( 'nth_notifications_settings', [] );
		$enabled_statuses = isset( $settings['enabled_statuses'] ) ? $settings['enabled_statuses'] : [ 'processing', 'completed', 'cancelled', 'failed' ];
		
		// Debug logging
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( sprintf(
				'NTH Notifications - Order status changed: #%d from %s to %s',
				$order_id,
				$old_status,
				$new_status
			) );
		}
		
		// Check if new status is in enabled statuses
		if ( ! in_array( $new_status, $enabled_statuses, true ) ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( sprintf(
					'NTH Notifications - Status %s is not enabled for notifications',
					$new_status
				) );
			}
			return;
		}
		
		// Trigger notification action
		do_action( 'nth_notifications_new_order', $order_id );
	}
}
