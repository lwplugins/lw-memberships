<?php
/**
 * Public API functions.
 *
 * @package LightweightPlugins\Memberships
 */

declare(strict_types=1);

use LightweightPlugins\Memberships\Database\LevelRepository;
use LightweightPlugins\Memberships\Database\MembershipRepository;
use LightweightPlugins\Memberships\Database\RuleRepository;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if a user has an active membership to a specific level.
 *
 * @param int      $level_id Level ID.
 * @param int|null $user_id  User ID. Defaults to current user.
 * @return bool
 */
function lw_mship_user_has_level( int $level_id, ?int $user_id = null ): bool {
	if ( null === $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( 0 === $user_id ) {
		return false;
	}

	return MembershipRepository::user_has_level( $user_id, $level_id );
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
	$level_ids = RuleRepository::get_level_ids_by_post( $post_id );

	if ( empty( $level_ids ) ) {
		return true; // Not restricted.
	}

	if ( 0 === $user_id ) {
		return false; // Not logged in.
	}

	// Check if user has any of the required levels.
	foreach ( $level_ids as $level_id ) {
		if ( MembershipRepository::user_has_level( $user_id, $level_id ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Get all active membership levels for a user.
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
 * Grant a membership level to a user.
 *
 * @param int      $user_id  User ID.
 * @param int      $level_id Level ID.
 * @param string   $source   Source (purchase, subscription, manual, import).
 * @param int|null $order_id Order ID (optional).
 * @return int|false Membership ID on success, false on failure.
 */
function lw_mship_grant_membership( int $user_id, int $level_id, string $source = 'manual', ?int $order_id = null ) {
	$level = LevelRepository::get_by_id( $level_id );

	if ( ! $level ) {
		return false;
	}

	$start_date = current_time( 'mysql' );
	$end_date   = $level->get_expiration_date( $start_date );

	return MembershipRepository::create(
		[
			'user_id'    => $user_id,
			'level_id'   => $level_id,
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
 * @param int $user_id  User ID.
 * @param int $level_id Level ID.
 * @return bool
 */
function lw_mship_revoke_membership( int $user_id, int $level_id ): bool {
	$membership = MembershipRepository::get_by_user_and_level( $user_id, $level_id );

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
 * Get all membership levels.
 *
 * @param bool $active_only Only return active levels.
 * @return array<int, \LightweightPlugins\Memberships\Models\Level>
 */
function lw_mship_get_levels( bool $active_only = true ): array {
	return LevelRepository::get_all( $active_only );
}

/**
 * Get a membership level by ID.
 *
 * @param int $level_id Level ID.
 * @return \LightweightPlugins\Memberships\Models\Level|null
 */
function lw_mship_get_level( int $level_id ): ?object {
	return LevelRepository::get_by_id( $level_id );
}
