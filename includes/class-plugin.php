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
		add_action( 'woocommerce_new_order', [ $this, 'handle_new_order' ], 10, 1 );
		add_action( 'woocommerce_thankyou', [ $this, 'handle_new_order' ], 10, 1 );
		add_action( 'woocommerce_payment_complete', [ $this, 'handle_payment_complete' ], 10, 1 );
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
	 * Handle new WooCommerce order
	 *
	 * @param int $order_id Order ID.
	 */
	public function handle_new_order( int $order_id ): void {
		// Debug logging.
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'NTH Notifications - New order hook triggered: ' . $order_id );
		}
		
		// Trigger notification action.
		do_action( 'nth_notifications_new_order', $order_id );
	}
	
	/**
	 * Handle WooCommerce payment complete
	 *
	 * @param int $order_id Order ID.
	 */
	public function handle_payment_complete( int $order_id ): void {
		// Prepared for future notification implementation.
		do_action( 'nth_notifications_payment_complete', $order_id );
	}
}
