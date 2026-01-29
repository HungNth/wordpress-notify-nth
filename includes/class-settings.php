<?php
/**
 * Settings Class
 *
 * @package NTH\Notifications
 */

namespace NTH\Notifications;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings Class
 */
class Settings {
	
	/**
	 * General option name (enabled/disabled flags)
	 *
	 * @var string
	 */
	private string $general_option = 'nth_notify_settings';
	
	/**
	 * Telegram option name
	 *
	 * @var string
	 */
	private string $telegram_option = 'nth_notify_telegram';
	
	/**
	 * Zalo option name
	 *
	 * @var string
	 */
	private string $zalo_option = 'nth_notify_zalo';
	
	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'admin_init', [ $this, 'register_settings' ] );
	}
	
	/**
	 * Register settings
	 */
	public function register_settings(): void {
		// Register general settings
		register_setting(
			'nth_notify_general_group',
			$this->general_option,
			[
				'sanitize_callback' => [ $this, 'sanitize_general_settings' ],
			]
		);
		
		// Register Telegram settings
		register_setting(
			'nth_notify_telegram_group',
			$this->telegram_option,
			[
				'sanitize_callback' => [ $this, 'sanitize_telegram_settings' ],
			]
		);
		
		// Register Zalo settings
		register_setting(
			'nth_notify_zalo_group',
			$this->zalo_option,
			[
				'sanitize_callback' => [ $this, 'sanitize_zalo_settings' ],
			]
		);
	}
	
	/**
	 * Sanitize general settings
	 *
	 * @param array $input Input data.
	 *
	 * @return array
	 */
	public function sanitize_general_settings( array $input ): array {
		$sanitized = [
			'telegram_enabled' => ! empty( $input['telegram_enabled'] ),
			'zalo_enabled'     => ! empty( $input['zalo_enabled'] ),
			'enabled_statuses' => [],
		];
		
		// Sanitize enabled statuses
		if ( isset( $input['enabled_statuses'] ) && is_array( $input['enabled_statuses'] ) ) {
			$valid_statuses = [
				'pending',
				'processing',
				'on-hold',
				'completed',
				'cancelled',
				'refunded',
				'failed',
				'draft',
			];
			
			$sanitized['enabled_statuses'] = array_values(
				array_intersect( $input['enabled_statuses'], $valid_statuses )
			);
		}
		
		// Set default statuses if empty
		if ( empty( $sanitized['enabled_statuses'] ) ) {
			$sanitized['enabled_statuses'] = [ 'processing', 'cancelled', 'failed' ];
		}
		
		return $sanitized;
	}
	
	/**
	 * Sanitize Telegram settings
	 *
	 * @param array $input Input data.
	 *
	 * @return array
	 */
	public function sanitize_telegram_settings( array $input ): array {
		$sanitized = [
			'bot_token' => isset( $input['bot_token'] ) ? sanitize_text_field( $input['bot_token'] ) : '',
			'chat_ids'  => [],
		];
		
		// Sanitize chat IDs.
		if ( isset( $input['chat_ids'] ) && is_array( $input['chat_ids'] ) ) {
			// Sanitize all values first
			$sanitized_values = array_map( 'sanitize_text_field', $input['chat_ids'] );
			
			// Keep non-empty values
			$non_empty = array_filter( $sanitized_values, function ( $id ) {
				return ! empty( $id );
			} );
			
			// Re-index array
			$sanitized['chat_ids'] = array_values( $non_empty );
			
			// Debug logging
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'NTH Notify - Telegram chat IDs saved: ' . print_r( $sanitized['chat_ids'], true ) );
			}
		}
		
		// Ensure at least one empty chat ID for display.
		if ( empty( $sanitized['chat_ids'] ) ) {
			$sanitized['chat_ids'] = [ '' ];
		}
		
		return $sanitized;
	}
	
	/**
	 * Sanitize Zalo settings
	 *
	 * @param array $input Input data.
	 *
	 * @return array
	 */
	public function sanitize_zalo_settings( array $input ): array {
		$sanitized = [
			'bot_token' => isset( $input['bot_token'] ) ? sanitize_text_field( $input['bot_token'] ) : '',
			'chat_ids'  => [],
		];
		
		// Sanitize chat IDs.
		if ( isset( $input['chat_ids'] ) && is_array( $input['chat_ids'] ) ) {
			// Sanitize all values first
			$sanitized_values = array_map( 'sanitize_text_field', $input['chat_ids'] );
			
			// Keep non-empty values
			$non_empty = array_filter( $sanitized_values, function ( $id ) {
				return ! empty( $id );
			} );
			
			// Re-index array
			$sanitized['chat_ids'] = array_values( $non_empty );
			
			// Debug logging
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'NTH Notify - Zalo chat IDs saved: ' . print_r( $sanitized['chat_ids'], true ) );
			}
		}
		
		// Ensure at least one empty chat ID for display.
		if ( empty( $sanitized['chat_ids'] ) ) {
			$sanitized['chat_ids'] = [ '' ];
		}
		
		return $sanitized;
	}
	
	/**
	 * Render settings page
	 */
	public function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'nth-notify' ) );
		}
		
		// Get current tab.
		$current_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'general';
		?>
		<div id="nth-notify-settings-page" class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			
			<?php $this->render_tabs( $current_tab ); ?>
			
			<form method="post" action="options.php">
				<?php
				// Use different settings group based on current tab
				switch ( $current_tab ) {
					case 'telegram':
						settings_fields( 'nth_notify_telegram_group' );
						$this->render_telegram_tab();
						break;
					case 'zalo':
						settings_fields( 'nth_notify_zalo_group' );
						$this->render_zalo_tab();
						break;
					case 'general':
					default:
						settings_fields( 'nth_notify_general_group' );
						$this->render_general_tab();
						break;
				}
				?>
				
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
	
	/**
	 * Render tabs navigation
	 *
	 * @param string $current_tab Current active tab.
	 */
	private function render_tabs( string $current_tab ): void {
		$tabs = [
			'general'  => __( 'General', 'nth-notify' ),
			'telegram' => __( 'Telegram', 'nth-notify' ),
			'zalo'     => __( 'Zalo', 'nth-notify' ),
		];
		?>
		<h2 class="nav-tab-wrapper">
			<?php foreach ( $tabs as $tab_key => $tab_label ) : ?>
				<a href="<?php echo esc_url( add_query_arg( 'tab',
					$tab_key,
					admin_url( 'options-general.php?page=nth-notify' ) ) ); ?>"
				   class="nav-tab <?php echo $current_tab === $tab_key ? 'nav-tab-active' : ''; ?>">
					<?php echo esc_html( $tab_label ); ?>
				</a>
			<?php endforeach; ?>
		</h2>
		<?php
	}
	
	/**
	 * Render General tab
	 */
	private function render_general_tab(): void {
		$settings = get_option( $this->general_option, [
			'telegram_enabled' => false,
			'zalo_enabled'     => false,
			'enabled_statuses' => [ 'processing', 'cancelled', 'failed' ],
		] );
		
		$telegram_enabled = isset( $settings['telegram_enabled'] ) && $settings['telegram_enabled'];
		$zalo_enabled     = isset( $settings['zalo_enabled'] ) && $settings['zalo_enabled'];
		$enabled_statuses = isset( $settings['enabled_statuses'] ) ? $settings['enabled_statuses'] : [
			'processing',
			'cancelled',
			'failed'
		];
		
		// Available order statuses
		$order_statuses = [
			'pending'    => __( 'Pending Payment', 'nth-notify' ),
			'processing' => __( 'Processing', 'nth-notify' ),
			'on-hold'    => __( 'On Hold', 'nth-notify' ),
			'completed'  => __( 'Completed', 'nth-notify' ),
			'cancelled'  => __( 'Cancelled', 'nth-notify' ),
			'refunded'   => __( 'Refunded', 'nth-notify' ),
			'failed'     => __( 'Failed', 'nth-notify' ),
			'draft'      => __( 'Draft', 'nth-notify' ),
		];
		?>
		<table class="form-table">
			<tr>
				<th scope="row">
					<label for="telegram_enabled"><?php esc_html_e( 'Enable Telegram', 'nth-notify' ); ?></label>
				</th>
				<td>
					<label>
						<input type="checkbox"
							   name="<?php echo esc_attr( $this->general_option ); ?>[telegram_enabled]"
							   id="telegram_enabled"
							   value="1"
							<?php checked( $telegram_enabled, true ); ?> />
						<?php esc_html_e( 'Enable Telegram notifications', 'nth-notify' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="zalo_enabled"><?php esc_html_e( 'Enable Zalo', 'nth-notify' ); ?></label>
				</th>
				<td>
					<label>
						<input type="checkbox"
							   name="<?php echo esc_attr( $this->general_option ); ?>[zalo_enabled]"
							   id="zalo_enabled"
							   value="1"
							<?php checked( $zalo_enabled, true ); ?> />
						<?php esc_html_e( 'Enable Zalo notifications', 'nth-notify' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php esc_html_e( 'Order Statuses', 'nth-notify' ); ?>
				</th>
				<td>
					<fieldset>
						<legend class="screen-reader-text">
							<span><?php esc_html_e( 'Select order statuses to send notifications',
									'nth-notify' ); ?></span>
						</legend>
						<p class="description" style="margin-top: 0; margin-bottom: 10px;">
							<?php esc_html_e( 'Select which order statuses should trigger notifications:',
								'nth-notify' ); ?>
						</p>
						<?php foreach ( $order_statuses as $status_key => $status_label ) : ?>
							<label style="display: block; margin-bottom: 5px;">
								<input type="checkbox"
									   name="<?php echo esc_attr( $this->general_option ); ?>[enabled_statuses][]"
									   value="<?php echo esc_attr( $status_key ); ?>"
									<?php checked( in_array( $status_key, $enabled_statuses, true ), true ); ?> />
								<?php echo esc_html( $status_label ); ?>
							</label>
						<?php endforeach; ?>
					</fieldset>
				</td>
			</tr>
		</table>
		<?php
	}
	
	/**
	 * Render Telegram tab
	 */
	private function render_telegram_tab(): void {
		// Check if Telegram is enabled
		$general          = get_option( $this->general_option, [ 'telegram_enabled' => false ] );
		$telegram_enabled = isset( $general['telegram_enabled'] ) && $general['telegram_enabled'];
		
		if ( ! $telegram_enabled ) {
			?>
			<div class="notice notice-info">
				<p>
					<?php
					printf(
					/* translators: %s: link to General tab */
						esc_html__( 'Telegram is not enabled. Please enable it in the %s tab.', 'nth-notify' ),
						'<a href="' . esc_url( admin_url( 'options-general.php?page=nth-notify&tab=general' ) ) . '">' . esc_html__( 'General',
							'nth-notify' ) . '</a>'
					);
					?>
				</p>
			</div>
			<?php
			return;
		}
		
		// Get Telegram settings
		$settings = get_option( $this->telegram_option, [
			'bot_token' => '',
			'chat_ids'  => [ '' ],
		] );
		
		$bot_token = isset( $settings['bot_token'] ) ? $settings['bot_token'] : '';
		$chat_ids  = isset( $settings['chat_ids'] ) ? $settings['chat_ids'] : [ '' ];
		
		// Mask token for display.
		$masked_token = $this->mask_token( $bot_token );
		?>
		<table class="form-table">
			<tr>
				<th scope="row">
					<label for="telegram_bot_token"><?php esc_html_e( 'Bot Token', 'nth-notify' ); ?></label>
				</th>
				<td>
					<input type="password"
						   name="<?php echo esc_attr( $this->telegram_option ); ?>[bot_token]"
						   id="telegram_bot_token"
						   class="regular-text"
						   value="<?php echo esc_attr( $bot_token ); ?>" />
					<button type="button" class="button button-secondary nth-notify__toggle-token">
						<?php esc_html_e( 'Show', 'nth-notify' ); ?>
					</button>
					<p class="description">
						<?php
						printf(
						/* translators: %s: help URL */
							esc_html__( 'Enter your Telegram Bot Token. %s', 'nth-notify' ),
							'<a href="#" target="_blank">' . esc_html__( 'Learn more', 'nth-notify' ) . '</a>'
						);
						?>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label><?php esc_html_e( 'Chat IDs', 'nth-notify' ); ?></label>
				</th>
				<td>
					<div class="nth-notify__chat-ids">
						<?php
						foreach ( $chat_ids as $index => $chat_id ) {
							$this->render_tg_chat_id_row( $index + 1, $chat_id );
						}
						?>
					</div>
					<button type="button" class="button button-secondary nth-notify__add-chat-id">
						<?php esc_html_e( '+ Add Chat ID', 'nth-notify' ); ?>
					</button>
					<p class="description">
						<?php esc_html_e( 'Add Telegram Chat IDs where notifications will be sent. You can use Channel IDs, Group IDs, or User IDs.',
							'nth-notify' ); ?>
					</p>
				</td>
			</tr>
		</table>
		<?php
	}
	
	/**
	 * Render Zalo tab
	 */
	private function render_zalo_tab(): void {
		// Check if Zalo is enabled
		$general      = get_option( $this->general_option, [ 'zalo_enabled' => false ] );
		$zalo_enabled = isset( $general['zalo_enabled'] ) && $general['zalo_enabled'];
		
		if ( ! $zalo_enabled ) {
			?>
			<div class="notice notice-info">
				<p>
					<?php
					printf(
					/* translators: %s: link to General tab */
						esc_html__( 'Zalo is not enabled. Please enable it in the %s tab.', 'nth-notify' ),
						'<a href="' . esc_url( admin_url( 'options-general.php?page=nth-notify&tab=general' ) ) . '">' . esc_html__( 'General',
							'nth-notify' ) . '</a>'
					);
					?>
				</p>
			</div>
			<?php
			return;
		}
		
		// Get Zalo settings
		$settings = get_option( $this->zalo_option, [
			'bot_token' => '',
			'chat_ids'  => [ '' ],
		] );
		
		$bot_token = isset( $settings['bot_token'] ) ? $settings['bot_token'] : '';
		$chat_ids  = isset( $settings['chat_ids'] ) ? $settings['chat_ids'] : [ '' ];
		
		// Mask token for display.
		$masked_token = $this->mask_token( $bot_token );
		?>
		<table class="form-table">
			<tr>
				<th scope="row">
					<label for="zalo_bot_token"><?php esc_html_e( 'Bot Token', 'nth-notify' ); ?></label>
				</th>
				<td>
					<input type="password"
						   name="<?php echo esc_attr( $this->zalo_option ); ?>[bot_token]"
						   id="zalo_bot_token"
						   class="regular-text"
						   value="<?php echo esc_attr( $bot_token ); ?>" />
					<button type="button" class="button button-secondary nth-notify__toggle-token">
						<?php esc_html_e( 'Show', 'nth-notify' ); ?>
					</button>
					<p class="description">
						<?php
						printf(
						/* translators: %s: help URL */
							esc_html__( 'Enter your Zalo Bot Token. %s', 'nth-notify' ),
							'<a href="https://bot.zapps.me/docs/create-bot/" target="_blank">' . esc_html__( 'Learn more',
								'nth-notify' ) . '</a>'
						);
						?>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label><?php esc_html_e( 'Chat IDs', 'nth-notify' ); ?></label>
				</th>
				<td>
					<div
						style="margin-bottom: 15px; padding: 10px; background: #f0f6fc; border-left: 4px solid #0073aa;">
						<p style="margin: 0 0 8px 0; font-weight: 600;">
							<?php esc_html_e( '📱 How to get your Chat ID:', 'nth-notify' ); ?>
						</p>
						<ol style="margin: 0; padding-left: 20px;">
							<li><?php esc_html_e( 'Open Zalo app and search for your bot',
									'nth-notify' ); ?></li>
							<li><?php esc_html_e( 'Send any message to the bot (e.g., "Hello")',
									'nth-notify' ); ?></li>
							<li><?php esc_html_e( 'Click the button below to automatically find your Chat ID',
									'nth-notify' ); ?></li>
						</ol>
						<div style="margin-top: 10px;">
							<button type="button" class="button button-primary nth-notify__find-zalo-chat-id">
								<?php esc_html_e( '🔎 Find Chat ID', 'nth-notify' ); ?>
							</button>
						</div>
					</div>
					<div class="nth-notify__zalo-chat-ids">
						<?php
						foreach ( $chat_ids as $index => $chat_id ) {
							$this->render_zalo_chat_id_row( $index + 1, $chat_id );
						}
						?>
					</div>
					<button type="button" class="button button-secondary nth-notify__add-zalo-chat-id">
						<?php esc_html_e( '+ Add Chat ID', 'nth-notify' ); ?>
					</button>
					<p class="description">
						<?php esc_html_e( 'You can add multiple Chat IDs to send notifications to different users or groups.',
							'nth-notify' ); ?>
					</p>
				</td>
			</tr>
		</table>
		<?php
	}
	
	/**
	 * Render Zalo chat ID row
	 *
	 * @param int    $index Row index.
	 * @param string $value Chat ID value.
	 */
	private function render_zalo_chat_id_row( int $index, string $value = '' ): void {
		?>
		<div class="nth-notify__chat-row">
			<label>
				<?php
				/* translators: %d: chat ID number */
				printf( esc_html__( 'Chat ID #%d', 'nth-notify' ), $index );
				?>
			</label>
			<input type="text"
				   name="<?php echo esc_attr( $this->zalo_option ); ?>[chat_ids][]"
				   class="regular-text nth-notify__zalo-chat-id-input"
				   value="<?php echo esc_attr( $value ); ?>"
				   placeholder="<?php esc_attr_e( 'Enter Zalo Chat ID', 'nth-notify' ); ?>" />
			<button type="button" class="button button-secondary nth-notify__test-zalo"
					data-chat-id="<?php echo esc_attr( $value ); ?>">
				<?php esc_html_e( 'Test', 'nth-notify' ); ?>
			</button>
			<?php if ( $index > 1 ) : ?>
				<button type="button" class="button button-link-delete nth-notify__remove-chat-id">
					<?php esc_html_e( 'Remove', 'nth-notify' ); ?>
				</button>
			<?php endif; ?>
			<div class="nth-notify__test-result"></div>
		</div>
		<?php
	}
	
	/**
	 * Render chat ID row
	 *
	 * @param int    $index Row index.
	 * @param string $value Chat ID value.
	 */
	private function render_tg_chat_id_row( int $index, string $value = '' ): void {
		?>
		<div class="nth-notify__chat-row">
			<label>
				<?php
				/* translators: %d: chat ID number */
				printf( esc_html__( 'Chat ID #%d', 'nth-notify' ), $index );
				?>
			</label>
			<input type="text"
				   name="<?php echo esc_attr( $this->telegram_option ); ?>[chat_ids][]"
				   class="regular-text nth-notify__chat-id-input"
				   value="<?php echo esc_attr( $value ); ?>"
				   placeholder="<?php esc_attr_e( 'Enter Telegram Chat ID', 'nth-notify' ); ?>"
				   data-chat-id="<?php echo esc_attr( $value ); ?>" />
			<button type="button" class="button button-secondary nth-notify__test-chat-id"
					data-index="<?php echo esc_attr( $index ); ?>">
				<?php esc_html_e( 'Test', 'nth-notify' ); ?>
			</button>
			<?php if ( $index > 1 ) : ?>
				<button type="button" class="button button-link-delete nth-notify__remove-chat-id">
					<?php esc_html_e( 'Remove', 'nth-notify' ); ?>
				</button>
			<?php endif; ?>
			<span class="nth-notify__test-result"></span>
		</div>
		<?php
	}
	
	/**
	 * Mask token for display
	 *
	 * @param string $token Token to mask.
	 *
	 * @return string
	 */
	private function mask_token( string $token ): string {
		if ( empty( $token ) ) {
			return '';
		}
		
		// Format: 123456:ABC***************
		$parts = explode( ':', $token );
		if ( count( $parts ) === 2 ) {
			return $parts[0] . ':' . substr( $parts[1], 0, 3 ) . str_repeat( '*', 15 );
		}
		
		// Fallback for non-standard format.
		return substr( $token, 0, 10 ) . str_repeat( '*', 15 );
	}
}
