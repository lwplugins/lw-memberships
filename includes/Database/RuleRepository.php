<?php
/**
 * Rule repository.
 *
 * @package LightweightPlugins\Memberships
 */

declare(strict_types=1);

namespace LightweightPlugins\Memberships\Database;

use LightweightPlugins\Memberships\Models\ContentRule;

/**
 * Handles database operations for content rules.
 */
final class RuleRepository {

	/**
	 * Get rules by post ID.
	 *
	 * @param int $post_id Post ID.
	 * @return array<int, ContentRule>
	 */
	public static function get_by_post( int $post_id ): array {
		global $wpdb;

		$table   = Schema::rules_table();
		$results = $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE post_id = %d", $post_id ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);

		return array_map( [ ContentRule::class, 'from_row' ], $results );
	}

	/**
	 * Get plan IDs by post ID.
	 *
	 * @param int $post_id Post ID.
	 * @return array<int>
	 */
	public static function get_plan_ids_by_post( int $post_id ): array {
		global $wpdb;

		$table   = Schema::rules_table();
		$results = $wpdb->get_col(
			$wpdb->prepare( "SELECT plan_id FROM {$table} WHERE post_id = %d", $post_id ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);

		return array_map( 'intval', $results );
	}

	/**
	 * Get rules by plan ID.
	 *
	 * @param int $plan_id Plan ID.
	 * @return array<int, ContentRule>
	 */
	public static function get_by_plan( int $plan_id ): array {
		global $wpdb;

		$table   = Schema::rules_table();
		$results = $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE plan_id = %d", $plan_id ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);

		return array_map( [ ContentRule::class, 'from_row' ], $results );
	}

	/**
	 * Get post IDs by plan ID.
	 *
	 * @param int $plan_id Plan ID.
	 * @return array<int>
	 */
	public static function get_post_ids_by_plan( int $plan_id ): array {
		global $wpdb;

		$table = Schema::rules_table();

		$results = $wpdb->get_col(
			$wpdb->prepare( "SELECT post_id FROM {$table} WHERE plan_id = %d", $plan_id ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);

		return array_map( 'intval', $results );
	}

	/**
	 * Add rule.
	 *
	 * @param int    $post_id   Post ID.
	 * @param int    $plan_id   Plan ID.
	 * @param string $post_type Post type.
	 * @return int|false Rule ID on success, false on failure.
	 */
	public static function add( int $post_id, int $plan_id, string $post_type ) {
		global $wpdb;

		$result = $wpdb->insert(
			Schema::rules_table(),
			[
				'post_id'    => $post_id,
				'plan_id'    => $plan_id,
				'post_type'  => $post_type,
				'created_at' => current_time( 'mysql' ),
			],
			[ '%d', '%d', '%s', '%s' ]
		);

		return $result ? (int) $wpdb->insert_id : false;
	}

	/**
	 * Remove rule.
	 *
	 * @param int $post_id Post ID.
	 * @param int $plan_id Plan ID.
	 * @return bool
	 */
	public static function remove( int $post_id, int $plan_id ): bool {
		global $wpdb;

		$result = $wpdb->delete(
			Schema::rules_table(),
			[
				'post_id' => $post_id,
				'plan_id' => $plan_id,
			],
			[ '%d', '%d' ]
		);

		return false !== $result;
	}

	/**
	 * Remove all rules for a post.
	 *
	 * @param int $post_id Post ID.
	 * @return bool
	 */
	public static function remove_all_by_post( int $post_id ): bool {
		global $wpdb;

		$result = $wpdb->delete(
			Schema::rules_table(),
			[ 'post_id' => $post_id ],
			[ '%d' ]
		);

		return false !== $result;
	}

	/**
	 * Remove all rules for a plan.
	 *
	 * @param int $plan_id Plan ID.
	 * @return bool
	 */
	public static function remove_all_by_plan( int $plan_id ): bool {
		global $wpdb;

		$result = $wpdb->delete(
			Schema::rules_table(),
			[ 'plan_id' => $plan_id ],
			[ '%d' ]
		);

		return false !== $result;
	}

	/**
	 * Sync rules for a post.
	 *
	 * @param int        $post_id  Post ID.
	 * @param array<int> $plan_ids Plan IDs.
	 * @param string     $post_type Post type.
	 * @return bool
	 */
	public static function sync( int $post_id, array $plan_ids, string $post_type ): bool {
		self::remove_all_by_post( $post_id );

		foreach ( $plan_ids as $plan_id ) {
			self::add( $post_id, (int) $plan_id, $post_type );
		}

		return true;
	}

	/**
	 * Sync rules for a plan (from plan editor).
	 *
	 * @param int        $plan_id  Plan ID.
	 * @param array<int> $post_ids Post IDs.
	 * @return bool
	 */
	public static function sync_by_plan( int $plan_id, array $post_ids ): bool {
		self::remove_all_by_plan( $plan_id );

		foreach ( $post_ids as $post_id ) {
			$post = get_post( (int) $post_id );
			if ( $post ) {
				self::add( $post->ID, $plan_id, $post->post_type );
			}
		}

		return true;
	}

	/**
	 * Check if post is restricted.
	 *
	 * @param int $post_id Post ID.
	 * @return bool
	 */
	public static function is_restricted( int $post_id ): bool {
		global $wpdb;

		$table = Schema::rules_table();
		$count = $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE post_id = %d", $post_id ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);

		return (int) $count > 0;
	}
}
