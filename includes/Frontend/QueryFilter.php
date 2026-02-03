<?php
/**
 * WP_Query content filtering.
 *
 * @package LightweightPlugins\Memberships
 */

declare(strict_types=1);

namespace LightweightPlugins\Memberships\Frontend;

use LightweightPlugins\Memberships\Database\Schema;

/**
 * Filters WP_Query to hide restricted content from archives and feeds.
 */
final class QueryFilter {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'posts_join', [ $this, 'filter_join' ], 10, 2 );
		add_filter( 'posts_where', [ $this, 'filter_where' ], 10, 2 );
		add_filter( 'posts_groupby', [ $this, 'filter_groupby' ], 10, 2 );
	}

	/**
	 * Check if query should be filtered.
	 *
	 * @param \WP_Query $query Query object.
	 * @return bool
	 */
	private function should_filter( \WP_Query $query ): bool {
		if ( is_admin() ) {
			return false;
		}

		if ( $query->is_singular() ) {
			return false;
		}

		if ( current_user_can( 'manage_options' ) ) {
			return false;
		}

		if ( ! $query->is_main_query() && ! $query->get( 'lw_mship_filter' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Filter JOIN clause.
	 *
	 * @param string    $join  JOIN clause.
	 * @param \WP_Query $query Query object.
	 * @return string
	 */
	public function filter_join( string $join, \WP_Query $query ): string {
		if ( ! $this->should_filter( $query ) ) {
			return $join;
		}

		$rules_table = Schema::rules_table();
		$join       .= " LEFT JOIN {$rules_table} AS lw_rules ON ({$GLOBALS['wpdb']->posts}.ID = lw_rules.post_id)";

		$user_id = get_current_user_id();

		if ( $user_id > 0 ) {
			$memberships_table = Schema::memberships_table();
			$join             .= $GLOBALS['wpdb']->prepare(
				" LEFT JOIN {$memberships_table} AS lw_mem ON ("
				. 'lw_rules.plan_id = lw_mem.plan_id'
				. ' AND lw_mem.user_id = %d'
				. " AND lw_mem.status = 'active'"
				. ' AND (lw_mem.end_date IS NULL OR lw_mem.end_date > %s)'
				. ')',
				$user_id,
				current_time( 'mysql' )
			);
		}

		return $join;
	}

	/**
	 * Filter WHERE clause.
	 *
	 * @param string    $where WHERE clause.
	 * @param \WP_Query $query Query object.
	 * @return string
	 */
	public function filter_where( string $where, \WP_Query $query ): string {
		if ( ! $this->should_filter( $query ) ) {
			return $where;
		}

		$user_id = get_current_user_id();

		if ( $user_id > 0 ) {
			$where .= ' AND (lw_rules.post_id IS NULL OR lw_mem.id IS NOT NULL)';
		} else {
			$where .= ' AND lw_rules.post_id IS NULL';
		}

		return $where;
	}

	/**
	 * Filter GROUP BY clause.
	 *
	 * @param string    $groupby GROUP BY clause.
	 * @param \WP_Query $query   Query object.
	 * @return string
	 */
	public function filter_groupby( string $groupby, \WP_Query $query ): string {
		if ( ! $this->should_filter( $query ) ) {
			return $groupby;
		}

		global $wpdb;
		$column = "{$wpdb->posts}.ID";

		if ( empty( $groupby ) || false === strpos( $groupby, $column ) ) {
			$groupby = $column;
		}

		return $groupby;
	}
}
