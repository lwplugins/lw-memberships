<?php
/**
 * Levels list table.
 *
 * @package LightweightPlugins\Memberships
 */

declare(strict_types=1);

namespace LightweightPlugins\Memberships\Admin\Levels;

use LightweightPlugins\Memberships\Database\LevelRepository;

// Load WP_List_Table if not loaded.
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Levels list table.
 */
final class LevelsListTable extends \WP_List_Table {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			[
				'singular' => 'level',
				'plural'   => 'levels',
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

		$this->items = LevelRepository::get_all();
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
		return sprintf( '<input type="checkbox" name="level[]" value="%d">', $item->id );
	}

	/**
	 * Column name.
	 *
	 * @param object $item Item.
	 * @return string
	 */
	public function column_name( $item ): string {
		$edit_url   = admin_url( 'admin.php?page=' . LevelsPage::SLUG . '&action=edit&id=' . $item->id );
		$delete_url = wp_nonce_url(
			admin_url( 'admin.php?page=' . LevelsPage::SLUG . '&action=delete&id=' . $item->id ),
			'delete_level_' . $item->id
		);

		$actions = [
			'edit'   => sprintf( '<a href="%s">%s</a>', esc_url( $edit_url ), __( 'Edit', 'lw-memberships' ) ),
			'delete' => sprintf( '<a href="%s" onclick="return confirm(\'%s\');">%s</a>', esc_url( $delete_url ), esc_js( __( 'Are you sure?', 'lw-memberships' ) ), __( 'Delete', 'lw-memberships' ) ),
		];

		return sprintf( '<strong><a href="%s">%s</a></strong>%s', esc_url( $edit_url ), esc_html( $item->name ), $this->row_actions( $actions ) );
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
	 * Column status.
	 *
	 * @param object $item Item.
	 * @return string
	 */
	public function column_status( $item ): string {
		$style = 'active' === $item->status ? 'background: #00a32a; color: #fff;' : 'background: #dba617; color: #fff;';
		$label = 'active' === $item->status ? __( 'Active', 'lw-memberships' ) : __( 'Inactive', 'lw-memberships' );

		return sprintf( '<span style="padding: 2px 6px; border-radius: 3px; font-size: 11px; %s">%s</span>', $style, esc_html( $label ) );
	}

	/**
	 * No items message.
	 *
	 * @return void
	 */
	public function no_items(): void {
		esc_html_e( 'No membership levels found.', 'lw-memberships' );
	}
}
