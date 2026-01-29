=== NTH Notify - Enhance your WordPress site with powerful notification capabilities ===
Contributors: thienhungdev
Tags: woocommerce, notifications, telegram, zalo, orders, alerts, messaging
Requires at least: 5.8
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Receive instant WooCommerce order notifications via Telegram and Zalo. Support multiple channels, customizable order statuses, and easy Chat ID management.

== Description ==

**NTH Notify** is a powerful WordPress plugin that sends instant WooCommerce order notifications to your Telegram and Zalo accounts. Never miss an important order with real-time alerts delivered directly to your preferred messaging platforms.

= Key Features =

* **Dual Channel Support** - Send notifications to both Telegram and Zalo simultaneously
* **Multiple Chat IDs** - Add unlimited Chat IDs for team notifications
* **Order Status Selection** - Choose which order statuses trigger notifications (Pending, Processing, On-Hold, Completed, Cancelled, Refunded, Failed, Draft)
* **Dynamic Message Headers** - Order status-based headers with emoji indicators for quick recognition
* **Test Connection** - Built-in test buttons to verify your bot configuration
* **Find Chat ID** - Automated Chat ID finder for Zalo integration
* **Modern Admin UI** - Clean, intuitive interface built with Tailwind CSS
* **Debug Logging** - Comprehensive logging for troubleshooting
* **Translation Ready** - Full i18n support with Vietnamese and English included

= Supported Platforms =

* **Telegram** - Personal chats, groups, and channels
* **Zalo** - Personal accounts and business chats

= Use Cases =

* **E-commerce Store Owners** - Get instant alerts when new orders arrive
* **Team Management** - Notify multiple team members about order updates
* **Order Processing** - Stay informed about order status changes in real-time
* **Customer Service** - Quick notification for cancelled or failed orders
* **Business Analytics** - Track order flow through instant notifications

= How It Works =

1. **Install & Activate** - Simple one-click installation from WordPress admin
2. **Configure Bots** - Set up your Telegram and/or Zalo bots
3. **Add Chat IDs** - Enter the Chat IDs where you want to receive notifications
4. **Select Statuses** - Choose which order statuses should trigger notifications
5. **Test & Go Live** - Use the test buttons to verify, then start receiving real order notifications

= Message Format =

Notifications include comprehensive order details:
* Order number and status
* Customer name, email, and phone
* Order total with proper currency formatting
* Product list with quantities and prices
* Shipping address (if available)

= Privacy & Security =

* No data is sent to third-party servers (except Telegram/Zalo APIs)
* All bot tokens and Chat IDs are stored securely in your WordPress database
* Only order information is transmitted to configured notification channels
* Compliant with WordPress coding standards

= Documentation & Support =

* [GitHub Repository](https://github.com/yourusername/nth-notify)
* [Documentation](https://wptop.net/docs/nth-notify)
* [Report Issues](https://github.com/yourusername/nth-notify/issues)

== Installation ==

= Automatic Installation =

1. Log in to your WordPress admin panel
2. Navigate to **Plugins** > **Add New**
3. Search for "NTH Notify"
4. Click **Install Now** and then **Activate**

= Manual Installation =

1. Download the plugin ZIP file from [WordPress.org](https://wordpress.org/plugins/nth-notify/)
2. Navigate to **Plugins** > **Add New** > **Upload Plugin**
3. Select the downloaded ZIP file and click **Install Now**
4. After installation, click **Activate Plugin**

= Configuration =

**For Telegram:**

1. Go to **Settings** > **NTH Notify** > **General** tab
2. Enable **Telegram Notifications**
3. Go to **Telegram** tab
4. Create a bot using [@BotFather](https://t.me/BotFather) on Telegram
5. Enter your Bot Token
6. Get your Chat ID using [@JsonDumpBot](https://t.me/JsonDumpBot)
7. Enter Chat ID and click **Test** to verify
8. Save your settings

**For Zalo:**

1. Go to **Settings** > **NTH Notify** > **General** tab
2. Enable **Zalo Notifications**
3. Go to **Zalo** tab
4. Create a bot at [Zalo Bot Platform](https://bot.zapps.me/docs/create-bot/)
5. Enter your Bot Token
6. Click **Find Chat ID** and send a message to your bot
7. Click **Test** to verify
8. Save your settings

= First Steps =

1. Select order statuses you want to receive notifications for
2. Use the **Test** buttons to verify your configuration
3. Place a test order to see notifications in action
4. Add more Chat IDs as needed for team members

== Frequently Asked Questions ==

= Is this plugin free? =

Yes! NTH Notify is completely free and open-source under GPL v2 license.

= Do I need WooCommerce? =

Yes, this plugin requires WooCommerce to be installed and activated as it sends notifications for WooCommerce orders.

= Can I use both Telegram and Zalo simultaneously? =

Absolutely! You can enable both channels and receive notifications on both platforms for every order.

= How many Chat IDs can I add? =

There's no limit. You can add as many Chat IDs as you need for your team.

= What happens if the bot is down or offline? =

The plugin will attempt to send notifications but won't block order processing if the bot is unavailable. You can check debug logs for any errors.

= Can I customize the message format? =

The message format is fixed by default, but developers can use the `nth_notifications_new_order_message` filter hook to customize messages.

= Does this work with custom order statuses? =

Yes, it works with custom WooCommerce order statuses. Just enable them in the General settings.

= Is this plugin GDPR compliant? =

The plugin only sends order information to your configured bots. Ensure your Telegram/Zalo usage complies with your privacy policy and GDPR requirements.

= The test message works but I'm not receiving order notifications. Why? =

Check these items:
* Ensure notifications are enabled in **General** settings
* Verify you've selected the correct order statuses
* Confirm the order is changing to a selected status
* Check WP_DEBUG logs for any errors

= Can I send notifications to a Telegram group or channel? =

Yes! Add your bot to the group/channel, get the Chat ID (which will be negative), and add it to the plugin settings.

= How do I get support? =

* Check the documentation on [GitHub](https://github.com/HungNth/wordpress-notify-nth)
* Open an issue on [GitHub Issues](https://github.com/HungNth/wordpress-notify-nth/issues)
* Email: thienhungdev@gmail.com

== Screenshots ==

1. General settings - Enable/disable notifications and select order statuses
2. Telegram configuration - Bot token, Chat IDs, and test connection
3. Zalo configuration - Bot token, Find Chat ID feature, and test connection
4. Multiple Chat IDs management - Add unlimited Chat IDs for team notifications
5. Sample Telegram notification - Order details with formatted message
6. Sample Zalo notification - Clean text-based order notification

== Changelog ==

= 1.0.0 - January 29, 2026 =
* Initial release
* Telegram notification support
* Zalo notification support
* WooCommerce order notifications
* Admin settings page
* Multiple Chat IDs support
* Order status selection
* Test connection buttons
* Translation ready (i18n)
* Vietnamese and English languages included

== Upgrade Notice ==

= 1.0.0 =
Initial release. Install and start receiving order notifications today!

== Third-Party Services ==

This plugin connects to the following external services to send notifications:

= Telegram Bot API =
* **Service**: Telegram Bot API (https://api.telegram.org)
* **Purpose**: Send order notifications to Telegram
* **Data Sent**: Bot token, Chat IDs, order information (order number, customer details, products, amounts)
* **Privacy Policy**: https://telegram.org/privacy
* **Terms of Service**: https://telegram.org/tos

= Zalo Bot API =
* **Service**: Zalo Bot API (https://bot.zapps.me/docs/call-api/)
* **Purpose**: Send order notifications to Zalo
* **Data Sent**: Bot token, Chat IDs, order information (order number, customer details, products, amounts)
* **Privacy Policy**: https://zalo.vn/dieukhoan/
* **Terms of Service**: https://zalo.vn/dieukhoan/

**Important**: By using this plugin, you acknowledge that order data will be transmitted to these third-party services. Ensure your privacy policy reflects this data transmission. No data is stored on these services beyond message delivery.

== Technical Details ==

= System Requirements =
* WordPress 5.8 or higher
* PHP 7.4 or higher
* WooCommerce 5.0 or higher
* cURL extension enabled

= Database Tables =
The plugin uses WordPress options API and does not create additional tables:
* `nth_notify_settings` - General settings
* `nth_notify_telegram` - Telegram configuration
* `nth_notify_zalo` - Zalo configuration

= Hooks & Filters =

**Actions:**
* `nth_notifications_new_order` - Triggered when processing new order notifications

**Filters:**
* `nth_notifications_new_order_message` - Customize notification message content

Example usage:
`
add_filter( 'nth_notifications_new_order_message', function( $message, $order, $format_type ) {
    // Modify $message here
    return $message;
}, 10, 3 );
`

= Credits =
* Developed by [Hung Nth](https://wptop.net)
* Telegram Bot API by [Telegram](https://telegram.org)
* Zalo Bot API by [Zalo](https://zalo.me)

== Contributing ==

Contributions are welcome! Visit our [GitHub repository](https://github.com/HungNth/wordpress-notify-nth) to:
* Report bugs
* Suggest features
* Submit pull requests
* Improve documentation

== Translations ==

* Vietnamese (vi_VN) - 100% complete
* English (en_US) - 100% complete

Want to translate to your language? Visit our [GitHub repository](https://github.com/HungNth/wordpress-notify-nth) for translation instructions.
