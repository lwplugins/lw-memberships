<?php
/**
 * WooCommerce Subscriptions status handler.
 *
 * @package LightweightPlugins\Memberships
 */

declare(strict_types=1);

namespace LightweightPlugins\Memberships\Integrations\WooCommerce\Subscriptions;

use LightweightPlugins\Memberships\Database\MembershipRepository;
use LightweightPlugins\Memberships\Database\ProductRepository;
use LightweightPlugins\Memberships\Services\MembershipGranter;

/**
 * Handles WooCommerce Subscriptions status changes.
 */
final class StatusHandler {

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Subscription activated.
		add_action( 'woocommerce_subscription_status_active', [ $this, 'on_active' ] );

		// Subscription on hold.
		add_action( 'woocommerce_subscription_status_on-hold', [ $this, 'on_hold' ] );

		// Subscription cancelled.
		add_action( 'woocommerce_subscription_status_cancelled', [ $this, 'on_cancelled' ] );

		// Subscription expired.
		add_action( 'woocommerce_subscription_status_expired', [ $this, 'on_expired' ] );

		// Subscription pending cancel.
		add_action( 'woocommerce_subscription_status_pending-cancel', [ $this, 'on_pending_cancel' ] );
	}

	/**
	 * Handle subscription active.
	 *
	 * @param \WC_Subscription $subscription Subscription object.
	 * @return void
	 */
	public function on_active( \WC_Subscription $subscription ): void {
		$this->grant_memberships( $subscription );
	}

	/**
	 * Handle subscription on hold.
	 *
	 * @param \WC_Subscription $subscription Subscription object.
	 * @return void
	 */
	public function on_hold( \WC_Subscription $subscription ): void {
		$this->pause_memberships( $subscription );
	}

	/**
	 * Handle subscription cancelled.
	 *
	 * @param \WC_Subscription $subscription Subscription object.
	 * @return void
	 */
	public function on_cancelled( \WC_Subscription $subscription ): void {
		$this->cancel_memberships( $subscription );
	}

	/**
	 * Handle subscription expired.
	 *
	 * @param \WC_Subscription $subscription Subscription object.
	 * @return void
	 */
	public function on_expired( \WC_Subscription $subscription ): void {
		$this->expire_memberships( $subscription );
	}

	/**
	 * Handle subscription pending cancel.
	 *
	 * @param \WC_Subscription $subscription Subscription object.
	 * @return void
	 */
	public function on_pending_cancel( \WC_Subscription $subscription ): void {
		// Membership remains active until subscription actually ends.
	}

	/**
	 * Grant memberships for subscription.
	 *
	 * @param \WC_Subscription $subscription Subscription object.
	 * @return void
	 */
	private function grant_memberships( \WC_Subscription $subscription ): void {
		$user_id = $subscription->get_user_id();

		if ( ! $user_id ) {
			return;
		}

		foreach ( $subscription->get_items() as $item ) {
			$product_id = $item->get_product_id();
			$level_ids  = ProductRepository::get_levels_by_product( $product_id );

			foreach ( $level_ids as $level_id ) {
				// Check for existing membership.
				$existing = MembershipRepository::get_by_subscription( $subscription->get_id() );

				if ( $existing && $existing->level_id === $level_id ) {
					MembershipGranter::resume( $existing->id );
					continue;
				}

				MembershipGranter::grant(
					$user_id,
					$level_id,
					'subscription',
					null,
					$subscription->get_id()
				);
			}
		}
	}

	/**
	 * Pause memberships for subscription.
	 *
	 * @param \WC_Subscription $subscription Subscription object.
	 * @return void
	 */
	private function pause_memberships( \WC_Subscription $subscription ): void {
		$membership = MembershipRepository::get_by_subscription( $subscription->get_id() );

		if ( $membership ) {
			MembershipGranter::pause( $membership->id );
		}
	}

	/**
	 * Cancel memberships for subscription.
	 *
	 * @param \WC_Subscription $subscription Subscription object.
	 * @return void
	 */
	private function cancel_memberships( \WC_Subscription $subscription ): void {
		$user_id = $subscription->get_user_id();

		if ( ! $user_id ) {
			return;
		}

		foreach ( $subscription->get_items() as $item ) {
			$product_id = $item->get_product_id();
			$level_ids  = ProductRepository::get_levels_by_product( $product_id );

			foreach ( $level_ids as $level_id ) {
				MembershipGranter::revoke( $user_id, $level_id );
			}
		}
	}

	/**
	 * Expire memberships for subscription.
	 *
	 * @param \WC_Subscription $subscription Subscription object.
	 * @return void
	 */
	private function expire_memberships( \WC_Subscription $subscription ): void {
		$membership = MembershipRepository::get_by_subscription( $subscription->get_id() );

		if ( $membership ) {
			MembershipRepository::update( $membership->id, [ 'status' => 'expired' ] );
		}
	}
}
