<?php
/**
 * Message Formatter Class
 *
 * Unified message formatting for all notification channels (Telegram, Zalo, Discord, etc.)
 *
 * @package NTH\Notifications
 */

namespace NTH\Notifications;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Message Formatter Class
 */
class Message_Formatter {

	/**
	 * Format type (html for Telegram, text for Zalo)
	 *
	 * @var string
	 */
	private string $format_type;

	/**
	 * Constructor
	 *
	 * @param string $format_type Format type: 'html' or 'text'.
	 */
	public function __construct( string $format_type = 'text' ) {
		$this->format_type = $format_type;
	}

	/**
	 * Build new order message
	 *
	 * @param \WC_Order $order Order object.
	 *
	 * @return string
	 */
	public function build_new_order_message( \WC_Order $order ): string {
		$message = $this->format_header( '🛒 ' . __( 'NEW ORDER', 'nth-notifications' ) );
		$message .= "\n";
		$message .= $this->format_line( '🌐', __( 'Website: ', 'nth-notifications' ), get_site_url() );
		$message .= "\n\n";

		// Order details
		$message .= $this->format_line( '📋', __( 'Order ID:', 'nth-notifications' ), '#' . $order->get_order_number() );
		$message .= $this->format_line( '👤',
			__( 'Customer:', 'nth-notifications' ),
			$order->get_billing_first_name() . ' ' . $order->get_billing_last_name() );
		$message .= $this->format_line( '📧', __( 'Email:', 'nth-notifications' ), $order->get_billing_email() );
		$message .= $this->format_line( '📱', __( 'Phone:', 'nth-notifications' ), $order->get_billing_phone() );
		$message .= "\n";

		// Address
		$shipping_address = $this->get_shipping_address( $order );
		$billing_address = $this->get_billing_address( $order );
		if ( ! empty( $shipping_address ) ) {
			$message .= $this->format_line( '📍', __( 'Shipping Address:', 'nth-notifications' ), $shipping_address );
			$message .= "\n";
		} elseif ( ! empty( $billing_address ) ) {
			$message .= $this->format_line( '📍', __( 'Billing Address:', 'nth-notifications' ), $billing_address );
			$message .= "\n";
		}

		// Order totals and payment
		$message .= $this->format_line( '💰',
			__( 'Total:', 'nth-notifications' ),
			$this->format_price( $order->get_formatted_order_total() ) );
		$message .= $this->format_line( '💳',
			__( 'Payment Method:', 'nth-notifications' ),
			$order->get_payment_method_title() );
		$message .= $this->format_line( '📊',
			__( 'Status:', 'nth-notifications' ),
			wc_get_order_status_name( $order->get_status() ) );
		$message .= "\n";

		// Items
		$items = $order->get_items();
		if ( ! empty( $items ) ) {
			$message .= $this->format_bold( '📦 ' . __( 'Items:', 'nth-notifications' ) ) . "\n";
			foreach ( $items as $item ) {
				$product_name = $item->get_name();
				$quantity = $item->get_quantity();
				$message .= "   • {$product_name} x{$quantity}\n";
			}
			$message .= "\n";
		}

		// Order link
		$order_url = $order->get_edit_order_url();
		$message .= $this->format_link( '🔗 ' . __( 'View Order in Admin', 'nth-notifications' ), $order_url );
		$message .= "\n";

		// Date/time
		$date = $order->get_date_created()->date_i18n( 'd/m/Y H:i' );
		$message .= $this->format_line( '🕐', __( 'Date:', 'nth-notifications' ), $date );

		return apply_filters( 'nth_notifications_new_order_message', $message, $order, $this->format_type );
	}

	/**
	 * Get shipping address as formatted string
	 *
	 * @param \WC_Order $order Order object.
	 *
	 * @return string
	 */
	private function get_shipping_address( \WC_Order $order ): string {
		$address_parts = [];

		if ( $order->get_shipping_address_1() ) {
			$address_parts[] = $order->get_shipping_address_1();
		}

		if ( $order->get_shipping_address_2() ) {
			$address_parts[] = $order->get_shipping_address_2();
		}

		if ( $order->get_shipping_city() ) {
			$address_parts[] = $order->get_shipping_city();
		}

		if ( $order->get_shipping_state() ) {
			$address_parts[] = $order->get_shipping_state();
		}

		if ( $order->get_shipping_postcode() ) {
			$address_parts[] = $order->get_shipping_postcode();
		}

		if ( $order->get_shipping_country() ) {
			$address_parts[] = WC()->countries->countries[ $order->get_shipping_country()] ?? $order->get_shipping_country();
		}

		return implode( ', ', array_filter( $address_parts ) );
	}

	/**
	 * Get billing address as formatted string
	 *
	 * @param \WC_Order $order Order object.
	 *
	 * @return string
	 */
	private function get_billing_address( \WC_Order $order ): string {
		$address_parts = [];

		if ( $order->get_billing_address_1() ) {
			$address_parts[] = $order->get_billing_address_1();
		}

		if ( $order->get_billing_address_2() ) {
			$address_parts[] = $order->get_billing_address_2();
		}

		if ( $order->get_billing_city() ) {
			$address_parts[] = $order->get_billing_city();
		}

		if ( $order->get_billing_state() ) {
			$address_parts[] = $order->get_billing_state();
		}

		if ( $order->get_billing_postcode() ) {
			$address_parts[] = $order->get_billing_postcode();
		}

		if ( $order->get_billing_country() ) {
			$address_parts[] = WC()->countries->countries[ $order->get_billing_country()] ?? $order->get_billing_country();
		}

		return implode( ', ', array_filter( $address_parts ) );
	}

	/**
	 * Format header text
	 *
	 * @param string $text Header text.
	 *
	 * @return string
	 */
	private function format_header( string $text ): string {
		if ( 'html' === $this->format_type ) {
			return '<b>' . esc_html( $text ) . '</b>';
		}

		return $text;
	}

	/**
	 * Format bold text
	 *
	 * @param string $text Text to make bold.
	 *
	 * @return string
	 */
	private function format_bold( string $text ): string {
		if ( 'html' === $this->format_type ) {
			return '<b>' . esc_html( $text ) . '</b>';
		}

		return $text;
	}

	/**
	 * Format a line with icon, label, and value
	 *
	 * @param string $icon  Icon emoji.
	 * @param string $label Label text.
	 * @param string $value Value text.
	 *
	 * @return string
	 */
	private function format_line( string $icon, string $label, string $value ): string {
		$clean_value = $this->clean_text( $value );

		if ( 'html' === $this->format_type ) {
			return "{$icon} <b>" . esc_html( $label ) . "</b> {$clean_value}\n";
		}

		return "{$icon} {$label} {$clean_value}\n";
	}

	/**
	 * Format a link
	 *
	 * @param string $text Link text.
	 * @param string $url  URL.
	 *
	 * @return string
	 */
	private function format_link( string $text, string $url ): string {
		if ( 'html' === $this->format_type ) {
			return '<a href="' . esc_url( $url ) . '">' . esc_html( $text ) . '</a>';
		}

		return "{$text}: {$url}";
	}

	/**
	 * Format price - remove HTML entities and tags
	 *
	 * @param string $price Formatted price.
	 *
	 * @return string
	 */
	private function format_price( string $price ): string {
		// Remove all HTML tags
		$price = wp_strip_all_tags( $price );

		// Decode HTML entities (fixes &nbsp; and &#8363; issues)
		$price = html_entity_decode( $price, ENT_QUOTES | ENT_HTML5, 'UTF-8' );

		// Clean up extra whitespace
		$price = preg_replace( '/\s+/', ' ', $price );
		$price = trim( $price );

		return $price;
	}

	/**
	 * Clean text for messaging
	 *
	 * @param string $text Text to clean.
	 *
	 * @return string
	 */
	private function clean_text( string $text ): string {
		// Remove HTML tags
		$text = wp_strip_all_tags( $text );

		// Decode HTML entities
		$text = html_entity_decode( $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' );

		// For HTML format (Telegram), escape special characters
		if ( 'html' === $this->format_type ) {
			$text = htmlspecialchars( $text, ENT_QUOTES | ENT_HTML5, 'UTF-8', false );
		}

		// Clean up whitespace
		$text = preg_replace( '/\s+/', ' ', $text );
		$text = trim( $text );

		return $text;
	}
}
