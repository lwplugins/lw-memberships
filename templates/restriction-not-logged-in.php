<?php
/**
 * Restriction template: Not logged in.
 *
 * @package LightweightPlugins\Memberships
 *
 * Available variables:
 * @var int    $post_id Post ID.
 * @var string $message Restriction message.
 * @var string $reason  Restriction reason.
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="lw-mship-restriction lw-mship-restriction--not-logged-in">
	<p><?php echo esc_html( $message ); ?></p>
	<p>
		<a href="<?php echo esc_url( wp_login_url( get_permalink( $post_id ) ) ); ?>" class="button">
			<?php esc_html_e( 'Log In', 'lw-memberships' ); ?>
		</a>
		<?php if ( get_option( 'users_can_register' ) ) : ?>
			<a href="<?php echo esc_url( wp_registration_url() ); ?>" class="button">
				<?php esc_html_e( 'Register', 'lw-memberships' ); ?>
			</a>
		<?php endif; ?>
	</p>
</div>
