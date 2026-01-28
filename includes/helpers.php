<?php
/**
 * Helper Functions
 *
 * @package NTH\Notifications
 */

namespace NTH\Notifications;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get general settings
 *
 * @return array
 */
function get_general_settings(): array {
	$defaults = [
		'telegram_enabled' => false,
		'zalo_enabled'     => false,
	];
	
	return get_option( 'nth_notifications_settings', $defaults );
}

/**
 * Check if Telegram is enabled
 *
 * @return bool
 */
function is_telegram_enabled(): bool {
	$settings = get_general_settings();
	
	return ! empty( $settings['telegram_enabled'] );
}

/**
 * Check if Zalo is enabled
 *
 * @return bool
 */
function is_zalo_enabled(): bool {
	$settings = get_general_settings();
	
	return ! empty( $settings['zalo_enabled'] );
}

/**
 * Get Telegram settings
 *
 * @return array
 */
function get_telegram_settings(): array {
	$defaults = [
		'bot_token' => '',
		'chat_ids'  => [],
	];
	
	return get_option( 'nth_notifications_telegram', $defaults );
}

/**
 * Get Zalo settings
 *
 * @return array
 */
function get_zalo_settings(): array {
	$defaults = [
		'bot_token' => '',
		'chat_ids'  => [],
	];
	
	return get_option( 'nth_notifications_zalo', $defaults );
}
