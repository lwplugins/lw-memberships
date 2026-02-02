<?php
/**
 * Restricted content shortcode.
 *
 * @package LightweightPlugins\Memberships
 */

declare(strict_types=1);

namespace LightweightPlugins\Memberships\Frontend\Shortcodes;

use LightweightPlugins\Memberships\Database\MembershipRepository;
use LightweightPlugins\Memberships\Options;

/**
 * [lw_mship_restricted] shortcode.
 */
final class RestrictedContent {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_shortcode( 'lw_mship_restricted', [ $this, 'render' ] );
	}

	/**
	 * Render shortcode.
	 *
	 * @param array<string,string>|string $atts    Shortcode attributes.
	 * @param string|null                 $content Shortcode content.
	 * @return string
	 */
	public function render( $atts, ?string $content = null ): string {
		$atts = shortcode_atts(
			[
				'level'   => '',
				'message' => '',
			],
			$atts,
			'lw_mship_restricted'
		);

		if ( empty( $content ) ) {
			return '';
		}

		$user_id = get_current_user_id();

		// Check if user is logged in.
		if ( 0 === $user_id ) {
			return $this->get_message( $atts['message'], 'not_logged_in' );
		}

		// Check for specific level(s).
		if ( ! empty( $atts['level'] ) ) {
			$level_ids = $this->parse_levels( $atts['level'] );

			if ( ! $this->user_has_any_level( $user_id, $level_ids ) ) {
				return $this->get_message( $atts['message'], 'no_access' );
			}
		} else {
			// Any active membership.
			$memberships = MembershipRepository::get_by_user( $user_id, true );

			if ( empty( $memberships ) ) {
				return $this->get_message( $atts['message'], 'no_access' );
			}
		}

		return do_shortcode( $content );
	}

	/**
	 * Parse level IDs from attribute.
	 *
	 * @param string $level Level attribute (ID or comma-separated IDs).
	 * @return array<int>
	 */
	private function parse_levels( string $level ): array {
		$ids = array_map( 'trim', explode( ',', $level ) );
		return array_map( 'absint', $ids );
	}

	/**
	 * Check if user has any of the specified levels.
	 *
	 * @param int        $user_id   User ID.
	 * @param array<int> $level_ids Level IDs.
	 * @return bool
	 */
	private function user_has_any_level( int $user_id, array $level_ids ): bool {
		foreach ( $level_ids as $level_id ) {
			if ( MembershipRepository::user_has_level( $user_id, $level_id ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Get restriction message.
	 *
	 * @param string $custom_message Custom message.
	 * @param string $reason         Restriction reason.
	 * @return string
	 */
	private function get_message( string $custom_message, string $reason ): string {
		if ( ! empty( $custom_message ) ) {
			return '<div class="lw-mship-restricted-message">' . esc_html( $custom_message ) . '</div>';
		}

		$message = 'not_logged_in' === $reason
			? Options::get( 'not_logged_in_message' )
			: Options::get( 'restricted_message' );

		return '<div class="lw-mship-restricted-message">' . esc_html( $message ) . '</div>';
	}
}
