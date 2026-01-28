<?php
/**
 * Abstract Notification Channel Class
 *
 * @package NTH\Notifications
 */

namespace NTH\Notifications;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract Notification Channel Class
 * Base class for all notification channels (Telegram, Zalo, etc.)
 */
abstract class Abstract_Notification_Channel {
	
	/**
	 * Bot Token
	 *
	 * @var string
	 */
	protected string $bot_token = '';
	
	/**
	 * Chat IDs
	 *
	 * @var array
	 */
	protected array $chat_ids = [];
	
	/**
	 * Message formatter instance
	 *
	 * @var Message_Formatter
	 */
	protected Message_Formatter $formatter;
	
	/**
	 * Channel name (telegram, zalo, etc.)
	 *
	 * @var string
	 */
	protected string $channel_name = '';
	
	/**
	 * Constructor
	 *
	 * @param string $format_type Message format type (html or text).
	 */
	public function __construct( string $format_type = 'text' ) {
		$this->load_settings();
		$this->init_hooks();
		$this->formatter = new Message_Formatter( $format_type );
	}
	
	/**
	 * Load settings - must be implemented by child class
	 */
	abstract protected function load_settings(): void;
	
	/**
	 * Check if channel is enabled - must be implemented by child class
	 *
	 * @return bool
	 */
	abstract protected function is_enabled(): bool;
	
	/**
	 * Send message to specific chat - must be implemented by child class
	 *
	 * @param string $chat_id Chat ID.
	 * @param string $message Message to send.
	 *
	 * @return array
	 */
	abstract protected function send_to_chat( string $chat_id, string $message ): array;
	
	/**
	 * Initialize hooks
	 */
	protected function init_hooks(): void {
		add_action( 'nth_notifications_new_order', [ $this, 'send_new_order_notification' ], 10, 1 );
	}
	
	/**
	 * Check if channel is configured properly
	 *
	 * @return bool
	 */
	protected function is_configured(): bool {
		// Reload settings to ensure we have latest data.
		$this->load_settings();
		
		$is_enabled = $this->is_enabled();
		$has_token  = ! empty( $this->bot_token );
		$has_chats  = ! empty( $this->chat_ids );
		
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( sprintf(
				'NTH Notifications - %s is_configured check: enabled=%s, token=%s, chats=%s',
				ucfirst( $this->channel_name ),
				$is_enabled ? 'YES' : 'NO',
				$has_token ? 'YES' : 'NO',
				$has_chats ? 'YES' : 'NO'
			) );
		}
		
		return $has_token && $has_chats && $is_enabled;
	}
	
	/**
	 * Send message to all configured chats
	 *
	 * @param string $message Message to send.
	 *
	 * @return array Response with success status and results.
	 */
	public function send_message( string $message ): array {
		if ( ! $this->is_configured() ) {
			return [
				'success' => false,
				'message' => sprintf(
					/* translators: %s: channel name */
					__( '%s is not properly configured.', 'nth-notifications' ),
					ucfirst( $this->channel_name )
				),
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
	 * Send new order notification
	 *
	 * @param int $order_id Order ID.
	 */
	public function send_new_order_notification( int $order_id ): void {
		// Debug logging.
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( sprintf(
				'NTH Notifications - %s send_new_order_notification called for order: %d',
				ucfirst( $this->channel_name ),
				$order_id
			) );
		}
		
		if ( ! $this->is_configured() ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( sprintf(
					'NTH Notifications - %s not configured, exiting',
					ucfirst( $this->channel_name )
				) );
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
		
		// Check if notification already sent for this order and status combination.
		$current_status     = $order->get_status();
		$last_notified_status = $order->get_meta( "_nth_{$this->channel_name}_last_status", true );
		
		// Allow re-notification for cancelled and failed statuses.
		$allow_renotify_statuses = [ 'cancelled', 'failed' ];
		
		if ( $last_notified_status === $current_status && ! in_array( $current_status, $allow_renotify_statuses, true ) ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( sprintf(
					'NTH Notifications - %s notification already sent for order %d with status %s',
					ucfirst( $this->channel_name ),
					$order_id,
					$current_status
				) );
			}
			return;
		}
		
		// Build message.
		$message = $this->formatter->build_new_order_message( $order );
		
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( sprintf(
				'NTH Notifications - Sending %s message...',
				$this->channel_name
			) );
		}
		
		// Send notification.
		$result = $this->send_message( $message );
		
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( sprintf(
				'NTH Notifications - %s send result: %s',
				ucfirst( $this->channel_name ),
				print_r( $result, true )
			) );
		}
		
		// Log result.
		if ( $result['success'] ) {
			$order->add_order_note(
				sprintf(
					/* translators: %s: channel name */
					__( '%s notification sent successfully.', 'nth-notifications' ),
					ucfirst( $this->channel_name )
				)
			);
			
			// Update last notified status instead of simple boolean flag.
			$order->update_meta_data( "_nth_{$this->channel_name}_last_status", $current_status );
			$order->save();
		} else {
			$error_message = isset( $result['results'][0]['error'] ) ? $result['results'][0]['error'] : __( 'Unknown error', 'nth-notifications' );
			
			$order->add_order_note(
				sprintf(
					/* translators: 1: channel name, 2: error message */
					__( 'Failed to send %1$s notification: %2$s', 'nth-notifications' ),
					ucfirst( $this->channel_name ),
					$error_message
				)
			);
		}
		
		// Allow other plugins to hook into this action.
		do_action( "nth_{$this->channel_name}_notification_sent", $order_id, $result );
	}
}
