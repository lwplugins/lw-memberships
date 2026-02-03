<?php
/**
 * Plan fields trait.
 *
 * @package LightweightPlugins\Memberships
 */

declare(strict_types=1);

namespace LightweightPlugins\Memberships\Admin\Plans;

/**
 * Provides form field rendering for plan editor.
 */
trait PlanFieldsTrait {

	/**
	 * Render name field.
	 *
	 * @return void
	 */
	private function render_name_field(): void {
		$value = $this->plan->name ?? '';
		?>
		<tr>
			<th scope="row"><label for="name"><?php esc_html_e( 'Name', 'lw-memberships' ); ?> *</label></th>
			<td><input type="text" name="name" id="name" value="<?php echo esc_attr( $value ); ?>" class="regular-text" required></td>
		</tr>
		<?php
	}

	/**
	 * Render slug field.
	 *
	 * @return void
	 */
	private function render_slug_field(): void {
		$value = $this->plan->slug ?? '';
		?>
		<tr>
			<th scope="row"><label for="slug"><?php esc_html_e( 'Slug', 'lw-memberships' ); ?></label></th>
			<td>
				<input type="text" name="slug" id="slug" value="<?php echo esc_attr( $value ); ?>" class="regular-text">
				<p class="description"><?php esc_html_e( 'Leave empty to auto-generate from name.', 'lw-memberships' ); ?></p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Render description field.
	 *
	 * @return void
	 */
	private function render_description_field(): void {
		$value = $this->plan->description ?? '';
		?>
		<tr>
			<th scope="row"><label for="description"><?php esc_html_e( 'Description', 'lw-memberships' ); ?></label></th>
			<td><textarea name="description" id="description" rows="3" class="large-text"><?php echo esc_textarea( $value ); ?></textarea></td>
		</tr>
		<?php
	}

	/**
	 * Render duration field.
	 *
	 * @return void
	 */
	private function render_duration_field(): void {
		$type  = $this->plan->duration_type ?? 'forever';
		$value = $this->plan->duration_value ?? '';
		?>
		<tr>
			<th scope="row"><label><?php esc_html_e( 'Duration', 'lw-memberships' ); ?></label></th>
			<td>
				<select name="duration_type" id="duration_type">
					<option value="forever" <?php selected( $type, 'forever' ); ?>><?php esc_html_e( 'Lifetime', 'lw-memberships' ); ?></option>
					<option value="days" <?php selected( $type, 'days' ); ?>><?php esc_html_e( 'Days', 'lw-memberships' ); ?></option>
					<option value="months" <?php selected( $type, 'months' ); ?>><?php esc_html_e( 'Months', 'lw-memberships' ); ?></option>
					<option value="years" <?php selected( $type, 'years' ); ?>><?php esc_html_e( 'Years', 'lw-memberships' ); ?></option>
				</select>
				<input type="number" name="duration_value" id="duration_value" value="<?php echo esc_attr( (string) $value ); ?>" min="1" class="small-text">
			</td>
		</tr>
		<?php
	}
}
