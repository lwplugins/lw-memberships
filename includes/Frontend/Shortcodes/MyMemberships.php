<?php
/**
 * My memberships shortcode.
 *
 * @package LightweightPlugins\Memberships
 */

declare(strict_types=1);

namespace LightweightPlugins\Memberships\Frontend\Shortcodes;

use LightweightPlugins\Memberships\Database\LevelRepository;
use LightweightPlugins\Memberships\Database\MembershipRepository;
use LightweightPlugins\Memberships\Frontend\TemplateLoader;

/**
 * [lw_mship_memberships] shortcode.
 */
final class MyMemberships {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_shortcode( 'lw_mship_memberships', [ $this, 'render' ] );
	}

	/**
	 * Render shortcode.
	 *
	 * @param array<string,string>|string $atts Shortcode attributes.
	 * @return string
	 */
	public function render( $atts ): string {
		$atts = shortcode_atts(
			[
				'show_expired' => 'no',
			],
			$atts,
			'lw_mship_memberships'
		);

		$user_id = get_current_user_id();

		if ( 0 === $user_id ) {
			return $this->render_login_prompt();
		}

		$active_only  = 'yes' !== $atts['show_expired'];
		$memberships  = MembershipRepository::get_by_user( $user_id, $active_only );
		$levels_cache = [];

		// Enrich memberships with level data.
		foreach ( $memberships as $membership ) {
			if ( ! isset( $levels_cache[ $membership->level_id ] ) ) {
				$levels_cache[ $membership->level_id ] = LevelRepository::get_by_id( $membership->level_id );
			}
		}

		return TemplateLoader::get(
			'my-memberships.php',
			[
				'memberships' => $memberships,
				'levels'      => $levels_cache,
				'user_id'     => $user_id,
			]
		);
	}

	/**
	 * Render login prompt.
	 *
	 * @return string
	 */
	private function render_login_prompt(): string {
		$login_url = wp_login_url( get_permalink() );

		return sprintf(
			'<p class="lw-mship-login-prompt">%s <a href="%s">%s</a></p>',
			esc_html__( 'Please log in to view your memberships.', 'lw-memberships' ),
			esc_url( $login_url ),
			esc_html__( 'Log In', 'lw-memberships' )
		);
	}
}
