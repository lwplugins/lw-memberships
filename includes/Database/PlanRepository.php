<?php
/**
 * Plan repository.
 *
 * @package LightweightPlugins\Memberships
 */

declare(strict_types=1);

namespace LightweightPlugins\Memberships\Database;

use LightweightPlugins\Memberships\Models\Plan;

/**
 * Handles database operations for membership plans.
 */
final class PlanRepository {

	/**
	 * Get all plans.
	 *
	 * @param bool $active_only Only return active plans.
	 * @return array<int, Plan>
	 */
	public static function get_all( bool $active_only = false ): array {
		global $wpdb;

		$table = Schema::plans_table();
		$sql   = "SELECT * FROM {$table}";

		if ( $active_only ) {
			$sql .= " WHERE status = 'active'";
		}

		$sql .= ' ORDER BY priority DESC, name ASC';

		$results = $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		return array_map( [ Plan::class, 'from_row' ], $results );
	}

	/**
	 * Get plan by ID.
	 *
	 * @param int $id Plan ID.
	 * @return Plan|null
	 */
	public static function get_by_id( int $id ): ?Plan {
		global $wpdb;

		$table = Schema::plans_table();
		$row   = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);

		return $row ? Plan::from_row( $row ) : null;
	}

	/**
	 * Get plan by slug.
	 *
	 * @param string $slug Plan slug.
	 * @return Plan|null
	 */
	public static function get_by_slug( string $slug ): ?Plan {
		global $wpdb;

		$table = Schema::plans_table();
		$row   = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE slug = %s", $slug ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);

		return $row ? Plan::from_row( $row ) : null;
	}

	/**
	 * Create a new plan.
	 *
	 * @param array<string, mixed> $data Plan data.
	 * @return int|false Plan ID on success, false on failure.
	 */
	public static function create( array $data ) {
		global $wpdb;

		$now = current_time( 'mysql' );

		$result = $wpdb->insert(
			Schema::plans_table(),
			[
				'name'           => $data['name'],
				'slug'           => $data['slug'],
				'description'    => $data['description'] ?? '',
				'duration_type'  => $data['duration_type'] ?? 'forever',
				'duration_value' => $data['duration_value'] ?? null,
				'priority'       => $data['priority'] ?? 0,
				'status'         => $data['status'] ?? 'active',
				'created_at'     => $now,
				'updated_at'     => $now,
			],
			[ '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s', '%s' ]
		);

		return $result ? (int) $wpdb->insert_id : false;
	}

	/**
	 * Update a plan.
	 *
	 * @param int                  $id   Plan ID.
	 * @param array<string, mixed> $data Plan data.
	 * @return bool
	 */
	public static function update( int $id, array $data ): bool {
		global $wpdb;

		$data['updated_at'] = current_time( 'mysql' );

		$result = $wpdb->update(
			Schema::plans_table(),
			$data,
			[ 'id' => $id ],
			null,
			[ '%d' ]
		);

		return false !== $result;
	}

	/**
	 * Delete a plan.
	 *
	 * @param int $id Plan ID.
	 * @return bool
	 */
	public static function delete( int $id ): bool {
		global $wpdb;

		$result = $wpdb->delete(
			Schema::plans_table(),
			[ 'id' => $id ],
			[ '%d' ]
		);

		return false !== $result;
	}

	/**
	 * Check if slug exists.
	 *
	 * @param string   $slug       Slug to check.
	 * @param int|null $exclude_id Plan ID to exclude.
	 * @return bool
	 */
	public static function slug_exists( string $slug, ?int $exclude_id = null ): bool {
		global $wpdb;

		$table = Schema::plans_table();
		$sql   = $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE slug = %s", $slug ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		if ( null !== $exclude_id ) {
			$sql .= $wpdb->prepare( ' AND id != %d', $exclude_id );
		}

		return (int) $wpdb->get_var( $sql ) > 0; // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}
}
