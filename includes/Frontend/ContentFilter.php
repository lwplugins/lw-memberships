<?php
/**
 * Content filter.
 *
 * @package LightweightPlugins\Memberships
 */

declare(strict_types=1);

namespace LightweightPlugins\Memberships\Frontend;

use LightweightPlugins\Memberships\Options;
use LightweightPlugins\Memberships\Services\AccessChecker;

/**
 * Filters content based on membership access.
 */
final class ContentFilter {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'the_content', [ $this, 'filter_content' ], 999 );
		add_filter( 'the_excerpt', [ $this, 'filter_excerpt' ], 999 );
	}

	/**
	 * Filter content.
	 *
	 * @param string $content Post content.
	 * @return string
	 */
	public function filter_content( string $content ): string {
		if ( ! $this->should_filter() ) {
			return $content;
		}

		$post_id = get_the_ID();

		if ( ! $post_id ) {
			return $content;
		}

		if ( AccessChecker::can_access( $post_id ) ) {
			return $content;
		}

		return $this->get_restriction_message( $post_id );
	}

	/**
	 * Filter excerpt.
	 *
	 * @param string $excerpt Post excerpt.
	 * @return string
	 */
	public function filter_excerpt( string $excerpt ): string {
		if ( ! $this->should_filter() ) {
			return $excerpt;
		}

		$post_id = get_the_ID();

		if ( ! $post_id ) {
			return $excerpt;
		}

		if ( AccessChecker::can_access( $post_id ) ) {
			return $excerpt;
		}

		if ( Options::get( 'show_excerpt_restricted' ) ) {
			return $excerpt;
		}

		return '';
	}

	/**
	 * Check if content should be filtered.
	 *
	 * @return bool
	 */
	private function should_filter(): bool {
		if ( is_admin() ) {
			return false;
		}

		// Only filter singular views. Archives/feeds handled by QueryFilter.
		if ( ! is_singular() ) {
			return false;
		}

		return true;
	}

	/**
	 * Get restriction message.
	 *
	 * @param int $post_id Post ID.
	 * @return string
	 */
	private function get_restriction_message( int $post_id ): string {
		$reason = AccessChecker::get_restriction_reason( $post_id );

		switch ( $reason ) {
			case 'not_logged_in':
				$template = 'restriction-not-logged-in.php';
				$message  = Options::get( 'not_logged_in_message' );
				break;

			case 'expired':
				$template = 'restriction-expired.php';
				$message  = Options::get( 'expired_message' );
				break;

			case 'paused':
				$template = 'restriction-paused.php';
				$message  = Options::get( 'paused_message' );
				break;

			default:
				$template = 'restriction-no-access.php';
				$message  = Options::get( 'restricted_message' );
				break;
		}

		return TemplateLoader::get(
			$template,
			[
				'post_id' => $post_id,
				'message' => $message,
				'reason'  => $reason,
			]
		);
	}
}
