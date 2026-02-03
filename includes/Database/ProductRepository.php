<?php
/**
 * Product repository.
 *
 * @package LightweightPlugins\Memberships
 */

declare(strict_types=1);

namespace LightweightPlugins\Memberships\Database;

/**
 * Handles database operations for plan-product associations.
 */
final class ProductRepository {

	/**
	 * Get products by plan ID.
	 *
	 * @param int $plan_id Plan ID.
	 * @return array<int, object>
	 */
	public static function get_by_plan( int $plan_id ): array {
		global $wpdb;

		$table = Schema::plan_products_table();

		return $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE plan_id = %d", $plan_id ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);
	}

	/**
	 * Get plan IDs by product ID.
	 *
	 * @param int $product_id Product ID.
	 * @return array<int>
	 */
	public static function get_plans_by_product( int $product_id ): array {
		global $wpdb;

		$table = Schema::plan_products_table();

		$results = $wpdb->get_col(
			$wpdb->prepare( "SELECT plan_id FROM {$table} WHERE product_id = %d", $product_id ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);

		return array_map( 'intval', $results );
	}

	/**
	 * Add product to plan.
	 *
	 * @param int    $plan_id      Plan ID.
	 * @param int    $product_id   Product ID.
	 * @param string $product_type Product type.
	 * @return int|false Association ID on success, false on failure.
	 */
	public static function add( int $plan_id, int $product_id, string $product_type = 'simple' ) {
		global $wpdb;

		$result = $wpdb->insert(
			Schema::plan_products_table(),
			[
				'plan_id'      => $plan_id,
				'product_id'   => $product_id,
				'product_type' => $product_type,
				'created_at'   => current_time( 'mysql' ),
			],
			[ '%d', '%d', '%s', '%s' ]
		);

		return $result ? (int) $wpdb->insert_id : false;
	}

	/**
	 * Remove product from plan.
	 *
	 * @param int $plan_id    Plan ID.
	 * @param int $product_id Product ID.
	 * @return bool
	 */
	public static function remove( int $plan_id, int $product_id ): bool {
		global $wpdb;

		$result = $wpdb->delete(
			Schema::plan_products_table(),
			[
				'plan_id'    => $plan_id,
				'product_id' => $product_id,
			],
			[ '%d', '%d' ]
		);

		return false !== $result;
	}

	/**
	 * Remove all products from plan.
	 *
	 * @param int $plan_id Plan ID.
	 * @return bool
	 */
	public static function remove_all_by_plan( int $plan_id ): bool {
		global $wpdb;

		$result = $wpdb->delete(
			Schema::plan_products_table(),
			[ 'plan_id' => $plan_id ],
			[ '%d' ]
		);

		return false !== $result;
	}

	/**
	 * Sync products for a plan.
	 *
	 * @param int        $plan_id     Plan ID.
	 * @param array<int> $product_ids Product IDs.
	 * @return bool
	 */
	public static function sync( int $plan_id, array $product_ids ): bool {
		self::remove_all_by_plan( $plan_id );

		foreach ( $product_ids as $product_id ) {
			self::add( $plan_id, (int) $product_id );
		}

		return true;
	}
}
