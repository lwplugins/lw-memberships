<?php
/**
 * My memberships template.
 *
 * @package LightweightPlugins\Memberships
 *
 * Available variables:
 * @var array<\LightweightPlugins\Memberships\Models\Membership> $memberships User memberships.
 * @var array<\LightweightPlugins\Memberships\Models\Level>      $levels      Levels cache.
 * @var int                                                       $user_id     User ID.
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="lw-mship-my-memberships">
	<?php if ( empty( $memberships ) ) : ?>
		<p><?php esc_html_e( 'You do not have any active memberships.', 'lw-memberships' ); ?></p>
	<?php else : ?>
		<table class="lw-mship-memberships-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Membership', 'lw-memberships' ); ?></th>
					<th><?php esc_html_e( 'Status', 'lw-memberships' ); ?></th>
					<th><?php esc_html_e( 'Start Date', 'lw-memberships' ); ?></th>
					<th><?php esc_html_e( 'End Date', 'lw-memberships' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $memberships as $membership ) : ?>
					<?php
					$level = $levels[ $membership->level_id ] ?? null;
					if ( ! $level ) {
						continue;
					}
					?>
					<tr>
						<td><?php echo esc_html( $level->name ); ?></td>
						<td>
							<span class="lw-mship-status lw-mship-status--<?php echo esc_attr( $membership->status ); ?>">
								<?php echo esc_html( ucfirst( $membership->status ) ); ?>
							</span>
						</td>
						<td><?php echo esc_html( wp_date( get_option( 'date_format' ), strtotime( $membership->start_date ) ) ); ?></td>
						<td>
							<?php if ( $membership->end_date ) : ?>
								<?php echo esc_html( wp_date( get_option( 'date_format' ), strtotime( $membership->end_date ) ) ); ?>
							<?php else : ?>
								<?php esc_html_e( 'Lifetime', 'lw-memberships' ); ?>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
</div>
