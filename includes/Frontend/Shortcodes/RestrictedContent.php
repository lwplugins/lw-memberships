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
				'plan'    => '',
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

		// Check for specific plan(s).
		if ( ! empty( $atts['plan'] ) ) {
			$plan_ids = $this->parse_plans( $atts['plan'] );

			if ( ! $this->user_has_any_plan( $user_id, $plan_ids ) ) {
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
	 * Parse plan IDs from attribute.
	 *
	 * @param string $plan Plan attribute (ID or comma-separated IDs).
	 * @return array<int>
	 */
	private function parse_plans( string $plan ): array {
		$ids = array_map( 'trim', explode( ',', $plan ) );
		return array_map( 'absint', $ids );
	}

	/**
	 * Check if user has any of the specified plans.
	 *
	 * @param int        $user_id  User ID.
	 * @param array<int> $plan_ids Plan IDs.
	 * @return bool
	 */
	private function user_has_any_plan( int $user_id, array $plan_ids ): bool {
		foreach ( $plan_ids as $plan_id ) {
			if ( MembershipRepository::user_has_plan( $user_id, $plan_id ) ) {
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
