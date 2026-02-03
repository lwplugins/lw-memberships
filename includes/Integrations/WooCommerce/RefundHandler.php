<?php
/**
 * WooCommerce refund handler.
 *
 * @package LightweightPlugins\Memberships
 */

declare(strict_types=1);

namespace LightweightPlugins\Memberships\Integrations\WooCommerce;

use LightweightPlugins\Memberships\Database\ProductRepository;
use LightweightPlugins\Memberships\Options;
use LightweightPlugins\Memberships\Services\MembershipGranter;

/**
 * Handles WooCommerce refund events.
 */
final class RefundHandler {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'woocommerce_order_status_refunded', [ $this, 'on_order_refunded' ] );
		add_action( 'woocommerce_order_fully_refunded', [ $this, 'on_order_fully_refunded' ] );
	}

	/**
	 * Handle order refunded.
	 *
	 * @param int $order_id Order ID.
	 * @return void
	 */
	public function on_order_refunded( int $order_id ): void {
		$this->revoke_memberships( $order_id );
	}

	/**
	 * Handle order fully refunded.
	 *
	 * @param int $order_id Order ID.
	 * @return void
	 */
	public function on_order_fully_refunded( int $order_id ): void {
		$this->revoke_memberships( $order_id );
	}

	/**
	 * Revoke memberships for refunded order.
	 *
	 * @param int $order_id Order ID.
	 * @return void
	 */
	private function revoke_memberships( int $order_id ): void {
		if ( ! Options::get( 'revoke_on_refund' ) ) {
			return;
		}

		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return;
		}

		$user_id = $order->get_user_id();

		if ( ! $user_id ) {
			return;
		}

		$revoked = false;

		foreach ( $order->get_items() as $item ) {
			$product_id = $item->get_product_id();
			$plan_ids   = ProductRepository::get_plans_by_product( $product_id );

			foreach ( $plan_ids as $plan_id ) {
				$result = MembershipGranter::revoke( $user_id, $plan_id );

				if ( $result ) {
					$revoked = true;
				}
			}
		}

		if ( $revoked ) {
			$order->add_order_note(
				__( 'LW Memberships: Membership(s) revoked due to refund.', 'lw-memberships' )
			);
		}
	}
}
