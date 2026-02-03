<?php
/**
 * Members tab for plan editor.
 *
 * @package LightweightPlugins\Memberships
 */

declare(strict_types=1);

namespace LightweightPlugins\Memberships\Admin\Plans\Tabs;

use LightweightPlugins\Memberships\Admin\Plans\MembersListTable;

/**
 * Members management tab.
 */
final class TabMembers implements TabInterface {

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
		return 'members';
	}

	/**
	 * Get tab label.
	 *
	 * @return string
	 */
	public function get_label(): string {
		return __( 'Members', 'lw-memberships' );
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

		$this->render_add_member_form();
		$this->render_members_list();
	}

	/**
	 * Render add member form.
	 *
	 * @return void
	 */
	private function render_add_member_form(): void {
		?>
		<h3><?php esc_html_e( 'Add Member', 'lw-memberships' ); ?></h3>
		<div style="margin-bottom: 20px;">
			<input type="text" id="lw-mship-member-search-input" placeholder="<?php esc_attr_e( 'Search users by name or email...', 'lw-memberships' ); ?>" class="regular-text">
			<div id="lw-mship-member-search-results" style="display:none;"></div>
			<input type="hidden" name="add_member_user_id" id="lw-mship-add-member-id" value="">
			<button type="submit" name="lw_mship_add_member" value="1" class="button button-secondary" id="lw-mship-add-member-btn">
				<?php esc_html_e( 'Add Member', 'lw-memberships' ); ?>
			</button>
		</div>
		<?php
	}

	/**
	 * Render members list.
	 *
	 * @return void
	 */
	private function render_members_list(): void {
		$table = new MembersListTable( $this->plan_id );
		$table->prepare_items();

		?>
		<h3><?php esc_html_e( 'Current Members', 'lw-memberships' ); ?></h3>
		<?php $table->display(); ?>
		<?php
	}

	/**
	 * Render save first notice.
	 *
	 * @return void
	 */
	private function render_save_first_notice(): void {
		?>
		<p><?php esc_html_e( 'Please save the plan first to manage members.', 'lw-memberships' ); ?></p>
		<?php
	}
}
