<?php
/**
 * Membership granter service.
 *
 * @package LightweightPlugins\Memberships
 */

declare(strict_types=1);

namespace LightweightPlugins\Memberships\Services;

use LightweightPlugins\Memberships\Database\LevelRepository;
use LightweightPlugins\Memberships\Database\MembershipRepository;
use LightweightPlugins\Memberships\Models\Level;

/**
 * Handles granting and revoking memberships.
 */
final class MembershipGranter {

	/**
	 * Grant membership to user.
	 *
	 * @param int      $user_id         User ID.
	 * @param int      $level_id        Level ID.
	 * @param string   $source          Source (purchase, subscription, manual, import).
	 * @param int|null $order_id        Order ID.
	 * @param int|null $subscription_id Subscription ID.
	 * @return int|false Membership ID on success, false on failure.
	 */
	public static function grant(
		int $user_id,
		int $level_id,
		string $source = 'manual',
		?int $order_id = null,
		?int $subscription_id = null
	) {
		$level = LevelRepository::get_by_id( $level_id );

		if ( ! $level || ! $level->is_active() ) {
			return false;
		}

		// Check if user already has this level.
		$existing = MembershipRepository::get_by_user_and_level( $user_id, $level_id );

		if ( $existing && $existing->is_active() ) {
			return self::extend_membership( $existing->id, $level );
		}

		$start_date = current_time( 'mysql' );
		$end_date   = $level->get_expiration_date( $start_date );

		$membership_id = MembershipRepository::create(
			[
				'user_id'         => $user_id,
				'level_id'        => $level_id,
				'order_id'        => $order_id,
				'subscription_id' => $subscription_id,
				'source'          => $source,
				'status'          => 'active',
				'start_date'      => $start_date,
				'end_date'        => $end_date,
			]
		);

		if ( $membership_id ) {
			/**
			 * Fires when a membership is granted.
			 *
			 * @param int $membership_id Membership ID.
			 * @param int $user_id       User ID.
			 * @param int $level_id      Level ID.
			 */
			do_action( 'lw_mship_membership_granted', $membership_id, $user_id, $level_id );
		}

		return $membership_id;
	}

	/**
	 * Extend existing membership.
	 *
	 * @param int   $membership_id Membership ID.
	 * @param Level $level         Level object.
	 * @return int|false
	 */
	private static function extend_membership( int $membership_id, Level $level ) {
		$membership = MembershipRepository::get_by_id( $membership_id );

		if ( ! $membership ) {
			return false;
		}

		// Calculate new end date from current end date.
		$start_from = $membership->end_date ?? current_time( 'mysql' );
		$new_end    = $level->get_expiration_date( $start_from );

		MembershipRepository::update(
			$membership_id,
			[
				'end_date' => $new_end,
				'status'   => 'active',
			]
		);

		return $membership_id;
	}

	/**
	 * Revoke membership.
	 *
	 * @param int $user_id  User ID.
	 * @param int $level_id Level ID.
	 * @return bool
	 */
	public static function revoke( int $user_id, int $level_id ): bool {
		$membership = MembershipRepository::get_by_user_and_level( $user_id, $level_id );

		if ( ! $membership ) {
			return false;
		}

		$result = MembershipRepository::update(
			$membership->id,
			[
				'status'       => 'cancelled',
				'cancelled_at' => current_time( 'mysql' ),
			]
		);

		if ( $result ) {
			/**
			 * Fires when a membership is revoked.
			 *
			 * @param int $membership_id Membership ID.
			 * @param int $user_id       User ID.
			 * @param int $level_id      Level ID.
			 */
			do_action( 'lw_mship_membership_revoked', $membership->id, $user_id, $level_id );
		}

		return $result;
	}

	/**
	 * Pause membership.
	 *
	 * @param int $membership_id Membership ID.
	 * @return bool
	 */
	public static function pause( int $membership_id ): bool {
		return MembershipRepository::update( $membership_id, [ 'status' => 'paused' ] );
	}

	/**
	 * Resume membership.
	 *
	 * @param int $membership_id Membership ID.
	 * @return bool
	 */
	public static function resume( int $membership_id ): bool {
		return MembershipRepository::update( $membership_id, [ 'status' => 'active' ] );
	}
}
