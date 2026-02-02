<?php
/**
 * Meta box save handler.
 *
 * @package LightweightPlugins\Memberships
 */

declare(strict_types=1);

namespace LightweightPlugins\Memberships\Admin\MetaBox;

use LightweightPlugins\Memberships\Database\RuleRepository;

/**
 * Handles saving meta box data.
 */
final class MetaBoxSaveHandler {

	/**
	 * Save meta box data.
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post    Post object.
	 * @return void
	 */
	public static function save( int $post_id, \WP_Post $post ): void {
		// Verify nonce.
		if ( ! isset( $_POST['lw_mship_restriction_nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_key( $_POST['lw_mship_restriction_nonce'] ), 'lw_mship_restriction' ) ) {
			return;
		}

		// Check autosave.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check permissions.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Get selected levels.
		$level_ids = [];

		if ( isset( $_POST['lw_mship_levels'] ) && is_array( $_POST['lw_mship_levels'] ) ) {
			$level_ids = array_map( 'absint', $_POST['lw_mship_levels'] );
			$level_ids = array_filter( $level_ids );
		}

		// Sync rules.
		RuleRepository::sync( $post_id, $level_ids, $post->post_type );
	}
}
