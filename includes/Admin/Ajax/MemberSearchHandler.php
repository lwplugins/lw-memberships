<?php
/**
 * Member search AJAX handler.
 *
 * @package LightweightPlugins\Memberships
 */

declare(strict_types=1);

namespace LightweightPlugins\Memberships\Admin\Ajax;

/**
 * Handles AJAX user search for the Members tab.
 */
final class MemberSearchHandler {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_lw_mship_search_users', [ $this, 'handle' ] );
	}

	/**
	 * Handle AJAX request.
	 *
	 * @return void
	 */
	public function handle(): void {
		check_ajax_referer( 'lw_mship_ajax', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Permission denied.' );
		}

		$search = sanitize_text_field( wp_unslash( $_GET['search'] ?? '' ) );

		if ( strlen( $search ) < 2 ) {
			wp_send_json_success( [] );
		}

		$users = get_users(
			[
				'search'         => '*' . $search . '*',
				'search_columns' => [ 'user_login', 'user_email', 'display_name' ],
				'number'         => 20,
				'orderby'        => 'display_name',
				'order'          => 'ASC',
			]
		);

		$results = array_map(
			static function ( \WP_User $user ): array {
				return [
					'id'    => $user->ID,
					'name'  => $user->display_name,
					'email' => $user->user_email,
				];
			},
			$users
		);

		wp_send_json_success( $results );
	}
}
