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
class Zalo extends Abstract_Notification_Channel {
	
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
		$this->channel_name = 'zalo';
		parent::__construct( 'text' );
	}
	
	/**
	 * Load settings
	 */
	protected function load_settings(): void {
		$settings = get_zalo_settings();
		
		$this->bot_token = isset( $settings['bot_token'] ) ? $settings['bot_token'] : '';
		$this->chat_ids  = isset( $settings['chat_ids'] ) ? array_filter( $settings['chat_ids'] ) : [];
	}
	
	/**
	 * Check if Zalo is enabled
	 *
	 * @return bool
	 */
	protected function is_enabled(): bool {
		return is_zalo_enabled();
	}
	
	/**
	 * Send message to specific chat
	 *
	 * @param string $chat_id Chat ID.
	 * @param string $message Message to send.
	 *
	 * @return array
	 */
	protected function send_to_chat( string $chat_id, string $message ): array {
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
			'error'   => isset( $result['description'] ) ? $result['description'] : __( 'Unknown error', 'nth-notify' ),
		];
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
				'message' => __( 'Bot Token is required.', 'nth-notify' ),
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
				'nth-notify' ),
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
				'message' => __( 'Bot Token is required.', 'nth-notify' ),
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
				'message' => __( 'Webhook deleted successfully.', 'nth-notify' ),
			];
		}
		
		return [
			'success' => false,
			'message' => isset( $result['description'] ) ? $result['description'] : __( 'Unknown error',
				'nth-notify' ),
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
			error_log( 'NTH Notify - Zalo getUpdates response: ' . print_r( $updates, true ) );
		}
		
		if ( empty( $updates ) ) {
			return [
				'success' => false,
				'message' => __( 'No messages found. Please send a message to the bot on Zalo (e.g., "Hello") and try again.',
					'nth-notify' ),
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
				error_log( 'NTH Notify - Unknown data structure: ' . print_r( $updates, true ) );
			}
			
			return [
				'success' => false,
				'message' => __( 'Unknown data structure.', 'nth-notify' ),
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
			error_log( 'NTH Notify - Could not extract Chat ID. Last update structure: ' . print_r( $last_update,
					true ) );
		}
		
		return [
			'success' => false,
			'message' => __( 'Could not find Chat ID in the response data. Please try again.', 'nth-notify' ),
		];
	}
}
