<?php
/**
 * Content tab for plan editor.
 *
 * @package LightweightPlugins\Memberships
 */

declare(strict_types=1);

namespace LightweightPlugins\Memberships\Admin\Plans\Tabs;

use LightweightPlugins\Memberships\Database\RuleRepository;

/**
 * Content restriction tab — assign posts/pages to this plan.
 */
final class TabContent implements TabInterface {

	/**
	 * Plan ID.
	 *
	 * @var int
	 */
	private int $plan_id;

	/**
	 * Constructor.
	 *
	 * @param int $plan_id Plan ID.
	 */
	public function __construct( int $plan_id ) {
		$this->plan_id = $plan_id;
	}

	/**
	 * Get tab slug.
	 *
	 * @return string
	 */
	public function get_slug(): string {
		return 'content';
	}

	/**
	 * Get tab label.
	 *
	 * @return string
	 */
	public function get_label(): string {
		return __( 'Content', 'lw-memberships' );
	}

	/**
	 * Render tab content.
	 *
	 * @return void
	 */
	public function render(): void {
		if ( 0 === $this->plan_id ) {
			$this->render_save_first_notice();
			return;
		}

		$post_ids = RuleRepository::get_post_ids_by_plan( $this->plan_id );

		?>
		<h3><?php esc_html_e( 'Restricted Content', 'lw-memberships' ); ?></h3>
		<p class="description"><?php esc_html_e( 'Add posts or pages that require this plan for access.', 'lw-memberships' ); ?></p>

		<div id="lw-mship-content-search" style="margin: 15px 0;">
			<input type="text" id="lw-mship-content-search-input" placeholder="<?php esc_attr_e( 'Search posts/pages...', 'lw-memberships' ); ?>" class="regular-text">
			<div id="lw-mship-content-search-results" style="display:none;"></div>
		</div>

		<table class="wp-list-table widefat fixed striped" id="lw-mship-content-list">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Title', 'lw-memberships' ); ?></th>
					<th style="width:100px;"><?php esc_html_e( 'Type', 'lw-memberships' ); ?></th>
					<th style="width:80px;"><?php esc_html_e( 'Action', 'lw-memberships' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php $this->render_content_rows( $post_ids ); ?>
			</tbody>
		</table>

		<?php foreach ( $post_ids as $post_id ) : ?>
			<input type="hidden" name="content_post_ids[]" value="<?php echo esc_attr( (string) $post_id ); ?>">
		<?php endforeach; ?>
		<?php
	}

	/**
	 * Render content rows.
	 *
	 * @param array<int> $post_ids Post IDs.
	 * @return void
	 */
	private function render_content_rows( array $post_ids ): void {
		if ( empty( $post_ids ) ) {
			?>
			<tr class="lw-mship-no-items">
				<td colspan="3"><?php esc_html_e( 'No content assigned yet.', 'lw-memberships' ); ?></td>
			</tr>
			<?php
			return;
		}

		foreach ( $post_ids as $post_id ) {
			$post = get_post( $post_id );
			if ( ! $post ) {
				continue;
			}
			?>
			<tr data-post-id="<?php echo esc_attr( (string) $post_id ); ?>">
				<td>
					<a href="<?php echo esc_url( get_edit_post_link( $post_id ) ); ?>">
						<?php echo esc_html( $post->post_title ); ?>
					</a>
				</td>
				<td><?php echo esc_html( $post->post_type ); ?></td>
				<td>
					<button type="button" class="button button-small lw-mship-remove-content" data-post-id="<?php echo esc_attr( (string) $post_id ); ?>">
						<?php esc_html_e( 'Remove', 'lw-memberships' ); ?>
					</button>
				</td>
			</tr>
			<?php
		}
	}

	/**
	 * Render save first notice.
	 *
	 * @return void
	 */
	private function render_save_first_notice(): void {
		?>
		<p><?php esc_html_e( 'Please save the plan first to assign content.', 'lw-memberships' ); ?></p>
		<?php
	}
}
