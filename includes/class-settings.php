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
	private string $general_option = 'nth_notifications_settings';
	
	/**
	 * Telegram option name
	 *
	 * @var string
	 */
	private string $telegram_option = 'nth_notifications_telegram';
	
	/**
	 * Zalo option name
	 *
	 * @var string
	 */
	private string $zalo_option = 'nth_notifications_zalo';
	
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
			'nth_notifications_general_group',
			$this->general_option,
			[
				'sanitize_callback' => [ $this, 'sanitize_general_settings' ],
			]
		);
		
		// Register Telegram settings
		register_setting(
			'nth_notifications_telegram_group',
			$this->telegram_option,
			[
				'sanitize_callback' => [ $this, 'sanitize_telegram_settings' ],
			]
		);
		
		// Register Zalo settings
		register_setting(
			'nth_notifications_zalo_group',
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
		];
		
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
			$sanitized['chat_ids'] = array_values(
				array_filter(
					array_map( 'sanitize_text_field', $input['chat_ids'] ),
					function ( $id ) {
						return ! empty( $id );
					}
				)
			);
		}
		
		// Ensure at least one empty chat ID.
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
			$sanitized['chat_ids'] = array_values(
				array_filter(
					array_map( 'sanitize_text_field', $input['chat_ids'] ),
					function ( $id ) {
						return ! empty( $id );
					}
				)
			);
		}
		
		// Ensure at least one empty chat ID.
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
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'nth-notifications' ) );
		}
		
		// Get current tab.
		$current_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'general';
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			
			<?php $this->render_tabs( $current_tab ); ?>
			
			<form method="post" action="options.php">
				<?php
				// Use different settings group based on current tab
				switch ( $current_tab ) {
					case 'telegram':
						settings_fields( 'nth_notifications_telegram_group' );
						$this->render_telegram_tab();
						break;
					case 'zalo':
						settings_fields( 'nth_notifications_zalo_group' );
						$this->render_zalo_tab();
						break;
					case 'general':
					default:
						settings_fields( 'nth_notifications_general_group' );
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
			'general'  => __( 'General', 'nth-notifications' ),
			'telegram' => __( 'Telegram', 'nth-notifications' ),
			'zalo'     => __( 'Zalo', 'nth-notifications' ),
		];
		?>
		<h2 class="nav-tab-wrapper">
			<?php foreach ( $tabs as $tab_key => $tab_label ) : ?>
				<a href="<?php echo esc_url( add_query_arg( 'tab',
					$tab_key,
					admin_url( 'options-general.php?page=nth-notifications' ) ) ); ?>"
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
		] );
		
		$telegram_enabled = isset( $settings['telegram_enabled'] ) && $settings['telegram_enabled'];
		$zalo_enabled     = isset( $settings['zalo_enabled'] ) && $settings['zalo_enabled'];
		?>
		<table class="form-table">
			<tr>
				<th scope="row">
					<label for="telegram_enabled"><?php esc_html_e( 'Enable Telegram', 'nth-notifications' ); ?></label>
				</th>
				<td>
					<label>
						<input type="checkbox"
							   name="<?php echo esc_attr( $this->general_option ); ?>[telegram_enabled]"
							   id="telegram_enabled"
							   value="1"
							<?php checked( $telegram_enabled, true ); ?> />
						<?php esc_html_e( 'Enable Telegram notifications', 'nth-notifications' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="zalo_enabled"><?php esc_html_e( 'Enable Zalo', 'nth-notifications' ); ?></label>
				</th>
				<td>
					<label>
						<input type="checkbox"
							   name="<?php echo esc_attr( $this->general_option ); ?>[zalo_enabled]"
							   id="zalo_enabled"
							   value="1"
							<?php checked( $zalo_enabled, true ); ?> />
						<?php esc_html_e( 'Enable Zalo notifications', 'nth-notifications' ); ?>
					</label>
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
						esc_html__( 'Telegram is not enabled. Please enable it in the %s tab.', 'nth-notifications' ),
						'<a href="' . esc_url( admin_url( 'options-general.php?page=nth-notifications&tab=general' ) ) . '">' . esc_html__( 'General',
							'nth-notifications' ) . '</a>'
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
					<label for="telegram_bot_token"><?php esc_html_e( 'Bot Token', 'nth-notifications' ); ?></label>
				</th>
				<td>
					<input type="password"
						   name="<?php echo esc_attr( $this->telegram_option ); ?>[bot_token]"
						   id="telegram_bot_token"
						   class="regular-text"
						   value="<?php echo esc_attr( $bot_token ); ?>" />
					<button type="button" class="button button-secondary nth-notify__toggle-token">
						<?php esc_html_e( 'Show', 'nth-notifications' ); ?>
					</button>
					<p class="description">
						<?php
						printf(
						/* translators: %s: help URL */
							esc_html__( 'Enter your Telegram Bot Token. %s', 'nth-notifications' ),
							'<a href="#" target="_blank">' . esc_html__( 'Learn more', 'nth-notifications' ) . '</a>'
						);
						?>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label><?php esc_html_e( 'Chat IDs', 'nth-notifications' ); ?></label>
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
						<?php esc_html_e( '+ Add Chat ID', 'nth-notifications' ); ?>
					</button>
					<p class="description">
						<?php esc_html_e( 'Add Telegram Chat IDs where notifications will be sent. You can use Channel IDs, Group IDs, or User IDs.',
							'nth-notifications' ); ?>
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
						esc_html__( 'Zalo is not enabled. Please enable it in the %s tab.', 'nth-notifications' ),
						'<a href="' . esc_url( admin_url( 'options-general.php?page=nth-notifications&tab=general' ) ) . '">' . esc_html__( 'General',
							'nth-notifications' ) . '</a>'
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
					<label for="zalo_bot_token"><?php esc_html_e( 'Bot Token', 'nth-notifications' ); ?></label>
				</th>
				<td>
					<input type="password"
						   name="<?php echo esc_attr( $this->zalo_option ); ?>[bot_token]"
						   id="zalo_bot_token"
						   class="regular-text"
						   value="<?php echo esc_attr( $bot_token ); ?>" />
					<button type="button" class="button button-secondary nth-notify__toggle-token">
						<?php esc_html_e( 'Show', 'nth-notifications' ); ?>
					</button>
					<p class="description">
						<?php
						printf(
						/* translators: %s: help URL */
							esc_html__( 'Enter your Zalo Bot Token. %s', 'nth-notifications' ),
							'<a href="https://bot.zapps.me/docs/create-bot/" target="_blank">' . esc_html__( 'Learn more',
								'nth-notifications' ) . '</a>'
						);
						?>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label><?php esc_html_e( 'Chat IDs', 'nth-notifications' ); ?></label>
				</th>
				<td>
					<div
						style="margin-bottom: 15px; padding: 10px; background: #f0f6fc; border-left: 4px solid #0073aa;">
						<p style="margin: 0 0 8px 0; font-weight: 600;">
							<?php esc_html_e( '📱 How to get your Chat ID:', 'nth-notifications' ); ?>
						</p>
						<ol style="margin: 0; padding-left: 20px;">
							<li><?php esc_html_e( 'Open Zalo app and search for your bot',
									'nth-notifications' ); ?></li>
							<li><?php esc_html_e( 'Send any message to the bot (e.g., "Hello")',
									'nth-notifications' ); ?></li>
							<li><?php esc_html_e( 'Click the button below to automatically find your Chat ID',
									'nth-notifications' ); ?></li>
						</ol>
						<div style="margin-top: 10px;">
							<button type="button" class="button button-primary nth-notify__find-zalo-chat-id">
								<?php esc_html_e( '🔎 Find Chat ID', 'nth-notifications' ); ?>
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
						<?php esc_html_e( '+ Add Chat ID', 'nth-notifications' ); ?>
					</button>
					<p class="description">
						<?php esc_html_e( 'You can add multiple Chat IDs to send notifications to different users or groups.',
							'nth-notifications' ); ?>
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
				printf( esc_html__( 'Chat ID #%d', 'nth-notifications' ), $index );
				?>
			</label>
			<input type="text"
				   name="<?php echo esc_attr( $this->zalo_option ); ?>[chat_ids][]"
				   class="regular-text nth-notify__zalo-chat-id-input"
				   value="<?php echo esc_attr( $value ); ?>"
				   placeholder="<?php esc_attr_e( 'Enter Zalo Chat ID', 'nth-notifications' ); ?>" />
			<button type="button" class="button button-secondary nth-notify__test-zalo"
					data-chat-id="<?php echo esc_attr( $value ); ?>">
				<?php esc_html_e( 'Test', 'nth-notifications' ); ?>
			</button>
			<?php if ( $index > 1 ) : ?>
				<button type="button" class="button button-link-delete nth-notify__remove-chat-id">
					<?php esc_html_e( 'Remove', 'nth-notifications' ); ?>
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
				printf( esc_html__( 'Chat ID #%d', 'nth-notifications' ), $index );
				?>
			</label>
			<input type="text"
				   name="<?php echo esc_attr( $this->telegram_option ); ?>[chat_ids][]"
				   class="regular-text nth-notify__chat-id-input"
				   value="<?php echo esc_attr( $value ); ?>"
				   placeholder="<?php esc_attr_e( 'Enter Telegram Chat ID', 'nth-notifications' ); ?>"
				   data-chat-id="<?php echo esc_attr( $value ); ?>" />
			<button type="button" class="button button-secondary nth-notify__test-chat-id"
					data-index="<?php echo esc_attr( $index ); ?>">
				<?php esc_html_e( 'Test', 'nth-notifications' ); ?>
			</button>
			<?php if ( $index > 1 ) : ?>
				<button type="button" class="button button-link-delete nth-notify__remove-chat-id">
					<?php esc_html_e( 'Remove', 'nth-notifications' ); ?>
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
