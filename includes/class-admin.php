<?php
/**
 * Admin Class
 *
 * @package NTH\Notifications
 */

namespace NTH\Notifications;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Class
 */
class Admin {
	
	/**
	 * Settings instance
	 *
	 * @var Settings
	 */
	private Settings $settings;
	
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->settings = new Settings();
		$this->init_hooks();
	}
	
	/**
	 * Initialize hooks
	 */
	private function init_hooks(): void {
		add_action( 'admin_menu', [ $this, 'add_menu' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		add_action( 'wp_ajax_nth_test_telegram', [ $this, 'ajax_test_telegram' ] );
		add_action( 'wp_ajax_nth_test_zalo', [ $this, 'ajax_test_zalo' ] );
		add_action( 'wp_ajax_nth_find_zalo_chat_id', [ $this, 'ajax_find_zalo_chat_id' ] );
	}
	
	/**
	 * Add settings menu
	 */
	public function add_menu(): void {
		add_options_page(
			__( 'NTH Notifications', 'nth-notifications' ),
			__( 'NTH Notifications', 'nth-notifications' ),
			'manage_options',
			'nth-notifications',
			[ $this->settings, 'render_page' ]
		);
	}
	
	/**
	 * Enqueue admin assets
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_assets( string $hook ): void {
		// Only load on our settings page.
		if ( 'settings_page_nth-notifications' !== $hook ) {
			return;
		}
		
		// Enqueue CSS.
		wp_enqueue_style(
			'nth-notifications-admin',
			NTH_NOTIFY_URL . 'assets/css/admin.css',
			[],
			NTH_NOTIFY_VERSION
		);
		
		// Enqueue JavaScript.
		wp_enqueue_script(
			'nth-notifications-admin',
			NTH_NOTIFY_URL . 'assets/js/admin.js',
			[],
			NTH_NOTIFY_VERSION,
			true // Load in footer
		);
		
		// Localize script for AJAX and translations.
		wp_localize_script(
			'nth-notifications-admin',
			'nthNotifications',
			[
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'nth_notifications_test' ),
				'i18n'     => [
					'show'                => __( 'Show', 'nth-notifications' ),
					'hide'                => __( 'Hide', 'nth-notifications' ),
					'test'                => __( 'Test', 'nth-notifications' ),
					'remove'              => __( 'Remove', 'nth-notifications' ),
					'testing'             => __( 'Testing...', 'nth-notifications' ),
					'sendingTestMessage'  => __( 'Sending test message...', 'nth-notifications' ),
					'pleaseEnterChatId'   => __( 'Please enter Chat ID', 'nth-notifications' ),
					'pleaseEnterBotToken' => __( 'Please enter Bot Token first', 'nth-notifications' ),
					'connectionError'     => __( 'Connection error', 'nth-notifications' ),
					'atLeastOneChatId'    => __( 'At least one Chat ID is required.', 'nth-notifications' ),
					'waitingForMessage'   => __( '⏳ Waiting for message...', 'nth-notifications' ),
					'findChatId'          => __( '🔎 Find Chat ID', 'nth-notifications' ),
					'chatIdFound'         => __( '✅ Chat ID found:', 'nth-notifications' ),
					'chatIdExists'        => __( 'This ID already exists in your list!', 'nth-notifications' ),
					'chatIdFoundAndAdded' => __( '✅ Chat ID found and added:', 'nth-notifications' ),
					'noMessageFound'      => __( '⚠️ No message found!\n\nPlease:\n1. Open Zalo app on your phone\n2. Search for your bot\n3. Send any message (e.g., "Hello")\n4. Click "Find Chat ID" button again',
						'nth-notifications' ),
					'error'               => __( '⚠️ Error:', 'nth-notifications' ),
				],
			]
		);
	}
	
	/**
	 * AJAX handler for testing Telegram connection
	 */
	public function ajax_test_telegram(): void {
		// Check nonce.
		check_ajax_referer( 'nth_notifications_test', 'nonce' );
		
		// Check permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Permission denied.', 'nth-notifications' ) ] );
		}
		
		$bot_token = isset( $_POST['bot_token'] ) ? sanitize_text_field( $_POST['bot_token'] ) : '';
		$chat_id   = isset( $_POST['chat_id'] ) ? sanitize_text_field( $_POST['chat_id'] ) : '';
		
		if ( empty( $bot_token ) || empty( $chat_id ) ) {
			wp_send_json_error( [ 'message' => __( 'Bot token and Chat ID are required.', 'nth-notifications' ) ] );
		}
		
		// Send test message.
		$api_url = "https://api.telegram.org/bot{$bot_token}/sendMessage";
		$message = $this->build_test_message();
		
		$response = wp_remote_post(
			$api_url,
			[
				'timeout' => 15,
				'body'    => [
					'chat_id'    => $chat_id,
					'text'       => $message,
					'parse_mode' => 'HTML',
				],
			]
		);
		
		if ( is_wp_error( $response ) ) {
			wp_send_json_error( [ 'message' => $response->get_error_message() ] );
		}
		
		$body   = wp_remote_retrieve_body( $response );
		$result = json_decode( $body, true );
		
		if ( isset( $result['ok'] ) && $result['ok'] ) {
			wp_send_json_success( [ 'message' => __( 'Test message sent successfully!', 'nth-notifications' ) ] );
		} else {
			$error_message = isset( $result['description'] ) ? $result['description'] : __( 'Unknown error',
				'nth-notifications' );
			wp_send_json_error( [ 'message' => $error_message ] );
		}
	}
	
	/**
	 * AJAX handler for testing Zalo configuration
	 */
	public function ajax_test_zalo(): void {
		// Check nonce.
		check_ajax_referer( 'nth_notifications_test', 'nonce' );
		
		// Check permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Permission denied.', 'nth-notifications' ) ] );
		}
		
		$bot_token = isset( $_POST['bot_token'] ) ? sanitize_text_field( $_POST['bot_token'] ) : '';
		$chat_id   = isset( $_POST['chat_id'] ) ? sanitize_text_field( $_POST['chat_id'] ) : '';
		
		if ( empty( $bot_token ) || empty( $chat_id ) ) {
			wp_send_json_error( [ 'message' => __( 'Bot token and Chat ID are required.', 'nth-notifications' ) ] );
		}
		
		// Send test message.
		$api_url = 'https://bot-api.zaloplatforms.com/bot' . $bot_token . '/sendMessage';
		$message = $this->build_test_message();
		
		$response = wp_remote_post(
			$api_url,
			[
				'timeout' => 15,
				'headers' => [
					'Content-Type' => 'application/json',
				],
				'body'    => wp_json_encode(
					[
						'chat_id' => $chat_id,
						'text'    => $message,
					]
				),
			]
		);
		
		if ( is_wp_error( $response ) ) {
			wp_send_json_error( [ 'message' => $response->get_error_message() ] );
		}
		
		$body   = wp_remote_retrieve_body( $response );
		$result = json_decode( $body, true );
		
		if ( isset( $result['ok'] ) && $result['ok'] ) {
			wp_send_json_success( [ 'message' => __( 'Test message sent successfully!', 'nth-notifications' ) ] );
		} else {
			$error_message = isset( $result['description'] ) ? $result['description'] : __( 'Unknown error',
				'nth-notifications' );
			wp_send_json_error( [ 'message' => $error_message ] );
		}
	}
	
	/**
	 * AJAX handler for finding Zalo Chat ID
	 */
	public function ajax_find_zalo_chat_id(): void {
		// Check nonce.
		check_ajax_referer( 'nth_notifications_test', 'nonce' );
		
		// Check permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Permission denied.', 'nth-notifications' ) ] );
		}
		
		$bot_token = isset( $_POST['bot_token'] ) ? sanitize_text_field( $_POST['bot_token'] ) : '';
		
		if ( empty( $bot_token ) ) {
			wp_send_json_error( [ 'message' => __( 'Bot token is required.', 'nth-notifications' ) ] );
		}
		
		// Create temporary Zalo instance with provided token.
		$zalo = new Zalo();
		// Use reflection to set bot_token temporarily (or pass it differently).
		// For security, we'll create a new instance with the token.
		$reflection = new \ReflectionClass( $zalo );
		$property   = $reflection->getProperty( 'bot_token' );
		$property->setAccessible( true );
		$property->setValue( $zalo, $bot_token );
		
		// Try to get the latest Chat ID.
		$result = $zalo->get_latest_chat_id();
		
		if ( $result['success'] ) {
			wp_send_json_success(
				[
					'chat_id' => $result['chat_id'],
					'message' => __( 'Chat ID found successfully! Send a message to the bot on Zalo to get your Chat ID.',
						'nth-notifications' ),
				]
			);
		} else {
			wp_send_json_error( [ 'message' => $result['message'] ] );
		}
	}
	
	private function build_test_message(): string {
		$website_url = get_site_url();
		$message     = __( 'Test message from NTH Notifications', 'nth-notifications' ) . "\n\n";
		$message     .= __( 'Website: ', 'nth-notifications' ) . "$website_url\n\n";
		$message     .= "✅ " . __( 'Configuration is working correctly!', 'nth-notifications' ) . "\n";
		$message     .= "🕐 " . current_time( 'd/m/Y H:i:s' );
		
		return $message;
	}
}