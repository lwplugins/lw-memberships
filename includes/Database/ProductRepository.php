<?php
/**
 * Product repository.
 *
 * @package LightweightPlugins\Memberships
 */

declare(strict_types=1);

namespace LightweightPlugins\Memberships\Database;

/**
 * Handles database operations for level-product associations.
 */
final class ProductRepository {

	/**
	 * Get products by level ID.
	 *
	 * @param int $level_id Level ID.
	 * @return array<int, object>
	 */
	public static function get_by_level( int $level_id ): array {
		global $wpdb;

		$table = Schema::level_products_table();

		return $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE level_id = %d", $level_id ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);
	}

	/**
	 * Get level IDs by product ID.
	 *
	 * @param int $product_id Product ID.
	 * @return array<int>
	 */
	public static function get_levels_by_product( int $product_id ): array {
		global $wpdb;

		$table = Schema::level_products_table();

		$results = $wpdb->get_col(
			$wpdb->prepare( "SELECT level_id FROM {$table} WHERE product_id = %d", $product_id ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);

		return array_map( 'intval', $results );
	}

	/**
	 * Add product to level.
	 *
	 * @param int    $level_id     Level ID.
	 * @param int    $product_id   Product ID.
	 * @param string $product_type Product type.
	 * @return int|false Association ID on success, false on failure.
	 */
	public static function add( int $level_id, int $product_id, string $product_type = 'simple' ) {
		global $wpdb;

		$result = $wpdb->insert(
			Schema::level_products_table(),
			[
				'level_id'     => $level_id,
				'product_id'   => $product_id,
				'product_type' => $product_type,
				'created_at'   => current_time( 'mysql' ),
			],
			[ '%d', '%d', '%s', '%s' ]
		);

		return $result ? (int) $wpdb->insert_id : false;
	}

	/**
	 * Remove product from level.
	 *
	 * @param int $level_id   Level ID.
	 * @param int $product_id Product ID.
	 * @return bool
	 */
	public static function remove( int $level_id, int $product_id ): bool {
		global $wpdb;

		$result = $wpdb->delete(
			Schema::level_products_table(),
			[
				'level_id'   => $level_id,
				'product_id' => $product_id,
			],
			[ '%d', '%d' ]
		);

		return false !== $result;
	}

	/**
	 * Remove all products from level.
	 *
	 * @param int $level_id Level ID.
	 * @return bool
	 */
	public static function remove_all_by_level( int $level_id ): bool {
		global $wpdb;

		$result = $wpdb->delete(
			Schema::level_products_table(),
			[ 'level_id' => $level_id ],
			[ '%d' ]
		);

		return false !== $result;
	}

	/**
	 * Sync products for a level.
	 *
	 * @param int        $level_id    Level ID.
	 * @param array<int> $product_ids Product IDs.
	 * @return bool
	 */
	public static function sync( int $level_id, array $product_ids ): bool {
		self::remove_all_by_level( $level_id );

		foreach ( $product_ids as $product_id ) {
			self::add( $level_id, (int) $product_id );
		}

		return true;
	}
}
