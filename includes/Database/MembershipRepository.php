<?php
/**
 * Membership repository.
 *
 * @package LightweightPlugins\Memberships
 */

declare(strict_types=1);

namespace LightweightPlugins\Memberships\Database;

use LightweightPlugins\Memberships\Models\Membership;

/**
 * Handles database operations for user memberships.
 */
final class MembershipRepository {

	/**
	 * Get memberships by user ID.
	 *
	 * @param int  $user_id     User ID.
	 * @param bool $active_only Only return active memberships.
	 * @return array<int, Membership>
	 */
	public static function get_by_user( int $user_id, bool $active_only = false ): array {
		global $wpdb;

		$table = Schema::memberships_table();
		$sql   = $wpdb->prepare( "SELECT * FROM {$table} WHERE user_id = %d", $user_id ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		if ( $active_only ) {
			$sql .= " AND status = 'active'";
		}

		$sql .= ' ORDER BY created_at DESC';

		$results = $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		return array_map( [ Membership::class, 'from_row' ], $results );
	}

	/**
	 * Get membership by ID.
	 *
	 * @param int $id Membership ID.
	 * @return Membership|null
	 */
	public static function get_by_id( int $id ): ?Membership {
		global $wpdb;

		$table = Schema::memberships_table();
		$row   = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);

		return $row ? Membership::from_row( $row ) : null;
	}

	/**
	 * Get membership by user and plan.
	 *
	 * @param int $user_id User ID.
	 * @param int $plan_id Plan ID.
	 * @return Membership|null
	 */
	public static function get_by_user_and_plan( int $user_id, int $plan_id ): ?Membership {
		global $wpdb;

		$table = Schema::memberships_table();
		$row   = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE user_id = %d AND plan_id = %d ORDER BY created_at DESC LIMIT 1", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$user_id,
				$plan_id
			)
		);

		return $row ? Membership::from_row( $row ) : null;
	}

	/**
	 * Get membership by subscription ID.
	 *
	 * @param int $subscription_id Subscription ID.
	 * @return Membership|null
	 */
	public static function get_by_subscription( int $subscription_id ): ?Membership {
		global $wpdb;

		$table = Schema::memberships_table();
		$row   = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE subscription_id = %d", $subscription_id ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);

		return $row ? Membership::from_row( $row ) : null;
	}

	/**
	 * Create a new membership.
	 *
	 * @param array<string, mixed> $data Membership data.
	 * @return int|false Membership ID on success, false on failure.
	 */
	public static function create( array $data ) {
		global $wpdb;

		$now = current_time( 'mysql' );

		$result = $wpdb->insert(
			Schema::memberships_table(),
			[
				'user_id'         => $data['user_id'],
				'plan_id'         => $data['plan_id'],
				'order_id'        => $data['order_id'] ?? null,
				'subscription_id' => $data['subscription_id'] ?? null,
				'source'          => $data['source'] ?? 'manual',
				'status'          => $data['status'] ?? 'active',
				'start_date'      => $data['start_date'] ?? $now,
				'end_date'        => $data['end_date'] ?? null,
				'cancelled_at'    => null,
				'created_at'      => $now,
				'updated_at'      => $now,
			],
			[ '%d', '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ]
		);

		return $result ? (int) $wpdb->insert_id : false;
	}

	/**
	 * Update membership.
	 *
	 * @param int                  $id   Membership ID.
	 * @param array<string, mixed> $data Membership data.
	 * @return bool
	 */
	public static function update( int $id, array $data ): bool {
		global $wpdb;

		$data['updated_at'] = current_time( 'mysql' );

		$result = $wpdb->update(
			Schema::memberships_table(),
			$data,
			[ 'id' => $id ],
			null,
			[ '%d' ]
		);

		return false !== $result;
	}

	/**
	 * Delete membership.
	 *
	 * @param int $id Membership ID.
	 * @return bool
	 */
	public static function delete( int $id ): bool {
		global $wpdb;

		$result = $wpdb->delete(
			Schema::memberships_table(),
			[ 'id' => $id ],
			[ '%d' ]
		);

		return false !== $result;
	}

	/**
	 * Get expired memberships.
	 *
	 * @return array<int, Membership>
	 */
	public static function get_expired(): array {
		global $wpdb;

		$table = Schema::memberships_table();
		$now   = current_time( 'mysql' );

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE status = 'active' AND end_date IS NOT NULL AND end_date < %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$now
			)
		);

		return array_map( [ Membership::class, 'from_row' ], $results );
	}

	/**
	 * Check if user has active membership to plan.
	 *
	 * @param int $user_id User ID.
	 * @param int $plan_id Plan ID.
	 * @return bool
	 */
	public static function user_has_plan( int $user_id, int $plan_id ): bool {
		global $wpdb;

		$table = Schema::memberships_table();
		$now   = current_time( 'mysql' );

		$count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE user_id = %d AND plan_id = %d AND status = 'active' AND (end_date IS NULL OR end_date > %s)", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$user_id,
				$plan_id,
				$now
			)
		);

		return (int) $count > 0;
	}

	/**
	 * Get memberships by plan (paginated).
	 *
	 * @param int $plan_id  Plan ID.
	 * @param int $page     Current page.
	 * @param int $per_page Items per page.
	 * @return array<int, Membership>
	 */
	public static function get_by_plan( int $plan_id, int $page = 1, int $per_page = 20 ): array {
		global $wpdb;

		$table  = Schema::memberships_table();
		$offset = ( $page - 1 ) * $per_page;

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE plan_id = %d ORDER BY created_at DESC LIMIT %d OFFSET %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$plan_id,
				$per_page,
				$offset
			)
		);

		return array_map( [ Membership::class, 'from_row' ], $results );
	}

	/**
	 * Count memberships by plan.
	 *
	 * @param int $plan_id Plan ID.
	 * @return int
	 */
	public static function count_by_plan( int $plan_id ): int {
		global $wpdb;

		$table = Schema::memberships_table();

		return (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE plan_id = %d", $plan_id ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);
	}
}
