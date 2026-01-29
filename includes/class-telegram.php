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
class Telegram extends Abstract_Notification_Channel {
	
	/**
	 * API Base URL
	 *
	 * @var string
	 */
	private string $api_base_url = 'https://api.telegram.org/bot';
	
	/**
	 * Parse mode for Telegram messages
	 *
	 * @var string
	 */
	private string $parse_mode = 'HTML';
	
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->channel_name = 'telegram';
		parent::__construct( 'html' );
	}
	
	/**
	 * Load settings
	 */
	protected function load_settings(): void {
		$settings = get_telegram_settings();
		
		$this->bot_token = isset( $settings['bot_token'] ) ? $settings['bot_token'] : '';
		$this->chat_ids  = isset( $settings['chat_ids'] ) ? array_filter( $settings['chat_ids'] ) : [];
	}
	
	/**
	 * Check if Telegram is enabled
	 *
	 * @return bool
	 */
	protected function is_enabled(): bool {
		return is_telegram_enabled();
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
		
		$data = [
			'chat_id'    => $chat_id,
			'text'       => $message,
			'parse_mode' => $this->parse_mode,
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
			'error'   => isset( $result['description'] ) ? $result['description'] : __( 'Unknown error', 'nth-notify' ),
		];
	}
}
