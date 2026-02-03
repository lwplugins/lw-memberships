<?php
/**
 * Public API functions.
 *
 * @package LightweightPlugins\Memberships
 */

declare(strict_types=1);

use LightweightPlugins\Memberships\Database\PlanRepository;
use LightweightPlugins\Memberships\Database\MembershipRepository;
use LightweightPlugins\Memberships\Database\RuleRepository;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if a user has an active membership to a specific plan.
 *
 * @param int      $plan_id Plan ID.
 * @param int|null $user_id User ID. Defaults to current user.
 * @return bool
 */
function lw_mship_user_has_plan( int $plan_id, ?int $user_id = null ): bool {
	if ( null === $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( 0 === $user_id ) {
		return false;
	}

	return MembershipRepository::user_has_plan( $user_id, $plan_id );
}

/**
 * Check if a user has access to content.
 *
 * @param int      $post_id Post ID.
 * @param int|null $user_id User ID. Defaults to current user.
 * @return bool
 */
function lw_mship_user_can_access( int $post_id, ?int $user_id = null ): bool {
	if ( null === $user_id ) {
		$user_id = get_current_user_id();
	}

	// Check if post is restricted.
	$plan_ids = RuleRepository::get_plan_ids_by_post( $post_id );

	if ( empty( $plan_ids ) ) {
		return true; // Not restricted.
	}

	if ( 0 === $user_id ) {
		return false; // Not logged in.
	}

	// Check if user has any of the required plans.
	foreach ( $plan_ids as $plan_id ) {
		if ( MembershipRepository::user_has_plan( $user_id, $plan_id ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Get all active memberships for a user.
 *
 * @param int|null $user_id User ID. Defaults to current user.
 * @return array<int, \LightweightPlugins\Memberships\Models\Membership>
 */
function lw_mship_get_user_memberships( ?int $user_id = null ): array {
	if ( null === $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( 0 === $user_id ) {
		return [];
	}

	return MembershipRepository::get_by_user( $user_id, true );
}

/**
 * Grant a membership plan to a user.
 *
 * @param int      $user_id User ID.
 * @param int      $plan_id Plan ID.
 * @param string   $source  Source (purchase, subscription, manual, import).
 * @param int|null $order_id Order ID (optional).
 * @return int|false Membership ID on success, false on failure.
 */
function lw_mship_grant_membership( int $user_id, int $plan_id, string $source = 'manual', ?int $order_id = null ) {
	$plan = PlanRepository::get_by_id( $plan_id );

	if ( ! $plan ) {
		return false;
	}

	$start_date = current_time( 'mysql' );
	$end_date   = $plan->get_expiration_date( $start_date );

	return MembershipRepository::create(
		[
			'user_id'    => $user_id,
			'plan_id'    => $plan_id,
			'source'     => $source,
			'order_id'   => $order_id,
			'start_date' => $start_date,
			'end_date'   => $end_date,
		]
	);
}

/**
 * Revoke a membership from a user.
 *
 * @param int $user_id User ID.
 * @param int $plan_id Plan ID.
 * @return bool
 */
function lw_mship_revoke_membership( int $user_id, int $plan_id ): bool {
	$membership = MembershipRepository::get_by_user_and_plan( $user_id, $plan_id );

	if ( ! $membership ) {
		return false;
	}

	return MembershipRepository::update(
		$membership->id,
		[
			'status'       => 'cancelled',
			'cancelled_at' => current_time( 'mysql' ),
		]
	);
}

/**
 * Get all membership plans.
 *
 * @param bool $active_only Only return active plans.
 * @return array<int, \LightweightPlugins\Memberships\Models\Plan>
 */
function lw_mship_get_plans( bool $active_only = true ): array {
	return PlanRepository::get_all( $active_only );
}

/**
 * Get a membership plan by ID.
 *
 * @param int $plan_id Plan ID.
 * @return \LightweightPlugins\Memberships\Models\Plan|null
 */
function lw_mship_get_plan( int $plan_id ): ?object {
	return PlanRepository::get_by_id( $plan_id );
}
