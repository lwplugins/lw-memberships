<?php
/**
 * Plan save handler.
 *
 * @package LightweightPlugins\Memberships
 */

declare(strict_types=1);

namespace LightweightPlugins\Memberships\Admin\Plans;

use LightweightPlugins\Memberships\Database\PlanRepository;
use LightweightPlugins\Memberships\Database\ProductRepository;
use LightweightPlugins\Memberships\Database\RuleRepository;

/**
 * Handles plan save operations.
 */
final class PlanSaveHandler {

	/**
	 * Handle save/delete actions.
	 *
	 * @return void
	 */
	public static function handle(): void {
		self::handle_save();
		self::handle_delete();
	}

	/**
	 * Handle save action.
	 *
	 * @return void
	 */
	private static function handle_save(): void {
		if ( empty( $_POST['lw_mship_action'] ) || 'save_plan' !== $_POST['lw_mship_action'] ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_key( $_POST['lw_mship_nonce'] ?? '' ), 'save_plan' ) ) {
			wp_die( esc_html__( 'Invalid nonce.', 'lw-memberships' ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'lw-memberships' ) );
		}

		$plan_id    = absint( $_POST['plan_id'] ?? 0 );
		$active_tab = sanitize_key( $_POST['active_tab'] ?? 'general' );
		$data       = self::sanitize_input();

		if ( $plan_id > 0 ) {
			PlanRepository::update( $plan_id, $data );
		} else {
			$plan_id = PlanRepository::create( $data );
		}

		if ( $plan_id ) {
			self::save_products( $plan_id );
			self::save_content_rules( $plan_id );
		}

		$redirect = add_query_arg(
			[
				'page'    => PlansPage::SLUG,
				'action'  => 'edit',
				'id'      => $plan_id,
				'tab'     => $active_tab,
				'message' => 'saved',
			],
			admin_url( 'admin.php' )
		);

		wp_safe_redirect( $redirect );
		exit;
	}

	/**
	 * Handle delete action.
	 *
	 * @return void
	 */
	private static function handle_delete(): void {
		if ( empty( $_GET['action'] ) || 'delete' !== $_GET['action'] ) {
			return;
		}

		$plan_id = absint( $_GET['id'] ?? 0 );

		if ( ! $plan_id ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ?? '' ), 'delete_plan_' . $plan_id ) ) {
			wp_die( esc_html__( 'Invalid nonce.', 'lw-memberships' ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'lw-memberships' ) );
		}

		PlanRepository::delete( $plan_id );
		ProductRepository::remove_all_by_plan( $plan_id );
		RuleRepository::remove_all_by_plan( $plan_id );

		wp_safe_redirect( admin_url( 'admin.php?page=' . PlansPage::SLUG . '&message=deleted' ) );
		exit;
	}

	/**
	 * Save product associations.
	 *
	 * @param int $plan_id Plan ID.
	 * @return void
	 */
	private static function save_products( int $plan_id ): void {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		if ( ! isset( $_POST['products'] ) ) {
			return;
		}

		$products = array_map( 'absint', (array) $_POST['products'] );
		ProductRepository::sync( $plan_id, $products );
	}

	/**
	 * Save content rules.
	 *
	 * @param int $plan_id Plan ID.
	 * @return void
	 */
	private static function save_content_rules( int $plan_id ): void {
		if ( ! isset( $_POST['content_post_ids'] ) ) {
			return;
		}

		$post_ids = array_map( 'absint', (array) $_POST['content_post_ids'] );
		$post_ids = array_filter( $post_ids );
		RuleRepository::sync_by_plan( $plan_id, $post_ids );
	}

	/**
	 * Sanitize input data.
	 *
	 * @return array<string, mixed>
	 */
	private static function sanitize_input(): array {
		$name = sanitize_text_field( $_POST['name'] ?? '' );
		$slug = sanitize_title( $_POST['slug'] ?? '' );

		if ( empty( $slug ) ) {
			$slug = sanitize_title( $name );
		}

		$duration_type  = sanitize_key( $_POST['duration_type'] ?? 'forever' );
		$duration_value = absint( $_POST['duration_value'] ?? 0 );

		if ( 'forever' === $duration_type ) {
			$duration_value = null;
		}

		return [
			'name'           => $name,
			'slug'           => $slug,
			'description'    => sanitize_textarea_field( $_POST['description'] ?? '' ),
			'duration_type'  => $duration_type,
			'duration_value' => $duration_value,
			'priority'       => absint( $_POST['priority'] ?? 0 ),
			'status'         => sanitize_key( $_POST['status'] ?? 'active' ),
		];
	}
}
