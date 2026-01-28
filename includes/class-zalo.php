<?php
/**
 * Zalo Notification Class
 *
 * @package NTH\Notifications
 */

namespace NTH\Notifications;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Zalo Class
 */
class Zalo {
	
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
	 * API Base URL
	 *
	 * @var string
	 */
	private string $api_base_url = 'https://bot-api.zaloplatforms.com/bot';
	
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->load_settings();
		$this->init_hooks();
		$this->formatter = new Message_Formatter( 'text' );
	}
	
	/**
	 * Load settings
	 */
	private function load_settings(): void {
		$settings = get_zalo_settings();
		
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
	 * Check if Zalo is configured properly
	 *
	 * @return bool
	 */
	private function is_configured(): bool {
		// Reload settings to ensure we have latest data.
		$this->load_settings();
		
		$is_enabled = is_zalo_enabled();
		$has_token  = ! empty( $this->bot_token );
		$has_chats  = ! empty( $this->chat_ids );
		
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'NTH Notifications - Zalo is_configured check:' );
			error_log( '  - is_enabled: ' . ( $is_enabled ? 'YES' : 'NO' ) );
			error_log( '  - has_token: ' . ( $has_token ? 'YES' : 'NO' ) );
			error_log( '  - has_chats: ' . ( $has_chats ? 'YES' : 'NO' ) );
		}
		
		return $has_token && $has_chats && $is_enabled;
	}
	
	/**
	 * Send message to Zalo
	 *
	 * @param string $message Message to send.
	 *
	 * @return bool|array True on success, error array on failure.
	 */
	public function send_message( string $message ): bool|array {
		if ( ! $this->is_configured() ) {
			return [
				'success' => false,
				'message' => __( 'Zalo is not properly configured.', 'nth-notifications' ),
			];
		}
		
		$results = [];
		
		foreach ( $this->chat_ids as $chat_id ) {
			$result    = $this->send_to_chat( $chat_id, $message );
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
	 * @param string $chat_id Chat ID.
	 * @param string $message Message to send.
	 *
	 * @return array
	 */
	private function send_to_chat( string $chat_id, string $message ): array {
		$api_url = $this->api_base_url . $this->bot_token . '/sendMessage';
		
		$body = [
			'chat_id' => $chat_id,
			'text'    => $message,
		];
		
		$response = wp_remote_post(
			$api_url,
			[
				'timeout' => 15,
				'headers' => [
					'Content-Type' => 'application/json',
				],
				'body'    => wp_json_encode( $body ),
			]
		);
		
		if ( is_wp_error( $response ) ) {
			return [
				'success' => false,
				'chat_id' => $chat_id,
				'error'   => $response->get_error_message(),
			];
		}
		
		$body_response = wp_remote_retrieve_body( $response );
		$result        = json_decode( $body_response, true );
		
		if ( isset( $result['ok'] ) && $result['ok'] ) {
			return [
				'success'    => true,
				'chat_id'    => $chat_id,
				'message_id' => isset( $result['result']['message_id'] ) ? $result['result']['message_id'] : null,
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
			error_log( 'NTH Notifications - Zalo send_new_order_notification called for order: ' . $order_id );
		}
		
		if ( ! $this->is_configured() ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'NTH Notifications - Zalo not configured, exiting' );
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
		$notification_sent = $order->get_meta( '_nth_zalo_notification_sent', true );
		if ( $notification_sent ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'NTH Notifications - Zalo notification already sent for order: ' . $order_id );
			}
			
			return;
		}
		
		// Build message.
		$message = $this->build_new_order_message( $order );
		
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'NTH Notifications - Sending Zalo message...' );
		}
		
		// Send notification.
		$result = $this->send_message( $message );
		
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'NTH Notifications - Zalo send result: ' . print_r( $result, true ) );
		}
		
		// Log result.
		if ( $result['success'] ) {
			$order->add_order_note(
				__( 'Zalo notification sent successfully.', 'nth-notifications' )
			);
			// Mark as sent to prevent duplicates.
			$order->update_meta_data( '_nth_zalo_notification_sent', true );
			$order->save();
		} else {
			$order->add_order_note(
				sprintf(
				/* translators: %s: error message */
					__( 'Failed to send Zalo notification: %s', 'nth-notifications' ),
					isset( $result['results'][0]['error'] ) ? $result['results'][0]['error'] : __( 'Unknown error',
						'nth-notifications' )
				)
			);
		}
		
		// Allow other plugins to hook into this action.
		do_action( 'nth_zalo_notification_sent', $order_id, $result );
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
		
		// Log result.
		if ( $result['success'] ) {
			$order->add_order_note(
				__( 'Zalo payment notification sent successfully.', 'nth-notifications' )
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
	
	/**
	 * Get updates from Zalo (polling mode)
	 * Note: This only works when webhook is NOT set
	 *
	 * @param int $offset Offset for updates.
	 * @param int $limit  Limit of updates.
	 *
	 * @return array
	 */
	public function get_updates( int $offset = 0, int $limit = 100 ): array {
		if ( empty( $this->bot_token ) ) {
			return [
				'success' => false,
				'message' => __( 'Bot Token is required.', 'nth-notifications' ),
			];
		}
		
		$api_url = $this->api_base_url . $this->bot_token . '/getUpdates';
		$api_url = add_query_arg(
			[
				'offset' => $offset,
				'limit'  => $limit,
			],
			$api_url
		);
		
		$response = wp_remote_get(
			$api_url,
			[
				'timeout' => 30,
			]
		);
		
		if ( is_wp_error( $response ) ) {
			return [
				'success' => false,
				'message' => $response->get_error_message(),
			];
		}
		
		$body   = wp_remote_retrieve_body( $response );
		$result = json_decode( $body, true );
		
		if ( isset( $result['ok'] ) && $result['ok'] ) {
			return [
				'success' => true,
				'data'    => isset( $result['result'] ) ? $result['result'] : [],
			];
		}
		
		return [
			'success' => false,
			'message' => isset( $result['description'] ) ? $result['description'] : __( 'Unknown error',
				'nth-notifications' ),
		];
	}
	
	/**
	 * Delete webhook
	 *
	 * @return array
	 */
	public function delete_webhook(): array {
		if ( empty( $this->bot_token ) ) {
			return [
				'success' => false,
				'message' => __( 'Bot Token is required.', 'nth-notifications' ),
			];
		}
		
		$api_url = $this->api_base_url . $this->bot_token . '/deleteWebhook';
		
		$response = wp_remote_post(
			$api_url,
			[
				'timeout' => 15,
				'headers' => [
					'Content-Type' => 'application/json',
				],
			]
		);
		
		if ( is_wp_error( $response ) ) {
			return [
				'success' => false,
				'message' => $response->get_error_message(),
			];
		}
		
		$body   = wp_remote_retrieve_body( $response );
		$result = json_decode( $body, true );
		
		if ( isset( $result['ok'] ) && $result['ok'] ) {
			return [
				'success' => true,
				'message' => __( 'Webhook deleted successfully.', 'nth-notifications' ),
			];
		}
		
		return [
			'success' => false,
			'message' => isset( $result['description'] ) ? $result['description'] : __( 'Unknown error',
				'nth-notifications' ),
		];
	}
	
	/**
	 * Get latest Chat ID from recent messages
	 *
	 * @return array
	 */
	public function get_latest_chat_id(): array {
		// First, try to delete webhook to ensure getUpdates works.
		$this->delete_webhook();
		
		// Get updates.
		$result = $this->get_updates();
		
		if ( ! $result['success'] ) {
			return $result;
		}
		
		$updates = $result['data'];
		
		// Debug logging.
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'NTH Notifications - Zalo getUpdates response: ' . print_r( $updates, true ) );
		}
		
		if ( empty( $updates ) ) {
			return [
				'success' => false,
				'message' => __( 'No messages found. Please send a message to the bot on Zalo (e.g., "Hello") and try again.',
					'nth-notifications' ),
			];
		}
		
		// Handle different response formats.
		$last_update = null;
		
		// If result is a single object with 'message' key.
		if ( isset( $updates['message'] ) ) {
			$last_update = $updates;
		} elseif ( is_array( $updates ) ) {
			// If result is an array of updates (standard case).
			$last_update = end( $updates );
		} else {
			// Debug: log unknown structure.
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'NTH Notifications - Unknown data structure: ' . print_r( $updates, true ) );
			}
			
			return [
				'success' => false,
				'message' => __( 'Unknown data structure.', 'nth-notifications' ),
			];
		}
		
		// Try to extract Chat ID from various structures.
		// Structure 1: message.chat.id (most common).
		if ( isset( $last_update['message']['chat']['id'] ) ) {
			return [
				'success' => true,
				'chat_id' => (string) $last_update['message']['chat']['id'],
			];
		}
		
		// Structure 2: my_chat_member.chat.id (when user joins/blocks bot).
		if ( isset( $last_update['my_chat_member']['chat']['id'] ) ) {
			return [
				'success' => true,
				'chat_id' => (string) $last_update['my_chat_member']['chat']['id'],
			];
		}
		
		// Structure 3: sender.id (alternative format).
		if ( isset( $last_update['sender']['id'] ) ) {
			return [
				'success' => true,
				'chat_id' => (string) $last_update['sender']['id'],
			];
		}
		
		// Structure 4: message.from.id (alternative format).
		if ( isset( $last_update['message']['from']['id'] ) ) {
			return [
				'success' => true,
				'chat_id' => (string) $last_update['message']['from']['id'],
			];
		}
		
		// Debug: log when no structure matches.
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'NTH Notifications - Could not extract Chat ID. Last update structure: ' . print_r( $last_update,
					true ) );
		}
		
		return [
			'success' => false,
			'message' => __( 'Could not find Chat ID in the response data. Please try again.', 'nth-notifications' ),
		];
	}
}
