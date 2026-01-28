<?php
/**
 * Telegram Notification Class
 *
 * @package NTH\Notifications
 */

namespace NTH\Notifications;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Telegram Class
 */
class Telegram {
	
	/**
	 * Bot Token
	 *
	 * @var string
	 */
	private string $bot_token;
	
	/**
	 * Chat IDs
	 *
	 * @var array
	 */
	private array $chat_ids;
	/**
	 * Message formatter instance
	 *
	 * @var Message_Formatter
	 */
	private Message_Formatter $formatter;
	
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->load_settings();
		$this->init_hooks();
		$this->formatter = new Message_Formatter( 'html' );
	}
	
	/**
	 * Load settings
	 */
	private function load_settings(): void {
		$settings = get_telegram_settings();
		
		$this->bot_token = isset( $settings['bot_token'] ) ? $settings['bot_token'] : '';
		$this->chat_ids  = isset( $settings['chat_ids'] ) ? array_filter( $settings['chat_ids'] ) : [];
	}
	
	/**
	 * Initialize hooks
	 */
	private function init_hooks(): void {
		// Hook to custom notification actions.
		add_action( 'nth_notifications_new_order', [ $this, 'send_new_order_notification' ], 10, 1 );
		add_action( 'nth_notifications_payment_complete', [ $this, 'send_payment_complete_notification' ], 10, 1 );
	}
	
	/**
	 * Check if Telegram is configured properly
	 *
	 * @return bool
	 */
	private function is_configured(): bool {
		// Reload settings to ensure we have latest data.
		$this->load_settings();
		
		$is_enabled = is_telegram_enabled();
		$has_token  = ! empty( $this->bot_token );
		$has_chats  = ! empty( $this->chat_ids );
		
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'NTH Notifications - is_configured check:' );
			error_log( '  - is_enabled: ' . ( $is_enabled ? 'YES' : 'NO' ) );
			error_log( '  - has_token: ' . ( $has_token ? 'YES' : 'NO' ) );
			error_log( '  - has_chats: ' . ( $has_chats ? 'YES' : 'NO' ) );
		}
		
		return $has_token && $has_chats && $is_enabled;
	}
	
	/**
	 * Send message to Telegram
	 *
	 * @param string $message    Message to send.
	 * @param string $parse_mode Parse mode (Markdown, HTML, or empty).
	 *
	 * @return bool|array True on success, error array on failure.
	 */
	public function send_message( string $message, string $parse_mode = 'HTML' ): bool|array {
		if ( ! $this->is_configured() ) {
			return [
				'success' => false,
				'message' => __( 'Telegram is not properly configured.', 'nth-notifications' ),
			];
		}
		
		$results = [];
		
		foreach ( $this->chat_ids as $chat_id ) {
			$result    = $this->send_to_chat( $chat_id, $message, $parse_mode );
			$results[] = $result;
		}
		
		// Return true if at least one message was sent successfully.
		$success = in_array( true, array_column( $results, 'success' ), true );
		
		return [
			'success' => $success,
			'results' => $results,
		];
	}
	
	/**
	 * Send message to specific chat
	 *
	 * @param string $chat_id    Chat ID.
	 * @param string $message    Message to send.
	 * @param string $parse_mode Parse mode.
	 *
	 * @return array
	 */
	private function send_to_chat( string $chat_id, string $message, string $parse_mode = 'HTML' ): array {
		$api_url = "https://api.telegram.org/bot{$this->bot_token}/sendMessage";
		
		$data = [
			'chat_id'    => $chat_id,
			'text'       => $message,
			'parse_mode' => $parse_mode,
		];
		
		$response = wp_remote_post(
			$api_url,
			[
				'timeout' => 15,
				'body'    => $data,
			]
		);
		
		if ( is_wp_error( $response ) ) {
			return [
				'success' => false,
				'chat_id' => $chat_id,
				'error'   => $response->get_error_message(),
			];
		}
		
		$body   = wp_remote_retrieve_body( $response );
		$result = json_decode( $body, true );
		
		if ( isset( $result['ok'] ) && $result['ok'] ) {
			return [
				'success'    => true,
				'chat_id'    => $chat_id,
				'message_id' => $result['result']['message_id'],
			];
		}
		
		return [
			'success' => false,
			'chat_id' => $chat_id,
			'error'   => isset( $result['description'] ) ? $result['description'] : __( 'Unknown error',
				'nth-notifications' ),
		];
	}
	
	/**
	 * Send new order notification
	 *
	 * @param int $order_id Order ID.
	 */
	public function send_new_order_notification( int $order_id ): void {
		// Debug logging.
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'NTH Notifications - Telegram send_new_order_notification called for order: ' . $order_id );
			error_log( 'NTH Notifications - Bot token: ' . ( ! empty( $this->bot_token ) ? 'SET' : 'EMPTY' ) );
			error_log( 'NTH Notifications - Chat IDs: ' . print_r( $this->chat_ids, true ) );
			error_log( 'NTH Notifications - is_telegram_enabled: ' . ( is_telegram_enabled() ? 'YES' : 'NO' ) );
			error_log( 'NTH Notifications - is_configured: ' . ( $this->is_configured() ? 'YES' : 'NO' ) );
		}
		
		if ( ! $this->is_configured() ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'NTH Notifications - Telegram not configured, exiting' );
			}
			
			return;
		}
		
		// Get order object.
		$order = wc_get_order( $order_id );
		
		if ( ! $order ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'NTH Notifications - Order not found: ' . $order_id );
			}
			
			return;
		}
		
		// Check if notification already sent (prevent duplicate notifications).
		$notification_sent = $order->get_meta( '_nth_telegram_notification_sent', true );
		if ( $notification_sent ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'NTH Notifications - Notification already sent for order: ' . $order_id );
			}
			
			return;
		}
		
		// Build message.
		$message = $this->build_new_order_message( $order );
		
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'NTH Notifications - Sending message to Telegram...' );
		}
		
		// Send notification.
		$result = $this->send_message( $message );
		
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'NTH Notifications - Send result: ' . print_r( $result, true ) );
		}
		
		// Log result (optional).
		if ( $result['success'] ) {
			$order->add_order_note(
				__( 'Telegram notification sent successfully.', 'nth-notifications' )
			);
			// Mark as sent to prevent duplicates.
			$order->update_meta_data( '_nth_telegram_notification_sent', true );
			$order->save();
		} else {
			$order->add_order_note(
				sprintf(
				/* translators: %s: error message */
					__( 'Failed to send Telegram notification: %s', 'nth-notifications' ),
					isset( $result['results'][0]['error'] ) ? $result['results'][0]['error'] : __( 'Unknown error',
						'nth-notifications' )
				)
			);
		}
		
		// Allow other plugins to hook into this action.
		do_action( 'nth_telegram_notification_sent', $order_id, $result );
	}
	
	/**
	 * Send payment complete notification
	 *
	 * @param int $order_id Order ID.
	 */
	public function send_payment_complete_notification( int $order_id ): void {
		if ( ! $this->is_configured() ) {
			return;
		}
		
		// Get order object.
		$order = wc_get_order( $order_id );
		
		if ( ! $order ) {
			return;
		}
		
		// Build message.
		$message = $this->build_payment_complete_message( $order );
		
		// Send notification.
		$result = $this->send_message( $message );
		
		// Log result (optional).
		if ( $result['success'] ) {
			$order->add_order_note(
				__( 'Telegram payment notification sent successfully.', 'nth-notifications' )
			);
		}
	}
	
	/**
	 * Build new order message
	 *
	 * @param \WC_Order $order Order object.
	 *
	 * @return string
	 */
	private function build_new_order_message( \WC_Order $order ): string {
		return $this->formatter->build_new_order_message( $order );
	}
	
	/**
	 * Build payment complete message
	 *
	 * @param \WC_Order $order Order object.
	 *
	 * @return string
	 */
	private function build_payment_complete_message( \WC_Order $order ): string {
		return $this->formatter->build_payment_complete_message( $order );
	}
}
