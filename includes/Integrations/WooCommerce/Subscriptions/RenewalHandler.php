<?php
/**
 * WooCommerce Subscriptions renewal handler.
 *
 * @package LightweightPlugins\Memberships
 */

declare(strict_types=1);

namespace LightweightPlugins\Memberships\Integrations\WooCommerce\Subscriptions;

use LightweightPlugins\Memberships\Database\MembershipRepository;
use LightweightPlugins\Memberships\Database\PlanRepository;

/**
 * Handles WooCommerce Subscriptions renewal.
 */
final class RenewalHandler {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'woocommerce_subscription_renewal_payment_complete', [ $this, 'on_renewal' ] );
		add_action( 'woocommerce_subscription_renewal_payment_failed', [ $this, 'on_renewal_failed' ] );
	}

	/**
	 * Handle renewal payment complete.
	 *
	 * @param \WC_Subscription $subscription Subscription object.
	 * @return void
	 */
	public function on_renewal( \WC_Subscription $subscription ): void {
		$membership = MembershipRepository::get_by_subscription( $subscription->get_id() );

		if ( ! $membership ) {
			return;
		}

		$plan = PlanRepository::get_by_id( $membership->plan_id );

		if ( ! $plan ) {
			return;
		}

		// Extend membership based on plan duration.
		$start_from = $membership->end_date ?? current_time( 'mysql' );
		$new_end    = $plan->get_expiration_date( $start_from );

		MembershipRepository::update(
			$membership->id,
			[
				'end_date' => $new_end,
				'status'   => 'active',
			]
		);
	}

	/**
	 * Handle renewal payment failed.
	 *
	 * @param \WC_Subscription $subscription Subscription object.
	 * @return void
	 */
	public function on_renewal_failed( \WC_Subscription $subscription ): void {
		$membership = MembershipRepository::get_by_subscription( $subscription->get_id() );

		if ( ! $membership ) {
			return;
		}

		// Put membership on hold.
		MembershipRepository::update( $membership->id, [ 'status' => 'paused' ] );
	}
}
