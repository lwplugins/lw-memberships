<?php
/**
 * Settings Page.
 *
 * @package LightweightPlugins\Memberships
 */

declare(strict_types=1);

namespace LightweightPlugins\Memberships\Admin;

use LightweightPlugins\Memberships\Options;

/**
 * Handles the settings page.
 */
final class SettingsPage {

	/**
	 * Settings page slug.
	 */
	public const SLUG = 'lw-memberships';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_menu_page' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
	}

	/**
	 * Add menu page.
	 *
	 * @return void
	 */
	public function add_menu_page(): void {
		ParentPage::maybe_register();

		add_submenu_page(
			ParentPage::SLUG,
			__( 'LW Memberships', 'lw-memberships' ),
			__( 'Memberships', 'lw-memberships' ),
			'manage_options',
			self::SLUG,
			[ $this, 'render' ]
		);
	}

	/**
	 * Register settings.
	 *
	 * @return void
	 */
	public function register_settings(): void {
		register_setting(
			'lw_mship_settings',
			Options::OPTION_NAME,
			[
				'type'              => 'array',
				'sanitize_callback' => [ $this, 'sanitize_options' ],
			]
		);
	}

	/**
	 * Sanitize options.
	 *
	 * @param array<string, mixed> $input Input options.
	 * @return array<string, mixed>
	 */
	public function sanitize_options( array $input ): array {
		$defaults  = Options::get_defaults();
		$sanitized = [];

		foreach ( $defaults as $key => $default_val ) {
			if ( is_bool( $default_val ) ) {
				$sanitized[ $key ] = ! empty( $input[ $key ] );
			} elseif ( is_string( $default_val ) ) {
				$sanitized[ $key ] = isset( $input[ $key ] )
					? sanitize_textarea_field( $input[ $key ] )
					: $default_val;
			} else {
				$sanitized[ $key ] = $input[ $key ] ?? $default_val;
			}
		}

		return $sanitized;
	}

	/**
	 * Render the settings page.
	 *
	 * @return void
	 */
	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$options = Options::get_all();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'LW Memberships', 'lw-memberships' ); ?></h1>

			<p><?php esc_html_e( 'Lightweight membership system with WooCommerce integration.', 'lw-memberships' ); ?></p>

			<form method="post" action="options.php">
				<?php settings_fields( 'lw_mship_settings' ); ?>

				<h2><?php esc_html_e( 'Messages', 'lw-memberships' ); ?></h2>
				<table class="form-table">
					<?php $this->render_textarea_field( 'restricted_message', __( 'Restricted Content Message', 'lw-memberships' ), $options ); ?>
					<?php $this->render_textarea_field( 'not_logged_in_message', __( 'Not Logged In Message', 'lw-memberships' ), $options ); ?>
					<?php $this->render_textarea_field( 'expired_message', __( 'Membership Expired Message', 'lw-memberships' ), $options ); ?>
					<?php $this->render_textarea_field( 'paused_message', __( 'Membership Paused Message', 'lw-memberships' ), $options ); ?>
				</table>

				<h2><?php esc_html_e( 'WooCommerce', 'lw-memberships' ); ?></h2>
				<table class="form-table">
					<?php $this->render_checkbox_field( 'auto_grant_on_complete', __( 'Auto-grant membership on order complete', 'lw-memberships' ), $options ); ?>
					<?php $this->render_checkbox_field( 'revoke_on_refund', __( 'Revoke membership on refund', 'lw-memberships' ), $options ); ?>
				</table>

				<h2><?php esc_html_e( 'Content Restriction', 'lw-memberships' ); ?></h2>
				<table class="form-table">
					<?php $this->render_checkbox_field( 'hide_restricted_in_archive', __( 'Hide restricted content in archives', 'lw-memberships' ), $options ); ?>
					<?php $this->render_checkbox_field( 'show_excerpt_restricted', __( 'Show excerpt for restricted content', 'lw-memberships' ), $options ); ?>
				</table>

				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Render textarea field.
	 *
	 * @param string               $key     Option key.
	 * @param string               $label   Field label.
	 * @param array<string, mixed> $options Current options.
	 * @return void
	 */
	private function render_textarea_field( string $key, string $label, array $options ): void {
		$name  = Options::OPTION_NAME . '[' . $key . ']';
		$value = $options[ $key ] ?? '';
		?>
		<tr>
			<th scope="row"><label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th>
			<td>
				<textarea name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $key ); ?>" class="large-text" rows="2"><?php echo esc_textarea( $value ); ?></textarea>
			</td>
		</tr>
		<?php
	}

	/**
	 * Render checkbox field.
	 *
	 * @param string               $key     Option key.
	 * @param string               $label   Field label.
	 * @param array<string, mixed> $options Current options.
	 * @return void
	 */
	private function render_checkbox_field( string $key, string $label, array $options ): void {
		$name    = Options::OPTION_NAME . '[' . $key . ']';
		$checked = ! empty( $options[ $key ] );
		?>
		<tr>
			<th scope="row"><?php echo esc_html( $label ); ?></th>
			<td>
				<label>
					<input type="checkbox" name="<?php echo esc_attr( $name ); ?>" value="1" <?php checked( $checked ); ?>>
					<?php esc_html_e( 'Enable', 'lw-memberships' ); ?>
				</label>
			</td>
		</tr>
		<?php
	}
}
