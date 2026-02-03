<?php
/**
 * Plans list table.
 *
 * @package LightweightPlugins\Memberships
 */

declare(strict_types=1);

namespace LightweightPlugins\Memberships\Admin\Plans;

use LightweightPlugins\Memberships\Database\PlanRepository;
use LightweightPlugins\Memberships\Database\MembershipRepository;

// Load WP_List_Table if not loaded.
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Plans list table.
 */
final class PlansListTable extends \WP_List_Table {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			[
				'singular' => 'plan',
				'plural'   => 'plans',
				'ajax'     => false,
			]
		);
	}

	/**
	 * Get columns.
	 *
	 * @return array<string, string>
	 */
	public function get_columns(): array {
		return [
			'cb'       => '<input type="checkbox">',
			'name'     => __( 'Name', 'lw-memberships' ),
			'slug'     => __( 'Slug', 'lw-memberships' ),
			'duration' => __( 'Duration', 'lw-memberships' ),
			'members'  => __( 'Members', 'lw-memberships' ),
			'priority' => __( 'Priority', 'lw-memberships' ),
			'status'   => __( 'Status', 'lw-memberships' ),
		];
	}

	/**
	 * Get sortable columns.
	 *
	 * @return array<string, array{0: string, 1: bool}>
	 */
	public function get_sortable_columns(): array {
		return [
			'name'     => [ 'name', false ],
			'priority' => [ 'priority', true ],
		];
	}

	/**
	 * Prepare items.
	 *
	 * @return void
	 */
	public function prepare_items(): void {
		$this->_column_headers = [
			$this->get_columns(),
			[],
			$this->get_sortable_columns(),
		];

		$this->items = PlanRepository::get_all();
	}

	/**
	 * Column default.
	 *
	 * @param object $item        Item.
	 * @param string $column_name Column name.
	 * @return string
	 */
	public function column_default( $item, $column_name ): string {
		return esc_html( $item->$column_name ?? '' );
	}

	/**
	 * Column checkbox.
	 *
	 * @param object $item Item.
	 * @return string
	 */
	public function column_cb( $item ): string {
		return sprintf( '<input type="checkbox" name="plan[]" value="%d">', $item->id );
	}

	/**
	 * Column name.
	 *
	 * @param object $item Item.
	 * @return string
	 */
	public function column_name( $item ): string {
		$edit_url   = admin_url( 'admin.php?page=' . PlansPage::SLUG . '&action=edit&id=' . $item->id );
		$delete_url = wp_nonce_url(
			admin_url( 'admin.php?page=' . PlansPage::SLUG . '&action=delete&id=' . $item->id ),
			'delete_plan_' . $item->id
		);

		$actions = [
			'edit'   => sprintf( '<a href="%s">%s</a>', esc_url( $edit_url ), __( 'Edit', 'lw-memberships' ) ),
			'delete' => sprintf(
				'<a href="%s" onclick="return confirm(\'%s\');">%s</a>',
				esc_url( $delete_url ),
				esc_js( __( 'Are you sure?', 'lw-memberships' ) ),
				__( 'Delete', 'lw-memberships' )
			),
		];

		return sprintf(
			'<strong><a href="%s">%s</a></strong>%s',
			esc_url( $edit_url ),
			esc_html( $item->name ),
			$this->row_actions( $actions )
		);
	}

	/**
	 * Column duration.
	 *
	 * @param object $item Item.
	 * @return string
	 */
	public function column_duration( $item ): string {
		if ( 'forever' === $item->duration_type ) {
			return esc_html__( 'Lifetime', 'lw-memberships' );
		}

		/* translators: %1$d: duration value, %2$s: duration type */
		return sprintf( esc_html__( '%1$d %2$s', 'lw-memberships' ), $item->duration_value, $item->duration_type );
	}

	/**
	 * Column members count.
	 *
	 * @param object $item Item.
	 * @return string
	 */
	public function column_members( $item ): string {
		return (string) MembershipRepository::count_by_plan( $item->id );
	}

	/**
	 * Column status.
	 *
	 * @param object $item Item.
	 * @return string
	 */
	public function column_status( $item ): string {
		$class = 'active' === $item->status ? 'lw-mship-status--active' : 'lw-mship-status--inactive';
		$label = 'active' === $item->status ? __( 'Active', 'lw-memberships' ) : __( 'Inactive', 'lw-memberships' );

		return sprintf( '<span class="lw-mship-status %s">%s</span>', esc_attr( $class ), esc_html( $label ) );
	}

	/**
	 * No items message.
	 *
	 * @return void
	 */
	public function no_items(): void {
		esc_html_e( 'No membership plans found.', 'lw-memberships' );
	}
}
