<?php
/**
 * Members save handler.
 *
 * @package LightweightPlugins\Memberships
 */

declare(strict_types=1);

namespace LightweightPlugins\Memberships\Admin\Plans;

use LightweightPlugins\Memberships\Database\MembershipRepository;
use LightweightPlugins\Memberships\Services\MembershipGranter;

/**
 * Handles add/remove member actions.
 */
final class MembersSaveHandler {

	/**
	 * Handle member actions.
	 *
	 * @return void
	 */
	public static function handle(): void {
		self::handle_add();
		self::handle_remove();
	}

	/**
	 * Handle add member.
	 *
	 * @return void
	 */
	private static function handle_add(): void {
		if ( empty( $_POST['lw_mship_add_member'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_key( $_POST['lw_mship_nonce'] ?? '' ), 'save_plan' ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$plan_id = absint( $_POST['plan_id'] ?? 0 );
		$user_id = absint( $_POST['add_member_user_id'] ?? 0 );

		if ( ! $plan_id || ! $user_id ) {
			return;
		}

		$user = get_user_by( 'id', $user_id );

		if ( ! $user ) {
			return;
		}

		MembershipGranter::grant( $user_id, $plan_id, 'manual' );

		$redirect = add_query_arg(
			[
				'page'    => PlansPage::SLUG,
				'action'  => 'edit',
				'id'      => $plan_id,
				'tab'     => 'members',
				'message' => 'member_added',
			],
			admin_url( 'admin.php' )
		);

		wp_safe_redirect( $redirect );
		exit;
	}

	/**
	 * Handle remove member.
	 *
	 * @return void
	 */
	private static function handle_remove(): void {
		if ( empty( $_GET['remove_member'] ) ) {
			return;
		}

		$membership_id = absint( $_GET['remove_member'] );

		if ( ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ?? '' ), 'remove_member_' . $membership_id ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$membership = MembershipRepository::get_by_id( $membership_id );

		if ( ! $membership ) {
			return;
		}

		MembershipRepository::update(
			$membership_id,
			[
				'status'       => 'cancelled',
				'cancelled_at' => current_time( 'mysql' ),
			]
		);

		$redirect = add_query_arg(
			[
				'page'    => PlansPage::SLUG,
				'action'  => 'edit',
				'id'      => $membership->plan_id,
				'tab'     => 'members',
				'message' => 'member_removed',
			],
			admin_url( 'admin.php' )
		);

		wp_safe_redirect( $redirect );
		exit;
	}
}
