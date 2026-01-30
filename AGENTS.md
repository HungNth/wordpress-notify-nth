# AGENTS.md - NTH Notify Plugin

> Guidelines for AI coding agents working in this WordPress plugin codebase.

## Project Overview

NTH Notify is a WordPress plugin that sends order notifications via Telegram and Zalo when WooCommerce order statuses change. It follows WordPress coding standards with a modern PHP 7.4+ approach.

**Requirements:** WordPress 5.8+, PHP 7.4+, WooCommerce

## Build/Lint/Test Commands

This plugin has no build system, package manager, or automated testing framework.

### Manual Testing

- **Test notification channels:** Use the "Test" buttons in Settings > NTH Notify
- **Debug logging:** Enable `WP_DEBUG` in `wp-config.php` - logs appear in `wp-content/debug.log` with prefix `NTH Notify -`

### Release Workflow

```bash
# Create release ZIP via GitHub Actions (manual trigger)
gh workflow run release-plugin.yml -f confirm=yes
```

## Directory Structure

```
nth-nofi/
├── assets/
│   ├── css/admin.css          # Admin styles (BEM naming)
│   └── js/admin.js            # Vanilla JS admin scripts
├── includes/
│   ├── class-plugin.php                       # Main singleton class
│   ├── class-abstract-notification-channel.php # Base channel class
│   ├── class-telegram.php                     # Telegram implementation
│   ├── class-zalo.php                         # Zalo implementation
│   ├── class-admin.php                        # Admin UI & AJAX handlers
│   ├── class-settings.php                     # Settings page
│   ├── class-message-formatter.php            # Message formatting
│   └── helpers.php                            # Helper functions
├── languages/                 # Translation files
├── nth-notify.php             # Main plugin file, autoloader
└── uninstall.php              # Cleanup on uninstall
```

## Code Style Guidelines

### PHP

#### Namespace & Autoloading

- **Namespace:** `NTH\Notifications`
- **Autoloader:** PSR-4 style in `nth-notify.php`
- Class `Message_Formatter` maps to `includes/class-message-formatter.php`

#### File & Class Naming

```php
// File naming: class-{name}.php (WordPress convention)
class-abstract-notification-channel.php
class-message-formatter.php

// Class naming: PascalCase with underscores
class Abstract_Notification_Channel {}
class Message_Formatter {}
```

#### Method & Property Naming

```php
// Methods and properties: snake_case
private string $bot_token = '';
protected array $chat_ids = [];

public function load_settings(): void {}
public function send_new_order_notification( int $order_id ): void {}
```

#### Type Declarations (Required)

```php
// Use PHP 7.4+ typed properties
private static ?Plugin $instance = null;
protected string $channel_name = '';
protected array $chat_ids = [];

// Use return types on all methods
public function is_enabled(): bool {}
public function send_message( string $message ): array {}
protected function load_settings(): void {}
```

#### DocBlocks

```php
/**
 * Send message to specific chat
 *
 * @param string $chat_id Chat ID.
 * @param string $message Message to send.
 *
 * @return array
 */
protected function send_to_chat( string $chat_id, string $message ): array {}
```

#### Security (Critical)

```php
// ALWAYS include at file start
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ALWAYS verify nonces for AJAX
check_ajax_referer( 'nth_notifications_test', 'nonce' );

// ALWAYS check capabilities
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( 'Unauthorized' );
}

// ALWAYS sanitize input
$chat_id = sanitize_text_field( $_POST['chat_id'] );

// ALWAYS escape output
echo esc_html( $message );
echo esc_url( $url );
echo esc_attr( $attribute );
```

#### Internationalization

```php
// Text domain: nth-notify
__( 'Settings', 'nth-notify' )
esc_html__( 'Test Message', 'nth-notify' )
esc_html_e( 'Save Changes', 'nth-notify' )

// With placeholders - add translator comment
/* translators: %s: channel name */
sprintf( __( '%s notification sent successfully.', 'nth-notify' ), $channel_name )
```

#### WordPress Hooks

```php
// Use array syntax for callbacks
add_action( 'plugins_loaded', [ $this, 'init' ] );
add_filter( 'plugin_action_links_' . NTH_NOTIFY_BASENAME, [ $this, 'add_settings_link' ] );

// Custom hooks for extensibility
do_action( 'nth_notifications_new_order', $order_id );
do_action( "nth_{$this->channel_name}_notification_sent", $order_id, $result );
apply_filters( 'nth_notifications_new_order_message', $message, $order, $this->format_type );
```

#### Formatting

- **Indentation:** Tabs (not spaces)
- **Braces:** Opening brace on same line
- **Spaces:** After commas, around operators, inside parentheses for function calls

```php
if ( $condition ) {
    do_something( $param1, $param2 );
}
```

### JavaScript

#### Pattern

```javascript
// IIFE with strict mode
(function() {
    'use strict';
    
    // DOM ready check
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
    function init() {
        initFeatureOne();
        initFeatureTwo();
    }
})();
```

#### Style

- **No jQuery** - Use vanilla JavaScript only
- **Function naming:** camelCase (`initTokenToggle`, `sendAjaxRequest`)
- **DOM queries:** `document.querySelector()`, `document.querySelectorAll()`
- **Events:** `addEventListener()` with event delegation for dynamic elements

### CSS

- **BEM-like naming** with plugin prefix: `.nth-notify__chat-row`, `.nth-notify__test-result`
- **Component prefix:** `nth-notify`

## Architecture Patterns

### Singleton (Main Plugin)

```php
class Plugin {
    private static ?Plugin $instance = null;
    
    public static function instance(): ?Plugin {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() { /* ... */ }
}
```

### Abstract Channel Pattern

Extend `Abstract_Notification_Channel` for new notification channels:

```php
class New_Channel extends Abstract_Notification_Channel {
    protected string $channel_name = 'new_channel';
    
    protected function load_settings(): void { /* ... */ }
    protected function is_enabled(): bool { /* ... */ }
    protected function send_to_chat( string $chat_id, string $message ): array { /* ... */ }
}
```

### AJAX Handler Pattern

```php
add_action( 'wp_ajax_nth_action_name', [ $this, 'handle_ajax' ] );

public function handle_ajax(): void {
    check_ajax_referer( 'nth_notifications_test', 'nonce' );
    
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( [ 'message' => 'Unauthorized' ] );
    }
    
    // Process request...
    wp_send_json_success( [ 'message' => 'Success' ] );
}
```

## Database Options

| Option Key | Description |
|------------|-------------|
| `nth_notify_settings` | General settings (enabled channels, statuses) |
| `nth_notify_telegram` | Telegram bot token and chat IDs |
| `nth_notify_zalo` | Zalo bot token and chat IDs |

## Key Constants

```php
NTH_NOTIFY_VERSION   // Plugin version
NTH_NOTIFY_PATH      // Plugin directory path
NTH_NOTIFY_URL       // Plugin URL
NTH_NOTIFY_BASENAME  // Plugin basename for hooks
```
