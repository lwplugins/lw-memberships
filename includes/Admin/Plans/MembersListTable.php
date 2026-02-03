<?php
/**
 * Members list table for plan editor.
 *
 * @package LightweightPlugins\Memberships
 */

declare(strict_types=1);

namespace LightweightPlugins\Memberships\Admin\Plans;

use LightweightPlugins\Memberships\Database\MembershipRepository;

// Load WP_List_Table if not loaded.
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Members list table for a specific plan.
 */
final class MembersListTable extends \WP_List_Table {

	/**
	 * Plan ID.
	 *
	 * @var int
	 */
	private int $plan_id;

	/**
	 * Constructor.
	 *
	 * @param int $plan_id Plan ID.
	 */
	public function __construct( int $plan_id ) {
		$this->plan_id = $plan_id;

		parent::__construct(
			[
				'singular' => 'member',
				'plural'   => 'members',
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
			'user'       => __( 'User', 'lw-memberships' ),
			'email'      => __( 'Email', 'lw-memberships' ),
			'status'     => __( 'Status', 'lw-memberships' ),
			'start_date' => __( 'Start Date', 'lw-memberships' ),
			'end_date'   => __( 'End Date', 'lw-memberships' ),
			'actions'    => __( 'Actions', 'lw-memberships' ),
		];
	}

	/**
	 * Prepare items.
	 *
	 * @return void
	 */
	public function prepare_items(): void {
		$per_page = 20;
		$page     = $this->get_pagenum();
		$total    = MembershipRepository::count_by_plan( $this->plan_id );

		$this->_column_headers = [ $this->get_columns(), [], [] ];
		$this->items           = MembershipRepository::get_by_plan( $this->plan_id, $page, $per_page );

		$this->set_pagination_args(
			[
				'total_items' => $total,
				'per_page'    => $per_page,
				'total_pages' => (int) ceil( $total / $per_page ),
			]
		);
	}

	/**
	 * Column default.
	 *
	 * @param object $item        Item.
	 * @param string $column_name Column name.
	 * @return string
	 */
	public function column_default( $item, $column_name ): string {
		return '';
	}

	/**
	 * Column user.
	 *
	 * @param object $item Item.
	 * @return string
	 */
	public function column_user( $item ): string {
		$user = get_user_by( 'id', $item->user_id );

		if ( ! $user ) {
			/* translators: %d: user ID */
			return sprintf( esc_html__( 'User #%d (deleted)', 'lw-memberships' ), $item->user_id );
		}

		return esc_html( $user->display_name );
	}

	/**
	 * Column email.
	 *
	 * @param object $item Item.
	 * @return string
	 */
	public function column_email( $item ): string {
		$user = get_user_by( 'id', $item->user_id );
		return $user ? esc_html( $user->user_email ) : '';
	}

	/**
	 * Column status.
	 *
	 * @param object $item Item.
	 * @return string
	 */
	public function column_status( $item ): string {
		$class = 'lw-mship-status--' . $item->status;
		return sprintf(
			'<span class="lw-mship-status %s">%s</span>',
			esc_attr( $class ),
			esc_html( ucfirst( $item->status ) )
		);
	}

	/**
	 * Column start date.
	 *
	 * @param object $item Item.
	 * @return string
	 */
	public function column_start_date( $item ): string {
		return esc_html( wp_date( get_option( 'date_format' ), strtotime( $item->start_date ) ) );
	}

	/**
	 * Column end date.
	 *
	 * @param object $item Item.
	 * @return string
	 */
	public function column_end_date( $item ): string {
		if ( ! $item->end_date ) {
			return esc_html__( 'Lifetime', 'lw-memberships' );
		}

		return esc_html( wp_date( get_option( 'date_format' ), strtotime( $item->end_date ) ) );
	}

	/**
	 * Column actions.
	 *
	 * @param object $item Item.
	 * @return string
	 */
	public function column_actions( $item ): string {
		$remove_url = wp_nonce_url(
			add_query_arg(
				[
					'page'              => PlansPage::SLUG,
					'action'            => 'edit',
					'id'                => $this->plan_id,
					'tab'               => 'members',
					'remove_member'     => $item->id,
				],
				admin_url( 'admin.php' )
			),
			'remove_member_' . $item->id
		);

		return sprintf(
			'<a href="%s" class="button button-small" onclick="return confirm(\'%s\');">%s</a>',
			esc_url( $remove_url ),
			esc_js( __( 'Remove this member?', 'lw-memberships' ) ),
			esc_html__( 'Remove', 'lw-memberships' )
		);
	}

	/**
	 * No items message.
	 *
	 * @return void
	 */
	public function no_items(): void {
		esc_html_e( 'No members found for this plan.', 'lw-memberships' );
	}
}
