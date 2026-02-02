<?php
/**
 * Level editor.
 *
 * @package LightweightPlugins\Memberships
 */

declare(strict_types=1);

namespace LightweightPlugins\Memberships\Admin\Levels;

use LightweightPlugins\Memberships\Database\LevelRepository;
use LightweightPlugins\Memberships\Database\ProductRepository;
use LightweightPlugins\Memberships\Models\Level;

/**
 * Level editor form.
 */
final class LevelEditor {

	use LevelFieldsTrait;

	/**
	 * Level ID.
	 *
	 * @var int
	 */
	private int $id;

	/**
	 * Level data.
	 *
	 * @var Level|null
	 */
	private ?Level $level;

	/**
	 * Constructor.
	 *
	 * @param int $id Level ID.
	 */
	public function __construct( int $id ) {
		$this->id    = $id;
		$this->level = $id > 0 ? LevelRepository::get_by_id( $id ) : null;
	}

	/**
	 * Render the editor.
	 *
	 * @return void
	 */
	public function render(): void {
		$is_new = null === $this->level;
		$title  = $is_new ? __( 'Add New Level', 'lw-memberships' ) : __( 'Edit Level', 'lw-memberships' );

		?>
		<h1><?php echo esc_html( $title ); ?></h1>

		<form method="post" action="">
			<?php wp_nonce_field( 'save_level', 'lw_mship_nonce' ); ?>
			<input type="hidden" name="lw_mship_action" value="save_level">
			<input type="hidden" name="level_id" value="<?php echo esc_attr( (string) $this->id ); ?>">

			<table class="form-table">
				<?php $this->render_name_field(); ?>
				<?php $this->render_slug_field(); ?>
				<?php $this->render_description_field(); ?>
				<?php $this->render_duration_field(); ?>
				<?php $this->render_priority_field(); ?>
				<?php $this->render_status_field(); ?>
				<?php $this->render_products_field(); ?>
			</table>

			<?php submit_button( $is_new ? __( 'Create Level', 'lw-memberships' ) : __( 'Update Level', 'lw-memberships' ) ); ?>
		</form>

		<p>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . LevelsPage::SLUG ) ); ?>">
				&larr; <?php esc_html_e( 'Back to Levels', 'lw-memberships' ); ?>
			</a>
		</p>
		<?php
	}

	/**
	 * Render priority field.
	 *
	 * @return void
	 */
	private function render_priority_field(): void {
		$value = $this->level->priority ?? 0;
		?>
		<tr>
			<th scope="row"><label for="priority"><?php esc_html_e( 'Priority', 'lw-memberships' ); ?></label></th>
			<td>
				<input type="number" name="priority" id="priority" value="<?php echo esc_attr( (string) $value ); ?>" class="small-text">
				<p class="description"><?php esc_html_e( 'Higher priority levels take precedence.', 'lw-memberships' ); ?></p>
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
		$value = $this->level->status ?? 'active';
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

	/**
	 * Render products field.
	 *
	 * @return void
	 */
	private function render_products_field(): void {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		$product_ids = $this->id > 0 ? ProductRepository::get_by_level( $this->id ) : [];
		$product_ids = array_column( $product_ids, 'product_id' );
		?>
		<tr>
			<th scope="row"><label for="products"><?php esc_html_e( 'WooCommerce Products', 'lw-memberships' ); ?></label></th>
			<td>
				<select name="products[]" id="products" multiple class="regular-text" style="min-width: 400px; min-height: 100px;">
					<?php foreach ( $product_ids as $product_id ) : ?>
						<?php
						$product = wc_get_product( $product_id );
						if ( $product ) :
							?>
							<option value="<?php echo esc_attr( $product_id ); ?>" selected><?php echo esc_html( $product->get_name() ); ?></option>
						<?php endif; ?>
					<?php endforeach; ?>
				</select>
				<p class="description"><?php esc_html_e( 'Select products that grant this membership level.', 'lw-memberships' ); ?></p>
			</td>
		</tr>
		<?php
	}
}
