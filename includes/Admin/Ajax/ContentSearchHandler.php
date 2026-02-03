<?php
/**
 * Content search AJAX handler.
 *
 * @package LightweightPlugins\Memberships
 */

declare(strict_types=1);

namespace LightweightPlugins\Memberships\Admin\Ajax;

/**
 * Handles AJAX post search for the Content tab.
 */
final class ContentSearchHandler {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_lw_mship_search_posts', [ $this, 'handle' ] );
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

		$posts = get_posts(
			[
				's'              => $search,
				'post_type'      => $this->get_searchable_types(),
				'post_status'    => 'publish',
				'posts_per_page' => 20,
				'orderby'        => 'title',
				'order'          => 'ASC',
			]
		);

		$results = array_map(
			static function ( \WP_Post $post ): array {
				return [
					'id'        => $post->ID,
					'title'     => $post->post_title,
					'post_type' => $post->post_type,
				];
			},
			$posts
		);

		wp_send_json_success( $results );
	}

	/**
	 * Get searchable post types.
	 *
	 * @return array<string>
	 */
	private function get_searchable_types(): array {
		$types = get_post_types( [ 'public' => true ], 'names' );
		unset( $types['attachment'] );
		return array_values( $types );
	}
}
