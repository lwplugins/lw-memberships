<?php
/**
 * Expiration handler service.
 *
 * @package LightweightPlugins\Memberships
 */

declare(strict_types=1);

namespace LightweightPlugins\Memberships\Services;

use LightweightPlugins\Memberships\Database\MembershipRepository;

/**
 * Handles membership expiration.
 */
final class ExpirationHandler {

	/**
	 * Expire a membership.
	 *
	 * @param int $membership_id Membership ID.
	 * @return bool
	 */
	public static function expire( int $membership_id ): bool {
		$membership = MembershipRepository::get_by_id( $membership_id );

		if ( ! $membership ) {
			return false;
		}

		$result = MembershipRepository::update(
			$membership_id,
			[ 'status' => 'expired' ]
		);

		if ( $result ) {
			/**
			 * Fires when a membership expires.
			 *
			 * @param int $membership_id Membership ID.
			 * @param int $user_id       User ID.
			 * @param int $plan_id       Plan ID.
			 */
			do_action(
				'lw_mship_membership_expired',
				$membership_id,
				$membership->user_id,
				$membership->plan_id
			);
		}

		return $result;
	}

	/**
	 * Check if membership is expiring soon.
	 *
	 * @param int $membership_id Membership ID.
	 * @param int $days          Days threshold.
	 * @return bool
	 */
	public static function is_expiring_soon( int $membership_id, int $days = 7 ): bool {
		$membership = MembershipRepository::get_by_id( $membership_id );

		if ( ! $membership || null === $membership->end_date ) {
			return false;
		}

		$remaining = $membership->get_remaining_days();

		if ( null === $remaining ) {
			return false;
		}

		return $remaining > 0 && $remaining <= $days;
	}
}
