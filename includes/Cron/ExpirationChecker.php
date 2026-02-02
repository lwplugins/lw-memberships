<?php
/**
 * Expiration checker cron job.
 *
 * @package LightweightPlugins\Memberships
 */

declare(strict_types=1);

namespace LightweightPlugins\Memberships\Cron;

use LightweightPlugins\Memberships\Database\MembershipRepository;
use LightweightPlugins\Memberships\Options;
use LightweightPlugins\Memberships\Services\ExpirationHandler;

/**
 * Checks for expired memberships.
 */
final class ExpirationChecker {

	/**
	 * Run the expiration check.
	 *
	 * @return void
	 */
	public static function run(): void {
		if ( ! Options::get( 'check_expiration_daily' ) ) {
			return;
		}

		$expired = MembershipRepository::get_expired();

		foreach ( $expired as $membership ) {
			ExpirationHandler::expire( $membership->id );
		}
	}
}
