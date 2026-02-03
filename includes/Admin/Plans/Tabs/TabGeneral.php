<?php
/**
 * General tab for plan editor.
 *
 * @package LightweightPlugins\Memberships
 */

declare(strict_types=1);

namespace LightweightPlugins\Memberships\Admin\Plans\Tabs;

use LightweightPlugins\Memberships\Admin\Plans\PlanFieldsTrait;
use LightweightPlugins\Memberships\Models\Plan;

/**
 * General settings tab.
 */
final class TabGeneral implements TabInterface {

	use PlanFieldsTrait;

	/**
	 * Plan data.
	 *
	 * @var Plan|null
	 */
	private ?Plan $plan;

	/**
	 * Constructor.
	 *
	 * @param Plan|null $plan Plan object.
	 */
	public function __construct( ?Plan $plan ) {
		$this->plan = $plan;
	}

	/**
	 * Get tab slug.
	 *
	 * @return string
	 */
	public function get_slug(): string {
		return 'general';
	}

	/**
	 * Get tab label.
	 *
	 * @return string
	 */
	public function get_label(): string {
		return __( 'General', 'lw-memberships' );
	}

	/**
	 * Render tab content.
	 *
	 * @return void
	 */
	public function render(): void {
		?>
		<table class="form-table">
			<?php $this->render_name_field(); ?>
			<?php $this->render_slug_field(); ?>
			<?php $this->render_description_field(); ?>
			<?php $this->render_duration_field(); ?>
			<?php $this->render_priority_field(); ?>
			<?php $this->render_status_field(); ?>
		</table>
		<?php
	}

	/**
	 * Render priority field.
	 *
	 * @return void
	 */
	private function render_priority_field(): void {
		$value = $this->plan->priority ?? 0;
		?>
		<tr>
			<th scope="row"><label for="priority"><?php esc_html_e( 'Priority', 'lw-memberships' ); ?></label></th>
			<td>
				<input type="number" name="priority" id="priority" value="<?php echo esc_attr( (string) $value ); ?>" class="small-text">
				<p class="description"><?php esc_html_e( 'Higher priority plans take precedence.', 'lw-memberships' ); ?></p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Render status field.
	 *
	 * @return void
	 */
	private function render_status_field(): void {
		$value = $this->plan->status ?? 'active';
		?>
		<tr>
			<th scope="row"><label for="status"><?php esc_html_e( 'Status', 'lw-memberships' ); ?></label></th>
			<td>
				<select name="status" id="status">
					<option value="active" <?php selected( $value, 'active' ); ?>><?php esc_html_e( 'Active', 'lw-memberships' ); ?></option>
					<option value="inactive" <?php selected( $value, 'inactive' ); ?>><?php esc_html_e( 'Inactive', 'lw-memberships' ); ?></option>
				</select>
			</td>
		</tr>
		<?php
	}
}
