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
	 * Get level IDs by post ID.
	 *
	 * @param int $post_id Post ID.
	 * @return array<int>
	 */
	public static function get_level_ids_by_post( int $post_id ): array {
		global $wpdb;

		$table   = Schema::rules_table();
		$results = $wpdb->get_col(
			$wpdb->prepare( "SELECT level_id FROM {$table} WHERE post_id = %d", $post_id ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);

		return array_map( 'intval', $results );
	}

	/**
	 * Get rules by level ID.
	 *
	 * @param int $level_id Level ID.
	 * @return array<int, ContentRule>
	 */
	public static function get_by_level( int $level_id ): array {
		global $wpdb;

		$table   = Schema::rules_table();
		$results = $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE level_id = %d", $level_id ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);

		return array_map( [ ContentRule::class, 'from_row' ], $results );
	}

	/**
	 * Add rule.
	 *
	 * @param int    $post_id   Post ID.
	 * @param int    $level_id  Level ID.
	 * @param string $post_type Post type.
	 * @return int|false Rule ID on success, false on failure.
	 */
	public static function add( int $post_id, int $level_id, string $post_type ) {
		global $wpdb;

		$result = $wpdb->insert(
			Schema::rules_table(),
			[
				'post_id'    => $post_id,
				'level_id'   => $level_id,
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
	 * @param int $post_id  Post ID.
	 * @param int $level_id Level ID.
	 * @return bool
	 */
	public static function remove( int $post_id, int $level_id ): bool {
		global $wpdb;

		$result = $wpdb->delete(
			Schema::rules_table(),
			[
				'post_id'  => $post_id,
				'level_id' => $level_id,
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
	 * Sync rules for a post.
	 *
	 * @param int        $post_id   Post ID.
	 * @param array<int> $level_ids Level IDs.
	 * @param string     $post_type Post type.
	 * @return bool
	 */
	public static function sync( int $post_id, array $level_ids, string $post_type ): bool {
		self::remove_all_by_post( $post_id );

		foreach ( $level_ids as $level_id ) {
			self::add( $post_id, (int) $level_id, $post_type );
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
