<?php
/**
 * Uninstall Script
 *
 * @package NTH\Notifications
 */

// Exit if not called from WordPress.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete plugin options.
delete_option( 'nth_notifications_settings' );
delete_option( 'nth_notifications_telegram' );
delete_option( 'nth_notifications_zalo' );

// For multisite installations.
if ( is_multisite() ) {
	global $wpdb;

	// Get all blog IDs.
	$blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" );

	// Delete options for each blog.
	foreach ( $blog_ids as $blog_id ) {
		switch_to_blog( $blog_id );
		delete_option( 'nth_notifications_settings' );
		delete_option( 'nth_notifications_telegram' );
		delete_option( 'nth_notifications_zalo' );
		restore_current_blog();
	}
}
