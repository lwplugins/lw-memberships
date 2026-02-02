<?php
/**
 * Access checker service.
 *
 * @package LightweightPlugins\Memberships
 */

declare(strict_types=1);

namespace LightweightPlugins\Memberships\Services;

use LightweightPlugins\Memberships\Database\MembershipRepository;
use LightweightPlugins\Memberships\Database\RuleRepository;

/**
 * Checks user access to content.
 */
final class AccessChecker {

	/**
	 * Check if user can access content.
	 *
	 * @param int      $post_id Post ID.
	 * @param int|null $user_id User ID.
	 * @return bool
	 */
	public static function can_access( int $post_id, ?int $user_id = null ): bool {
		if ( null === $user_id ) {
			$user_id = get_current_user_id();
		}

		// Admins always have access.
		if ( user_can( $user_id, 'manage_options' ) ) {
			return true;
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
	 * Get restriction reason.
	 *
	 * @param int      $post_id Post ID.
	 * @param int|null $user_id User ID.
	 * @return string|null Restriction reason or null if not restricted.
	 */
	public static function get_restriction_reason( int $post_id, ?int $user_id = null ): ?string {
		if ( null === $user_id ) {
			$user_id = get_current_user_id();
		}

		$level_ids = RuleRepository::get_level_ids_by_post( $post_id );

		if ( empty( $level_ids ) ) {
			return null;
		}

		if ( 0 === $user_id ) {
			return 'not_logged_in';
		}

		// Check user membership status.
		foreach ( $level_ids as $level_id ) {
			$membership = MembershipRepository::get_by_user_and_level( $user_id, $level_id );

			if ( ! $membership ) {
				continue;
			}

			if ( 'paused' === $membership->status ) {
				return 'paused';
			}

			if ( 'expired' === $membership->status || $membership->is_expired() ) {
				return 'expired';
			}
		}

		return 'no_access';
	}

	/**
	 * Get required levels for content.
	 *
	 * @param int $post_id Post ID.
	 * @return array<int>
	 */
	public static function get_required_levels( int $post_id ): array {
		return RuleRepository::get_level_ids_by_post( $post_id );
	}

	/**
	 * Check if content is restricted.
	 *
	 * @param int $post_id Post ID.
	 * @return bool
	 */
	public static function is_restricted( int $post_id ): bool {
		return RuleRepository::is_restricted( $post_id );
	}
}
