<?php
/**
 * WooCommerce order handler.
 *
 * @package LightweightPlugins\Memberships
 */

declare(strict_types=1);

namespace LightweightPlugins\Memberships\Integrations\WooCommerce;

use LightweightPlugins\Memberships\Database\ProductRepository;
use LightweightPlugins\Memberships\Options;
use LightweightPlugins\Memberships\Services\MembershipGranter;

/**
 * Handles WooCommerce order events.
 */
final class OrderHandler {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'woocommerce_order_status_completed', [ $this, 'on_order_complete' ] );
		add_action( 'woocommerce_order_status_processing', [ $this, 'on_order_processing' ] );
	}

	/**
	 * Handle order complete.
	 *
	 * @param int $order_id Order ID.
	 * @return void
	 */
	public function on_order_complete( int $order_id ): void {
		if ( ! Options::get( 'auto_grant_on_complete' ) ) {
			return;
		}

		$this->process_order( $order_id );
	}

	/**
	 * Handle order processing (for virtual products).
	 *
	 * @param int $order_id Order ID.
	 * @return void
	 */
	public function on_order_processing( int $order_id ): void {
		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return;
		}

		// Only process virtual orders immediately.
		if ( ! $this->is_virtual_order( $order ) ) {
			return;
		}

		$this->process_order( $order_id );
	}

	/**
	 * Process order and grant memberships.
	 *
	 * @param int $order_id Order ID.
	 * @return void
	 */
	private function process_order( int $order_id ): void {
		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return;
		}

		// Skip if already processed.
		if ( $order->get_meta( '_lw_mship_processed' ) ) {
			return;
		}

		$user_id = $order->get_user_id();

		if ( ! $user_id ) {
			return;
		}

		$granted = false;

		foreach ( $order->get_items() as $item ) {
			$product_id = $item->get_product_id();
			$level_ids  = ProductRepository::get_levels_by_product( $product_id );

			foreach ( $level_ids as $level_id ) {
				$result = MembershipGranter::grant(
					$user_id,
					$level_id,
					'purchase',
					$order_id
				);

				if ( $result ) {
					$granted = true;
				}
			}
		}

		if ( $granted ) {
			$order->update_meta_data( '_lw_mship_processed', true );
			$order->save();

			/* translators: %d: order ID */
			$order->add_order_note(
				sprintf( __( 'LW Memberships: Membership(s) granted.', 'lw-memberships' ) )
			);
		}
	}

	/**
	 * Check if order contains only virtual products.
	 *
	 * @param \WC_Order $order Order object.
	 * @return bool
	 */
	private function is_virtual_order( \WC_Order $order ): bool {
		foreach ( $order->get_items() as $item ) {
			$product = $item->get_product();

			if ( $product && ! $product->is_virtual() ) {
				return false;
			}
		}

		return true;
	}
}
