<?php
/**
 * WooCommerce tab for plan editor.
 *
 * @package LightweightPlugins\Memberships
 */

declare(strict_types=1);

namespace LightweightPlugins\Memberships\Admin\Plans\Tabs;

use LightweightPlugins\Memberships\Database\ProductRepository;

/**
 * WooCommerce product association tab.
 */
final class TabWooCommerce implements TabInterface {

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
		return 'woocommerce';
	}

	/**
	 * Get tab label.
	 *
	 * @return string
	 */
	public function get_label(): string {
		return __( 'WooCommerce', 'lw-memberships' );
	}

	/**
	 * Render tab content.
	 *
	 * @return void
	 */
	public function render(): void {
		$product_ids = [];

		if ( $this->plan_id > 0 ) {
			$products    = ProductRepository::get_by_plan( $this->plan_id );
			$product_ids = array_column( $products, 'product_id' );
		}

		?>
		<h3><?php esc_html_e( 'Linked Products', 'lw-memberships' ); ?></h3>
		<p class="description"><?php esc_html_e( 'Select WooCommerce products that grant this membership plan on purchase.', 'lw-memberships' ); ?></p>

		<table class="form-table">
			<tr>
				<th scope="row">
					<label for="products"><?php esc_html_e( 'Products', 'lw-memberships' ); ?></label>
				</th>
				<td>
					<select name="products[]" id="products" multiple class="regular-text" style="min-width: 400px; min-height: 120px;">
						<?php foreach ( $product_ids as $product_id ) : ?>
							<?php
							$product = wc_get_product( $product_id );
							if ( $product ) :
								?>
								<option value="<?php echo esc_attr( (string) $product_id ); ?>" selected>
									<?php echo esc_html( $product->get_name() ); ?>
								</option>
							<?php endif; ?>
						<?php endforeach; ?>
					</select>
					<p class="description">
						<?php esc_html_e( 'Hold Ctrl/Cmd to select multiple products.', 'lw-memberships' ); ?>
					</p>
				</td>
			</tr>
		</table>
		<?php
	}
}
