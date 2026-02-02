<?php
/**
 * Level save handler.
 *
 * @package LightweightPlugins\Memberships
 */

declare(strict_types=1);

namespace LightweightPlugins\Memberships\Admin\Levels;

use LightweightPlugins\Memberships\Database\LevelRepository;
use LightweightPlugins\Memberships\Database\ProductRepository;

/**
 * Handles level save operations.
 */
final class LevelSaveHandler {

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
		if ( empty( $_POST['lw_mship_action'] ) || 'save_level' !== $_POST['lw_mship_action'] ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_key( $_POST['lw_mship_nonce'] ?? '' ), 'save_level' ) ) {
			wp_die( esc_html__( 'Invalid nonce.', 'lw-memberships' ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'lw-memberships' ) );
		}

		$level_id = absint( $_POST['level_id'] ?? 0 );
		$data     = self::sanitize_input();

		if ( $level_id > 0 ) {
			LevelRepository::update( $level_id, $data );
		} else {
			$level_id = LevelRepository::create( $data );
		}

		// Sync products if WooCommerce is active.
		if ( $level_id && class_exists( 'WooCommerce' ) ) {
			$products = isset( $_POST['products'] ) ? array_map( 'absint', (array) $_POST['products'] ) : [];
			ProductRepository::sync( $level_id, $products );
		}

		wp_safe_redirect( admin_url( 'admin.php?page=' . LevelsPage::SLUG . '&message=saved' ) );
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

		$level_id = absint( $_GET['id'] ?? 0 );

		if ( ! $level_id ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ?? '' ), 'delete_level_' . $level_id ) ) {
			wp_die( esc_html__( 'Invalid nonce.', 'lw-memberships' ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'lw-memberships' ) );
		}

		LevelRepository::delete( $level_id );
		ProductRepository::remove_all_by_level( $level_id );

		wp_safe_redirect( admin_url( 'admin.php?page=' . LevelsPage::SLUG . '&message=deleted' ) );
		exit;
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
